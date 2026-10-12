<?php

declare(strict_types=1);

namespace App\Http\Requests\Projectiles;

use Illuminate\Foundation\Http\FormRequest;

class ProjectileGameRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEstudiante() === true;
    }

    public function rules(): array
    {
        return [
            'level' => ['required', 'integer', 'min:1', 'max:5'],
            'score' => ['required', 'integer', 'min:0'],
            'finished' => ['required', 'boolean'],
            'is_win' => ['required', 'boolean'],
            'session_id' => ['required', 'string', 'max:64'],
            'telemetry' => ['nullable', 'array'],
        ];
    }
}
