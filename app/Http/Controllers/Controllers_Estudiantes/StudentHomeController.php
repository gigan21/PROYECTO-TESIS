<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Services\Gamification\StudentBadgeService;
use App\Services\Gamification\StudentProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentHomeController extends Controller
{
    public function __construct(
        private readonly StudentProgressService $progress,
        private readonly StudentBadgeService $badges
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $progress = $this->progress->forUser($user);
        $this->badges->syncForUser($user);

        return view('estudiante.inicio', [
            'progress' => $progress,
            'badges' => $this->badges->displayItems($user),
        ]);
    }
}
