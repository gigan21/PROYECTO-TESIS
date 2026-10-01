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

        return [
            'question_option_id' => ['required', 'integer', 'exists:question_options,id'],
            'response_time_seconds' => ['required', 'integer', 'min:1', 'max:'.$max],
        ];
    }

    public function room(): ChallengeRoom
    {
        return $this->route('challenge_room');
    }

    public function option(): QuestionOption
    {
        /** @var Question $question */
        $question = $this->route('question');

        return QuestionOption::query()
            ->where('question_id', $question->id)
            ->findOrFail($this->validated('question_option_id'));
    }
}
