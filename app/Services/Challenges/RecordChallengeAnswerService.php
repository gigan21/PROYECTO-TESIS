<?php

namespace App\Services\Challenges;

use App\Models\ChallengeAnswer;
use App\Models\ChallengeRoom;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\Questions\RecordStudentAnswerService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RecordChallengeAnswerService
{
    public function __construct(
        private readonly ChallengeAccessService $access,
        private readonly RecordStudentAnswerService $studentAnswers
    ) {}

    public function record(
        User $student,
        ChallengeRoom $room,
        Question $question,
        QuestionOption $option,
        int $responseTimeSeconds
    ): ChallengeAnswer {
        $this->access->assertStudentCanAnswer($student, $room);

        if (! $room->questions()->where('questions.id', $question->id)->exists()) {
            throw new RuntimeException('Esta pregunta no pertenece al desafío.');
        }

        if ($option->question_id !== $question->id) {
            throw new RuntimeException('La opción no pertenece a esta pregunta.');
        }

        $maxTime = max(1, (int) $question->time_limit_seconds);
        $responseTimeSeconds = max(1, min($responseTimeSeconds, $maxTime));

        return DB::transaction(function () use ($student, $room, $question, $option, $responseTimeSeconds) {
            $exists = ChallengeAnswer::query()
                ->where('challenge_room_id', $room->id)
                ->where('student_id', $student->id)
                ->where('question_id', $question->id)
                ->exists();

            if ($exists) {
                throw new RuntimeException('Ya respondiste esta pregunta en el desafío.');
            }

            $globalAnswer = $this->studentAnswers->record($student, $question, $option);

            return ChallengeAnswer::query()->create([
                'challenge_room_id' => $room->id,
                'student_id' => $student->id,
                'question_id' => $question->id,
                'question_option_id' => $option->id,
                'is_correct' => $globalAnswer->is_correct,
                'xp_earned' => $globalAnswer->xp_earned,
                'response_time_seconds' => $responseTimeSeconds,
                'answered_at' => now(),
            ]);
        });
    }
}
