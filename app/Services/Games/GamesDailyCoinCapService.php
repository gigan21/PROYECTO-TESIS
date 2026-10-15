<?php

declare(strict_types=1);

namespace App\Services\Games;

use App\Models\CoinTransaction;

class GamesDailyCoinCapService
{
    /** Fuentes que comparten el tope diario de monedas en juegos. */
    private const GAME_SOURCES = ['projectiles', 'cure', 'chart_race'];

    public function dailyCap(): int
    {
        return (int) config('projectiles.daily_cap', 35);
    }

    public function earnedToday(int $userId): int
    {
        return (int) CoinTransaction::query()
            ->where('user_id', $userId)
            ->whereIn('source', self::GAME_SOURCES)
            ->where('amount', '>', 0)
            ->whereDate('created_at', today())
            ->sum('amount');
    }

    public function remainingToday(int $userId): int
    {
        return max(0, $this->dailyCap() - $this->earnedToday($userId));
    }

    public function applyCap(int $userId, int $requested): array
    {
        $available = $this->remainingToday($userId);
        $toAward = min($requested, $available);

        return [
            'to_award' => $toAward,
            'capped' => $toAward < $requested,
            'daily_earned' => $this->earnedToday($userId),
        ];
    }
}
