<?php

namespace App\Services\Questions;

use App\Models\Question;
use App\Models\QuestionOption;

class QuestionOptionsPersistenceService
{
    /**
     * @param  array<int, array<string, mixed>>  $options
     */
    public function syncForDifficulty(
        Question $question,
        string $difficulty,
        array $options,
        ?int $correctOptionIndex = null
    ): void {
        if ($difficulty === 'Difícil') {
            $this->syncHardSteps($question, $options);

            return;
        }

        $this->syncClassicOptions($question, $options, $correctOptionIndex ?? 0);
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     */
    private function syncClassicOptions(Question $question, array $options, int $correctOptionIndex): void
    {
        $existingOptions = $question->options()->orderBy('id')->get();

        foreach ($options as $index => $option) {
            $payload = [
                'option_text' => $option['option_text'],
                'step_label' => null,
                'hint_formula' => null,
                'is_correct' => $correctOptionIndex === $index,
            ];

            if (isset($existingOptions[$index])) {
                $existingOptions[$index]->update($payload);

                continue;
            }

            QuestionOption::query()->create([
                'question_id' => $question->id,
                ...$payload,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     */
    private function syncHardSteps(Question $question, array $options): void
    {
        $existingOptions = $question->options()->orderBy('id')->get();
        $stepCount = count($options);

        foreach ($options as $index => $option) {
            $payload = [
                'option_text' => $option['option_text'],
                'step_label' => $option['step_label'] ?? null,
                'hint_formula' => $option['hint_formula'] ?? null,
                'is_correct' => true,
            ];

            if (isset($existingOptions[$index])) {
                $existingOptions[$index]->update($payload);

                continue;
            }

            QuestionOption::query()->create([
                'question_id' => $question->id,
                ...$payload,
            ]);
        }

        $existingOptions->slice($stepCount)->each(fn (QuestionOption $option) => $option->delete());
    }
}
