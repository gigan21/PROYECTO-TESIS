<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Crossword\CrosswordProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrosswordStatsController extends Controller
{
    public function __construct(
        private readonly CrosswordProgressService $progressService
    ) {}

    public function index(Request $request): View
    {
        $sort = $request->string('sort', 'level')->toString();
        $query = User::query()
            ->where('role', 'estudiante')
            ->with(['crosswordProgress', 'studentProfile']);

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $students = $query->get()->sortBy(function (User $user) use ($sort) {
            $progress = $user->crosswordProgress;

            return match ($sort) {
                'coins' => -1 * ($progress->coins_earned ?? 0),
                default => -1 * ($progress->current_level ?? 0),
            };
        })->values();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;
        $items = $students->slice(($page - 1) * $perPage, $perPage)->values();

        return view('docente.crucigrama.stats', [
            'students' => new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $students->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            ),
        ]);
    }

    public function show(User $student): View
    {
        abort_unless($student->isStudent(), 404);

        return view('docente.crucigrama.stats-show', [
            'statistics' => $this->progressService->getStatistics($student),
            'student' => $student,
        ]);
    }
}
