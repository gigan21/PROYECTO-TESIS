<?php

namespace App\Services\Questions;

use App\Enums\QuestionBlockType;
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
            $this->syncHardBlocks($question, $options);

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
                'block_type' => null,
                'content' => null,
                'unit' => null,
                'tolerance' => null,
                'sort_order' => 0,
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
    private function syncHardBlocks(Question $question, array $options): void
    {
        $existingById = $question->options()->get()->keyBy('id');
        $keptIds = [];

        foreach ($options as $index => $option) {
            $blockType = QuestionBlockType::from($option['block_type']);
            $sortOrder = isset($option['sort_order']) ? (int) $option['sort_order'] : $index;

            $payload = [
                'option_text' => (string) ($option['option_text'] ?? ''),
                'block_type' => $blockType,
                'content' => $option['content'] ?? null,
                'unit' => $option['unit'] ?? null,
                'tolerance' => isset($option['tolerance']) && $option['tolerance'] !== ''
                    ? (float) $option['tolerance']
                    : null,
                'sort_order' => $sortOrder,
                'is_correct' => $blockType === QuestionBlockType::Input,
            ];

            $optionId = isset($option['id']) ? (int) $option['id'] : null;
            $existing = ($optionId && $existingById->has($optionId))
                ? $existingById->get($optionId)
                : null;

            if ($existing !== null && (int) $existing->question_id === (int) $question->id) {
                $existing->update($payload);
                $keptIds[] = $existing->id;

                continue;
            }

            $created = QuestionOption::query()->create([
                'question_id' => $question->id,
                ...$payload,
            ]);
            $keptIds[] = $created->id;
        }

        if ($keptIds !== []) {
            $question->options()
                ->whereNotIn('id', $keptIds)
                ->each(fn (QuestionOption $option) => $option->delete());
        }
    }
}
