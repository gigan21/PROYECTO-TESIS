<?php

declare(strict_types=1);

namespace App\Services\ML;

use App\Enums\QuestionDifficulty;
use App\Models\ChartRaceSession;
use App\Models\CureGameSession;
use App\Models\CrosswordEvent;
use App\Models\CrosswordProgress;
use App\Models\ProjectileGameSession;
use App\Models\StudentQuestionAnswer;
use App\Models\User;

/**
 * Features por juego: null = sin actividad; 0 = jugó y métrica cero.
 * Meta has_played_* siempre 0/1.
 */
class FeatureExtractorService
{
    private const RECENT_WINDOW = 10;

    private const CROSSWORD_GAP_MINUTES = 30;

    /** @var list<array{at: \Carbon\CarbonInterface, id: int, accuracy: float, failed: bool}> */
    private array $recentPartidas = [];

    /** @return array<string, mixed> */
    public function extractForUser(User $user): array
    {
        $user->loadMissing('studentProfile');
        $this->recentPartidas = [];

        $crossword = $this->extractCrossword($user->id);
        $cure = $this->extractCure($user->id);
        $projectile = $this->extractProjectile($user->id);
        $questions = $this->extractQuestions($user->id);
        $chartRace = $this->extractChartRace($user->id);

        $hasCrossword = $crossword !== null ? 1 : 0;
        $hasCure = $cure !== null ? 1 : 0;
        $hasProjectile = $projectile !== null ? 1 : 0;
        $hasQuestions = $questions !== null ? 1 : 0;
        $hasChartRace = $chartRace !== null ? 1 : 0;

        $cureSessions = (int) ($cure['cure_total_sessions'] ?? 0);
        $projectileSessions = (int) ($projectile['projectile_total_sessions'] ?? 0);
        $chartRaceSessions = (int) ($chartRace['chart_race_total_sessions'] ?? 0);
        $totalSessions = ($cure !== null ? $cureSessions : 0)
            + ($projectile !== null ? $projectileSessions : 0)
            + ($chartRace !== null ? $chartRaceSessions : 0);

        $profile = $user->studentProfile;

        $general = [
            'student_xp_points' => (int) ($profile?->xp_points ?? 0),
            'student_coins' => (int) ($profile?->coins ?? 0),
            'student_level' => $profile ? (int) $profile->level : 1,
            'total_sessions_all_games' => $totalSessions,
            'has_played_crossword' => $hasCrossword,
            'has_played_cure' => $hasCure,
            'has_played_projectile' => $hasProjectile,
            'has_played_questions' => $hasQuestions,
            'has_played_chart_race' => $hasChartRace,
            'games_played_count' => $hasCrossword + $hasCure + $hasProjectile + $hasQuestions + $hasChartRace,
        ];

        return array_merge(
            $crossword ?? $this->nullCrossword(),
            $cure ?? $this->nullCure(),
            $projectile ?? $this->nullProjectile(),
            $questions ?? $this->nullQuestions(),
            $chartRace ?? $this->nullChartRace(),
            $general,
            $this->recentWindowFeatures($questions),
        );
    }

    /** @return list<string> */
    public static function featureKeysV1(): array
    {
        return config('ml_features.feature_keys_v1', []);
    }

