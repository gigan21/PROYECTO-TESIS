<?php

namespace App\Services\Questions;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\StudentQuestionAnswer;
use App\Models\User;
use App\Services\Gamification\XpAwardService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RecordStudentAnswerService
{
    public function __construct(
        private readonly XpAwardService $xpAwards,
        private readonly \App\Services\Analytics\LearningLogSyncService $logSync
    ) {}

    public function record(User $student, Question $question, QuestionOption $option, int $timeTaken = 0): StudentQuestionAnswer
    {
        if (! $question->is_active || ! $question->topic?->is_active) {
            throw new RuntimeException('Esta pregunta no está disponible.');
        }

        if ($option->question_id !== $question->id) {
            throw new RuntimeException('La opción no pertenece a esta pregunta.');
        }

        $question->loadMissing('topic');

        return DB::transaction(function () use ($student, $question, $option, $timeTaken) { 
            
            $alreadyMastered = StudentQuestionAnswer::query()
                ->where('student_id', $student->id)
                ->where('question_id', $question->id)
                ->where('is_correct', true)
                ->exists();

            $isCorrect = $option->is_correct;
            $xpEarned = 0;

            if ($isCorrect && ! $alreadyMastered) {
                $xpEarned = $question->xp_reward;

                $this->xpAwards->award($student, $xpEarned);
            }

            $answer = StudentQuestionAnswer::query()->create([
                'student_id' => $student->id,
                'question_id' => $question->id,
                'question_option_id' => $option->id,
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