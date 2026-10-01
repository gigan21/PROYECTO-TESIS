<?php

namespace App\Services\Gamification;

use App\Models\User;

class XpAwardService
{
    public function award(User $user, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $profile = $user->studentProfile()->firstOrCreate(
            ['user_id' => $user->id],
            ['xp_points' => 0]
        );

        $profile->increment('xp_points', $amount);
    }
}
