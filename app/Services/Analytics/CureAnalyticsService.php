<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\CoinTransaction;
use App\Models\CureGameSession;
use App\Models\User;
use App\Support\Classroom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CureAnalyticsService
{
    /** @return array{total_sessions: int, avg_accuracy: float, unique_players: int} */
    public function globalOverview(): array
    {
        $row = CureGameSession::query()
            ->withProgress()
            ->selectRaw('COUNT(*) as total_sessions')
            ->selectRaw('AVG(accuracy) as avg_accuracy')
            ->selectRaw('COUNT(DISTINCT user_id) as unique_players')
            ->first();

        return [
            'total_sessions' => (int) ($row->total_sessions ?? 0),
            'avg_accuracy' => round((float) ($row->avg_accuracy ?? 0), 1),
            'unique_players' => (int) ($row->unique_players ?? 0),
        ];
    }

    /** @return list<array{classroom: string, students: int, sessions: int, avg_accuracy: float, avg_time: float}> */
    public function byClassroom(): array
    {
        $rows = CureGameSession::query()
            ->withProgress()
            ->whereNotNull('classroom')
            ->select('classroom')
            ->selectRaw('COUNT(DISTINCT user_id) as students')
            ->selectRaw('COUNT(*) as sessions')
            ->selectRaw('AVG(accuracy) as avg_accuracy')
            ->selectRaw('AVG(total_time_sec) as avg_time')
            ->groupBy('classroom')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            try {
                $canonical = Classroom::normalize((string) $row->classroom);
            } catch (\InvalidArgumentException) {
                continue;
            }
            if (! isset($grouped[$canonical])) {
                $grouped[$canonical] = [
                    'classroom' => $canonical,
                    'students' => 0,
                    'sessions' => 0,
                    'avg_accuracy' => 0.0,
                    'avg_time' => 0.0,
                    '_acc_sum' => 0.0,
                    '_time_sum' => 0.0,
                ];
            }
            $grouped[$canonical]['students'] += (int) $row->students;
            $grouped[$canonical]['sessions'] += (int) $row->sessions;
            $grouped[$canonical]['_acc_sum'] += (float) $row->avg_accuracy * (int) $row->sessions;
            $grouped[$canonical]['_time_sum'] += (float) $row->avg_time * (int) $row->sessions;
        }

        $out = [];
        foreach ($grouped as $item) {
            $sessions = max(1, $item['sessions']);
            $out[] = [
                'classroom' => $item['classroom'],
                'slug' => Classroom::slugFor($item['classroom']),
                'students' => $item['students'],
                'sessions' => $item['sessions'],
                'avg_accuracy' => round($item['_acc_sum'] / $sessions, 1),
                'avg_time' => round($item['_time_sum'] / $sessions, 0),
            ];
        }

        usort($out, fn ($a, $b) => strcmp($a['classroom'], $b['classroom']));

        return $out;
    }

    /** @return list<array{user: User, sessions: int, avg_accuracy: float, avg_time: float, best_score: int, common_rank: string, last_played_at: ?string, last_played_human: ?string, win_streak: int, trend: string}> */
    public function byStudent(string $canonical): array
    {
        $sessions = CureGameSession::query()
            ->withProgress()
            ->forClassroom($canonical)
            ->with('user.studentProfile')
            ->recent()
            ->get()
            ->groupBy('user_id');

        $cards = [];
        foreach ($sessions as $userId => $userSessions) {
            /** @var Collection<int, CureGameSession> $userSessions */
            $user = $userSessions->first()->user;
            $rankMode = $userSessions->groupBy('rank')->sortByDesc(fn ($g) => $g->count())->keys()->first();

            $cards[] = [
                'user' => $user,
                'sessions' => $userSessions->count(),
                'avg_accuracy' => round((float) $userSessions->avg('accuracy'), 1),
                'avg_time' => round((float) $userSessions->avg('total_time_sec'), 0),
                'best_score' => (int) $userSessions->max('score'),
                'common_rank' => (string) ($rankMode ?? '—'),
                'last_played_at' => $userSessions->first()->created_at?->toDateTimeString(),
                'last_played_human' => $userSessions->first()->created_at?->diffForHumans(),
                'win_streak' => $this->studentWinStreak((int) $userId),
                'trend' => $this->studentAccuracyTrend((int) $userId),
            ];
        }

        usort($cards, fn ($a, $b) => strcmp($a['user']->name, $b['user']->name));

        return $cards;
    }

    /** @return Collection<int, CureGameSession> */
    public function studentTimeline(int $userId, int $limit = 10): Collection
    {
        return CureGameSession::query()
            ->withProgress()
            ->where('user_id', $userId)
            ->recent()
            ->limit($limit)
            ->get();
    }

    /** @return array{mastered: list<string>, review: list<string>} */
    public function studentFormulas(int $userId): array
    {
        $sessions = CureGameSession::query()->where('user_id', $userId)->get();
        $mastered = [];
        $review = [];
        foreach ($sessions as $session) {
            foreach ($session->formulas_mastered ?? [] as $f) {
                $mastered[$f] = ($mastered[$f] ?? 0) + 1;
            }
            foreach ($session->formulas_review ?? [] as $f) {
                $review[$f] = ($review[$f] ?? 0) + 1;
            }
        }

        return [
            'mastered' => array_keys($mastered),
            'review' => array_keys($review),
            'review_counts' => $review,
        ];
    }

    /** @return array{concepto: int, calculo: int, decimales: int} */
    public function studentErrorsByType(int $userId): array
    {
        $totals = ['concepto' => 0, 'calculo' => 0, 'decimales' => 0];
        $sessions = CureGameSession::query()->where('user_id', $userId)->get();
        foreach ($sessions as $session) {
            $errors = $session->errors_by_type ?? [];
            foreach ($totals as $key => $_) {
                $totals[$key] += (int) ($errors[$key] ?? 0);
            }
        }

        return $totals;
    }

    public function studentCoinsEarned(int $userId): int
    {
        return (int) CoinTransaction::query()
            ->where('user_id', $userId)
            ->where('source', 'cure')
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function studentWinStreak(int $userId): int
    {
        $sessions = CureGameSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $streak = 0;
        foreach ($sessions as $session) {
            if ($session->won) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    public function studentAccuracyTrend(int $userId): string
    {
        $recent = CureGameSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->pluck('accuracy')
            ->map(fn ($v) => (float) $v)
            ->values();

        if ($recent->count() < 6) {
            return '→';
        }

        $last5 = $recent->slice(0, 5)->avg();
        $prev5 = $recent->slice(5, 5)->avg();

        if ($last5 > $prev5 + 2) {
            return '↑';
        }
        if ($last5 < $prev5 - 2) {
            return '↓';
        }

        return '→';
    }

    /** @return array{avg_accuracy: float, avg_time: float, sessions: int, best_score: int, common_rank: string} */
    public function studentAggregate(int $userId): array
    {
        $row = CureGameSession::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as sessions')
            ->selectRaw('AVG(accuracy) as avg_accuracy')
            ->selectRaw('AVG(total_time_sec) as avg_time')
            ->selectRaw('MAX(score) as best_score')
            ->first();

        $rank = CureGameSession::query()
            ->where('user_id', $userId)
            ->select('rank', DB::raw('COUNT(*) as c'))
            ->groupBy('rank')
            ->orderByDesc('c')
            ->value('rank');

        return [
            'sessions' => (int) ($row->sessions ?? 0),
            'avg_accuracy' => round((float) ($row->avg_accuracy ?? 0), 1),
            'avg_time' => round((float) ($row->avg_time ?? 0), 0),
            'best_score' => (int) ($row->best_score ?? 0),
            'common_rank' => (string) ($rank ?? '—'),
        ];
    }
}
