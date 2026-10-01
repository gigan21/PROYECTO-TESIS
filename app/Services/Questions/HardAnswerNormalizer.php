<?php

namespace App\Services\Questions;

class HardAnswerNormalizer
{
    /** Margen decimal: acepta 5.3m si la clave es 5.33m (|Δ| ≤ 0.05). */
    public const DEFAULT_DECIMAL_TOLERANCE = 0.05;

    public function normalize(string $raw): string
    {
        $lower = mb_strtolower(trim($raw), 'UTF-8');

        return preg_replace('/\s+/u', '', $lower) ?? $lower;
    }

    /**
     * @return array{value: float, unit: string}|null
     */
    public function parseNumericAndUnit(string $normalized): ?array
    {
        if (! preg_match('/^([-+]?\d+(?:\.\d+)?)(.*)$/u', $normalized, $matches)) {
            return null;
        }

        return [
            'value' => (float) $matches[1],
            'unit' => $this->normalize($matches[2]),
        ];
    }

    public function matches(
        string $studentRaw,
        string $expectedRaw,
        float $decimalTolerance = self::DEFAULT_DECIMAL_TOLERANCE
    ): bool {
        $studentNorm = $this->normalize($studentRaw);
        $expectedNorm = $this->normalize($expectedRaw);

        if ($studentNorm === $expectedNorm) {
            return true;
        }

        $studentParsed = $this->parseNumericAndUnit($studentNorm);
        $expectedParsed = $this->parseNumericAndUnit($expectedNorm);

        if ($studentParsed === null || $expectedParsed === null) {
            return false;
        }

        if ($studentParsed['unit'] !== $expectedParsed['unit']) {
            return false;
        }

        return abs($studentParsed['value'] - $expectedParsed['value']) <= $decimalTolerance;
    }
}
