<?php

namespace App\Support;

use InvalidArgumentException;

class Classroom
{
    public const CANONICAL = [
        '4to A',
        '4to B',
        '4to C',
    ];

    /**
     * Valores históricos/alias que ya existen en student_profiles.
     *
     * @var array<string, list<string>>
     */
    private const ALIASES = [
        '4to A' => ['4to A', '4A', '4.º A'],
        '4to B' => ['4to B', '4B', '4.º B'],
        '4to C' => ['4to C', '4C', '4.º C'],
    ];

    private const SLUGS = [
        '4to-a' => '4to A',
        '4to-b' => '4to B',
        '4to-c' => '4to C',
    ];

    public static function all(): array
    {
        return self::CANONICAL;
    }

    public static function slugPattern(): string
    {
        return '4to-a|4to-b|4to-c';
    }

    public static function slugFor(string $classroom): string
    {
        $canonical = self::normalize($classroom);

        return array_search($canonical, self::SLUGS, true) ?: '4to-a';
    }

    public static function fromSlug(string $slug): string
    {
        $normalizedSlug = strtolower(trim($slug));

        if (! isset(self::SLUGS[$normalizedSlug])) {
            throw new InvalidArgumentException('Paralelo no válido.');
        }

        return self::SLUGS[$normalizedSlug];
    }

    public static function isValidSlug(string $slug): bool
    {
        return isset(self::SLUGS[strtolower(trim($slug))]);
    }

    public static function normalize(string $value): string
    {
        $trimmed = trim($value);

        foreach (self::ALIASES as $canonical => $aliases) {
            if (in_array($trimmed, $aliases, true) || $trimmed === $canonical) {
                return $canonical;
            }
        }

        throw new InvalidArgumentException('Paralelo no válido.');
    }

    /**
     * @return list<string>
     */
    public static function storedValuesFor(string $classroom): array
    {
        $canonical = self::normalize($classroom);

        return self::ALIASES[$canonical];
    }
}
