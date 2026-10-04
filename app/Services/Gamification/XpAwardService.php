<?php

namespace App\Services\Gamification;

use App\Models\User;

class XpAwardService
{
    public function __construct(
        private readonly RankingService $ranking
    ) {}

    public function award(User $user, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $profile = $user->studentProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'xp_points' => 0,
                'coins' => 0,
            ]
        );

        $profile->increment('xp_points', $amount);

        // Limpiar cache del ranking para que se actualice
        $this->ranking->clearCache($profile->classroom);
    }
}