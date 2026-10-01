<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CrosswordLevel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrosswordProgress extends Model
{
    protected $fillable = [
        'user_id',
        'current_level',
        'max_level_reached',
        'learned_words',
        'total_correct_attempts',
        'total_wrong_attempts',
        'coins_earned',
        'last_played_at',
    ];

    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'max_level_reached' => 'integer',
            'learned_words' => 'array',
            'total_correct_attempts' => 'integer',
            'total_wrong_attempts' => 'integer',
            'coins_earned' => 'integer',
            'last_played_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Obtiene o crea el progreso del estudiante. */
    public static function forUser(int $userId): self
    {
        return self::query()->firstOrCreate(
            ['user_id' => $userId],
            [
                'current_level' => CrosswordLevel::MIN_LEVEL,
                'max_level_reached' => CrosswordLevel::MIN_LEVEL,
                'learned_words' => [],
            ]
        );
    }

    public function hasLearned(string $word): bool
    {
        $normalized = CrosswordWord::normalizeAnswer($word);
        $words = $this->learned_words ?? [];

        return in_array($normalized, $words, true);
    }

    public function markWordAsLearned(string $word): void
    {
        $normalized = CrosswordWord::normalizeAnswer($word);
        $words = $this->learned_words ?? [];

        if (! in_array($normalized, $words, true)) {
            $words[] = $normalized;
            $this->learned_words = $words;
            $this->save();
        }
    }

    public function addCoins(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->increment('coins_earned', $amount);
    }

    /** Recalcula monedas según aciertos totales (1 moneda cada 2 aciertos). */
    public function syncCoinsFromAttempts(): void
    {
        $this->coins_earned = CrosswordLevel::coinsForAttempts($this->total_correct_attempts);
        $this->save();
    }

    /** Cantidad de palabras aprendidas. */
    protected function learnedCount(): Attribute
    {
        return Attribute::get(fn (): int => count($this->learned_words ?? []));
    }
}
