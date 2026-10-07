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
        'coins',
        'banner_id',
        'active_pet_id',
    ];

    protected function casts(): array
    {
        return [
            'xp_points' => 'integer',
            'coins' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getLevelAttribute(): int
    {
        return floor(($this->xp_points ?? 0) / 100) + 1;
    }

    /**
     * Devuelve el rango con temática de Física según el XP acumulado.
     */
    public function getRankTitleAttribute(): string
    {
        $xp = $this->xp_points ?? 0;

        return match (true) {
            $xp >= 1000 => '⚡ Dios de la Física',
            $xp >= 800  => '🌌 Maestro Cinemático',
            $xp >= 600  => '🚀 Erudito del Movimiento',
            $xp >= 400  => '🍎 Aprendiz de Newton',
            $xp >= 200  => '📏 Explorador Vectorial',
            default     => '🌱 Novato Cinemático',
        };
    }

    /**
     * Calcula el porcentaje de progreso (0% a 100%) dentro del nivel actual.
     */
    public function getLevelProgressAttribute(): int
    {
        $xpInCurrentLevel = ($this->xp_points ?? 0) % 100;
        return $xpInCurrentLevel;
    }

    /**
     * Calcula cuántos XP faltan para el siguiente nivel.
     */
    public function getXpToNextLevelAttribute(): int
    {
        return 100 - ($this->level_progress);
    }

    /**
     * Suma monedas al perfil. Preferir CoinService::addCoins().
     */
    public function addCoins(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        $this->increment('coins', $amount);
    }

    /**
     * Resta monedas. Devuelve false si no alcanzan.
     * Preferir CoinService::spendCoins().
     */
    public function spendCoins(int $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }
        if (($this->coins ?? 0) < $amount) {
            return false;
        }
        $this->decrement('coins', $amount);
        return true;
    }

    public function hasCoins(int $amount): bool
    {
        return ($this->coins ?? 0) >= $amount;
    }

    public function coinTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CoinTransaction::class, 'user_id', 'user_id');
    }
        /** Banner equipado (si tiene uno). */
        public function banner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(\App\Models\ShopItem::class, 'banner_id');
        }

}
