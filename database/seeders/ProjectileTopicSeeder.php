<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class ProjectileTopicSeeder extends Seeder
{
    public function run(): void
    {
        Topic::query()->firstOrCreate(
            ['name' => config('projectiles.topic_name', 'Cinemática')],
            ['description' => 'Movimiento parabólico y cinemática', 'is_active' => true]
        );
    }
}