    /** @return array<string, null>|null */
    private function extractCrossword(int $userId): ?array
    {
        $progress = CrosswordProgress::query()->where('user_id', $userId)->first();
        $eventCount = (int) CrosswordEvent::query()->where('user_id', $userId)->count();

        $learnedWords = $progress !== null ? $this->parseJsonArray($progress->learned_words) : [];

        $hasProgressActivity = $progress !== null && (
            count($learnedWords) > 0
            || $progress->total_correct_attempts > 0
            || $progress->total_wrong_attempts > 0
            || $progress->last_played_at !== null
        );

        if (! $hasProgressActivity && $eventCount === 0) {
            return null;
        }

        $learnedCount = count($learnedWords);
        $groups = $this->crosswordGroups($userId);
        $groupCount = count($groups);
        $wrongRate = null;
        if ($groupCount > 0) {
            $accuracySum = 0.0;
            foreach ($groups as $group) {
                $accuracySum += $group['accuracy'];
                $this->recentPartidas[] = [
                    'at' => $group['at'],
                    'id' => $group['id'],
                    'accuracy' => $group['accuracy'],
                    'failed' => $group['failed'],
                ];
            }
            $wrongRate = round(1 - (($accuracySum / $groupCount) / 100), 4);
        } else {
            $correct = (int) ($progress?->total_correct_attempts ?? 0);
            $wrong = (int) ($progress?->total_wrong_attempts ?? 0);
            $attempts = $correct + $wrong;
            $wrongRate = $attempts > 0 ? round($wrong / $attempts, 4) : 0.0;
        }

        $eventStats = CrosswordEvent::query()
            ->where('user_id', $userId)
            ->selectRaw('AVG(CASE WHEN time_spent_seconds IS NOT NULL THEN time_spent_seconds END) as avg_time')
            ->selectRaw('COUNT(DISTINCT CASE WHEN was_correct = 1 AND level BETWEEN 1 AND 3 THEN word_attempted END) as words_1_3')
            ->selectRaw('COUNT(DISTINCT CASE WHEN was_correct = 1 AND level BETWEEN 4 AND 7 THEN word_attempted END) as words_4_7')
            ->first();

        return [
            'crossword_total_words_learned' => $learnedCount,
            'crossword_current_level' => $progress ? (int) $progress->current_level : null,
            'crossword_max_level_reached' => $progress ? (int) $progress->max_level_reached : null,
            'crossword_wrong_rate' => $wrongRate,
            'crossword_accuracy_last_10' => $groupCount > 0
                ? round(array_sum(array_column($groups, 'accuracy')) / $groupCount, 2)
                : null,
            'crossword_avg_time_per_word' => $eventStats->avg_time !== null
                ? round((float) $eventStats->avg_time, 2)
                : null,
            'crossword_words_level_1_3' => (int) ($eventStats->words_1_3 ?? 0),
            'crossword_words_level_4_7' => (int) ($eventStats->words_4_7 ?? 0),
        ];
    }

    /** @return array<string, mixed>|null */
    private function extractCure(int $userId): ?array
    {
        $total = (int) CureGameSession::query()->where('user_id', $userId)->count();
        if ($total === 0) {
            return null;
        }

        $sessions = CureGameSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::RECENT_WINDOW)
            ->get([
                'id', 'created_at', 'won', 'accuracy', 'hits',
                'errors_by_type', 'formulas_mastered', 'formulas_review',
            ]);

        $errors = ['concepto' => 0, 'calculo' => 0, 'decimales' => 0];
        $masteredCount = 0;
        $reviewCount = 0;
        $accuracySum = 0.0;
        $hitsSum = 0.0;
        $wins = 0;

        foreach ($sessions as $session) {
            $accuracySum += (float) $session->accuracy;
            $hitsSum += (float) $session->hits;
            $failed = ! (bool) $session->won;
            if (! $failed) {
                $wins++;
            }
            $this->recentPartidas[] = [
                'at' => $session->created_at ?? now(),
                'id' => (int) $session->id,
                'accuracy' => (float) $session->accuracy,
                'failed' => $failed,
            ];
            $err = $this->parseJsonArray($session->errors_by_type);
            foreach ($errors as $key => $_) {
                $errors[$key] += (int) ($err[$key] ?? 0);
            }

            foreach ($this->parseJsonArray($session->formulas_mastered) as $formula) {
                if (is_string($formula) && $formula !== '') {
                    $masteredCount++;
                }
            }

            foreach ($this->parseJsonArray($session->formulas_review) as $formula) {
                if (is_string($formula) && $formula !== '') {
                    $reviewCount++;
                }
            }
        }

        $window = max(1, $sessions->count());

