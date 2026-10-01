<?php

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Services\Attendance\AttendanceRosterService;
use App\Support\Classroom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceHubController extends Controller
{
    public function __construct(
        private readonly AttendanceRosterService $roster
    ) {}

    public function index(Request $request): View
    {
        $teacherId = $request->user()->id;
        $paralelos = [];

        foreach (Classroom::all() as $classroom) {
            $sessions = AttendanceSession::query()
                ->where('teacher_id', $teacherId)
                ->where('classroom', $classroom)
                ->with('records')
                ->latest('session_date')
                ->latest('id')
                ->limit(3)
                ->get();

            $paralelos[] = [
                'nombre' => $classroom,
                'slug' => Classroom::slugFor($classroom),
                'estudiantes' => $this->roster->studentsFor($teacherId, $classroom)->count(),
                'sesiones' => $sessions->map(fn (AttendanceSession $session) => [
                    'session' => $session,
                    'resumen' => $this->roster->summaryFor($session),
                ]),
            ];
        }

        return view('docente.asistencia.index', compact('paralelos'));
    }

    public function showClassroom(Request $request, string $classroom): View
    {
        $nombre = Classroom::fromSlug($classroom);
        $teacherId = $request->user()->id;

        $sesiones = AttendanceSession::query()
            ->where('teacher_id', $teacherId)
            ->where('classroom', $nombre)
            ->with('records')
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (AttendanceSession $session) => [
                'session' => $session,
                'resumen' => $this->roster->summaryFor($session),
            ]);

        $totalEstudiantes = $this->roster->studentsFor($teacherId, $nombre)->count();

        return view('docente.asistencia.historial', [
            'classroom' => $nombre,
            'classroomSlug' => $classroom,
            'sesiones' => $sesiones,
            'totalEstudiantes' => $totalEstudiantes,
        ]);
    }
}
