<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\LearningGameType;
use App\Enums\QuestionDifficulty;
use App\Models\LearningLog;
use App\Models\Question;
use App\Models\StudentQuestionAnswer;
use App\Models\User;

class LearningLogSyncService
{
    /**
     * Sincroniza learning_logs para misiones de preguntas (game_type challenge).
     * Otros juegos pueden registrar filas con distinto game_type en el futuro.
     */
    public function syncForQuestion(User $student, Question $question, int $timeTaken): void
    {
        $stats = $this->aggregateForTopic($student->id, (int) $question->topic_id);

        $totalAttempts = (int) $stats->total_attempts;
        $wrongAttempts = (int) $stats->wrong_attempts;
        $errorRate = $totalAttempts > 0
            ? round(($wrongAttempts / $totalAttempts) * 100, 2)
            : 0;

        $log = LearningLog::firstOrNew([
            'user_id' => $student->id,
            'topic_id' => $question->topic_id,
            'game_type' => LearningGameType::Challenge->value,
        ]);

        $timeColumn = $this->timeColumnForDifficulty($question->difficulty);
        if ($timeTaken > 0) {
            $log->{$timeColumn} = (int) ($log->{$timeColumn} ?? 0) + $timeTaken;
            $log->total_time_seconds = (int) ($log->total_time_seconds ?? 0) + $timeTaken;
        }

        $log->error_rate_percentage = $errorRate;
        $log->earned_xp = (int) $stats->total_xp;
        $log->attempts = $totalAttempts;
        $log->correct_attempts = (int) $stats->correct_attempts;
        $log->skipped_attempts = (int) $stats->skipped_attempts;

        $log->easy_attempts = (int) $stats->easy_attempts;
        $log->easy_errors = (int) $stats->easy_errors;
        $log->easy_skips = (int) $stats->easy_skips;

        $log->medium_attempts = (int) $stats->medium_attempts;
        $log->medium_errors = (int) $stats->medium_errors;
        $log->medium_skips = (int) $stats->medium_skips;

        $log->hard_attempts = (int) $stats->hard_attempts;
        $log->hard_errors = (int) $stats->hard_errors;
        $log->hard_skips = (int) $stats->hard_skips;
        

        $log->completed_at = now();
        $log->save();
    }

    public function recalculateTopic(int $studentId, int $topicId): void
    {
        $question = Question::query()->where('topic_id', $topicId)->first();
        if ($question === null) {
            return;
        }

        $student = User::query()->find($studentId);
        if ($student === null) {
            return;
        }

        $this->syncForQuestion($student, $question, 0);
    }

    private function aggregateForTopic(int $studentId, int $topicId): object
    {
        $facil = QuestionDifficulty::Facil->value;
        $medio = QuestionDifficulty::Medio->value;
        $dificil = QuestionDifficulty::Dificil->value;

        return StudentQuestionAnswer::query()
            ->join('questions', 'student_question_answers.question_id', '=', 'questions.id')
            ->where('student_question_answers.student_id', $studentId)
            ->where('questions.topic_id', $topicId)
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('SUM(CASE WHEN student_question_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_attempts')
            ->selectRaw('SUM(CASE WHEN student_question_answers.is_correct = 0 AND student_question_answers.is_skipped = 0 THEN 1 ELSE 0 END) as wrong_attempts')
            ->selectRaw('SUM(CASE WHEN student_question_answers.is_skipped = 1 THEN 1 ELSE 0 END) as skipped_attempts')
            ->selectRaw('SUM(student_question_answers.xp_earned) as total_xp')
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? THEN 1 ELSE 0 END) as easy_attempts", [$facil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_correct = 0 AND student_question_answers.is_skipped = 0 THEN 1 ELSE 0 END) as easy_errors", [$facil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_skipped = 1 THEN 1 ELSE 0 END) as easy_skips", [$facil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? THEN 1 ELSE 0 END) as medium_attempts", [$medio])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_correct = 0 AND student_question_answers.is_skipped = 0 THEN 1 ELSE 0 END) as medium_errors", [$medio])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_skipped = 1 THEN 1 ELSE 0 END) as medium_skips", [$medio])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? THEN 1 ELSE 0 END) as hard_attempts", [$dificil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_correct = 0 AND student_question_answers.is_skipped = 0 THEN 1 ELSE 0 END) as hard_errors", [$dificil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_skipped = 1 THEN 1 ELSE 0 END) as hard_skips", [$dificil])
            ->first();
    }

    private function timeColumnForDifficulty(QuestionDifficulty $difficulty): string
    {
        return match ($difficulty) {
            QuestionDifficulty::Facil => 'easy_time_seconds',
            QuestionDifficulty::Medio => 'medium_time_seconds',
            QuestionDifficulty::Dificil => 'hard_time_seconds',
        };
    }
}
