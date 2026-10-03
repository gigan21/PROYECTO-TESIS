<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    protected $fillable = [
        'topic_id',
        'type',
        'title',
        'resource_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'is_active' => 'boolean',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function aiRecommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class, 'recommended_content_id');
    }
}
