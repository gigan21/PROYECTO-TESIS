<?php

namespace App\Http\Requests\Questions;

use App\Enums\QuestionBlockType;
use App\Models\Question;
use App\Services\Questions\HardFormulaClozeParser;
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
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $question = $this->route('question');

            if (! $question instanceof Question) {
                return;
            }

            $question->loadMissing(['options' => fn ($q) => $q->ordered()]);
            $parser = app(HardFormulaClozeParser::class);
            $answers = $this->input('step_answers', []);

            $finalBlocks = $question->options->filter(
                fn ($option) => $option->block_type === QuestionBlockType::Input
            );

            if ($finalBlocks->isEmpty()) {
                $validator->errors()->add('step_answers', 'Esta pregunta no tiene respuesta final configurada.');

                return;
            }

            foreach ($finalBlocks as $option) {
                $key = (string) $option->id;
                $value = $answers[$option->id] ?? $answers[$key] ?? null;

                if (! is_string($value) || trim($value) === '') {
                    $validator->errors()->add('step_answers', 'Debes completar la respuesta final.');
                }
            }

            foreach ($question->options as $option) {
                if ($option->block_type !== QuestionBlockType::Formula) {
                    continue;
                }

                $holes = $parser->holeCount((string) $option->content);
                if ($holes === 0) {
                    continue;
                }

                $key = (string) $option->id;
                $value = $answers[$option->id] ?? $answers[$key] ?? null;

                if (! is_array($value)) {
                    $validator->errors()->add('step_answers', 'Debes completar todos los huecos del procedimiento.');

                    continue;
                }

                if (count($value) !== $holes) {
                    $validator->errors()->add('step_answers', 'Faltan huecos en el procedimiento matemático.');

                    continue;
                }

                foreach ($value as $holeAnswer) {
                    if (! is_string($holeAnswer) && ! is_numeric($holeAnswer)) {
                        $validator->errors()->add('step_answers', 'Los huecos del procedimiento deben ser numéricos.');
                        break;
                    }
                    if (strlen((string) $holeAnswer) > 500) {
                        $validator->errors()->add('step_answers', 'Una respuesta del procedimiento es demasiado larga.');
                        break;
                    }
                }
            }
        });
    }
}
