<?php

namespace App\Http\Requests\Challenges;

use App\Support\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChallengeRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDocente() === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'classroom' => ['required', 'string', Rule::in(Classroom::all())],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ];
    }
}
