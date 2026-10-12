<?php

declare(strict_types=1);

namespace App\Services\Projectiles;

/**
 * Reglas R1: 5 monedas por nivel intermedio; 15 en victoria final; 0 en derrota final.
 */
class ProjectileCoinRewardCalculator
{
    public function calculate(bool $finished, bool $isWin): int
    {
        if ($finished && ! $isWin) {
            return 0;
        }

        $levelCoins = (int) config('projectiles.level_coins');

        if ($finished && $isWin) {
            return $levelCoins + (int) config('projectiles.win_bonus');
        }

        if (! $finished && $isWin) {
            return $levelCoins;
        }

        return 0;
    }
}
