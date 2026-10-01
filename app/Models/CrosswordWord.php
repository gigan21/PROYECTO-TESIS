<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrosswordWord extends Model
{
    protected $fillable = [
        'user_id',
        'topic_id',
        'answer',
        'clue',
        'difficulty',
        'level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Docente que creó la palabra. */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder<self> $query */
    public function scopeOfDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty', $difficulty);
    }

    /** @param Builder<self> $query */
    public function scopeOfLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /** @param Builder<self> $query */
    public function scopeOfTopic(Builder $query, ?int $topicId): Builder
    {
        if ($topicId === null) {
            return $query->whereNull('topic_id');
        }

        return $query->where('topic_id', $topicId);
    }

    /** Normaliza respuestas: minúsculas, sin espacios ni tildes. */
    public static function normalizeAnswer(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $noSpaces = preg_replace('/\s+/u', '', $lower) ?? $lower;

        return strtr($noSpaces, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
    }

    protected function setAnswerAttribute(?string $value): void
    {
        $this->attributes['answer'] = $value === null ? null : self::normalizeAnswer($value);
    }
}
