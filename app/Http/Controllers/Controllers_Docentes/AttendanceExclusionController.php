<?php

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\ExcludeStudentFromAttendanceRequest;
use App\Models\AttendanceSession;
use App\Services\Attendance\AttendanceRosterService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class AttendanceExclusionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AttendanceRosterService $roster
    ) {}

    public function store(ExcludeStudentFromAttendanceRequest $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $this->authorize('update', $attendanceSession);

        $this->roster->excludeStudent(
            $request->user()->id,
            $attendanceSession->classroom,
            (int) $request->validated('student_id')
        );

        return back()->with('status', 'El estudiante fue excluido de tu gestión de asistencia. Su cuenta no fue eliminada.');
    }
}
