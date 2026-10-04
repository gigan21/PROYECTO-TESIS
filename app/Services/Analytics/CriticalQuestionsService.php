<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\StudentQuestionAnswer;

class CriticalQuestionsService
{
    private const MIN_ATTEMPTS = 3;

    private const FAILURE_RATE_THRESHOLD = 0.40;

    /**
     * @return list<array{id: int, question_text: string, topic: string, failure_rate: float, attempts: int}>
     */
    public function topCritical(int $limit = 5): array
    {
        $rows = StudentQuestionAnswer::query()
            ->join('questions', 'student_question_answers.question_id', '=', 'questions.id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->select('questions.id')
            ->selectRaw('MAX(questions.question_text) as question_text')
            ->selectRaw('MAX(topics.name) as topic_name')
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('SUM(CASE WHEN student_question_answers.is_correct = 0 AND student_question_answers.is_skipped = 0 THEN 1 ELSE 0 END) as total_errors')
            ->selectRaw('SUM(CASE WHEN student_question_answers.is_skipped = 1 THEN 1 ELSE 0 END) as total_skips')
            ->groupBy('questions.id')
            ->having('total_attempts', '>=', self::MIN_ATTEMPTS)
            ->get();

        return $rows
            ->map(function ($row) {
                $attempts = (int) $row->total_attempts;
                $failures = (int) $row->total_errors + (int) $row->total_skips;
                $rate = $attempts > 0 ? $failures / $attempts : 0;

                return [
                    'id' => (int) $row->id,
                    'question_text' => (string) $row->question_text,
                    'topic' => (string) $row->topic_name,
                    'failure_rate' => round($rate * 100, 1),
                    'attempts' => $attempts,
                    '_rate' => $rate,
                ];
            })
            ->filter(fn (array $item) => $item['_rate'] >= self::FAILURE_RATE_THRESHOLD)
            ->sortByDesc('_rate')
            ->take($limit)
            ->map(fn (array $item) => [
                'id' => $item['id'],
                'question_text' => $item['question_text'],
                'topic' => $item['topic'],
                'failure_rate' => $item['failure_rate'],
                'attempts' => $item['attempts'],
            ])
            ->values()
            ->all();
    }
}
