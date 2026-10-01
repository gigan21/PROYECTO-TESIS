<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;

class AttendanceSessionPolicy
{
    public function view(User $user, AttendanceSession $session): bool
    {
        return $user->isDocente() && $session->teacher_id === $user->id;
    }

    public function update(User $user, AttendanceSession $session): bool
    {
        return $this->view($user, $session);
    }

    public function close(User $user, AttendanceSession $session): bool
    {
        return $this->view($user, $session);
    }
}
