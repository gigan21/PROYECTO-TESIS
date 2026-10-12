<?php

declare(strict_types=1);

namespace App\Services\Games;

use App\Models\CureLevelReward;
use App\Models\User;
use Illuminate\Database\QueryException;

class CureLevelRewardGuard
{
    public function tryClaim(
        User $user,
        string $sessionId,
        int $level,
        int $coinsAwarded,
        int $xpAwarded,
    ): bool {
        try {
            CureLevelReward::query()->create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'level' => $level,
                'coins_awarded' => $coinsAwarded,
                'xp_awarded' => $xpAwarded,
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
