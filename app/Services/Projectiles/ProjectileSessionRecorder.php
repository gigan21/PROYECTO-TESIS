<?php

declare(strict_types=1);

namespace App\Services\Projectiles;

use App\Enums\LearningGameType;
use App\Models\LearningLog;
use App\Models\ProjectileGameSession;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectileSessionRecorder
{
    /**
     * Persiste sesión + LearningLog cuando finished === true (idempotente por session_id).
     */
    public function recordIfFinished(
        User $user,
        bool $finished,
        bool $isWin,
        int $level,
        int $score,
        ?array $telemetry
    ): bool {
        if (! $finished || ! is_array($telemetry)) {
            return false;
        }

        $sessionId = $telemetry['sessionId'] ?? null;
        if (! is_string($sessionId) || $sessionId === '') {
            Log::warning('ProjectileSessionRecorder: telemetry sin sessionId', ['user_id' => $user->id]);

            return false;
        }

        if (ProjectileGameSession::query()->where('session_id', $sessionId)->exists()) {
            return false;
        }

        $analysis = $telemetry['analysis'] ?? [];
        if (! is_array($analysis)) {
            $analysis = [];
        }

        $totalShots = (int) ($telemetry['totalShots'] ?? 0);
        $totalHits = (int) ($telemetry['totalHits'] ?? 0);
        $totalMisses = (int) ($telemetry['totalMisses'] ?? max(0, $totalShots - $totalHits));
        $accuracy = (float) ($telemetry['accuracy'] ?? 0);
        $totalTimeMs = (int) ($telemetry['totalTimeMs'] ?? 0);
        $levelReached = (int) ($telemetry['levelReached'] ?? $level);
        $result = $isWin ? 'win' : 'lose';

        $classroom = $user->studentProfile?->classroom;

        $topicId = Topic::query()
            ->where('name', config('projectiles.topic_name'))
            ->value('id');

        if ($topicId === null) {
            Log::warning('ProjectileSessionRecorder: topic no encontrado', [
                'topic_name' => config('projectiles.topic_name'),
            ]);
        }

        $xpPerHit = (int) config('projectiles.xp_per_hit');
        $earnedXp = $totalHits * $xpPerHit;
        $errorRate = $totalShots > 0
            ? round(100 - $accuracy, 2)
            : 0.0;

        DB::transaction(function () use (
            $user,
            $sessionId,
            $result,
            $levelReached,
            $score,
            $totalTimeMs,
            $accuracy,
            $totalShots,
            $totalHits,
            $totalMisses,
            $classroom,
            $telemetry,
            $analysis,
            $topicId,
            $earnedXp,
            $errorRate,
        ) {
            ProjectileGameSession::query()->create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => $result,
                'level_reached' => $levelReached,
                'score' => $score,
                'total_time_seconds' => (int) round($totalTimeMs / 1000),
                'accuracy' => $accuracy,
                'total_shots' => $totalShots,
                'total_hits' => $totalHits,
                'total_misses' => $totalMisses,
                'classroom' => $classroom,
                'telemetry_json' => $telemetry,
                'analysis_text' => $analysis,
            ]);

            LearningLog::query()->create([
                'user_id' => $user->id,
                'topic_id' => $topicId,
                'game_type' => LearningGameType::Projectiles->value,
                'total_time_seconds' => (int) round($totalTimeMs / 1000),
                'error_rate_percentage' => $errorRate,
                'earned_xp' => $earnedXp,
                'attempts' => $totalShots,
                'correct_attempts' => $totalHits,
                'skipped_attempts' => 0,
                'completed_at' => now(),
            ]);
        });

        return true;
    }
}
