<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartRaceEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'round_number',
        'question_type',
        'graph_kind',
        'graph_type',
        'topic',
        'correct',
        'answered_in_seconds',
        'timed_out',
        'wrong_answer',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'correct' => 'boolean',
            'timed_out' => 'boolean',
            'round_number' => 'integer',
            'answered_in_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
