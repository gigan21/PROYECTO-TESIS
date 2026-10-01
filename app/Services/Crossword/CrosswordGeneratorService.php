<?php

declare(strict_types=1);

namespace App\Services\Crossword;

final class CrosswordGeneratorService
{
    private const GRID_SIZE = 25;

    /** @var array<int, array<int, string|null>> */
    private array $grid = [];

    /** @var list<array<string, mixed>> */
    private array $placedWords = [];

    /**
     * Genera cuadrícula y palabras colocadas (mismo criterio que script.js).
     *
     * @param  list<array{answer: string, clue: string, direction?: string}>  $words
     * @return array{grid: array<int, array<int, string|null>>, placedWords: list<array<string, mixed>>, rows: int, cols: int}
     */
    public function generate(array $words): array
    {
        $this->createEmptyGrid();
        $this->placedWords = [];

        if ($words === []) {
            return ['grid' => [], 'placedWords' => [], 'rows' => 0, 'cols' => 0];
        }

        $sorted = $words;
        usort($sorted, fn (array $a, array $b): int => strlen($b['answer']) <=> strlen($a['answer']));

        $first = $sorted[0];
        $firstDir = $first['direction'] ?? 'across';
        $startRow = (int) floor(self::GRID_SIZE / 2);
        $startCol = (int) floor((self::GRID_SIZE - strlen($first['answer'])) / 2);

        $this->placeWord($first['answer'], $startRow, $startCol, $firstDir);
        $this->placedWords[] = array_merge($first, ['row' => $startRow, 'col' => $startCol, 'direction' => $firstDir]);

        for ($i = 1, $count = count($sorted); $i < $count; $i++) {
            $word = $sorted[$i];
            $answer = strtoupper($word['answer']);
            $best = null;

            foreach ($this->placedWords as $placed) {
                $placedWord = strtoupper((string) $placed['answer']);
                $placedLen = strlen($placedWord);
                $wordLen = strlen($answer);

                for ($pi = 0; $pi < $placedLen; $pi++) {
                    for ($wi = 0; $wi < $wordLen; $wi++) {
                        if ($placedWord[$pi] !== $answer[$wi]) {
                            continue;
                        }

                        if (($placed['direction'] ?? 'across') === 'across') {
                            $newDir = 'down';
                            $newRow = $placed['row'] - $wi;
                            $newCol = $placed['col'] + $pi;
                        } else {
                            $newDir = 'across';
                            $newRow = $placed['row'] + $pi;
                            $newCol = $placed['col'] - $wi;
                        }

                        if ($this->canPlace($answer, $newRow, $newCol, $newDir)) {
                            $dist = abs($newRow - self::GRID_SIZE / 2) + abs($newCol - self::GRID_SIZE / 2);
                            if ($best === null || $dist < $best['dist']) {
                                $best = ['row' => $newRow, 'col' => $newCol, 'dir' => $newDir, 'dist' => $dist];
                            }
                        }
                    }
                }
            }

            if ($best !== null) {
                $this->placeWord($answer, $best['row'], $best['col'], $best['dir']);
                $this->placedWords[] = array_merge($word, [
                    'answer' => $answer,
                    'direction' => $best['dir'],
                    'row' => $best['row'],
                    'col' => $best['col'],
                ]);
            }
        }

        $this->trimGrid();

        return [
            'grid' => $this->grid,
            'placedWords' => $this->placedWords,
            'rows' => count($this->grid),
            'cols' => $this->grid[0] !== [] ? count($this->grid[0]) : 0,
        ];
    }

    private function createEmptyGrid(): void
    {
        $this->grid = array_fill(0, self::GRID_SIZE, array_fill(0, self::GRID_SIZE, null));
    }

    private function canPlace(string $word, int $row, int $col, string $dir): bool
    {
        $len = strlen($word);
        if ($dir === 'across') {
            if ($col + $len > self::GRID_SIZE || $row < 0 || $row >= self::GRID_SIZE) {
                return false;
            }
        } elseif ($row + $len > self::GRID_SIZE || $col < 0 || $col >= self::GRID_SIZE) {
            return false;
        }

        $intersections = 0;

        for ($i = 0; $i < $len; $i++) {
            $r = $dir === 'across' ? $row : $row + $i;
            $c = $dir === 'across' ? $col + $i : $col;
            $cell = $this->grid[$r][$c];

            if ($cell !== null) {
                if ($cell !== $word[$i]) {
                    return false;
                }
                $intersections++;
            } elseif ($dir === 'across') {
                if ($r > 0 && $this->grid[$r - 1][$c] !== null) {
                    return false;
                }
                if ($r < self::GRID_SIZE - 1 && $this->grid[$r + 1][$c] !== null) {
                    return false;
                }
            } else {
                if ($c > 0 && $this->grid[$r][$c - 1] !== null) {
                    return false;
                }
                if ($c < self::GRID_SIZE - 1 && $this->grid[$r][$c + 1] !== null) {
                    return false;
                }
            }
        }

        if ($dir === 'across') {
            if ($col > 0 && $this->grid[$row][$col - 1] !== null) {
                return false;
            }
            if ($col + $len < self::GRID_SIZE && $this->grid[$row][$col + $len] !== null) {
                return false;
            }
        } else {
            if ($row > 0 && $this->grid[$row - 1][$col] !== null) {
                return false;
            }
            if ($row + $len < self::GRID_SIZE && $this->grid[$row + $len][$col] !== null) {
                return false;
            }
        }

        return $this->placedWords === [] || $intersections > 0;
    }

    private function placeWord(string $word, int $row, int $col, string $dir): void
    {
        $len = strlen($word);
        for ($i = 0; $i < $len; $i++) {
            $r = $dir === 'across' ? $row : $row + $i;
            $c = $dir === 'across' ? $col + $i : $col;
            $this->grid[$r][$c] = $word[$i];
        }
    }

    private function trimGrid(): void
    {
        $minR = self::GRID_SIZE;
        $maxR = 0;
        $minC = self::GRID_SIZE;
        $maxC = 0;

        for ($r = 0; $r < self::GRID_SIZE; $r++) {
            for ($c = 0; $c < self::GRID_SIZE; $c++) {
                if ($this->grid[$r][$c] !== null) {
                    $minR = min($minR, $r);
                    $maxR = max($maxR, $r);
                    $minC = min($minC, $c);
                    $maxC = max($maxC, $c);
                }
            }
        }

        if ($minR > $maxR) {
            $this->grid = [];

            return;
        }

        $rows = $maxR - $minR + 1;
        $cols = $maxC - $minC + 1;
        $newGrid = array_fill(0, $rows, array_fill(0, $cols, null));

        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $newGrid[$r][$c] = $this->grid[$r + $minR][$c + $minC];
            }
        }

        foreach ($this->placedWords as &$word) {
            $word['row'] -= $minR;
            $word['col'] -= $minC;
        }
        unset($word);

        $this->grid = $newGrid;
    }
}
