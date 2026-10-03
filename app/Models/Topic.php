<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function learningLogs(): HasMany
    {
        return $this->hasMany(LearningLog::class);
    }

    public function aiRecommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }
}
