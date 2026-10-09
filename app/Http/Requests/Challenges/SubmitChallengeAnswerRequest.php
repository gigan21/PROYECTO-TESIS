<?php

namespace App\Http\Requests\Challenges;

use App\Models\ChallengeRoom;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Http\FormRequest;

class SubmitChallengeAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEstudiante() === true;
    }

    public function rules(): array
    {
        /** @var Question $question */
        $question = $this->route('question');
        $max = max(10, (int) ($question->time_limit_seconds ?? 60));
        $difficulty = $question->difficulty->value;

        $timeRule = ['required', 'integer', 'min:1', 'max:' . $max];

        // Fácil: opción múltiple clásica
        if ($difficulty === 'Fácil') {
            return [
                'question_option_id' => ['required', 'integer', 'exists:question_options,id'],
                'response_time_seconds' => $timeRule,
            ];
        }

        // Medio: ordenamiento de pasos (los envía step-puzzle.js)
        if ($difficulty === 'Medio') {
            return [
                'ordered_option_ids' => ['required', 'array', 'min:1'],
                'ordered_option_ids.*' => ['integer', 'exists:question_options,id'],
                'response_time_seconds' => $timeRule,
            ];
        }

        // Difícil: huecos de fórmula + respuesta final
        return [
            'step_answers' => ['required', 'array'],
            'step_answers.*' => ['nullable'],
            'response_time_seconds' => $timeRule,
        ];
    }

    public function room(): ChallengeRoom
    {
        return $this->route('challenge_room');
    }

    /** Solo aplica a Fácil. */
    public function option(): QuestionOption
    {
        /** @var Question $question */
        $question = $this->route('question');

        return QuestionOption::query()
            ->where('question_id', $question->id)
            ->findOrFail($this->validated('question_option_id'));
    }

    /** @return list<int> */
    public function orderedOptionIds(): array
    {
        return array_values(array_map('intval', $this->validated('ordered_option_ids') ?? []));
    }

    /** @return array<int|string, mixed> */
    public function stepAnswers(): array
    {
        return $this->validated('step_answers') ?? [];
    }

    public function difficulty(): string
    {
        /** @var Question $question */
        $question = $this->route('question');

        return $question->difficulty->value;
    }
}