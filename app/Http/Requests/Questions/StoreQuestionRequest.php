<?php

namespace App\Http\Requests\Questions;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionOption;
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
                'options' => ['required', 'array', 'min:1', 'max:20'],
                'options.*.block_type' => ['required', Rule::in(['text', 'formula', 'input'])],
                'options.*.content' => ['nullable', 'string', 'max:5000'],
                'options.*.unit' => ['nullable', 'string', 'max:50'],
                'options.*.tolerance' => ['nullable', 'numeric', 'min:0'],
                 
                'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
                'options.*.option_text' => ['nullable', 'string', 'max:255'],
                'options.*.id' => ['nullable', 'integer', 'exists:question_options,id'],
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
                $options = collect($this->input('options', []));
                $hasInput = $options->contains(fn ($opt) => ($opt['block_type'] ?? '') === 'input');

                if (! $hasInput) {
                    $validator->errors()->add('options', 'Debe haber al menos un bloque de tipo input.');
                }

                foreach ($options as $index => $option) {
                    $type = $option['block_type'] ?? '';
                    $content = trim((string) ($option['content'] ?? ''));

                    if (in_array($type, ['text', 'formula', 'input'], true) && $content === '') {
                        $validator->errors()->add("options.$index.content", 'El contenido es obligatorio para este bloque.');
                    }

                    if (! empty($option['id'])) {
                        $question = $this->route('question');
                        if ($question instanceof Question) {
                            $belongs = QuestionOption::query()
                                ->where('id', (int) $option['id'])
                                ->where('question_id', $question->id)
                                ->exists();
                            if (! $belongs) {
                                $validator->errors()->add("options.$index.id", 'El bloque no pertenece a esta pregunta.');
                            }
                        }
                    }
                }

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
