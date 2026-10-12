<?php

declare(strict_types=1);

namespace App\Services\Games;

class CureCoinRewardCalculator
{
    /**
     * @param  array<string, mixed>  $telemetry
     */
    public function calculate(array $telemetry): int
    {
        $hits = (int) ($telemetry['hits'] ?? 0);
        $won = $this->countsAsWin($telemetry);
        $perHit = (int) config('cure.coins_per_hit', 1);
        $winBonus = (int) config('cure.coins_win_bonus', 5);

        return ($hits * $perHit) + ($won ? $winBonus : 0);
    }

    /**
     * @param  array<string, mixed>  $telemetry
     */
    public function calculateXp(array $telemetry): int
    {
        $hits = (int) ($telemetry['hits'] ?? 0);
        $won = $this->countsAsWin($telemetry);
        $perHit = (int) config('cure.xp_per_hit', 1);
        $winBonus = (int) config('cure.xp_win_bonus', 5);

        return ($hits * $perHit) + ($won ? $winBonus : 0);
    }

    /**
     * Bonus solo si completó los 4 niveles de la partida.
     *
     * @param  array<string, mixed>  $telemetry
     */
    public function countsAsWin(array $telemetry): bool
    {
        $completed = (int) ($telemetry['nivelesCompletados'] ?? 0);

        return $completed === 4 && filter_var($telemetry['won'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
}
