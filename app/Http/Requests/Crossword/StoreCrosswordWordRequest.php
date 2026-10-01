<?php

declare(strict_types=1);

namespace App\Http\Requests\Crossword;

use App\Models\CrosswordWord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrosswordWordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('answer')) {
            $this->merge([
                'answer' => CrosswordWord::normalizeAnswer((string) $this->input('answer')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'answer' => ['required', 'string', 'min:2', 'max:30'],
            'clue' => ['required', 'string', 'min:5', 'max:500'],
            'difficulty' => ['required', Rule::in(['facil', 'medio', 'dificil'])],
            'level' => ['required', 'integer', 'min:1', 'max:20'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
        ];
    }
}
