<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Enums\QuestionDifficulty;
use App\Models\Badge;
use App\Models\StudentQuestionAnswer;
use App\Models\User;
class StudentBadgeService
{
    public function syncForUser(User $user): void
    {
        $profile = $user->studentProfile;
        if ($profile === null) {
            return;
        }

        $level = (int) $profile->level;
        $toUnlock = [];

        foreach (Badge::query()->where('category', 'level')->orderBy('sort_order')->get() as $badge) {
            if ($badge->rule_key && preg_match('/level:(\d+)/', (string) $badge->rule_key, $matches)) {
                if ($level >= (int) $matches[1]) {
                    $toUnlock[] = $badge->id;
                }
            }
        }

        $easyCount = $this->countCorrectByDifficulty($user, QuestionDifficulty::Facil);
        $mediumCount = $this->countCorrectByDifficulty($user, QuestionDifficulty::Medio);
        $hardCount = $this->countCorrectByDifficulty($user, QuestionDifficulty::Dificil);

        if ($easyCount >= 15) {
            $toUnlock[] = Badge::query()->where('slug', 'questions_easy_15')->value('id');
        }
        if ($mediumCount >= 20) {
            $toUnlock[] = Badge::query()->where('slug', 'questions_medium_20')->value('id');
        }
        if ($hardCount >= 25) {
            $toUnlock[] = Badge::query()->where('slug', 'questions_hard_25')->value('id');
        }

        $toUnlock = array_filter(array_unique($toUnlock));
        if ($toUnlock === []) {
            return;
        }

        $sync = [];
        foreach ($toUnlock as $badgeId) {
            $sync[$badgeId] = ['unlocked_at' => now()];
        }

        $user->badges()->syncWithoutDetaching($sync);
    }

    /**
     * @return list<array{slug: string, name: string, image: string, unlocked: bool, requirement_text: string|null, is_placeholder: bool}>
     */
    public function displayItems(User $user): array
    {
        $unlockedIds = $user->badges()->pluck('badges.id')->all();

        return Badge::query()
            ->orderBy('sort_order')
            ->get()
            ->map(function (Badge $badge) use ($unlockedIds) {
                $isPlaceholder = $badge->category === 'placeholder';

                return [
                    'slug' => $badge->slug,
                    'name' => $badge->name,
                    'image' => $badge->image,
                    'unlocked' => ! $isPlaceholder && in_array($badge->id, $unlockedIds, true),
                    'requirement_text' => $badge->requirement_text,
                    'is_placeholder' => $isPlaceholder,
                ];
            })
            ->all();
    }

    private function countCorrectByDifficulty(User $user, QuestionDifficulty $difficulty): int
    {
        return (int) StudentQuestionAnswer::query()
            ->where('student_id', $user->id)
            ->where('is_correct', true)
            ->whereHas('question', fn ($query) => $query->where('difficulty', $difficulty))
            ->distinct('question_id')
            ->count('question_id');
    }
}
