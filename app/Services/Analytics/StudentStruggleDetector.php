<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\ChartRaceSession;
use App\Models\CureGameSession;
use App\Models\CrosswordEvent;
use App\Models\ProjectileGameSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Avisos pedagógicos determinísticos (complemento al ML).
 *
 * @phpstan-type StruggleAlert array{game: string, severity: string, title: string, suggestion: string, icon: string, rule: string, last_played_at: int}
 * @phpstan-type NormalizedSession array{game: string, won: bool, accuracy: float, level_reached: int, created_at: Carbon, pista_usadas: int, errors_by_type: array<string, int>, telemetry_json: array<string, mixed>, by_type_accuracy: array<string, float>}
 */
class StudentStruggleDetector
{
    /** @return list<StruggleAlert> */
    public function detect(User $user): array
    {
        $data = $this->loadGameDataForUser($user->id);

        return $this->buildAlertsFromData($data);
    }

    /** @return StruggleAlert|null */
    public function detectForGame(User $user, string $game): ?array
    {
        $alerts = $this->detect($user);
        foreach ($alerts as $alert) {
            if ($alert['game'] === $game) {
                return $alert;
            }
        }

        return null;
    }

    /**
     * @param  iterable<int, User|null>  $users
     * @return array<int, list<StruggleAlert>>
     */
    public function detectForUsers(iterable $users): array
    {
        $userIds = [];
        foreach ($users as $user) {
            if ($user instanceof User) {
                $userIds[] = $user->id;
            }
        }
        $userIds = array_values(array_unique($userIds));
        if ($userIds === []) {
            return [];
        }

        $dataByUser = $this->loadGameDataBatch($userIds);
        $out = [];
        foreach ($userIds as $id) {
            $out[$id] = $this->buildAlertsFromData($dataByUser[$id] ?? $this->emptyGameData());
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyGameData(): array
    {
        return [
            'cure' => [],
            'projectile' => [],
            'chart_race' => [],
            'crossword_sessions' => [],
            'crossword_events' => [],
            'recency' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadGameDataForUser(int $userId): array
    {
        $batch = $this->loadGameDataBatch([$userId]);

        return $batch[$userId] ?? $this->emptyGameData();
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, array<string, mixed>>
     */
    private function loadGameDataBatch(array $userIds): array
    {
        $limit = (int) config('struggle_alerts.session_limit', 5);
        $eventLimit = (int) config('struggle_alerts.crossword_event_limit', 200);

        $cure = CureGameSession::query()
            ->whereIn('user_id', $userIds)
            ->orderByDesc('created_at')
            ->get([
                'user_id', 'won', 'accuracy', 'level_reached', 'created_at',
                'pista_usadas', 'errors_by_type', 'telemetry_json',
            ])
            ->groupBy('user_id');

        $projectile = ProjectileGameSession::query()
            ->whereIn('user_id', $userIds)
            ->orderByDesc('created_at')
            ->get([
                'user_id', 'result', 'accuracy', 'level_reached', 'created_at', 'telemetry_json',
            ])
            ->groupBy('user_id');

        $chart = ChartRaceSession::query()
            ->whereIn('user_id', $userIds)
            ->orderByDesc('created_at')
            ->get([
                'user_id', 'won', 'accuracy', 'created_at', 'by_type_accuracy', 'telemetry_json',
            ])
            ->groupBy('user_id');

        $events = CrosswordEvent::query()
            ->whereIn('user_id', $userIds)
            ->orderByDesc('created_at')
            ->get(['user_id', 'level', 'was_correct', 'created_at'])
            ->groupBy('user_id');

        $out = [];
        foreach ($userIds as $userId) {
            $cureSessions = $this->normalizeCureSessions(
                ($cure->get($userId) ?? collect())->sortByDesc('created_at')->take($limit)
            );
            $projectileSessions = $this->normalizeProjectileSessions(
                ($projectile->get($userId) ?? collect())->sortByDesc('created_at')->take($limit)
            );
            $chartSessions = $this->normalizeChartSessions(
                ($chart->get($userId) ?? collect())->sortByDesc('created_at')->take($limit)
            );
            $crosswordEvents = ($events->get($userId) ?? collect())->take($eventLimit)->values();
            $crosswordSessions = $this->buildCrosswordPseudoSessions($crosswordEvents);

            $recency = $this->gameRecencyTimestamps(
                $cureSessions,
                $projectileSessions,
                $chartSessions,
                $crosswordSessions
            );

            $out[$userId] = [
                'cure' => $cureSessions,
                'projectile' => $projectileSessions,
                'chart_race' => $chartSessions,
                'crossword_sessions' => $crosswordSessions,
                'crossword_events' => $crosswordEvents->values()->all(),
                'recency' => $recency,
            ];
        }

        return $out;
    }

    /**
     * @param  Collection<int, CureGameSession>  $rows
     * @return list<NormalizedSession>
     */
    private function normalizeCureSessions(Collection $rows): array
    {
        return $rows->map(function (CureGameSession $s) {
            $errors = is_array($s->errors_by_type) ? $s->errors_by_type : [];

            return [
                'game' => 'cure',
                'won' => (bool) $s->won,
                'accuracy' => (float) $s->accuracy,
                'level_reached' => (int) $s->level_reached,
                'created_at' => $s->created_at ?? now(),
                'pista_usadas' => (int) $s->pista_usadas,
                'errors_by_type' => [
                    'concepto' => (int) ($errors['concepto'] ?? 0),
                    'calculo' => (int) ($errors['calculo'] ?? 0),
                    'decimales' => (int) ($errors['decimales'] ?? 0),
                ],
                'telemetry_json' => [],
                'by_type_accuracy' => [],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, ProjectileGameSession>  $rows
     * @return list<NormalizedSession>
     */
    private function normalizeProjectileSessions(Collection $rows): array
    {
        return $rows->map(function (ProjectileGameSession $s) {
            $tel = is_array($s->telemetry_json) ? $s->telemetry_json : [];

            return [
                'game' => 'projectile',
                'won' => $s->result === 'win',
                'accuracy' => (float) $s->accuracy,
                'level_reached' => (int) $s->level_reached,
                'created_at' => $s->created_at ?? now(),
                'pista_usadas' => 0,
                'errors_by_type' => [],
                'telemetry_json' => $tel,
                'by_type_accuracy' => [],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, ChartRaceSession>  $rows
     * @return list<NormalizedSession>
     */
    private function normalizeChartSessions(Collection $rows): array
    {
        return $rows->map(function (ChartRaceSession $s) {
            $byType = is_array($s->by_type_accuracy) ? $s->by_type_accuracy : [];

            return [
                'game' => 'chart_race',
                'won' => (bool) $s->won,
                'accuracy' => (float) $s->accuracy,
                'level_reached' => 0,
                'created_at' => $s->created_at ?? now(),
                'pista_usadas' => 0,
                'errors_by_type' => [],
                'telemetry_json' => is_array($s->telemetry_json) ? $s->telemetry_json : [],
                'by_type_accuracy' => [
                    'identify' => (float) ($byType['identify'] ?? $byType['chart_race_accuracy_identify'] ?? 0),
                    'predict' => (float) ($byType['predict'] ?? $byType['chart_race_accuracy_predict'] ?? 0),
                    'choose' => (float) ($byType['choose'] ?? $byType['chart_race_accuracy_choose'] ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, CrosswordEvent>  $events
     * @return list<NormalizedSession>
     */
    private function buildCrosswordPseudoSessions(Collection $events): array
    {
        if ($events->isEmpty()) {
            return [];
        }

        $gapMinutes = (int) config('struggle_alerts.crossword_session_gap_minutes', 30);
        $sorted = $events->sortByDesc('created_at')->values();
        $groups = [];
        $current = [];

        foreach ($sorted as $event) {
            if ($current === []) {
                $current[] = $event;
                continue;
            }
            $prev = $current[count($current) - 1];
            $prevAt = $prev->created_at ?? now();
            $at = $event->created_at ?? now();
            if ($prevAt->diffInMinutes($at) > $gapMinutes) {
                $groups[] = $current;
                $current = [$event];
            } else {
                $current[] = $event;
            }
        }
        if ($current !== []) {
            $groups[] = $current;
        }

        $sessions = [];
        foreach ($groups as $group) {
            $attempts = count($group);
            $hits = 0;
            foreach ($group as $ev) {
                if ($ev->was_correct) {
                    $hits++;
                }
            }
            $accuracy = $attempts > 0 ? ($hits / $attempts) * 100 : 0;
            $lastLevel = (int) ($group[0]->level ?? 1);
            $sessions[] = [
                'game' => 'crossword',
                'won' => $attempts > 0 && $hits === $attempts,
                'accuracy' => $accuracy,
                'level_reached' => $lastLevel,
                'created_at' => $group[0]->created_at ?? now(),
                'pista_usadas' => 0,
                'errors_by_type' => [],
                'telemetry_json' => [],
                'by_type_accuracy' => [],
                'events' => $group,
            ];
        }

        return array_slice($sessions, 0, (int) config('struggle_alerts.session_limit', 5));
    }

    /**
     * @param  list<NormalizedSession>  ...$sessionLists
     * @return array<string, int>
     */
    private function gameRecencyTimestamps(array ...$sessionLists): array
    {
        $recency = [];
        foreach ($sessionLists as $list) {
            foreach ($list as $session) {
                $game = $session['game'];
                $ts = $session['created_at'] instanceof Carbon
                    ? $session['created_at']->getTimestamp()
                    : now()->getTimestamp();
                $recency[$game] = max($recency[$game] ?? 0, $ts);
            }
        }

        return $recency;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<StruggleAlert>
     */
    private function buildAlertsFromData(array $data): array
    {
        $candidates = [];
        $recency = $data['recency'] ?? [];

        foreach (config('struggle_alerts.games', []) as $gameKey => $_meta) {
            if ($gameKey === 'generic') {
                continue;
            }
            if ($gameKey === 'crossword') {
                $sessions = $data['crossword_sessions'] ?? [];
                $events = $data['crossword_events'] ?? [];
            } else {
                $sessions = $data[$gameKey] ?? [];
                $events = [];
            }

            if ($sessions === [] && $events === []) {
                continue;
            }

            $gameRecency = $recency[$gameKey] ?? 0;

            if ($alert = $this->ruleConsecutiveFailures($gameKey, $sessions, $events, $gameRecency)) {
                $candidates[] = $alert;
            }
            if ($alert = $this->ruleLowAccuracy($gameKey, $sessions, $gameRecency)) {
                $candidates[] = $alert;
            }
            if ($alert = $this->ruleNoWinsRecent($gameKey, $sessions, $gameRecency)) {
                $candidates[] = $alert;
            }
            if ($alert = $this->ruleStuckLevel($gameKey, $sessions, $events, $gameRecency)) {
                $candidates[] = $alert;
            }
            if ($gameKey === 'cure' && ($alert = $this->ruleHintsOveruse($sessions, $gameRecency))) {
                $candidates[] = $alert;
            }
        }

        $candidates = $this->dedupeByRuleAndGame($candidates);
        usort($candidates, function (array $a, array $b): int {
            $sev = $this->severityWeight($b['severity']) <=> $this->severityWeight($a['severity']);
            if ($sev !== 0) {
                return $sev;
            }

            return ($b['last_played_at'] ?? 0) <=> ($a['last_played_at'] ?? 0);
        });

        return array_slice($candidates, 0, (int) config('struggle_alerts.max_alerts', 2));
    }

    /**
     * @param  list<StruggleAlert>  $candidates
     * @return list<StruggleAlert>
     */
    private function dedupeByRuleAndGame(array $candidates): array
    {
        $seen = [];
        $out = [];
        foreach ($candidates as $alert) {
            $key = $alert['game'].'|'.$alert['rule'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $alert;
        }

        return $out;
    }

    private function severityWeight(string $severity): int
    {
        return match ($severity) {
            'high' => 3,
            'medium' => 2,
            default => 1,
        };
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @param  list<CrosswordEvent>|list<mixed>  $events
     * @return StruggleAlert|null
     */
    private function ruleConsecutiveFailures(string $game, array $sessions, array $events, int $gameRecency): ?array
    {
        $need = (int) config('struggle_alerts.thresholds.consecutive_failures', 3);

        if ($game === 'crossword' && $events !== []) {
            $sorted = collect($events)->sortByDesc('created_at')->take($need)->values();
            if ($sorted->count() < $need) {
                return null;
            }
            foreach ($sorted as $ev) {
                if ($ev->was_correct) {
                    return null;
                }
            }

            return $this->makeAlert($game, 'high', 'consecutive_failures', [
                'count' => $need,
            ], $gameRecency, $sessions);
        }

        $recent = array_slice($sessions, 0, $need);
        if (count($recent) < $need) {
            return null;
        }
        foreach ($recent as $session) {
            if ($session['won']) {
                return null;
            }
        }

        return $this->makeAlert($game, 'high', 'consecutive_failures', [
            'count' => $need,
        ], $gameRecency, $sessions);
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @return StruggleAlert|null
     */
    private function ruleLowAccuracy(string $game, array $sessions, int $gameRecency): ?array
    {
        $need = (int) config('struggle_alerts.thresholds.consecutive_failures', 3);
        $threshold = (float) config('struggle_alerts.thresholds.low_accuracy', 30);
        $recent = array_slice($sessions, 0, $need);
        if (count($recent) < $need) {
            return null;
        }
        $sum = 0.0;
        foreach ($recent as $session) {
            $sum += $session['accuracy'];
        }
        $avg = $sum / $need;
        if ($avg >= $threshold) {
            return null;
        }

        return $this->makeAlert($game, 'high', 'low_accuracy', [
            'accuracy' => (int) round($avg),
        ], $gameRecency, $sessions);
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @return StruggleAlert|null
     */
    private function ruleNoWinsRecent(string $game, array $sessions, int $gameRecency): ?array
    {
        $need = (int) config('struggle_alerts.thresholds.no_wins_recent', 5);
        $recent = array_slice($sessions, 0, $need);
        if (count($recent) < $need) {
            return null;
        }
        foreach ($recent as $session) {
            if ($session['won']) {
                return null;
            }
        }

        return $this->makeAlert($game, 'medium', 'no_wins_recent', [
            'count' => $need,
        ], $gameRecency, $sessions);
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @param  list<CrosswordEvent>|list<mixed>  $events
     * @return StruggleAlert|null
     */
    private function ruleStuckLevel(string $game, array $sessions, array $events, int $gameRecency): ?array
    {
        $need = (int) config('struggle_alerts.thresholds.stuck_level_failures', 3);

        if ($game === 'crossword' && $events !== []) {
            $sorted = collect($events)->sortByDesc('created_at')->take($need)->values();
            if ($sorted->count() < $need) {
                return null;
            }
            $level = (int) ($sorted->first()->level ?? 0);
            foreach ($sorted as $ev) {
                if ($ev->was_correct || (int) $ev->level !== $level) {
                    return null;
                }
            }

            return $this->makeAlert($game, 'medium', 'stuck_level', [
                'level' => $level,
            ], $gameRecency, $sessions);
        }

        $recent = array_slice($sessions, 0, $need);
        if (count($recent) < $need) {
            return null;
        }
        $level = (int) $recent[0]['level_reached'];
        foreach ($recent as $session) {
            if ($session['won'] || (int) $session['level_reached'] !== $level) {
                return null;
            }
        }

        return $this->makeAlert($game, 'medium', 'stuck_level', [
            'level' => $level,
        ], $gameRecency, $sessions);
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @return StruggleAlert|null
     */
    private function ruleHintsOveruse(array $sessions, int $gameRecency): ?array
    {
        $need = (int) config('struggle_alerts.thresholds.hints_sessions', 3);
        $recent = array_slice($sessions, 0, $need);
        if (count($recent) < $need) {
            return null;
        }
        foreach ($recent as $session) {
            if (($session['pista_usadas'] ?? 0) <= 0) {
                return null;
            }
        }

        return $this->makeAlert('cure', 'low', 'hints_overuse', [], $gameRecency, $sessions);
    }

    /**
     * @param  list<NormalizedSession>  $sessionsForSuggestion
     * @param  array<string, scalar>  $vars
     * @return StruggleAlert
     */
    private function makeAlert(
        string $game,
        string $severity,
        string $rule,
        array $vars,
        int $gameRecency,
        array $sessionsForSuggestion,
    ): array {
        $meta = config("struggle_alerts.games.{$game}", config('struggle_alerts.games.generic'));
        $vars['game'] = (string) ($meta['label'] ?? $game);

        $situationKey = match ($rule) {
            'hints_overuse' => 'hints_overuse',
            'stuck_level' => 'stuck_level',
            default => $rule,
        };
        $template = (string) config("struggle_alerts.messages.situation.{$situationKey}", '');
        $title = $this->formatSituation($template, $vars);

        return [
            'game' => $game,
            'severity' => $severity,
            'title' => $title,
            'suggestion' => $this->suggestionFor($game, $sessionsForSuggestion),
            'icon' => (string) ($meta['icon'] ?? '⚠️'),
            'rule' => $rule,
            'last_played_at' => $gameRecency,
        ];
    }

    /**
     * @param  array<string, scalar>  $vars
     */
    private function formatSituation(string $template, array $vars): string
    {
        $replace = [];
        foreach ($vars as $key => $value) {
            $replace[':'.$key] = (string) $value;
        }

        return strtr($template, $replace);
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     */
    private function suggestionFor(string $game, array $sessions): string
    {
        $recent = array_slice($sessions, 0, 3);
        $suggestions = config("struggle_alerts.messages.suggestions.{$game}")
            ?? config('struggle_alerts.messages.suggestions.generic', []);

        return match ($game) {
            'cure' => $this->suggestionCure($recent, $suggestions),
            'projectile' => $this->suggestionProjectile($recent, $suggestions),
            'chart_race' => $this->suggestionChartRace($recent, $suggestions),
            'crossword' => (string) ($suggestions['default'] ?? ''),
            default => (string) (config('struggle_alerts.messages.suggestions.generic.default') ?? ''),
        };
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @param  array<string, string>  $suggestions
     */
    private function suggestionCure(array $sessions, array $suggestions): string
    {
        $totals = ['concepto' => 0, 'calculo' => 0, 'decimales' => 0];
        foreach ($sessions as $session) {
            foreach ($totals as $key => $_) {
                $totals[$key] += (int) ($session['errors_by_type'][$key] ?? 0);
            }
        }
        $order = ['concepto', 'calculo', 'decimales'];
        $best = 'concepto';
        $bestVal = -1;
        foreach ($order as $key) {
            if ($totals[$key] > $bestVal) {
                $bestVal = $totals[$key];
                $best = $key;
            }
        }
        if ($bestVal > 0 && isset($suggestions[$best])) {
            return $suggestions[$best];
        }

        return (string) ($suggestions['default'] ?? '');
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @param  array<string, string>  $suggestions
     */
    private function suggestionProjectile(array $sessions, array $suggestions): string
    {
        $angleSum = 0.0;
        $angleN = 0;
        $powerSum = 0.0;
        $powerN = 0;
        $gravity = 0;
        foreach ($sessions as $session) {
            $tel = $session['telemetry_json'];
            if (isset($tel['avgAngle'])) {
                $angleSum += (float) $tel['avgAngle'];
                $angleN++;
            }
            if (isset($tel['avgPower'])) {
                $powerSum += (float) $tel['avgPower'];
                $powerN++;
            }
            $gravity += (int) ($tel['gravityChanges'] ?? 0);
        }
        $angleLow = (float) config('struggle_alerts.thresholds.projectile_angle_low', 45);
        $angleHigh = (float) config('struggle_alerts.thresholds.projectile_angle_high', 135);
        $powerLow = (float) config('struggle_alerts.thresholds.projectile_power_low', 50);
        $gravityTh = (int) config('struggle_alerts.thresholds.projectile_gravity_changes', 2);

        if ($angleN > 0) {
            $avgAngle = $angleSum / $angleN;
            if ($avgAngle < $angleLow && isset($suggestions['angle_low'])) {
                return $suggestions['angle_low'];
            }
            if ($avgAngle > $angleHigh && isset($suggestions['angle_high'])) {
                return $suggestions['angle_high'];
            }
        }
        if ($powerN > 0 && ($powerSum / $powerN) < $powerLow && isset($suggestions['power_low'])) {
            return $suggestions['power_low'];
        }
        if ($gravity > $gravityTh && isset($suggestions['gravity'])) {
            return $suggestions['gravity'];
        }

        return (string) ($suggestions['default'] ?? '');
    }

    /**
     * @param  list<NormalizedSession>  $sessions
     * @param  array<string, string>  $suggestions
     */
    private function suggestionChartRace(array $sessions, array $suggestions): string
    {
        if ($sessions === []) {
            return (string) ($suggestions['default'] ?? '');
        }
        $latest = $sessions[0];
        $byType = $latest['by_type_accuracy'] ?? [];
        $threshold = (float) config('struggle_alerts.thresholds.chart_type_accuracy_low', 40);
        $lowestKey = null;
        $lowestVal = 101.0;
        foreach (['predict', 'identify', 'choose'] as $key) {
            $val = (float) ($byType[$key] ?? 100);
            if ($val < $lowestVal) {
                $lowestVal = $val;
                $lowestKey = $key;
            }
        }
        if ($lowestKey !== null && $lowestVal < $threshold && isset($suggestions[$lowestKey])) {
            return $suggestions[$lowestKey];
        }

        return (string) ($suggestions['default'] ?? '');
    }
}
