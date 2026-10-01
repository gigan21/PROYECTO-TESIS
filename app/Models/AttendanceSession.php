<?php

namespace App\Models;

use App\Enums\AttendanceSessionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $fillable = [
        'teacher_id',
        'classroom',
        'title',
        'description',
        'session_date',
        'starts_at',
        'ends_at',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'status' => AttendanceSessionStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function isOpen(): bool
    {
        return $this->status === AttendanceSessionStatus::Activa;
    }
}
