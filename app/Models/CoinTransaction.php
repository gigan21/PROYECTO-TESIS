<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'source',
        'description',
        'balance_after',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeEarnings($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeSpendings($query)
    {
        return $query->where('amount', '<', 0);
    }
}