<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectileLevelReward extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'level',
        'finished',
        'coins_awarded',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'finished' => 'boolean',
            'coins_awarded' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
