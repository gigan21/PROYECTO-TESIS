<?php

namespace App\Policies;

use App\Models\ChallengeRoom;
use App\Models\User;

class ChallengeRoomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDocente();
    }

    public function view(User $user, ChallengeRoom $room): bool
    {
        return $user->isDocente() && $room->teacher_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isDocente();
    }

    public function update(User $user, ChallengeRoom $room): bool
    {
        return $this->view($user, $room);
    }
}
