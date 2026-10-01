<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeAnswer extends Model
{
    protected $fillable = [
        'challenge_room_id',
        'student_id',
        'question_id',
        'question_option_id',
        'is_correct',
        'xp_earned',
        'response_time_seconds',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'xp_earned' => 'integer',
            'response_time_seconds' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChallengeRoom::class, 'challenge_room_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
