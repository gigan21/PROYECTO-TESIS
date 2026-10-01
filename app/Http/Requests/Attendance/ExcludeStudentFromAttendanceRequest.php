<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class ExcludeStudentFromAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDocente() === true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
