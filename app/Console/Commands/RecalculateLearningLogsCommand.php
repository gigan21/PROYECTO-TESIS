<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\StudentQuestionAnswer;
use App\Services\Analytics\LearningLogSyncService;
use Illuminate\Console\Command;

class RecalculateLearningLogsCommand extends Command
{
    protected $signature = 'learning-logs:recalculate-questions';

    protected $description = 'Recalcula contadores de learning_logs desde student_question_answers (sin alterar tiempos acumulados)';

    public function handle(LearningLogSyncService $sync): int
    {
        $pairs = StudentQuestionAnswer::query()
            ->join('questions', 'student_question_answers.question_id', '=', 'questions.id')
            ->select('student_question_answers.student_id', 'questions.topic_id')
            ->distinct()
            ->get();

        $bar = $this->output->createProgressBar($pairs->count());
        $bar->start();

        foreach ($pairs as $pair) {
            $sync->recalculateTopic((int) $pair->student_id, (int) $pair->topic_id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Recalculados {$pairs->count()} pares estudiante/tema.");

        return self::SUCCESS;
    }
}
