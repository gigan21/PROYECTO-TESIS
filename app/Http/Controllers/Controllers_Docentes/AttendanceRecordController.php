<?php

namespace App\Http\Controllers\Controllers_Docentes;

use App\Enums\AttendanceRecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\UpdateAttendanceRecordRequest;
use App\Models\AttendanceSession;
use App\Services\Attendance\AttendanceRosterService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AttendanceRecordController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AttendanceRosterService $roster
    ) {}

    public function upsert(UpdateAttendanceRecordRequest $request, AttendanceSession $attendanceSession): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $attendanceSession);

        $status = AttendanceRecordStatus::from($request->validated('status'));
        $record = $this->roster->upsertRecord(
            $attendanceSession,
            (int) $request->validated('student_id'),
            $status
        );

        $attendanceSession->load('records');
        $resumen = $this->roster->summaryFor($attendanceSession);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'record' => [
                    'student_id' => $record->student_id,
                    'status' => $record->status->value,
                    'marked_at' => $record->marked_at->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                ],
                'resumen' => $resumen,
            ]);
        }

        return back()->with('status', 'Asistencia actualizada.');
    }
}
