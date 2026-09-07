<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'classroom',
        'nickname',
        'avatar_name',
        'xp_points',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
