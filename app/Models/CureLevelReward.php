<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CureLevelReward extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'level',
        'coins_awarded',
        'xp_awarded',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'coins_awarded' => 'integer',
            'xp_awarded' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
