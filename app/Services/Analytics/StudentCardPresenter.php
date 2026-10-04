<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\User;
use Illuminate\Support\Collection;

class StudentCardPresenter
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forStudents(Collection $students): array
    {
        return $students->map(fn (User $student) => $this->forStudent($student))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function forStudent(User $student): array
    {
        $profile = $student->studentProfile;
        $logs = $student->learningLogs;

        $totals = $this->sumLogBuckets($logs);
        $globalPrecision = $this->domainScore(
            $totals['attempts'],
            $totals['errors'],
            $totals['skips']
        );

        $avgTime = $logs->isNotEmpty()
            ? (int) round($logs->avg('total_time_seconds'))
            : 0;

        $modalPayload = [
            'name' => $student->name,
            'radar' => [
                'labels' => ['Dominio Fácil', 'Dominio Medio', 'Dominio Difícil'],
                'values' => [
                    $this->domainScore($totals['easy_attempts'], $totals['easy_errors'], $totals['easy_skips']),
                    $this->domainScore($totals['medium_attempts'], $totals['medium_errors'], $totals['medium_skips']),
                    $this->domainScore($totals['hard_attempts'], $totals['hard_errors'], $totals['hard_skips']),
                ],
            ],
            'failures' => $this->groupFailures($student),
        ];

        return [
            'id' => $student->id,
            'name' => $student->name,
            'avatar' => $profile?->avatar_name ?? 'default_avatar.png',
            'rating' => (int) ($profile?->xp_points ?? 0),
            'classroom' => $profile?->classroom,
            'precision' => $globalPrecision,
            'avg_time_seconds' => $avgTime,
            'modal' => $modalPayload,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\LearningLog>  $logs
     * @return array{
     *     attempts: int, errors: int, skips: int,
     *     easy_attempts: int, easy_errors: int, easy_skips: int,
     *     medium_attempts: int, medium_errors: int, medium_skips: int,
     *     hard_attempts: int, hard_errors: int, hard_skips: int
     * }
     */
    private function sumLogBuckets(Collection $logs): array
    {
        return [
            'attempts' => (int) $logs->sum('attempts'),
            'errors' => (int) $logs->sum('easy_errors')
                + (int) $logs->sum('medium_errors')
                + (int) $logs->sum('hard_errors'),
            'skips' => (int) $logs->sum('skipped_attempts'),
            'easy_attempts' => (int) $logs->sum('easy_attempts'),
            'easy_errors' => (int) $logs->sum('easy_errors'),
            'easy_skips' => (int) $logs->sum('easy_skips'),
            'medium_attempts' => (int) $logs->sum('medium_attempts'),
            'medium_errors' => (int) $logs->sum('medium_errors'),
            'medium_skips' => (int) $logs->sum('medium_skips'),
            'hard_attempts' => (int) $logs->sum('hard_attempts'),
            'hard_errors' => (int) $logs->sum('hard_errors'),
            'hard_skips' => (int) $logs->sum('hard_skips'),
        ];
    }

    private function domainScore(int $attempts, int $errors, int $skips): float
    {
        if ($attempts <= 0) {
            return 0.0;
        }

        return round(100 - ((($errors + $skips) / $attempts) * 100), 1);
    }

    /**
     * @return list<array{text: string, count: int}>
     */
    private function groupFailures(User $student): array
    {
        return $student->problemQuestionAnswers
            ->groupBy('question_id')
            ->map(function ($answers) {
                $question = $answers->first()?->question;

                return [
                    'text' => $question?->question_text ?? 'Pregunta eliminada',
                    'count' => $answers->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }
}
