<?php

namespace App\Services\Questions;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use ValueError;

class QuestionCsvImporter
{
    private const EXPECTED_HEADERS = [
        'topic',
        'difficulty',
        'type',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'xp_reward',
    ];

    /**
     * @return array{topics_found: int, topics_created: int, questions_imported: int, questions_skipped: int, options_created: int}
     */
    public function import(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("No se encontró el archivo CSV: {$path}");
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el archivo CSV.');
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new RuntimeException('El archivo CSV está vacío.');
            }

            $this->assertHeaders($header);

            return DB::transaction(function () use ($handle) {
                $stats = [
                    'topics_found' => 0,
                    'topics_created' => 0,
                    'questions_imported' => 0,
                    'questions_skipped' => 0,
                    'options_created' => 0,
                ];

                $knownTopics = [];
                $rowNumber = 1;

                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;

                    if ($this->isEmptyRow($row)) {
                        continue;
                    }

                    $record = $this->mapRow($row, $rowNumber);
                    $this->importRecord($record, $knownTopics, $stats);
                }

                return $stats;
            });
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  list<string|null>  $header
     */
    private function assertHeaders(array $header): void
    {
        $normalized = array_map(fn ($column) => strtolower(trim((string) $column)), $header);

        if ($normalized !== self::EXPECTED_HEADERS) {
            throw new RuntimeException(
                'El CSV no tiene las columnas esperadas: '.implode(',', self::EXPECTED_HEADERS)
            );
        }
    }

    /**
     * @param  list<string|null>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        return count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0;
    }

    /**
     * @param  list<string|null>  $row
     * @return array<string, string>
     */
    private function mapRow(array $row, int $rowNumber): array
    {
        if (count($row) < count(self::EXPECTED_HEADERS)) {
            throw new RuntimeException("La fila {$rowNumber} no tiene todas las columnas requeridas.");
        }

        $data = [];

        foreach (self::EXPECTED_HEADERS as $index => $column) {
            $data[$column] = trim((string) $row[$index]);
        }

        foreach (['topic', 'difficulty', 'type', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'xp_reward'] as $column) {
            if ($data[$column] === '') {
                throw new RuntimeException("La fila {$rowNumber} tiene vacío el campo {$column}.");
            }
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $record
     * @param  array<string, array{id: int, created: bool}>  $knownTopics
     * @param  array<string, int>  $stats
     */
    private function importRecord(array $record, array &$knownTopics, array &$stats): void
    {
        $topicName = $record['topic'];

        if (! isset($knownTopics[$topicName])) {
            $topic = Topic::query()->firstOrCreate(
                ['name' => $topicName],
                ['is_active' => true]
            );

            $knownTopics[$topicName] = [
                'id' => $topic->id,
                'created' => $topic->wasRecentlyCreated,
            ];

            if ($topic->wasRecentlyCreated) {
                $stats['topics_created']++;
            } else {
                $stats['topics_found']++;
            }
        }

        $existing = Question::query()
            ->where('topic_id', $knownTopics[$topicName]['id'])
            ->where('question_text', $record['question_text'])
            ->first();

        if ($existing) {
            $stats['questions_skipped']++;

            return;
        }

        try {
            $difficulty = QuestionDifficulty::from($record['difficulty']);
            $type = QuestionType::from($record['type']);
        } catch (ValueError $exception) {
            throw new InvalidArgumentException(
                "Dificultad o tipo inválido para la pregunta \"{$record['question_text']}\".",
                0,
                $exception
            );
        }

        $correct = strtolower($record['correct_option']);

        if (! in_array($correct, ['a', 'b', 'c', 'd'], true)) {
            throw new InvalidArgumentException(
                "correct_option inválido para la pregunta \"{$record['question_text']}\"."
            );
        }

        $question = Question::query()->create([
            'topic_id' => $knownTopics[$topicName]['id'],
            'difficulty' => $difficulty,
            'type' => $type,
            'question_text' => $record['question_text'],
            'xp_reward' => (int) $record['xp_reward'],
            'is_active' => true,
        ]);

        $options = [
            'a' => $record['option_a'],
            'b' => $record['option_b'],
            'c' => $record['option_c'],
            'd' => $record['option_d'],
        ];

        foreach ($options as $letter => $text) {
            QuestionOption::query()->create([
                'question_id' => $question->id,
                'option_text' => $text,
                'is_correct' => $letter === $correct,
            ]);
            $stats['options_created']++;
        }

        $stats['questions_imported']++;
    }
}
