<?php

namespace App\Services\Questions;

use App\Models\Question;
use App\Models\StudentQuestionAnswer;
use App\Models\User;
use App\Services\Gamification\XpAwardService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HardQuestionEvaluationService
{
    public function __construct(
        private readonly HardAnswerNormalizer $normalizer,
        private readonly XpAwardService $xpAwards
    ) {}

    /**
     * @param  array<int|string, string>  $stepAnswers
     */
    public function evaluate(User $student, Question $question, array $stepAnswers, int $timeTaken): StudentQuestionAnswer
    {
        if (! $question->is_active || ! $question->topic?->is_active) {
            throw new RuntimeException('Esta pregunta no está disponible.');
        }

        return DB::transaction(function () use ($student, $question, $stepAnswers, $timeTaken) {
            $options = $question->options()->orderBy('id')->get();

            $isCorrect = true;
            foreach ($options as $option) {
                $submitted = $stepAnswers[$option->id] ?? $stepAnswers[(string) $option->id] ?? null;
                if ($submitted === null || ! $this->normalizer->matches($submitted, $option->option_text)) {
                    $isCorrect = false;
                    break;
                }
            }

            $alreadyMastered = StudentQuestionAnswer::query()
                ->where('student_id', $student->id)
                ->where('question_id', $question->id)
                ->where('is_correct', true)
                ->exists();

            $xpEarned = 0;

            if ($isCorrect && ! $alreadyMastered) {
                $xpEarned = $question->xp_reward;

                if ($timeTaken <= 15) {
                    $xpEarned += (int) ($xpEarned * 0.5);
                }

                $this->xpAwards->award($student, $xpEarned);
            }

            $firstOptionId = $options->first()?->id;

            return StudentQuestionAnswer::query()->create([
                'student_id' => $student->id,
                'question_id' => $question->id,
                'question_option_id' => $firstOptionId,
                'is_correct' => $isCorrect,
                'xp_earned' => $xpEarned,
                'answered_at' => now(),
            ]);
        });
    }
}
