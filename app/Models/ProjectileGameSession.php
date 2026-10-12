<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectileGameSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'result',
        'level_reached',
        'score',
        'total_time_seconds',
        'accuracy',
        'total_shots',
        'total_hits',
        'total_misses',
        'classroom',
        'telemetry_json',
        'analysis_text',
    ];

    protected function casts(): array
    {
        return [
            'level_reached' => 'integer',
            'score' => 'integer',
            'total_time_seconds' => 'integer',
            'accuracy' => 'decimal:2',
            'total_shots' => 'integer',
            'total_hits' => 'integer',
            'total_misses' => 'integer',
            'telemetry_json' => 'array',
            'analysis_text' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isWin(): bool
    {
        return $this->result === 'win';
    }
}
