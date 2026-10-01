<?php

namespace App\Services\Challenges;

use App\Enums\ChallengeRoomStatus;
use App\Models\ChallengeRoom;
use App\Models\User;
use App\Services\Attendance\AttendanceRosterService;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ChallengeAccessService
{
    public function __construct(
        private readonly AttendanceRosterService $roster
    ) {}

    public function findByCode(string $code): ChallengeRoom
    {
        return ChallengeRoom::query()
            ->where('code', Strtoupper(trim($code)))
            ->firstOrFail();
    }

    public function assertStudentCanJoin(User $student, ChallengeRoom $room): void
    {
        if (! $student->isEstudiante()) {
            throw new AccessDeniedHttpException('Solo estudiantes pueden unirse al desafío.');
        }

        if ($room->status === ChallengeRoomStatus::Finished) {
            throw new AccessDeniedHttpException('Este desafío ya finalizó.');
        }

        if ($room->status !== ChallengeRoomStatus::Active) {
            throw new AccessDeniedHttpException('El desafío aún no está activo.');
        }

        if (! $this->roster->studentBelongsToClassroom($student, $room->classroom)) {
            throw new AccessDeniedHttpException('No perteneces al paralelo de este desafío.');
        }
    }

    public function assertStudentCanAnswer(User $student, ChallengeRoom $room): void
    {
        $this->assertStudentCanJoin($student, $room);
    }
}
