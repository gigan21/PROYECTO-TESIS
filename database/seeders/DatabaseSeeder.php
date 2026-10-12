<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CrosswordWordsSeeder::class,
            BadgeSeeder::class,
            LoreVideoSeeder::class,
            ProjectileTopicSeeder::class,
            // Lanza la Cura: topic "Cinemática aplicada" (también se crea en CureSessionRecorder si falta)
            // CureTopicSeeder::class,
            //LearningAnalyticsDemoSeeder::class,
        ]);
    }
}
