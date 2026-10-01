<?php

namespace App\Http\Requests\Questions;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitHardQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEstudiante() === true;
    }

    public function rules(): array
    {
        return [
            'time_taken' => ['required', 'integer', 'min:0'],
            'step_answers' => ['required', 'array'],
            'step_answers.*' => ['required', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $question = $this->route('question');

            if (! $question instanceof Question) {
                return;
            }

            $question->loadMissing('options');
            $expectedIds = $question->options->pluck('id')->map(fn ($id) => (string) $id)->sort()->values();
            $submittedKeys = collect(array_keys($this->input('step_answers', [])))
                ->map(fn ($key) => (string) $key)
                ->sort()
                ->values();

            if ($expectedIds->isEmpty()) {
                $validator->errors()->add('step_answers', 'Esta pregunta no tiene pasos configurados.');

                return;
            }

            if ($submittedKeys->count() !== $expectedIds->count() || ! $submittedKeys->every(fn ($key) => $expectedIds->contains($key))) {
                $validator->errors()->add('step_answers', 'Debes responder todos los pasos del laboratorio.');
            }
        });
    }
}
