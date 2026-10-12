<?php

declare(strict_types=1);

namespace App\Services\Projectiles;

use App\Models\ProjectileGameSession;
use App\Models\User;
use App\Support\Classroom;
use Illuminate\Support\Collection;

class ProjectileAnalyticsService
{
    /**
     * @return array{
     *     sessions: int,
     *     wins: int,
     *     avg_accuracy: float,
     *     avg_time_seconds: float,
     *     best_score: int
     * }
     */
    public function classroomSummary(string $canonicalClassroom): array
    {
        $stored = Classroom::storedValuesFor($canonicalClassroom);

        $query = ProjectileGameSession::query()->whereIn('classroom', $stored);

        $sessions = (clone $query)->count();
        $wins = (clone $query)->where('result', 'win')->count();

        return [
            'sessions' => $sessions,
            'wins' => $wins,
            'avg_accuracy' => round((float) (clone $query)->avg('accuracy'), 1),
            'avg_time_seconds' => round((float) (clone $query)->avg('total_time_seconds'), 0),
            'best_score' => (int) ((clone $query)->max('score') ?? 0),
        ];
    }

    /**
     * @return list<array{
     *     user: User,
     *     sessions: int,
     *     avg_accuracy: float,
     *     avg_time_seconds: float,
     *     best_score: int,
     *     last_played_at: ?\Illuminate\Support\Carbon,
     *     last_played_human: ?string,
     *     win_streak: int,
     *     accuracy_trend: string
     * }>
     */
    public function studentsInClassroom(string $canonicalClassroom): array
    {
        $stored = Classroom::storedValuesFor($canonicalClassroom);

        $students = User::query()
            ->where('role', 'estudiante')
            ->whereHas('studentProfile', fn ($q) => $q->whereIn('classroom', $stored))
            ->with('studentProfile')
            ->orderBy('name')
            ->get();

        return $students->map(function (User $user) {
            $stats = $this->studentAggregate($user->id);
            $last = $this->studentLastPlayed($user->id);

            return array_merge($stats, [
                'user' => $user,
                'last_played_at' => $last,
                'last_played_human' => $last?->diffForHumans(),
                'win_streak' => $this->studentWinStreak($user->id),
                'accuracy_trend' => $this->studentAccuracyTrend($user->id),
            ]);
        })->all();
    }

    /**
     * @return array{sessions: int, avg_accuracy: float, avg_time_seconds: float, best_score: int}
     */
    public function studentAggregate(int $userId): array
    {
        $query = ProjectileGameSession::query()->where('user_id', $userId);

        return [
            'sessions' => (clone $query)->count(),
            'avg_accuracy' => round((float) (clone $query)->avg('accuracy'), 1),
            'avg_time_seconds' => round((float) (clone $query)->avg('total_time_seconds'), 0),
            'best_score' => (int) ((clone $query)->max('score') ?? 0),
        ];
    }

    public function studentLastPlayed(int $userId): ?\Illuminate\Support\Carbon
    {
        $at = ProjectileGameSession::query()
            ->where('user_id', $userId)
            ->max('created_at');

        return $at ? \Illuminate\Support\Carbon::parse($at) : null;
    }

    public function studentWinStreak(int $userId): int
    {
        $results = ProjectileGameSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->pluck('result');

        $streak = 0;
        foreach ($results as $result) {
            if ($result === 'win') {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /** @return 'up'|'down'|'flat' */
    public function studentAccuracyTrend(int $userId): string
    {
        $accuracies = ProjectileGameSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->pluck('accuracy');

        if ($accuracies->count() < 6) {
            return 'flat';
        }

        $last5 = $accuracies->take(5);
        $prev5 = $accuracies->slice(5, 5);
        $avgLast = (float) $last5->avg();
        $avgPrev = (float) $prev5->avg();

        if ($avgLast > $avgPrev + 2) {
            return 'up';
        }
        if ($avgLast < $avgPrev - 2) {
            return 'down';
        }

        return 'flat';
    }

    /**
     * @return Collection<int, ProjectileGameSession>
     */
    public function studentSessions(int $userId, int $limit = 20): Collection
    {
        return ProjectileGameSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Datos para Chart.js: precisión y tiempo por sesión (orden cronológico).
     *
     * @return array{labels: list<string>, accuracy: list<float>, time_minutes: list<float>}
     */
    public function studentChartSeries(int $userId, int $limit = 15): array
    {
        $sessions = ProjectileGameSession::query()
            ->where('user_id', $userId)
            ->orderBy('created_at')
            ->limit($limit)
            ->get(['created_at', 'accuracy', 'total_time_seconds']);

        return [
            'labels' => $sessions->map(fn ($s) => $s->created_at->format('d/m H:i'))->all(),
            'accuracy' => $sessions->map(fn ($s) => (float) $s->accuracy)->all(),
            'time_minutes' => $sessions->map(fn ($s) => round($s->total_time_seconds / 60, 1))->all(),
        ];
    }

    public function globalOverview(): array
    {
        $total = ProjectileGameSession::query()->count();
        $wins = ProjectileGameSession::query()->where('result', 'win')->count();

        return [
            'total_sessions' => $total,
            'win_rate' => $total > 0 ? round($wins / $total * 100, 1) : 0,
            'avg_accuracy' => round((float) ProjectileGameSession::query()->avg('accuracy'), 1),
            'unique_players' => (int) ProjectileGameSession::query()->distinct('user_id')->count('user_id'),
        ];
    }
}
