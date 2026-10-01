<?php

namespace App\Http\Requests\Questions;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPuzzleAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEstudiante() === true;
    }

    public function rules(): array
    {
        return [
            'time_taken' => ['required', 'integer', 'min:0'],
            // Validamos que envíe un array con exactamente las 4 opciones
            'ordered_option_ids' => ['required', 'array', 'size:4'],
            // Validamos que cada ID dentro del array exista en la base de datos
            'ordered_option_ids.*' => ['required', 'integer', 'exists:question_options,id'],
        ];
    }
}