<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\LearningLog;
use Illuminate\Database\Eloquent\Builder;

class LearningLogAnalyticsService
{
    /**
     * @return array{
     *     global: array{avg_error_rate: float, avg_time_seconds: float, total_sessions: int, total_xp: int},
     *     by_classroom: list<array{classroom: string, avg_error_rate: float, avg_time_seconds: float, sessions: int}>,
     *     by_topic_game: list<array{topic: string, game_type: string, avg_error_rate: float, avg_time_seconds: float, sessions: int}>
     * }
     */
    public function summarize(): array
    {
        return [
            'global' => $this->globalMetrics(),
            'by_classroom' => $this->byClassroom(),
            'by_topic_game' => $this->byTopicAndGame(),
        ];
    }

    /**
     * @param  array{global: array, by_classroom: list, by_topic_game: list}  $analytics
     * @return array{classroom_errors: array, game_time: array, by_topic_game: list}
     */
    public function chartPayload(array $analytics): array
    {
        $byClassroom = $analytics['by_classroom'];
        $byGame = $this->aggregateTimeByGameType($analytics['by_topic_game']);

        $traducciones = [
            'crossword' => 'Crucigrama',
            'challenge' => 'Sala de Retos',
            'other' => 'Otros'
        ];

        //  Mapear las claves (crossword, challenge) usando el diccionario
        $etiquetasTraducidas = array_map(
            fn($key) => $traducciones[$key] ?? $key, 
            array_keys($byGame)
        );

        return [
            'classroom_errors' => [
                'labels' => array_column($byClassroom, 'classroom'),
                'data' => array_map(fn (array $row) => round($row['avg_error_rate'], 2), $byClassroom),
            ],
            'game_time' => [
                //  etiquetas ya en español  gráfico
                'labels' => $etiquetasTraducidas, 
                'data' => array_values($byGame),
            ],
            'by_topic_game' => $analytics['by_topic_game'],
        ];
    }

    /** @return array{avg_error_rate: float, avg_time_seconds: float, total_sessions: int, total_xp: int} */
    private function globalMetrics(): array
    {
        $row = $this->studentLogsQuery()
            ->selectRaw('AVG(error_rate_percentage) as avg_error_rate')
            ->selectRaw('AVG(total_time_seconds) as avg_time_seconds')
            ->selectRaw('COUNT(*) as total_sessions')
            ->selectRaw('COALESCE(SUM(earned_xp), 0) as total_xp')
            ->first();

        return [
            'avg_error_rate' => round((float) ($row->avg_error_rate ?? 0), 2),
            'avg_time_seconds' => round((float) ($row->avg_time_seconds ?? 0), 2),
            'total_sessions' => (int) ($row->total_sessions ?? 0),
            'total_xp' => (int) ($row->total_xp ?? 0),
        ];
    }

    /** @return list<array{classroom: string, avg_error_rate: float, avg_time_seconds: float, sessions: int}> */
    private function byClassroom(): array
    {
        return $this->studentLogsQuery()
            ->join('users', 'learning_logs.user_id', '=', 'users.id')
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->selectRaw('student_profiles.classroom as classroom')
            ->selectRaw('AVG(learning_logs.error_rate_percentage) as avg_error_rate')
            ->selectRaw('AVG(learning_logs.total_time_seconds) as avg_time_seconds')
            ->selectRaw('COUNT(*) as sessions')
            ->groupBy('student_profiles.classroom')
            ->orderBy('student_profiles.classroom')
            ->get()
            ->map(fn ($row) => [
                'classroom' => (string) $row->classroom,
                'avg_error_rate' => round((float) $row->avg_error_rate, 2),
                'avg_time_seconds' => round((float) $row->avg_time_seconds, 2),
                'sessions' => (int) $row->sessions,
            ])
            ->all();
    }

    /** @return list<array{topic: string, game_type: string, avg_error_rate: float, avg_time_seconds: float, sessions: int}> */
    private function byTopicAndGame(): array
    {
        // Diccionario de traducción
        $traducciones = [
            'crossword' => 'Crucigrama',
            'challenge' => 'Sala de Retos',
            'other' => 'Otros'
        ];

        return $this->studentLogsQuery()
            ->join('topics', 'learning_logs.topic_id', '=', 'topics.id')
            ->selectRaw('topics.name as topic')
            ->selectRaw('learning_logs.game_type as game_type')
            ->selectRaw('AVG(learning_logs.error_rate_percentage) as avg_error_rate')
            ->selectRaw('AVG(learning_logs.total_time_seconds) as avg_time_seconds')
            ->selectRaw('COUNT(*) as sessions')
            ->groupBy('topics.id', 'topics.name', 'learning_logs.game_type')
            ->orderBy('topics.name')
            ->orderBy('learning_logs.game_type')
            ->get()
            ->map(function ($row) use ($traducciones) {
                // Extraemos el valor limpio
                $gameTypeRaw = $row->game_type instanceof \BackedEnum ? $row->game_type->value : (string) $row->game_type;
                
                return [
                    'topic' => (string) $row->topic,
                    // Traducimos usando el diccionario, si no existe deja el original
                    'game_type' => $traducciones[$gameTypeRaw] ?? $gameTypeRaw,
                    'avg_error_rate' => round((float) $row->avg_error_rate, 2),
                    'avg_time_seconds' => round((float) $row->avg_time_seconds, 2),
                    'sessions' => (int) $row->sessions,
                ];
            })
            ->all();
    }

    /** @param  list<array{game_type: string, avg_time_seconds: float, sessions: int}>  $rows */
    private function aggregateTimeByGameType(array $rows): array
    {
        $weighted = [];
        foreach ($rows as $row) {
            $type = $row['game_type'];
            if (! isset($weighted[$type])) {
                $weighted[$type] = ['time' => 0.0, 'sessions' => 0];
            }
            $weighted[$type]['time'] += $row['avg_time_seconds'] * $row['sessions'];
            $weighted[$type]['sessions'] += $row['sessions'];
        }

        $result = [];
        foreach ($weighted as $type => $stats) {
            $result[$type] = $stats['sessions'] > 0
                ? round($stats['time'] / $stats['sessions'], 2)
                : 0.0;
        }

        ksort($result);

        return $result;
    }

    /** @return Builder<LearningLog> */
    private function studentLogsQuery(): Builder
    {
        return LearningLog::query()
            ->whereHas('user.studentProfile');
    }
}
