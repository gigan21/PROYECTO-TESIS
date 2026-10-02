<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Models\User;

class StudentProgressService
{
    /**
     * @return array{level: int, xp_in_level: int, xp_per_level: int, percent: float, xp_total: int, coins: int, rank_title: string}
     */
    public function forUser(User $student): array
    {
        $profile = $student->studentProfile()->firstOrCreate(
            ['user_id' => $student->id],
            ['xp_points' => 0, 'coins' => 0, 'classroom' => '4A', 'avatar_name' => 'default_avatar.png']
        );

        $xpTotal = (int) ($profile->xp_points ?? 0);
        $xpInLevel = $xpTotal % 100;

        return [
            'level' => (int) $profile->level,
            'xp_in_level' => $xpInLevel,
            'xp_per_level' => 100,
            'percent' => (float) $xpInLevel,
            'xp_total' => $xpTotal,
            'coins' => (int) ($profile->coins ?? 0),
            'rank_title' => $profile->rank_title,
        ];
    }
}
