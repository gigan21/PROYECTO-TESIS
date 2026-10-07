<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentItem extends Model
{
    public $timestamps = false; // solo usamos obtained_at

    protected $fillable = [
        'user_id',
        'shop_item_id',
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

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class);
    }
}