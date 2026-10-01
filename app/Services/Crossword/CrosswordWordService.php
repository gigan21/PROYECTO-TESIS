<?php

declare(strict_types=1);

namespace App\Services\Crossword;

use App\Enums\CrosswordLevel;
use App\Models\CrosswordWord;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CrosswordWordService
{
    public function getWordsForLevel(int $level): Collection
    {
        $level = max(CrosswordLevel::MIN_LEVEL, min(CrosswordLevel::MAX_LEVEL, $level));

        return CrosswordWord::query()
            ->active()
            ->where('level', '<=', $level)
            ->orderBy('level')
            ->get();
    }

    public function getRandomWordsForLevel(int $level, int $userId): Collection
    {
        $level = max(CrosswordLevel::MIN_LEVEL, min(CrosswordLevel::MAX_LEVEL, $level));
        $count = CrosswordLevel::wordsForLevel($level);
        $seed = $userId * 1000 + $level;

        $pool = CrosswordWord::query()
            ->active()
            ->where('level', '<=', $level)
            ->get();

        if ($pool->isEmpty()) {
            return $pool;
        }

        return $pool
            ->sortBy(fn (CrosswordWord $word): int => crc32("{$seed}-{$word->id}"))
            ->values()
            ->take($count);
    }

    /** @param array<string, mixed> $data */
    public function createWord(array $data, User $teacher): CrosswordWord
    {
        return CrosswordWord::query()->create([
            ...$data,
            'user_id' => $teacher->id,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateWord(CrosswordWord $word, array $data): CrosswordWord
    {
        $word->fill($data);
        $word->save();

        return $word->fresh();
    }

    public function deleteWord(CrosswordWord $word): bool
    {
        return (bool) $word->delete();
    }

    /** @param array<string, mixed> $filters */
    public function getAllForTeacher(User $teacher, array $filters = []): LengthAwarePaginator
    {
        $query = CrosswordWord::query()
            ->with('topic')
            ->where('user_id', $teacher->id);

        if (! empty($filters['difficulty'])) {
            $query->ofDifficulty((string) $filters['difficulty']);
        }

        if (! empty($filters['level'])) {
            $query->ofLevel((int) $filters['level']);
        }

        if (! empty($filters['topic_id'])) {
            $query->ofTopic((int) $filters['topic_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('answer', 'like', "%{$search}%")
                    ->orWhere('clue', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('level')->paginate(15)->withQueryString();
    }
}
