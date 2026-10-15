<?php

declare(strict_types=1);

namespace App\Services\Games;

use App\Enums\LearningGameType;
use App\Models\CureGameSession;
use App\Models\LearningLog;
use App\Models\Topic;
use App\Models\User;
use App\Support\Classroom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class CureSessionRecorder
{
    /**
     * @param  array<string, mixed>  $telemetry
     */
    public function record(User $user, array $telemetry): CureGameSession
    {
        return $this->upsert($user, $telemetry);
    }

    /**
     * @param  array<string, mixed>  $telemetry
     */
    public function upsert(User $user, array $telemetry): CureGameSession
    {
        $sessionId = $telemetry['sessionId'] ?? null;
        if (! is_string($sessionId) || $sessionId === '') {
            throw new InvalidArgumentException('telemetry.sessionId requerido');
        }
        
        $nivelesCompletados = (int) ($telemetry['nivelesCompletados'] ?? 0);
        if ($nivelesCompletados < 1) {
            throw new InvalidArgumentException('telemetry.nivelesCompletados debe ser >= 1');
        }

        $classroom = $this->resolveClassroom($user);
        $topicId = $this->resolveTopicId();
        $attributes = $this->mapSessionAttributes($user, $telemetry, $sessionId, $classroom);
        $logPayload = $this->mapLearningLogPayload($telemetry, $topicId);

        return DB::transaction(function () use ($user, $sessionId, $attributes, $logPayload) {
            $session = CureGameSession::query()->firstOrCreate(
                ['session_id' => $sessionId],
                array_merge($attributes, ['user_id' => $user->id])
            );

            $session->update($attributes);

            if ($session->learning_log_id) {
                LearningLog::query()
                    ->where('id', $session->learning_log_id)
                    ->update($logPayload);
            } else {
                $log = LearningLog::query()->create(array_merge(
                    ['user_id' => $user->id],
                    $logPayload
                ));
                $session->update(['learning_log_id' => $log->id]);
            }

            return $session->fresh();
        });
        
    }

    private function resolveClassroom(User $user): ?string
    {
        $classroomRaw = $user->studentProfile?->classroom;
        if (! $classroomRaw) {
            return null;
        }

        try {
            return Classroom::normalize((string) $classroomRaw);
        } catch (\InvalidArgumentException) {
            return (string) $classroomRaw;
        }
    }

    private function resolveTopicId(): int
    {
        $topic = Topic::query()->firstOrCreate(
            ['name' => config('cure.topic_name')],
            [
                'description' => 'MRU, MRUV, caída libre y lanzamiento',
                'is_active' => true,
            ]
        );

        return (int) $topic->id;
    }

    /**
     * @param  array<string, mixed>  $telemetry
     * @return array<string, mixed>
     */
    private function mapSessionAttributes(User $user, array $telemetry, string $sessionId, ?string $classroom): array
    {
        $attempts = (int) ($telemetry['attempts'] ?? 0);
        $hits = (int) ($telemetry['hits'] ?? 0);
        $misses = (int) ($telemetry['misses'] ?? max(0, $attempts - $hits));
        $accuracy = (float) ($telemetry['accuracy'] ?? 0);

        return [
            'user_id' => $user->id,
            'classroom' => $classroom,
            'session_id' => $sessionId,
            'level_reached' => (int) ($telemetry['levelReached'] ?? 1),
            'niveles_completados' => (int) ($telemetry['nivelesCompletados'] ?? 0),
            'attempts' => $attempts,
            'hits' => $hits,
            'misses' => $misses,
            'accuracy' => $accuracy,
            'score' => (int) ($telemetry['score'] ?? 0),
            'rank' => (string) ($telemetry['rank'] ?? ''),
            'max_streak' => (int) ($telemetry['maxStreak'] ?? 0),
            'won' => filter_var($telemetry['won'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'abandoned' => filter_var($telemetry['abandoned'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'pista_usadas' => (int) ($telemetry['pistaUsadas'] ?? 0),
            'total_time_sec' => (int) ($telemetry['totalTimeSec'] ?? 0),
            'formulas_mastered' => $telemetry['formulasMastered'] ?? [],
            'formulas_review' => $telemetry['formulasReview'] ?? [],
            'errors_by_type' => $telemetry['errorsByType'] ?? [],
            'levels_json' => $telemetry['levels'] ?? [],
            'telemetry_json' => $telemetry,
        ];
    }

    /**
     * @param  array<string, mixed>  $telemetry
     * @return array<string, mixed>
     */
    private function mapLearningLogPayload(array $telemetry, int $topicId): array
    {
        $attempts = (int) ($telemetry['attempts'] ?? 0);
        $hits = (int) ($telemetry['hits'] ?? 0);
        $accuracy = (float) ($telemetry['accuracy'] ?? 0);
        $finished = filter_var($telemetry['finished'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $abandoned = filter_var($telemetry['abandoned'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $calculator = app(CureCoinRewardCalculator::class);
        $earnedXp = $this->estimatedTotalXp($telemetry, $calculator);

        $payload = [
            'topic_id' => $topicId,
            'game_type' => LearningGameType::Cure->value,
            'total_time_seconds' => (int) ($telemetry['totalTimeSec'] ?? 0),
            'error_rate_percentage' => round(100 - $accuracy, 2),
            'earned_xp' => $earnedXp,
            'attempts' => $attempts,
            'correct_attempts' => $hits,
            
        ];

        if ($finished) {
            $payload['completed_at'] = now();
        }

        if (Schema::hasColumn('learning_logs', 'abandoned')) {
            $payload['abandoned'] = $abandoned;
        }

        return $payload;
    }

    /**
     * XP mostrada en learning log: niveles completados + bonus si victoria final.
     *
     * @param  array<string, mixed>  $telemetry
     */
    private function estimatedTotalXp(array $telemetry, CureCoinRewardCalculator $calculator): int
    {
        $levels = (int) ($telemetry['nivelesCompletados'] ?? 0);
        $perLevel = (int) config('cure.xp_per_level', 1);
        $xp = $levels * $perLevel;

        $abandoned = filter_var($telemetry['abandoned'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (! $abandoned && $calculator->countsAsWin($telemetry)) {
            $xp += (int) config('cure.xp_win_bonus', 5);
        }

        return $xp;
    }
}
