<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceRecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDocente() === true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::enum(AttendanceRecordStatus::class)],
        ];
    }
}
