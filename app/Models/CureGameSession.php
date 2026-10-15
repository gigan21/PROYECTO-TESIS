<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Classroom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CureGameSession extends Model
{
    protected $fillable = [
        'user_id',
        'learning_log_id',
        'classroom',
        'session_id',
        'level_reached',
        'niveles_completados',
        'attempts',
        'hits',
        'misses',
        'accuracy',
        'score',
        'rank',
        'max_streak',
        'won',
        'pista_usadas',
        'total_time_sec',
        'formulas_mastered',
        'formulas_review',
        'errors_by_type',
        'levels_json',
        'telemetry_json',
        'abandoned',
    ];

    protected function casts(): array
    {
        return [
            'accuracy' => 'float',
            'won' => 'boolean',
            'abandoned' => 'boolean',
            'formulas_mastered' => 'array',
            'formulas_review' => 'array',
            'errors_by_type' => 'array',
            'levels_json' => 'array',
            'telemetry_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningLog(): BelongsTo
    {
        return $this->belongsTo(LearningLog::class);
    }

    /** @param  Builder<CureGameSession>  $query */
    public function scopeForClassroom(Builder $query, string $canonical): Builder
    {
        return $query->whereIn('classroom', Classroom::storedValuesFor($canonical));
    }

    /** @param  Builder<CureGameSession>  $query */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    /** Sesiones con al menos un nivel completado o un acierto registrado. */
    public function scopeWithProgress(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('hits', '>', 0)
                ->orWhere('niveles_completados', '>', 0);
        });
    }
}
