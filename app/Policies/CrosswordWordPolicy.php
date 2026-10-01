<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CrosswordWord;
use App\Models\User;

class CrosswordWordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTeacher();
    }

    public function view(User $user, CrosswordWord $crosswordWord): bool
    {
        return $user->isTeacher();
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, CrosswordWord $crosswordWord): bool
    {
        return $user->isTeacher() && $user->id === $crosswordWord->user_id;
    }

    public function delete(User $user, CrosswordWord $crosswordWord): bool
    {
        return $user->isTeacher() && $user->id === $crosswordWord->user_id;
    }
}
