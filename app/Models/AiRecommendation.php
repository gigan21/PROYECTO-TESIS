<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'topic_id',
        'recommended_content_id',
        'reason',
        'confidence',
        'model_version',
        'is_completed',
        'teacher_feedback',
        'teacher_feedback_at',
        'teacher_feedback_by',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'confidence' => 'float',
            'teacher_feedback' => 'boolean',
            'teacher_feedback_at' => 'datetime',
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

    public function recommendedContent(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'recommended_content_id');
    }

    public function feedbackBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_feedback_by');
    }
}
