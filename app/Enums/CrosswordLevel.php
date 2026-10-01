<?php

declare(strict_types=1);

namespace App\Enums;

final class CrosswordLevel
{
    public const MIN_LEVEL = 1;

    public const MAX_LEVEL = 20;

    public static function wordsForLevel(int $level): int
    {
        return self::clampLevel($level);
    }

    public static function coinsForAttempts(int $attempts): int
    {
        return (int) floor($attempts / 2);
    }

    public static function coinsForLevel(int $level): int
    {
        return self::clampLevel($level) * 2;
    }

    public static function difficultyForLevel(int $level): string
    {
        $level = self::clampLevel($level);

        if ($level <= 7) {
            return 'facil';
        }

        if ($level <= 14) {
            return 'medio';
        }

        return 'dificil';
    }

    private static function clampLevel(int $level): int
    {
        return max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
    }
}
