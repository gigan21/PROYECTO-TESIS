<?php

namespace App\Services\Challenges;

use App\Models\ChallengeAnswer;
use App\Models\ChallengeRoom;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\Questions\HardQuestionEvaluationService;
use App\Services\Questions\RecordStudentAnswerService;
use App\Services\Questions\StepPuzzleService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RecordChallengeAnswerService
{
    public function __construct(
        private readonly ChallengeAccessService $access,
        private readonly RecordStudentAnswerService $studentAnswers,
        private readonly StepPuzzleService $puzzleService,
        private readonly HardQuestionEvaluationService $hardEvaluator,
    ) {}

    /**
     * Registra la respuesta de un estudiante en un desafío, delegando
     * a los servicios del quiz libre según la dificultad.
     */
    public function record(
        User $student,
        ChallengeRoom $room,
        Question $question,
        ?QuestionOption $option,
        ?array $orderedIds,
        ?array $stepAnswers,
        int $responseTimeSeconds
    ): ChallengeAnswer {
        $this->access->assertStudentCanAnswer($student, $room);

        if (! $room->questions()->where('questions.id', $question->id)->exists()) {
            throw new RuntimeException('Esta pregunta no pertenece al desafío.');
        }

        $difficulty = $question->difficulty->value;
        $maxTime = max(1, (int) $question->time_limit_seconds);
        $responseTimeSeconds = max(1, min($responseTimeSeconds, $maxTime));

        return DB::transaction(function () use (
            $student, $room, $question, $option, $orderedIds, $stepAnswers, $responseTimeSeconds, $difficulty
        ) {
            // Evitar duplicados
            $exists = ChallengeAnswer::query()
                ->where('challenge_room_id', $room->id)
                ->where('student_id', $student->id)
                ->where('question_id', $question->id)
                ->exists();

            if ($exists) {
                throw new RuntimeException('Ya respondiste esta pregunta en el desafío.');
            }

            // Delegar según dificultad al servicio del quiz libre
            [$globalAnswer, $optionId] = match ($difficulty) {
                'Fácil'   => $this->evaluateEasy($student, $question, $option),
                'Medio'   => $this->evaluateMedium($student, $question, $orderedIds ?? [], $responseTimeSeconds),
                'Difícil' => $this->evaluateHard($student, $question, $stepAnswers ?? [], $responseTimeSeconds),
                default   => throw new RuntimeException('Dificultad no soportada.'),
            };

            return ChallengeAnswer::query()->create([
                'challenge_room_id' => $room->id,
                'student_id' => $student->id,
                'question_id' => $question->id,
                'question_option_id' => $optionId,
                'is_correct' => $globalAnswer->is_correct,
                'xp_earned' => $globalAnswer->xp_earned,
                'response_time_seconds' => $responseTimeSeconds,
                'answered_at' => now(),
            ]);
        });
    }

    /** @return array{0: \App\Models\StudentQuestionAnswer, 1: int|null} */
    private function evaluateEasy(User $student, Question $question, ?QuestionOption $option): array
    {
        if (! $option || $option->question_id !== $question->id) {
            throw new RuntimeException('La opción no pertenece a esta pregunta.');
        }

        $answer = $this->studentAnswers->record($student, $question, $option);

        return [$answer, $option->id];
    }

    /** @return array{0: \App\Models\StudentQuestionAnswer, 1: int|null} */
    private function evaluateMedium(User $student, Question $question, array $orderedIds, int $timeTaken): array
    {
        if ($orderedIds === []) {
            throw new RuntimeException('Debes ordenar los pasos antes de enviar.');
        }

        $answer = $this->puzzleService->evaluate($student, $question, $orderedIds, $timeTaken);

        return [$answer, $answer->question_option_id];
    }

    /** @return array{0: \App\Models\StudentQuestionAnswer, 1: int|null} */
    private function evaluateHard(User $student, Question $question, array $stepAnswers, int $timeTaken): array
    {
        if ($stepAnswers === []) {
            throw new RuntimeException('Debes completar los pasos antes de enviar.');
        }

        $answer = $this->hardEvaluator->evaluate($student, $question, $stepAnswers, $timeTaken);

        return [$answer, $answer->question_option_id];
    }
}