<?php

namespace App\Http\Requests\Attendance;

use App\Support\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDocente() === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->starts_at) {
            $this->merge(['starts_at' => substr((string) $this->starts_at, 0, 5)]);
        }

        if ($this->ends_at) {
            $this->merge(['ends_at' => substr((string) $this->ends_at, 0, 5)]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'session_date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->starts_at && $this->ends_at && $this->ends_at <= $this->starts_at) {
                $validator->errors()->add('ends_at', 'La hora de cierre debe ser posterior a la hora de inicio.');
            }
        });
    }

    public function classroom(): string
    {
        return Classroom::fromSlug((string) $this->route('classroom'));
    }
}
