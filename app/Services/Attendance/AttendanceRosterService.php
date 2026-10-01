<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceRecordStatus;
use App\Enums\AttendanceSessionStatus;
use App\Models\AttendanceExclusion;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use App\Support\Classroom;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AttendanceRosterService
{
    public function studentsFor(int $teacherId, string $classroom): Collection
    {
        $excludedIds = $this->excludedStudentIds($teacherId);

        return User::query()
            ->where('role', 'estudiante')
            ->whereHas('studentProfile', function ($query) use ($classroom) {
                $query->whereIn('classroom', Classroom::storedValuesFor($classroom));
            })
            ->when($excludedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->with('studentProfile')
            ->orderBy('name')
            ->get();
    }

    public function excludedStudentIds(int $teacherId): \Illuminate\Support\Collection
    {
        return AttendanceExclusion::query()
            ->where('teacher_id', $teacherId)
            ->pluck('student_id');
    }

    public function studentBelongsToClassroom(User $student, string $classroom): bool
    {
        if ($student->role !== 'estudiante' || ! $student->studentProfile) {
            return false;
        }

        return in_array(
            $student->studentProfile->classroom,
            Classroom::storedValuesFor($classroom),
            true
        );
    }

    public function isExcluded(int $teacherId, int $studentId): bool
    {
        return AttendanceExclusion::query()
            ->where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->exists();
    }

    public function summaryFor(AttendanceSession $session): array
    {
        $students = $this->studentsFor($session->teacher_id, $session->classroom);
        $total = $students->count();

        $presentIds = $session->records
            ->where('status', AttendanceRecordStatus::Presente)
            ->pluck('student_id');

        $presentes = $students->whereIn('id', $presentIds)->count();
        $ausentes = max($total - $presentes, 0);
        $porcentaje = $total > 0 ? (int) round(($presentes / $total) * 100) : 0;

        return [
            'total' => $total,
            'presentes' => $presentes,
            'ausentes' => $ausentes,
            'porcentaje' => $porcentaje,
        ];
    }

    public function upsertRecord(AttendanceSession $session, int $studentId, AttendanceRecordStatus $status): AttendanceRecord
    {
        $this->assertSessionIsOpen($session);

        $student = User::query()->findOrFail($studentId);

        if (! $this->studentBelongsToClassroom($student, $session->classroom)) {
            throw new AccessDeniedHttpException('El estudiante no pertenece a este paralelo.');
        }

        if ($this->isExcluded($session->teacher_id, $student->id)) {
            throw new AccessDeniedHttpException('El estudiante está excluido de esta gestión de asistencia.');
        }

        return AttendanceRecord::query()->updateOrCreate(
            [
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
            ],
            [
                'status' => $status,
                'marked_at' => now(),
            ]
        );
    }

    public function excludeStudent(int $teacherId, string $classroom, int $studentId): AttendanceExclusion
    {
        $student = User::query()->findOrFail($studentId);

        if (! $this->studentBelongsToClassroom($student, $classroom)) {
            throw ValidationException::withMessages([
                'student_id' => 'El estudiante no pertenece a este paralelo.',
            ]);
        }

        return AttendanceExclusion::query()->firstOrCreate(
            [
                'teacher_id' => $teacherId,
                'student_id' => $student->id,
            ],
            [
                'classroom' => Classroom::normalize($classroom),
            ]
        );
    }

    public function closeSession(AttendanceSession $session): AttendanceSession
    {
        $this->assertSessionIsOpen($session);

        return DB::transaction(function () use ($session) {
            $students = $this->studentsFor($session->teacher_id, $session->classroom);

            foreach ($students as $student) {
                AttendanceRecord::query()->firstOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => AttendanceRecordStatus::Ausente,
                        'marked_at' => now(),
                    ]
                );
            }

            $session->update([
                'status' => AttendanceSessionStatus::Cerrada,
                'closed_at' => now(),
            ]);

            return $session->fresh(['records']);
        });
    }

    private function assertSessionIsOpen(AttendanceSession $session): void
    {
        if (! $session->isOpen()) {
            throw ValidationException::withMessages([
                'session' => 'Esta sesión de asistencia ya está cerrada.',
            ]);
        }
    }
}
