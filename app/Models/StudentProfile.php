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
}
