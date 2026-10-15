<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Analytics\ChartRaceAnalyticsService;
use App\Services\Analytics\CureAnalyticsService;
use App\Services\Projectiles\ProjectileAnalyticsService;
use App\Support\Classroom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteGamesController extends Controller
{
    public function __construct(
        private readonly ProjectileAnalyticsService $analytics,
        private readonly CureAnalyticsService $cureAnalytics,
        private readonly ChartRaceAnalyticsService $chartRaceAnalytics,
    ) {}

    public function index(): View
    {
        return view('docente.juegos.index', [
            'overview' => $this->analytics->globalOverview(),
            'cureOverview' => $this->cureAnalytics->globalOverview(),
            'chartRaceOverview' => $this->chartRaceAnalytics->globalOverview(),
        ]);
    }

    public function chartRaceClassrooms(): View
    {
        return view('docente.juegos.chart_race.classrooms', [
            'rows' => $this->chartRaceAnalytics->byClassroom(),
        ]);
    }

    public function chartRaceStudents(string $classroom): View
    {
        $canonical = Classroom::fromSlug($classroom);

        return view('docente.juegos.chart_race.students', [
            'classroom' => $canonical,
            'classroomSlug' => $classroom,
            'rows' => $this->chartRaceAnalytics->byStudent($canonical),
        ]);
    }

    public function chartRaceStudentDetail(string $classroom, User $user): View
    {
        $this->assertStudentAuthorized($classroom, $user);

        return view('docente.juegos.chart_race.student_detail', [
            'classroom' => Classroom::fromSlug($classroom),
            'classroomSlug' => $classroom,
            'student' => $user,
            'aggregate' => $this->chartRaceAnalytics->studentAggregate($user->id),
            'timeline' => $this->chartRaceAnalytics->studentTimeline($user->id, 10),
            'weakAreas' => $this->chartRaceAnalytics->studentWeakAreas($user->id),
            'byType' => $this->chartRaceAnalytics->studentByType($user->id),
            'coins_earned' => $this->chartRaceAnalytics->studentCoinsEarned($user->id),
        ]);
    }

    public function cureClassrooms(): View
    {
        return view('docente.juegos.cure.classrooms', [
            'rows' => $this->cureAnalytics->byClassroom(),
        ]);
    }

    public function cureStudents(string $classroom): View
    {
        $canonical = Classroom::fromSlug($classroom);

        return view('docente.juegos.cure.students', [
            'classroom' => $canonical,
            'classroomSlug' => $classroom,
            'rows' => $this->cureAnalytics->byStudent($canonical),
        ]);
    }

    public function cureStudentDetail(string $classroom, User $user): View
    {
        $this->assertStudentAuthorized($classroom, $user);

        $timeline = $this->cureAnalytics->studentTimeline($user->id, 10);

        return view('docente.juegos.cure.student_detail', [
            'classroom' => Classroom::fromSlug($classroom),
            'classroomSlug' => $classroom,
            'student' => $user,
            'aggregate' => $this->cureAnalytics->studentAggregate($user->id),
            'timeline' => $timeline,
            'formulas' => $this->cureAnalytics->studentFormulas($user->id),
            'errorsByType' => $this->cureAnalytics->studentErrorsByType($user->id),
            'coins_earned' => $this->cureAnalytics->studentCoinsEarned($user->id),
        ]);
    }

    public function projectilesClassrooms(): View
    {
        $paralelos = [];
        foreach (Classroom::all() as $classroom) {
            $paralelos[] = [
                'nombre' => $classroom,
                'slug' => Classroom::slugFor($classroom),
                'stats' => $this->analytics->classroomSummary($classroom),
            ];
        }

        return view('docente.juegos.projectiles.classrooms', compact('paralelos'));
    }

    public function projectilesStudents(Request $request, string $classroom): View
    {
        $canonical = Classroom::fromSlug($classroom);
        $cards = $this->analytics->studentsInClassroom($canonical);

        if ($request->filled('search')) {
            $term = mb_strtolower($request->string('search')->toString());
            $cards = array_values(array_filter($cards, function (array $row) use ($term) {
                $user = $row['user'];

                return str_contains(mb_strtolower($user->name), $term)
                    || str_contains(mb_strtolower($user->email), $term);
            }));
        }

        return view('docente.juegos.projectiles.students', [
            'classroom' => $canonical,
            'classroomSlug' => $classroom,
            'cards' => $cards,
        ]);
    }

    public function projectilesStudentDetail(string $classroom, User $user): View
    {
        $this->assertStudentAuthorized($classroom, $user);

        $sessions = $this->analytics->studentSessions($user->id);
        $chart = $this->analytics->studentChartSeries($user->id);
        $aggregate = $this->analytics->studentAggregate($user->id);
        $latest = $sessions->first();
        $analysis = $latest?->analysis_text ?? [];

        return view('docente.juegos.projectiles.student_detail', [
            'classroom' => Classroom::fromSlug($classroom),
            'classroomSlug' => $classroom,
            'student' => $user,
            'sessions' => $sessions,
            'chart' => $chart,
            'aggregate' => $aggregate,
            'win_streak' => $this->analytics->studentWinStreak($user->id),
            'accuracy_trend' => $this->analytics->studentAccuracyTrend($user->id),
            'analysis' => is_array($analysis) ? $analysis : [],
        ]);
    }

    private function assertStudentAuthorized(string $classroomSlug, User $user): void
    {
        abort_unless($user->isStudent(), 404);

        $canonical = Classroom::fromSlug($classroomSlug);
        $stored = Classroom::storedValuesFor($canonical);
        $profileClassroom = $user->studentProfile?->classroom;

        if ($profileClassroom === null || ! in_array($profileClassroom, $stored, true)) {
            abort(403);
        }
    }
}
