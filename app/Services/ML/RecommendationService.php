<?php

declare(strict_types=1);

namespace App\Services\ML;

use App\Models\AiRecommendation;
use App\Models\StudentFeatureSnapshot;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecommendationService
{
    public function __construct(
        private readonly PedagogicalRulesEvaluator $pedagogicalRules,
    ) {}

    // 🔗 FASE 6
    public function getForUser(User $user, bool $forceRefresh = false): ?AiRecommendation
    {
        $latest = AiRecommendation::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        $snapshotForCache = StudentFeatureSnapshot::query()
            ->where('user_id', $user->id)
            ->latest('snapshot_date')
            ->first();

        $snapshotStale = $latest !== null
            && $snapshotForCache !== null
            && $snapshotForCache->updated_at !== null
            && $latest->created_at !== null
            && $snapshotForCache->updated_at->gt($latest->created_at);

        $hours = (int) config('ml.cache_hours', 24);
        if (
            ! $forceRefresh
            && ! $snapshotStale
            && $latest !== null
            && $latest->created_at !== null
            && $latest->created_at->gt(now()->subHours($hours))
        ) {
            return $latest;
        }

        try {
            Artisan::call('features:extract', ['--user' => $user->id]);
        } catch (Throwable $e) {
            Log::warning('ML features:extract falló', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }

        $snapshot = StudentFeatureSnapshot::query()
            ->where('user_id', $user->id)
            ->latest('snapshot_date')
            ->first();

        if ($snapshot === null) {
            return $latest;
        }

        $payload = $this->payloadFromSnapshot($snapshot);
        $url = rtrim((string) config('ml.api_url'), '/').'/recommend';
        $started = microtime(true);

        try {
            $response = Http::timeout((int) config('ml.timeout', 3))
                ->acceptJson()
                ->post($url, ['features' => $payload]);

            $ms = (int) round((microtime(true) - $started) * 1000);

            if (! $response->successful()) {
                Log::warning('ML recommend HTTP', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'ms' => $ms,
                ]);

                return $latest;
            }

            $slugModel = $response->json('recommendation');
            $confidence = $response->json('confidence');
            if (! is_string($slugModel) || $slugModel === '') {
                Log::warning('ML recommend sin slug', [
                    'user_id' => $user->id,
                    'ms' => $ms,
                ]);

                return $latest;
            }

            $slugGuardrail = $this->pedagogicalRules->criticalReinforcement($payload);
            $slugFinal = $slugModel;
            $guardrailApplied = false;
            $modelConfidence = is_numeric($confidence) ? (float) $confidence : null;

            if (
                $this->pedagogicalRules->isAdvanceSlug($slugModel)
                && is_string($slugGuardrail)
                && str_starts_with($slugGuardrail, 'reforzar_')
            ) {
                $slugFinal = $slugGuardrail;
                $guardrailApplied = true;
                Log::info('[ML Guardrail Applied] Override model slug '.$slugModel.' to '.$slugGuardrail, [
                    'user_id' => $user->id,
                ]);
            }

            $finalConfidence = $guardrailApplied ? 0.95 : $modelConfidence;
            $modelVersion = $guardrailApplied
                ? 'v1-guardrail'
                : (string) config('ml.model_version', 'v1');

            Log::info('ML recommend OK', [
                'user_id' => $user->id,
                'ms' => $ms,
                'slug_model' => $slugModel,
                'slug_final' => $slugFinal,
                'guardrail_applied' => $guardrailApplied,
                'confidence' => $finalConfidence,
            ]);

            return AiRecommendation::query()->create([
                'user_id' => $user->id,
                'topic_id' => null,
                'recommended_content_id' => null,
                'reason' => $slugFinal,
                'confidence' => $finalConfidence,
                'model_version' => $modelVersion,
                'is_completed' => false,
            ]);
        } catch (Throwable $e) {
            Log::warning('ML recommend falló', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return $latest;
        }
    }

    public function healthCheck(): bool
    {
        try {
            $url = rtrim((string) config('ml.api_url'), '/').'/health';
            $response = Http::timeout(2)->acceptJson()->get($url);

            return $response->successful() && $response->json('status') === 'ok';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromSnapshot(StudentFeatureSnapshot $snapshot): array
    {
        $raw = $snapshot->features_json ?? [];
        $payload = [];
        foreach (config('ml.feature_keys', []) as $key) {
            $payload[$key] = array_key_exists($key, $raw) ? $raw[$key] : null;
        }

        foreach ([
            'accuracy_last_10',
            'cure_accuracy_last_10',
            'projectile_accuracy_last_10',
            'chart_race_accuracy_last_10',
            'crossword_accuracy_last_10',
            'questions_accuracy_easy_last_10',
            'questions_accuracy_medium_last_10',
            'questions_accuracy_hard_last_10',
            'has_failed_5_consecutive',
            'has_failed_5_of_10',
        ] as $key) {
            $payload[$key] = array_key_exists($key, $raw) ? $raw[$key] : null;
        }

        return $payload;
    }
}
