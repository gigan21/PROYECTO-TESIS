<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Enums\QuestionBlockType;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\StudentQuestionAnswer;
use App\Models\User;
use App\Services\Analytics\LearningLogSyncService;
use App\Services\Gamification\XpAwardService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HardQuestionEvaluationService
{
    public function __construct(
        private readonly HardAnswerNormalizer $normalizer,
        private readonly HardFormulaClozeParser $clozeParser,
        private readonly XpAwardService $xpAwards,
        private readonly LearningLogSyncService $logSync
    ) {}

    /**
     * @param  array<int|string, string|array<int, string>>  $stepAnswers
     */
    public function evaluate(User $student, Question $question, array $stepAnswers, int $timeTaken): StudentQuestionAnswer
    {
        if (! $question->is_active || ! $question->topic?->is_active) {
            throw new RuntimeException('Esta pregunta no está disponible.');
        }

        $answer = DB::transaction(function () use ($student, $question, $stepAnswers, $timeTaken) {
            $question->loadMissing(['options' => fn ($q) => $q->ordered()]);
            $options = $question->options;

            $finalBlocks = $options->filter(fn (QuestionOption $option) => $option->block_type === QuestionBlockType::Input);
            if ($finalBlocks->isEmpty()) {
                throw new RuntimeException('Esta pregunta no tiene respuesta final configurada.');
            }

            $isFinalCorrect = true;
            foreach ($finalBlocks as $option) {
                $submitted = $stepAnswers[$option->id] ?? $stepAnswers[(string) $option->id] ?? null;
                if (! is_string($submitted) || ! $this->inputMatches($option, $submitted)) {
                    $isFinalCorrect = false;
                    break;
                }
            }

            $isProcedureCorrect = true;
            foreach ($options as $option) {
                if ($option->block_type !== QuestionBlockType::Formula) {
                    continue;
                }

                $expected = $this->clozeParser->extractExpectedValues((string) $option->content);
                if ($expected === []) {
                    continue;
                }

                $submitted = $stepAnswers[$option->id] ?? $stepAnswers[(string) $option->id] ?? null;
                if (! is_array($submitted) || ! $this->formulaMatches($option, $expected, $submitted)) {
                    $isProcedureCorrect = false;
                    break;
                }
            }

            if ($isFinalCorrect && ! $isProcedureCorrect) {
                throw new RuntimeException('Respuesta correcta, pero el procedimiento tiene errores. Revisa tus pasos.');
            }

            $isCorrect = $isFinalCorrect && $isProcedureCorrect;

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
        }); // <-- Aquí termina y se cierra la transacción de base de datos rápido

        // AHORA sincronizamos la analítica (fuera del bloqueo de la base de datos)
        $this->logSync->syncForQuestion($student, $question, $timeTaken);

        return $answer;
    }

    /**
 * @param  list<array{value: string, tolerance: float|null}>  $expected
 * @param  array<int, string>  $submitted
 */
    private function formulaMatches(QuestionOption $option, array $expected, array $submitted): bool
    {
        if (count($submitted) !== count($expected)) {
            \Log::info('FALLO: cantidad distinta', [
                'expected_count' => count($expected),
                'submitted_count' => count($submitted),
                'expected' => $expected,
                'submitted' => $submitted,
            ]);
            return false;
        }
    
        $blockTolerance = $option->tolerance ?? 0.0;
    
        foreach ($expected as $index => $item) {
            $studentRaw = $submitted[$index] ?? null;
            if ($studentRaw === null || $studentRaw === '') {
                \Log::info('FALLO: hueco vacío', ['index' => $index, 'raw' => $studentRaw]);
                return false;
            }
    
            $expectedValue = $this->toFloat($item['value']);
            $studentValue  = $this->toFloat($studentRaw);
    
            if ($expectedValue === null || $studentValue === null) {
                \Log::info('FALLO: no numérico', [
                    'index' => $index,
                    'expected_raw' => $item['value'],
                    'expected_float' => $expectedValue,
                    'student_raw' => $studentRaw,
                    'student_float' => $studentValue,
                ]);
                return false;
            }
    
            $tolerance = $item['tolerance'] ?? $blockTolerance;
    
            if (abs($studentValue - $expectedValue) > $tolerance) {
                \Log::info('FALLO: fuera de tolerancia', [
                    'index' => $index,
                    'expected' => $expectedValue,
                    'student' => $studentValue,
                    'diff' => abs($studentValue - $expectedValue),
                    'tolerance' => $tolerance,
                ]);
                return false;
            }
        }
    
        return true;
    }

/** Conviertir "13,33" o "13.33" a float de forma segura */
private function toFloat(string $value): ?float
{
    // Quitar caracteres invisibles (zero-width spaces)
    $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);
    // Quitar espacios
    $value = trim($value);
    // Cambiar coma decimal por punto
    $normalized = str_replace(',', '.', $value);

    return is_numeric($normalized) ? (float) $normalized : null;
}
    
    private function inputMatches(QuestionOption $option, string $submitted): bool
    {
        $correct = (float) trim((string) $option->content);
        $normalizedSubmitted = $this->normalizer->normalize($submitted);
        $parsed = $this->normalizer->parseNumericAndUnit($normalizedSubmitted);

        $studentValue = $parsed !== null
            ? $parsed['value']
            : (float) trim($submitted);

        $tolerance = $option->tolerance ?? 0.0;
        
        // Validamos el valor numérico y la tolerancia configurada
        return abs($studentValue - $correct) <= $tolerance;
    }
}
