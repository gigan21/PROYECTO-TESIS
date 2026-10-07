<?php

declare(strict_types=1);

namespace App\Services\Crossword;

use App\Enums\CrosswordLevel;
use App\Models\CrosswordEvent;
use App\Models\CrosswordProgress;
use App\Models\CrosswordWord;
use App\Models\User;
use App\Services\Gamification\CoinService;
use App\Services\Gamification\XpAwardService;
use Illuminate\Support\Facades\DB;

class CrosswordProgressService
{
    private const XP_PER_WORD = 2;

    public function __construct(
        private readonly XpAwardService $xpAwards,
        private readonly CoinService $coins,
    ) {}

    public function getOrCreate(User $user): CrosswordProgress
    {
        return CrosswordProgress::forUser($user->id);
    }

    /**
     * @return array{progress: array<string, mixed>, coins: int, xp: int, coins_delta: int}
     */
    public function recordWordLearned(User $user, string $word, int $level, int $timeSpent = 0): array
    {
        return DB::transaction(function () use ($user, $word, $level, $timeSpent) {
            $progress = CrosswordProgress::forUser($user->id);
            $normalized = CrosswordWord::normalizeAnswer($word);
            $wordModel = CrosswordWord::query()->where('answer', $normalized)->first();

            // Si ya la aprendió antes, no damos nada
            if ($progress->hasLearned($normalized)) {
                return [
                    'progress' => $this->toArray($progress),
                    'coins' => $this->coins->getBalance($user),
                    'xp' => 0,
                    'coins_delta' => 0,
                ];
            }

            // 1) Actualizar contadores LOCALES del crucigrama
            $oldCoinsEarned = $progress->coins_earned;
            $progress->markWordAsLearned($normalized);
            $progress->increment('total_correct_attempts');
            $progress->refresh();
            $progress->syncCoinsFromAttempts(); // recalcula coins_earned LOCAL (histórico del juego)
            $coinsDelta = $progress->coins_earned - $oldCoinsEarned;

            // 2) Sumar monedas al PERFIL (fuente de verdad) usando CoinService
            if ($coinsDelta > 0) {
                $this->coins->addCoins(
                    user: $user,
                    amount: $coinsDelta,
                    source: 'crossword',
                    description: "Palabra aprendida: {$normalized}",
                    metadata: [
                        'word' => $normalized,
                        'level' => $level,
                        'word_id' => $wordModel?->id,
                    ]
                );
            }

            // 3) Dar XP como siempre
            $xpAwarded = self::XP_PER_WORD;
            $this->xpAwards->award($user, $xpAwarded);

            $progress->last_played_at = now();
            $progress->save();

            // 4) Registrar evento del crucigrama (igual que antes)
            CrosswordEvent::query()->create([
                'user_id' => $user->id,
                'crossword_word_id' => $wordModel?->id,
                'level' => $level,
                'word_attempted' => $normalized,
                'was_correct' => true,
                'time_spent_seconds' => $timeSpent > 0 ? $timeSpent : null,
                'coins_awarded' => $coinsDelta,
                'xp_awarded' => $xpAwarded,
                'created_at' => now(),
            ]);

            return [
                'progress' => $this->toArray($progress->fresh()),
                'coins' => $this->coins->getBalance($user), // ← saldo REAL del perfil
                'xp' => $xpAwarded,
                'coins_delta' => $coinsDelta,
            ];
        });
    }

    public function recordWrongAttempt(User $user, string $word, int $level): CrosswordProgress
    {
        $progress = CrosswordProgress::forUser($user->id);
        $normalized = CrosswordWord::normalizeAnswer($word);

        $progress->increment('total_wrong_attempts');
        $progress->last_played_at = now();
        $progress->save();

        CrosswordEvent::query()->create([
            'user_id' => $user->id,
            'crossword_word_id' => CrosswordWord::query()->where('answer', $normalized)->value('id'),
            'level' => $level,
            'word_attempted' => $normalized,
            'was_correct' => false,
            'created_at' => now(),
        ]);

        return $progress->fresh();
    }

    public function advanceLevel(User $user, int $newLevel): CrosswordProgress
    {
        $newLevel = max(CrosswordLevel::MIN_LEVEL, min(CrosswordLevel::MAX_LEVEL, $newLevel));
        $progress = CrosswordProgress::forUser($user->id);
        $progress->current_level = $newLevel;
        $progress->max_level_reached = max($progress->max_level_reached, $newLevel);
        $progress->last_played_at = now();
        $progress->save();

        return $progress;
    }

    /**
     * Resetea el progreso del crucigrama.
     * ⚠️ NO toca las monedas del perfil: ya son del estudiante.
     */
    public function reset(User $user): CrosswordProgress
    {
        $progress = CrosswordProgress::forUser($user->id);
        $progress->fill([
            'current_level' => CrosswordLevel::MIN_LEVEL,
            'max_level_reached' => CrosswordLevel::MIN_LEVEL,
            'learned_words' => [],
            'total_correct_attempts' => 0,
            'total_wrong_attempts' => 0,
            'coins_earned' => 0, // solo reinicia el contador LOCAL del juego
            'last_played_at' => null,
        ]);
        $progress->save();

        return $progress;
    }

    /** @return array<string, mixed> */
    public function toArray(CrosswordProgress $progress): array
    {
        return [
            'current_level' => $progress->current_level,
            'max_level_reached' => $progress->max_level_reached,
            'learned_words' => $progress->learned_words ?? [],
            'learned_count' => $progress->learned_count,
            'total_correct_attempts' => $progress->total_correct_attempts,
            'total_wrong_attempts' => $progress->total_wrong_attempts,
            'coins_earned' => $progress->coins_earned,
            'last_played_at' => $progress->last_played_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function getStatistics(User $student): array
    {
        $progress = CrosswordProgress::query()->where('user_id', $student->id)->first();
        $events = CrosswordEvent::query()->where('user_id', $student->id);

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
            ],
            'progress' => $progress ? $this->toArray($progress) : null,
            'total_events' => (clone $events)->count(),
            'correct_events' => (clone $events)->where('was_correct', true)->count(),
            'avg_time_seconds' => (int) round((float) (clone $events)
                ->where('was_correct', true)
                ->whereNotNull('time_spent_seconds')
                ->avg('time_spent_seconds')),
            'recent_events' => CrosswordEvent::query()
                ->where('user_id', $student->id)
                ->recent()
                ->limit(20)
                ->get()
                ->map(fn (CrosswordEvent $event) => [
                    'word' => $event->word_attempted,
                    'level' => $event->level,
                    'was_correct' => $event->was_correct,
                    'time_spent_seconds' => $event->time_spent_seconds,
                    'xp_awarded' => $event->xp_awarded,
                    'created_at' => $event->created_at?->toIso8601String(),
                ]),
        ];
    }
}