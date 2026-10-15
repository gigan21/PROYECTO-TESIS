<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Models\User;
use App\Services\Analytics\StudentStruggleDetector;
use App\Services\ML\RecommendationService;
use App\Support\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteRecommendationsController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommendations,
        private readonly StudentStruggleDetector $struggleDetector,
    ) {}

    public function index(Request $request): View
    {
        $latestIds = AiRecommendation::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        $query = AiRecommendation::query()
            ->whereIn('id', $latestIds)
            ->with(['user.studentProfile'])
            ->orderByDesc('created_at');

        $classroomFilter = $request->string('classroom')->toString();
        if ($classroomFilter !== '') {
            $canonical = Classroom::fromSlug($classroomFilter);
            $stored = Classroom::storedValuesFor($canonical);
            $query->whereHas('user.studentProfile', fn ($q) => $q->whereIn('classroom', $stored));
        }

        $rows = $query->get();
        $reviewed = $rows->filter(fn (AiRecommendation $r) => $r->teacher_feedback !== null);
        $correct = $reviewed->filter(fn (AiRecommendation $r) => $r->teacher_feedback === true)->count();
        $reviewedCount = $reviewed->count();

        $users = $rows->map(fn (AiRecommendation $r) => $r->user)->filter();
        $alertsByUserId = $this->struggleDetector->detectForUsers($users);

        return view('docente.recomendaciones.index', [
            'rows' => $rows,
            'alertsByUserId' => $alertsByUserId,
            'classroomFilter' => $classroomFilter,
            'classrooms' => Classroom::all(),
            'stats' => [
                'total' => $rows->count(),
                'reviewed' => $reviewedCount,
                'correct' => $correct,
                'accuracy' => $reviewedCount > 0 ? round(($correct / $reviewedCount) * 100, 1) : null,
            ],
        ]);
    }

    public function feedback(Request $request, AiRecommendation $recommendation): RedirectResponse
    {
        $validated = $request->validate([
            'is_correct' => ['required', 'boolean'],
        ]);

        $recommendation->update([
            'teacher_feedback' => $request->boolean('is_correct'),
            'teacher_feedback_at' => now(),
            'teacher_feedback_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Feedback guardado.');
    }

    public function refresh(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isStudent(), 404);
        $this->recommendations->getForUser($user, true);

        return back()->with('success', "Recomendación recalculada para {$user->name}.");
    }

    public function refreshClassroom(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom' => ['required', 'string'],
        ]);

        $canonical = Classroom::fromSlug($validated['classroom']);
        $stored = Classroom::storedValuesFor($canonical);

        $students = User::query()
            ->where('role', 'estudiante')
            ->where('is_active', 1)
            ->whereHas('studentProfile', fn ($q) => $q->whereIn('classroom', $stored))
            ->get();

        foreach ($students as $student) {
            try {
                $this->recommendations->getForUser($student, true);
            } catch (\Throwable) {
                continue;
            }
        }

        return back()->with('success', 'Recálculo del paralelo terminado ('.$students->count().' estudiantes).');
    }
}
