<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPet extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'pet_id',
        'obtained_at',
    ];

    protected function casts(): array
    {
        return [
            'obtained_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}