<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrosswordEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'crossword_word_id',
        'level',
        'word_attempted',
        'was_correct',
        'time_spent_seconds',
        'coins_awarded',
        'xp_awarded',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'was_correct' => 'boolean',
            'time_spent_seconds' => 'integer',
            'coins_awarded' => 'integer',
            'xp_awarded' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(CrosswordWord::class, 'crossword_word_id');
    }

    /** @param Builder<self> $query */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    /** @param Builder<self> $query */
    public function scopeOfLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }
}
