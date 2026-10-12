<?php

declare(strict_types=1);

namespace App\Services\Projectiles;

use App\Models\ProjectileLevelReward;
use App\Models\User;
use Illuminate\Database\QueryException;
class ProjectileLevelRewardGuard
{
    /**
     * Reserva el cobro de monedas para (session_id, level, finished).
     * Devuelve false si ya existía (idempotencia R2).
     */
    public function tryClaim(
        User $user,
        string $sessionId,
        int $level,
        bool $finished,
        int $coinsAwarded
    ): bool {
        try {
            ProjectileLevelReward::query()->create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'level' => $level,
                'finished' => $finished,
                'coins_awarded' => $coinsAwarded,
                'created_at' => now(),
            ]);

            return true;
        } catch (QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                return false;
            }

            throw $e;
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $code = (string) ($e->errorInfo[1] ?? '');

        return in_array($code, ['1062', '23000', '23505'], true)
            || str_contains(strtolower($e->getMessage()), 'unique');
    }
}
