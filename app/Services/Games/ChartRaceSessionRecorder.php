<?php

declare(strict_types=1);

namespace App\Services\Games;

use App\Enums\LearningGameType;
use App\Models\ChartRaceEvent;
use App\Models\ChartRaceSession;
use App\Models\LearningLog;
use App\Models\Topic;
use App\Models\User;
use App\Support\Classroom;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChartRaceSessionRecorder
{
    /**
     * @param  array<string, mixed>  $telemetry
     */
    public function upsert(User $user, array $telemetry): ChartRaceSession
    {
        $sessionId = $telemetry['sessionId'] ?? null;
        if (! is_string($sessionId) || $sessionId === '') {
            throw new InvalidArgumentException('telemetry.sessionId requerido');
        }

        $classroom = $this->resolveClassroom($user);
        $topicId = $this->resolveTopicId();
        $attributes = $this->mapSessionAttributes($user, $telemetry, $sessionId, $classroom);
        $logPayload = $this->mapLearningLogPayload($telemetry, $topicId);

        return DB::transaction(function () use ($user, $sessionId, $attributes, $logPayload) {
            $session = ChartRaceSession::query()->firstOrCreate(
                ['session_id' => $sessionId],
                array_merge($attributes, ['user_id' => $user->id])
            );

            $session->update($attributes);

            if ($session->learning_log_id) {
                LearningLog::query()
                    ->where('id', $session->learning_log_id)
                    ->update($logPayload);
            } else {
                $log = LearningLog::query()->create(array_merge(
                    ['user_id' => $user->id],
                    $logPayload
                ));
                $session->update(['learning_log_id' => $log->id]);
            }

            return $session->fresh();
        });
    }

    /**
     * @param  list<array<string, mixed>>  $events
     */
    public function syncEvents(User $user, string $sessionId, array $events): void
    {
        foreach ($events as $event) {
            if (! is_array($event)) {
                continue;
            }

            $round = (int) ($event['round_number'] ?? $event['roundNumber'] ?? 0);
            if ($round < 1 || $round > 6) {
                continue;
            }

            ChartRaceEvent::query()->updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'round_number' => $round,
                ],
                [
                    'user_id' => $user->id,
                    'question_type' => (string) ($event['question_type'] ?? $event['questionType'] ?? ''),
                    'graph_kind' => (string) ($event['graph_kind'] ?? $event['graphKind'] ?? ''),
                    'graph_type' => (string) ($event['graph_type'] ?? $event['graphType'] ?? ''),
                    'topic' => (string) ($event['topic'] ?? ''),
                    'correct' => filter_var($event['correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'answered_in_seconds' => (int) round((float) ($event['answered_in_seconds'] ?? $event['answeredInSeconds'] ?? 0)),
                    'timed_out' => filter_var($event['timed_out'] ?? $event['timedOut'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'wrong_answer' => isset($event['wrong_answer']) || isset($event['wrongAnswer'])
                        ? (string) ($event['wrong_answer'] ?? $event['wrongAnswer'])
                        : null,
                ]
            );
        }
    }

    private function resolveClassroom(User $user): ?string
    {
        $classroomRaw = $user->studentProfile?->classroom;
        if (! $classroomRaw) {
            return null;
        }

        try {
            return Classroom::normalize((string) $classroomRaw);
        } catch (\InvalidArgumentException) {
            return (string) $classroomRaw;
        }
    }

    private function resolveTopicId(): int
    {
        $topic = Topic::query()->firstOrCreate(
            ['name' => config('chart_race.topic_name')],
            [
                'description' => 'Interpretación de gráficas MRU/MRUV',
                'is_active' => true,
            ]
        );

        return (int) $topic->id;
    }

    /**
     * @param  array<string, mixed>  $telemetry
     * @return array<string, mixed>
     */
    private function mapSessionAttributes(User $user, array $telemetry, string $sessionId, ?string $classroom): array
    {
        $hits = (int) ($telemetry['hits'] ?? 0);
        $misses = (int) ($telemetry['misses'] ?? max(0, 6 - $hits));
        $accuracy = (float) ($telemetry['accuracy'] ?? 0);

        return [
            'user_id' => $user->id,
            'classroom' => $classroom,
            'session_id' => $sessionId,
            'hits' => $hits,
            'misses' => $misses,
            'accuracy' => $accuracy,
            'total_time_sec' => (int) ($telemetry['totalTimeSec'] ?? $telemetry['total_time_sec'] ?? 0),
            'exploded' => filter_var($telemetry['exploded'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'completed' => filter_var($telemetry['completed'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'won' => filter_var($telemetry['won'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'weak_areas' => $telemetry['weakAreas'] ?? $telemetry['weak_areas'] ?? [],
            'strong_areas' => $telemetry['strongAreas'] ?? $telemetry['strong_areas'] ?? [],
            'by_type_accuracy' => $telemetry['byTypeAccuracy'] ?? $telemetry['by_type_accuracy'] ?? [],
            'by_graph_kind_accuracy' => $telemetry['byGraphKindAccuracy'] ?? $telemetry['by_graph_kind_accuracy'] ?? [],
            'telemetry_json' => $telemetry,
        ];
    }

    /**
     * @param  array<string, mixed>  $telemetry
     * @return array<string, mixed>
     */
    private function mapLearningLogPayload(array $telemetry, int $topicId): array
    {
        $hits = (int) ($telemetry['hits'] ?? 0);
        $attempts = max($hits + (int) ($telemetry['misses'] ?? 0), $hits);
        $accuracy = (float) ($telemetry['accuracy'] ?? 0);
        $completed = filter_var($telemetry['completed'] ?? false, FILTER_VALIDATE_BOOLEAN);

        [$coins, $xp] = app(ChartRaceRewardCalculator::class)->calculate($telemetry);

        $payload = [
            'topic_id' => $topicId,
            'game_type' => LearningGameType::ChartRace->value,
            'total_time_seconds' => (int) ($telemetry['totalTimeSec'] ?? $telemetry['total_time_sec'] ?? 0),
            'error_rate_percentage' => round(100 - $accuracy, 2),
            'earned_xp' => $xp,
            'attempts' => $attempts,
            'correct_attempts' => $hits,
        ];

        if ($completed) {
            $payload['completed_at'] = now();
        }

        return $payload;
    }
}
