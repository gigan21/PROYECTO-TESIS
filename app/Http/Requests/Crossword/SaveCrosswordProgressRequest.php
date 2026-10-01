<?php

declare(strict_types=1);

namespace App\Http\Requests\Crossword;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCrosswordProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() === true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['word_learned', 'wrong_attempt', 'advance_level', 'reset'])],
            'word' => ['nullable', 'string', 'max:30'],
            'level' => ['nullable', 'integer', 'min:1', 'max:20'],
            'time_spent' => ['nullable', 'integer', 'min:0', 'max:600'],
        ];
    }
}
