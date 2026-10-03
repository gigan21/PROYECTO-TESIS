<?php

namespace App\Services\Questions;

use App\Models\Question;
use App\Models\StudentQuestionAnswer;
use App\Models\User;
use App\Services\Gamification\XpAwardService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StepPuzzleService
{
    public function __construct(
        private readonly XpAwardService $xpAwards,
        private readonly \App\Services\Analytics\LearningLogSyncService $logSync
    ) {}

    public function evaluate(User $student, Question $question, array $submittedOptionIds, int $timeTaken): StudentQuestionAnswer
    {
        if (! $question->is_active || ! $question->topic?->is_active) {
            throw new RuntimeException('Esta pregunta no está disponible.');
        }

        return DB::transaction(function () use ($student, $question, $submittedOptionIds, $timeTaken) {
            
            // 1. Obtenemos las opciones en el orden original de la base de datos
            $options = $question->options()->orderBy('id')->get();
            $correctOrderIds = $options->pluck('id')->toArray();

            // 🛠️ SOLUCIÓN AQUÍ: Convertimos los IDs que envió el HTML a números enteros
            $submittedOptionIds = array_map('intval', $submittedOptionIds);

            // 2. Ahora la comparación estricta funcionará perfectamente
            $isCorrect = array_values($submittedOptionIds) === array_values($correctOrderIds);

            // 3. Verificamos si ya había ganado XP antes con esta pregunta
            $alreadyMastered = StudentQuestionAnswer::query()
                ->where('student_id', $student->id)
                ->where('question_id', $question->id)
                ->where('is_correct', true)
                ->exists();

            $xpEarned = 0;

            if ($isCorrect && ! $alreadyMastered) {
                $xpEarned = $question->xp_reward;
                
                // Bono de adrenalina si respondió rápido
                if ($timeTaken <= 15) {
                    $xpEarned += (int)($xpEarned * 0.5);
                }

                $this->xpAwards->award($student, $xpEarned);
            }

            // 4. Guardamos el intento
            $recordedOptionId = $isCorrect ? $correctOrderIds[0] : $submittedOptionIds[0];

            $answer = StudentQuestionAnswer::query()->create([
                'student_id' => $student->id,
                'question_id' => $question->id,
                'question_option_id' => $recordedOptionId,
                'is_correct' => $isCorrect,
                'xp_earned' => $xpEarned,
                'answered_at' => now(),
            ]);
            
            // Sincronizar con la IA / Analytics
            $this->logSync->syncForQuestion($student, $question, $timeTaken);

            return $answer;
        });
    }
}