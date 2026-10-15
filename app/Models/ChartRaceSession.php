<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Classroom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartRaceSession extends Model
{
    protected $fillable = [
        'user_id',
        'learning_log_id',
        'classroom',
        'session_id',
        'hits',
        'misses',
        'accuracy',
        'total_time_sec',
        'exploded',
        'completed',
        'won',
        'weak_areas',
        'strong_areas',
        'by_type_accuracy',
        'by_graph_kind_accuracy',
        'telemetry_json',
    ];

    protected function casts(): array
    {
        return [
            'accuracy' => 'float',
            'exploded' => 'boolean',
            'completed' => 'boolean',
            'won' => 'boolean',
            'weak_areas' => 'array',
            'strong_areas' => 'array',
            'by_type_accuracy' => 'array',
            'by_graph_kind_accuracy' => 'array',
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

    public function events(): HasMany
    {
        return $this->hasMany(ChartRaceEvent::class, 'session_id', 'session_id');
    }

    /** @param  Builder<ChartRaceSession>  $query */
    public function scopeForClassroom(Builder $query, string $canonical): Builder
    {
        return $query->whereIn('classroom', Classroom::storedValuesFor($canonical));
    }

    /** @param  Builder<ChartRaceSession>  $query */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    /** @param  Builder<ChartRaceSession>  $query */
    public function scopeWithProgress(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('hits', '>', 0)
                ->orWhere('completed', true)
                ->orWhere('exploded', true);
        });
    }
}
