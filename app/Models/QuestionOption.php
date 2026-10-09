<?php

namespace App\Models;

use App\Enums\QuestionBlockType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'block_type',
        'content',
        'unit',
        'tolerance',
        'sort_order',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'block_type' => QuestionBlockType::class,
            'tolerance' => 'float',
            'sort_order' => 'integer',
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentQuestionAnswer::class);
    }

    /** @param  Builder<QuestionOption>  $query */
    public function scopeInputs(Builder $query): Builder
    {
        return $query->where('block_type', QuestionBlockType::Input);
    }

    /** @param  Builder<QuestionOption>  $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
