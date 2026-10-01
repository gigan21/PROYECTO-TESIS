<?php

namespace App\Http\Controllers\Controllers_Docentes;

use App\Enums\AttendanceSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceSessionRequest;
use App\Models\AttendanceSession;
use App\Services\Attendance\AttendanceRosterService;
use App\Support\Classroom;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceSessionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AttendanceRosterService $roster
    ) {}

    public function create(Request $request, string $classroom): View
    {
        $nombre = Classroom::fromSlug($classroom);

        return view('docente.asistencia.crear', [
            'classroom' => $nombre,
            'classroomSlug' => $classroom,
            'totalEstudiantes' => $this->roster->studentsFor($request->user()->id, $nombre)->count(),
        ]);
    }

    public function store(StoreAttendanceSessionRequest $request, string $classroom): RedirectResponse
    {
        $session = AttendanceSession::query()->create([
            'teacher_id' => $request->user()->id,
            'classroom' => $request->classroom(),
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'session_date' => $request->validated('session_date'),
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'status' => AttendanceSessionStatus::Activa,
        ]);

        return redirect()
            ->route('docente.asistencia.sesiones.show', $session)
            ->with('status', 'Sesión de asistencia creada.');
    }

    public function show(Request $request, AttendanceSession $attendanceSession): View
    {
        $this->authorize('view', $attendanceSession);

        $attendanceSession->load('records');

        $estudiantes = $this->roster->studentsFor($request->user()->id, $attendanceSession->classroom);
        $registros = $attendanceSession->records->keyBy('student_id');
        $resumen = $this->roster->summaryFor($attendanceSession);

        return view('docente.asistencia.sesion', [
            'session' => $attendanceSession,
            'estudiantes' => $estudiantes,
            'registros' => $registros,
            'resumen' => $resumen,
        ]);
    }

    public function close(Request $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $this->authorize('close', $attendanceSession);

        $this->roster->closeSession($attendanceSession);

        return redirect()
            ->route('docente.asistencia.sesiones.show', $attendanceSession)
            ->with('status', 'Sesión de asistencia cerrada.');
    }
}
