<?php

namespace App\Models;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'topic_id',
        'difficulty',
        'type',
        'question_text',
        'xp_reward',
        'is_active',
        'time_limit_seconds',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => QuestionDifficulty::class,
            'type' => QuestionType::class,
            'xp_reward' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentQuestionAnswer::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('topic', fn (Builder $topicQuery) => $topicQuery->where('is_active', true));
    }
}
