<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\ChartRaceEvent;
use App\Models\ChartRaceSession;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Support\Classroom;
use Illuminate\Support\Collection;
class ChartRaceAnalyticsService
{
    /** @return array{total_sessions: int, avg_accuracy: float, unique_players: int} */
    public function globalOverview(): array
    {
        $row = ChartRaceSession::query()
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

    /** @return list<array{classroom: string, slug: string, students: int, sessions: int, avg_accuracy: float, avg_time: float}> */
    public function byClassroom(): array
    {
        $rows = ChartRaceSession::query()
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

    /** @return list<array{user: User, sessions: int, avg_accuracy: float, avg_time: float, wins: int, last_played_human: ?string, trend: string}> */
    public function byStudent(string $canonical): array
    {
        $sessions = ChartRaceSession::query()
            ->withProgress()
            ->forClassroom($canonical)
            ->with('user.studentProfile')
            ->recent()
            ->get()
            ->groupBy('user_id');

        $cards = [];
        foreach ($sessions as $userId => $userSessions) {
            /** @var Collection<int, ChartRaceSession> $userSessions */
            $user = $userSessions->first()->user;

            $cards[] = [
                'user' => $user,
                'sessions' => $userSessions->count(),
                'avg_accuracy' => round((float) $userSessions->avg('accuracy'), 1),
                'avg_time' => round((float) $userSessions->avg('total_time_sec'), 0),
                'wins' => $userSessions->where('won', true)->count(),
                'last_played_human' => $userSessions->first()->created_at?->diffForHumans(),
                'trend' => $this->studentAccuracyTrend((int) $userId),
            ];
        }

        usort($cards, fn ($a, $b) => strcmp($a['user']->name, $b['user']->name));

        return $cards;
    }

    /** @return Collection<int, ChartRaceSession> */
    public function studentTimeline(int $userId, int $limit = 10): Collection
    {
        return ChartRaceSession::query()
            ->withProgress()
            ->where('user_id', $userId)
            ->recent()
            ->limit($limit)
            ->get();
    }

    /** @return list<string> */
    public function studentWeakAreas(int $userId): array
    {
        $counts = [];
        $sessions = ChartRaceSession::query()->where('user_id', $userId)->get();
        foreach ($sessions as $session) {
            foreach ($session->weak_areas ?? [] as $topic) {
                if (! is_string($topic) || $topic === '') {
                    continue;
                }
                $counts[$topic] = ($counts[$topic] ?? 0) + 1;
            }
        }
        arsort($counts);

        return array_keys($counts);
    }

    /** @return array<string, float> */
    public function studentByType(int $userId): array
    {
        $rows = ChartRaceEvent::query()
            ->where('user_id', $userId)
            ->select('question_type')
            ->selectRaw('SUM(CASE WHEN correct = 1 THEN 1 ELSE 0 END) as ok')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('question_type')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $total = (int) $row->total;
            $out[(string) $row->question_type] = $total > 0
                ? round(((int) $row->ok / $total) * 100, 1)
                : 0.0;
        }

        return $out;
    }

    public function studentCoinsEarned(int $userId): int
    {
        return (int) CoinTransaction::query()
            ->where('user_id', $userId)
            ->where('source', 'chart_race')
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function studentAccuracyTrend(int $userId): string
    {
        $recent = ChartRaceSession::query()
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

    /** @return array{avg_accuracy: float, avg_time: float, sessions: int, wins: int} */
    public function studentAggregate(int $userId): array
    {
        $row = ChartRaceSession::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as sessions')
            ->selectRaw('AVG(accuracy) as avg_accuracy')
            ->selectRaw('AVG(total_time_sec) as avg_time')
            ->selectRaw('SUM(CASE WHEN won = 1 THEN 1 ELSE 0 END) as wins')
            ->first();

        return [
            'sessions' => (int) ($row->sessions ?? 0),
            'avg_accuracy' => round((float) ($row->avg_accuracy ?? 0), 1),
            'avg_time' => round((float) ($row->avg_time ?? 0), 0),
            'wins' => (int) ($row->wins ?? 0),
        ];
    }
}
