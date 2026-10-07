<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StudentItem;
class ShopItem extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'rarity',
        'price',
        'media_type',
        'media_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // --- Relaciones ---

    public function studentItems(): HasMany
    {
        return $this->hasMany(StudentItem::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_items')
            ->withPivot('obtained_at');
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // --- Helpers ---

    public function getRarityLabelAttribute(): string
    {
        return match ($this->rarity) {
            'common' => 'Común',
            'rare' => 'Raro',
            'epic' => 'Épico',
            'legendary' => 'Legendario',
            default => 'Común',
        };
    }

    public function getRarityColorAttribute(): string
    {
        return match ($this->rarity) {
            'common' => 'slate',
            'rare' => 'blue',
            'epic' => 'purple',
            'legendary' => 'amber',
            default => 'slate',
        };
    }
}