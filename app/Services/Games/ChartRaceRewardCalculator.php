<?php

declare(strict_types=1);

namespace App\Services\Games;

class ChartRaceRewardCalculator
{
    /**
     * @param  array<string, mixed>  $telemetry
     * @return array{0: int, 1: int} coins, xp
     */
    public function calculate(array $telemetry): array
    {
        $misses = (int) ($telemetry['misses'] ?? $this->fallbackMisses($telemetry));
        $failMin = (int) config('chart_race.errors_fail_min', 4);
        $goodMax = (int) config('chart_race.errors_good_max', 3);

        if ($misses >= $failMin) {
            return [
                (int) config('chart_race.coins_fail', 0),
                (int) config('chart_race.xp_fail', 0),
            ];
        }

        if ($misses === 0) {
            return [
                (int) config('chart_race.coins_perfect', 10),
                (int) config('chart_race.xp_perfect', 10),
            ];
        }

        if ($misses <= $goodMax) {
            return [
                (int) config('chart_race.coins_good', 3),
                (int) config('chart_race.xp_good', 3),
            ];
        }

        return [
            (int) config('chart_race.coins_fail', 0),
            (int) config('chart_race.xp_fail', 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $telemetry
     */
    private function fallbackMisses(array $telemetry): int
    {
        $hits = (int) ($telemetry['hits'] ?? 0);

        return max(0, 6 - $hits);
    }
}
