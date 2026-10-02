<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'image',
        'category',
        'requirement_text',
        'rule_key',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'badge_student')
            ->withTimestamps()
            ->withPivot('unlocked_at');
    }
}
