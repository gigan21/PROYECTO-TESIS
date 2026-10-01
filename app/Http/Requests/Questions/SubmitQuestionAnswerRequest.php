<?php

namespace App\Http\Requests\Questions;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Http\FormRequest;

class SubmitQuestionAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEstudiante() === true;
    }

    public function rules(): array
    {
        return [
            'question_option_id' => ['required', 'integer', 'exists:question_options,id'],
            'time_taken' => ['required', 'integer', 'min:0'], // <-- Agregamos el tiempo aquí
        ];
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