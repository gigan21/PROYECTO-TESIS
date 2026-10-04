<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LearningGameType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class LearningLog extends Model
{
    protected $fillable = [
        'user_id',
        'topic_id',
        'game_type',
        'total_time_seconds',
        'error_rate_percentage',
        'earned_xp',
        'attempts',
        'correct_attempts',
        'skipped_attempts',
        'easy_attempts',
        'easy_errors',
        'easy_skips',
        'easy_time_seconds',
        'medium_attempts',
        'medium_errors',
        'medium_skips',
        'medium_time_seconds',
        'hard_attempts',
        'hard_errors',
        'hard_skips',
        'hard_time_seconds',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'game_type' => LearningGameType::class,
            'error_rate_percentage' => 'decimal:2',
            'total_time_seconds' => 'integer',
            'earned_xp' => 'integer',
            'attempts' => 'integer',
            'correct_attempts' => 'integer',
            'skipped_attempts' => 'integer',
            'easy_attempts' => 'integer',
            'easy_errors' => 'integer',
            'easy_skips' => 'integer',
            'easy_time_seconds' => 'integer',
            'medium_attempts' => 'integer',
            'medium_errors' => 'integer',
            'medium_skips' => 'integer',
            'medium_time_seconds' => 'integer',
            'hard_attempts' => 'integer',
            'hard_errors' => 'integer',
            'hard_skips' => 'integer',
            'hard_time_seconds' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /** Paralelo del estudiante (vía users → student_profiles). */
    public function studentProfile(): HasOneThrough
    {
        return $this->hasOneThrough(
            StudentProfile::class,
            User::class,
            'id',
            'user_id',
            'user_id',
            'id'
        );
    }

    public function getClassroomAttribute(): ?string
    {
        return $this->user?->studentProfile?->classroom;
    }
}
