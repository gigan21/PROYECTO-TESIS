<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Classroom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeatureSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'classroom',
        'snapshot_date',
        'features_json',
        'features_version',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'features_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param  Builder<StudentFeatureSnapshot>  $query */
    public function scopeForClassroom(Builder $query, string $canonical): Builder
    {
        return $query->whereIn('classroom', Classroom::storedValuesFor($canonical));
    }

    /** @param  Builder<StudentFeatureSnapshot>  $query */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('snapshot_date');
    }
}
