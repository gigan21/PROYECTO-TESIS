<?php

namespace App\Http\Requests\Questions;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDocente() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $base = [
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'difficulty' => ['required', Rule::enum(QuestionDifficulty::class)],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'question_text' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'xp_reward' => ['required', 'integer', 'min:0', 'max:1000'],
            'time_limit_seconds' => ['required', 'integer', 'min:10', 'max:600'],
            'is_active' => ['boolean'],
        ];

        if ($this->input('difficulty') === QuestionDifficulty::Dificil->value) {
            return array_merge($base, [
                'options' => ['required', 'array', 'min:1', 'max:10'],
                'options.*.step_label' => ['required', 'string', 'max:255'],
                'options.*.option_text' => ['required', 'string', 'max:2000'],
                'options.*.hint_formula' => ['nullable', 'string', 'max:2000'],
            ]);
        }

        return array_merge($base, [
            'options' => ['required', 'array', 'size:4'],
            'options.*.option_text' => ['required', 'string', 'max:2000'],
            'correct_option' => ['required', 'integer', 'between:0,3'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('difficulty') === QuestionDifficulty::Dificil->value) {
                $labels = collect($this->input('options', []))
                    ->pluck('step_label')
                    ->map(fn ($label) => trim((string) $label))
                    ->filter();

                if ($labels->count() > 1 && $labels->unique()->count() !== $labels->count()) {
                    $validator->errors()->add('options', 'Cada paso debe tener una etiqueta distinta.');
                }   // borrar para no vALIDAR LAS ETIQUETAS

                return;
            }

            if ($this->input('difficulty') !== QuestionDifficulty::Facil->value) {
                return;
            }

            $options = collect($this->input('options', []))
                ->pluck('option_text')
                ->map(fn ($text) => trim((string) $text))
                ->filter();

            if ($options->count() === 4 && $options->unique()->count() !== 4) {
                $validator->errors()->add('options', 'Las cuatro opciones deben ser distintas.');
            }
        });
    }
}
