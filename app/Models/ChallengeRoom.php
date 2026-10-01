<?php

namespace App\Models;

use App\Enums\ChallengeRoomStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeRoom extends Model
{
    protected $fillable = [
        'teacher_id',
        'classroom',
        'title',
        'code',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChallengeRoomStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'challenge_room_question')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ChallengeAnswer::class);
    }

    public function isActive(): bool
    {
        return $this->status === ChallengeRoomStatus::Active;
    }

    public function isJoinable(): bool
    {
        return $this->status === ChallengeRoomStatus::Active;
    }
}
