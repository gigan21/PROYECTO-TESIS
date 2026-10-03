<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Enums\LearningGameType;
use App\Models\AiRecommendation;
use App\Models\Content;
use App\Models\LearningLog;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LearningAnalyticsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $students = $this->ensureDemoStudents();
        $topics = Topic::query()->where('is_active', true)->orderBy('name')->get();

        if ($topics->isEmpty()) {
            $topics = collect([
                Topic::query()->firstOrCreate(
                    ['name' => 'Cinemática'],
                    ['description' => 'Demo CRISP-DM', 'is_active' => true]
                ),
            ]);
        }

        $contents = collect();
        foreach ($topics as $topic) {
            foreach ([ContentType::Video, ContentType::Pdf, ContentType::Infografia] as $type) {
                $contents->push(Content::query()->updateOrCreate(
                    [
                        'topic_id' => $topic->id,
                        'title' => "{$topic->name} — {$type->value}",
                    ],
                    [
                        'type' => $type,
                        'resource_url' => "https://example.com/{$topic->id}/{$type->value}",
                        'is_active' => true,
                    ]
                ));
            }
        }

        $gameTypes = [
            LearningGameType::Crossword,
            LearningGameType::Challenge,
            LearningGameType::Other,
        ];

        for ($i = 0; $i < 40; $i++) {
            $student = $students->random();
            $topic = $topics->random();

            LearningLog::query()->create([
                'user_id' => $student->id,
                'topic_id' => $topic->id,
                'game_type' => $gameTypes[array_rand($gameTypes)],
                'total_time_seconds' => random_int(120, 900),
                'error_rate_percentage' => random_int(5, 45) + (random_int(0, 99) / 100),
                'earned_xp' => random_int(5, 50),
                'attempts' => random_int(1, 4),
                'completed_at' => now()->subDays(random_int(0, 14)),
            ]);
        }

        $firstStudent = $students->first();
        $firstContent = $contents->first();
        if ($firstStudent && $firstContent) {
            AiRecommendation::query()->updateOrCreate(
                [
                    'user_id' => $firstStudent->id,
                    'topic_id' => $firstContent->topic_id,
                    'recommended_content_id' => $firstContent->id,
                ],
                [
                    'reason' => 'Alta tasa de error en crucigrama (demo C4.5)',
                    'is_completed' => false,
                ]
            );
        }
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function ensureDemoStudents(): \Illuminate\Support\Collection
    {
        $classrooms = ['4A', '4B', '4C'];
        $students = collect();

        foreach ($classrooms as $index => $classroom) {
            $email = "estudiante.demo.{$classroom}@correo.com";
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => "Estudiante Demo {$classroom}",
                    'password' => Hash::make('password'),
                    'role' => 'estudiante',
                ]
            );

            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'classroom' => $classroom,
                    'nickname' => "hero_{$index}",
                    'avatar_name' => 'default_avatar.png',
                    'xp_points' => random_int(0, 300),
                    'coins' => random_int(0, 50),
                ]
            );

            $students->push($user->fresh());
        }

        User::query()
            ->where('role', 'estudiante')
            ->whereHas('studentProfile')
            ->each(fn (User $user) => $students->push($user));

        return $students->unique('id')->values();
    }
}