        return [
            'cure_total_sessions' => $total,
            'cure_avg_accuracy' => round($accuracySum / $window, 2),
            'cure_accuracy_last_10' => round($accuracySum / $window, 2),
            'cure_won_rate' => round($wins / $window, 4),
            'cure_avg_hits' => round($hitsSum / $window, 2),
            'cure_errors_concepto' => $errors['concepto'],
            'cure_errors_calculo' => $errors['calculo'],
            'cure_errors_decimales' => $errors['decimales'],
            'cure_formulas_mastered_count' => $masteredCount,
            'cure_formulas_review_count' => $reviewCount,
        ];
    }

    /** @return array<string, mixed>|null */
    private function extractProjectile(int $userId): ?array
    {
        $total = (int) ProjectileGameSession::query()->where('user_id', $userId)->count();
        if ($total === 0) {
            return null;
        }

        $rows = ProjectileGameSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::RECENT_WINDOW)
            ->get(['id', 'created_at', 'result', 'accuracy', 'telemetry_json']);

        $angleSum = 0.0;
        $angleN = 0;
        $powerSum = 0.0;
        $powerN = 0;
        $gravityTotal = 0;
        $accuracySum = 0.0;
        $wins = 0;

        foreach ($rows as $row) {
            $accuracySum += (float) $row->accuracy;
            $failed = $row->result !== 'win';
            if (! $failed) {
                $wins++;
            }
            $this->recentPartidas[] = [
                'at' => $row->created_at ?? now(),
                'id' => (int) $row->id,
                'accuracy' => (float) $row->accuracy,
                'failed' => $failed,
            ];
            $tel = $this->parseJsonArray($row->telemetry_json);
            if (isset($tel['avgAngle'])) {
                $angleSum += (float) $tel['avgAngle'];
                $angleN++;
            }
            if (isset($tel['avgPower'])) {
                $powerSum += (float) $tel['avgPower'];
                $powerN++;
            }
            $gravityTotal += (int) ($tel['gravityChanges'] ?? 0);
        }

        $window = max(1, $rows->count());

        return [
            'projectile_total_sessions' => $total,
            'projectile_avg_accuracy' => round($accuracySum / $window, 2),
            'projectile_accuracy_last_10' => round($accuracySum / $window, 2),
            'projectile_avg_angle' => $angleN > 0 ? round($angleSum / $angleN, 2) : null,
            'projectile_avg_power' => $powerN > 0 ? round($powerSum / $powerN, 2) : null,
            'projectile_gravity_changes_total' => $gravityTotal,
            'projectile_won_rate' => round($wins / $window, 4),
        ];
    }

    /** @return array<string, mixed>|null */
    private function extractQuestions(int $userId): ?array
    {
        $facil = QuestionDifficulty::Facil->value;
        $medio = QuestionDifficulty::Medio->value;
        $dificil = QuestionDifficulty::Dificil->value;

        $row = StudentQuestionAnswer::query()
            ->join('questions', 'student_question_answers.question_id', '=', 'questions.id')
            ->where('student_question_answers.student_id', $userId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? THEN 1 ELSE 0 END) as att_easy", [$facil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? THEN 1 ELSE 0 END) as att_medium", [$medio])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? THEN 1 ELSE 0 END) as att_hard", [$dificil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_correct = 1 THEN 1 ELSE 0 END) as ok_easy", [$facil])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_correct = 1 THEN 1 ELSE 0 END) as ok_medium", [$medio])
            ->selectRaw("SUM(CASE WHEN questions.difficulty = ? AND student_question_answers.is_correct = 1 THEN 1 ELSE 0 END) as ok_hard", [$dificil])
            ->first();

        $total = (int) ($row->total ?? 0);
        if ($total === 0) {
            return null;
        }

        $attEasy = (int) ($row->att_easy ?? 0);
        $attMedium = (int) ($row->att_medium ?? 0);
        $attHard = (int) ($row->att_hard ?? 0);
        $easyWindow = $this->questionWindow($userId, $facil);
        $mediumWindow = $this->questionWindow($userId, $medio);
        $hardWindow = $this->questionWindow($userId, $dificil);

        return [
            'questions_attempts_easy' => $attEasy,
            'questions_attempts_medium' => $attMedium,
            'questions_attempts_hard' => $attHard,
            'questions_accuracy_easy' => $easyWindow['ratio'],
            'questions_accuracy_medium' => $mediumWindow['ratio'],
            'questions_accuracy_hard' => $hardWindow['ratio'],
            'questions_accuracy_easy_last_10' => $easyWindow['pct'],
            'questions_accuracy_medium_last_10' => $mediumWindow['pct'],
            'questions_accuracy_hard_last_10' => $hardWindow['pct'],
        ];
    }

    /** @return array<string, mixed>|null */
    private function extractChartRace(int $userId): ?array
    {
        $total = (int) ChartRaceSession::query()->where('user_id', $userId)->count();
        if ($total === 0) {
            return null;
        }

        $sessions = ChartRaceSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::RECENT_WINDOW)
            ->get(['id', 'created_at', 'won', 'accuracy', 'exploded', 'by_type_accuracy']);

        $types = ['identify', 'predict', 'choose'];
        $weighted = [];
        $accuracySum = 0.0;
        $exploded = 0;
        foreach ($sessions as $session) {
            $accuracySum += (float) $session->accuracy;
            if ($session->exploded) {
                $exploded++;
            }
            $this->recentPartidas[] = [
                'at' => $session->created_at ?? now(),
                'id' => (int) $session->id,
                'accuracy' => (float) $session->accuracy,
                'failed' => ! (bool) $session->won,
            ];
        }
        foreach ($types as $type) {
            $sum = 0.0;
            $weight = 0;
            foreach ($sessions as $session) {
                $byType = $this->parseJsonArray($session->by_type_accuracy);
                if (isset($byType[$type]) && is_numeric($byType[$type])) {
                    $sum += (float) $byType[$type];
                    $weight++;
                }
            }
            $weighted[$type] = $weight > 0 ? round($sum / $weight, 2) : null;
        }

        $window = max(1, $sessions->count());

        return [
            'chart_race_total_sessions' => $total,
            'chart_race_avg_accuracy' => round($accuracySum / $window, 2),
            'chart_race_accuracy_last_10' => round($accuracySum / $window, 2),
            'chart_race_accuracy_identify' => $weighted['identify'],
            'chart_race_accuracy_predict' => $weighted['predict'],
            'chart_race_accuracy_choose' => $weighted['choose'],
            'chart_race_exploded_rate' => round($exploded / $window, 4),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $questions
     * @return array<string, mixed>
     */
    private function recentWindowFeatures(?array $questions): array
    {
        $global = $this->recentPartidas;
        usort($global, function (array $a, array $b): int {
            $byTime = $b['at'] <=> $a['at'];
            if ($byTime !== 0) {
                return $byTime;
            }

            return $b['id'] <=> $a['id'];
        });
        $global = array_slice($global, 0, self::RECENT_WINDOW);
        $count = count($global);

        $accuracy = null;
        if ($count > 0) {
            $sum = 0.0;
            foreach ($global as $partida) {
                $sum += $partida['accuracy'];
            }
            $accuracy = round($sum / $count, 2);
        }

        $consecutive = false;
        if ($count >= 5) {
            $consecutive = true;
            foreach (array_slice($global, 0, 5) as $partida) {
                if (! $partida['failed']) {
                    $consecutive = false;
                    break;
                }
            }
        }

        $failures = 0;
        foreach ($global as $partida) {
            if ($partida['failed']) {
                $failures++;
            }
        }

        $features = [
            'accuracy_last_10' => $accuracy,
            'has_failed_5_consecutive' => $consecutive ? 1 : 0,
            'has_failed_5_of_10' => ($count >= 5 && $failures >= 5) ? 1 : 0,
        ];

        if ($questions === null) {
            $features['questions_accuracy_easy_last_10'] = null;
            $features['questions_accuracy_medium_last_10'] = null;
            $features['questions_accuracy_hard_last_10'] = null;
        }

        return $features;
    }

    /**
     * @return list<array{at: \Carbon\CarbonInterface, id: int, accuracy: float, failed: bool}>
     */
    private function crosswordGroups(int $userId): array
    {
        $events = CrosswordEvent::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(400)
            ->get(['id', 'created_at', 'was_correct']);

        if ($events->isEmpty()) {
            return [];
        }

        $rawGroups = [];
        $current = [];
        foreach ($events as $event) {
            if ($current === []) {
                $current[] = $event;
                continue;
            }
            $previous = $current[array_key_last($current)];
            $minutes = abs($previous->created_at->diffInMinutes($event->created_at));
            if ($minutes > self::CROSSWORD_GAP_MINUTES) {
                $rawGroups[] = $current;
                $current = [$event];
                if (count($rawGroups) >= self::RECENT_WINDOW) {
                    $current = [];
                    break;
                }
            } else {
                $current[] = $event;
            }
        }
        if ($current !== [] && count($rawGroups) < self::RECENT_WINDOW) {
            $rawGroups[] = $current;
        }

        $groups = [];
        foreach ($rawGroups as $group) {
            $attempts = count($group);
            $hits = 0;
            $maxId = 0;
            foreach ($group as $event) {
                if ($event->was_correct) {
                    $hits++;
                }
                $maxId = max($maxId, (int) $event->id);
            }
            $groups[] = [
                'at' => $group[0]->created_at,
                'id' => $maxId,
                'accuracy' => $attempts > 0 ? round(($hits / $attempts) * 100, 2) : 0.0,
                'failed' => $hits < $attempts,
            ];
        }

        return $groups;
    }

    /**
     * @return array{ratio: ?float, pct: ?float}
     */
    private function questionWindow(int $userId, string $difficulty): array
    {
        $rows = StudentQuestionAnswer::query()
            ->join('questions', 'student_question_answers.question_id', '=', 'questions.id')
            ->where('student_question_answers.student_id', $userId)
            ->where('questions.difficulty', $difficulty)
            ->orderByDesc('student_question_answers.answered_at')
            ->orderByDesc('student_question_answers.id')
            ->limit(self::RECENT_WINDOW)
            ->get(['student_question_answers.is_correct']);

        if ($rows->isEmpty()) {
            return ['ratio' => null, 'pct' => null];
        }

        $correct = $rows->where('is_correct', true)->count();
        $count = $rows->count();

        return [
            'ratio' => round($correct / $count, 4),
            'pct' => round(($correct / $count) * 100, 2),
        ];
    }

    /**
     * Parsea un valor que puede ser array, JSON string, o null.
     *
     * @return array<mixed>
     */
    private function parseJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /** @return array<string, null> */
    private function nullCrossword(): array
    {
        return [
            'crossword_total_words_learned' => null,
            'crossword_current_level' => null,
            'crossword_max_level_reached' => null,
            'crossword_wrong_rate' => null,
            'crossword_avg_time_per_word' => null,
            'crossword_words_level_1_3' => null,
            'crossword_words_level_4_7' => null,
        ];
    }

    /** @return array<string, null> */
    private function nullCure(): array
    {
        return [
            'cure_total_sessions' => null,
            'cure_avg_accuracy' => null,
            'cure_won_rate' => null,
            'cure_avg_hits' => null,
            'cure_errors_concepto' => null,
            'cure_errors_calculo' => null,
            'cure_errors_decimales' => null,
            'cure_formulas_mastered_count' => null,
            'cure_formulas_review_count' => null,
        ];
    }

    /** @return array<string, null> */
    private function nullProjectile(): array
    {
        return [
            'projectile_total_sessions' => null,
            'projectile_avg_accuracy' => null,
            'projectile_avg_angle' => null,
            'projectile_avg_power' => null,
            'projectile_gravity_changes_total' => null,
            'projectile_won_rate' => null,
        ];
    }

    /** @return array<string, null> */
    private function nullQuestions(): array
    {
        return [
            'questions_attempts_easy' => null,
            'questions_attempts_medium' => null,
            'questions_attempts_hard' => null,
            'questions_accuracy_easy' => null,
            'questions_accuracy_medium' => null,
            'questions_accuracy_hard' => null,
        ];
    }

    /** @return array<string, null> */
    private function nullChartRace(): array
    {
        return [
            'chart_race_total_sessions' => null,
            'chart_race_avg_accuracy' => null,
            'chart_race_accuracy_identify' => null,
            'chart_race_accuracy_predict' => null,
            'chart_race_accuracy_choose' => null,
            'chart_race_exploded_rate' => null,
        ];
    }
}
