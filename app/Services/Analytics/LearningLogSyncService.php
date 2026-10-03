<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\LearningLog;
use App\Models\Question;
use App\Models\StudentQuestionAnswer;
use App\Models\User;
use App\Enums\LearningGameType;

class LearningLogSyncService
{
    /**
     * Sincroniza el rendimiento del estudiante en la tabla learning_logs.
     */
    public function syncForQuestion(User $student, Question $question, int $timeTaken): void
    {
        // 1. Calcular historial separando aciertos, errores y saltos
        $stats = StudentQuestionAnswer::query()
            ->where('student_id', $student->id)
            ->whereHas('question', fn($q) => $q->where('topic_id', $question->topic_id))
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_attempts')
            ->selectRaw('SUM(CASE WHEN is_correct = 0 AND is_skipped = 0 THEN 1 ELSE 0 END) as wrong_attempts')
            ->selectRaw('SUM(CASE WHEN is_skipped = 1 THEN 1 ELSE 0 END) as skipped_attempts')
            ->selectRaw('SUM(xp_earned) as total_xp')
            ->first();

        $totalAttempts = (int) $stats->total_attempts;
        $wrongAttempts = (int) $stats->wrong_attempts;
        
        // La tasa de error excluye los saltos (saltar no es equivocarse por ignorancia, es omitir)
        $errorRate = $totalAttempts > 0 
            ? round(($wrongAttempts / $totalAttempts) * 100, 2) 
            : 0;

        $gameTypeValue = LearningGameType::Challenge->value; 

        // 2. Buscar o crear el Log
        $log = LearningLog::firstOrNew([
            'user_id' => $student->id,
            'topic_id' => $question->topic_id,
            'game_type' => $gameTypeValue,
        ]);

        // 3. Inyectar valores (incluyendo los nuevos campos)
        $log->total_time_seconds = ($log->total_time_seconds ?? 0) + $timeTaken;
        $log->error_rate_percentage = $errorRate;
        $log->earned_xp = (int) $stats->total_xp;
        $log->attempts = $totalAttempts;
        
        // NUEVOS CAMPOS AQUÍ SKIPEO:
        $log->correct_attempts = (int) $stats->correct_attempts;
        $log->skipped_attempts = (int) $stats->skipped_attempts;
        
        $log->completed_at = now();
        $log->save();
    }
}