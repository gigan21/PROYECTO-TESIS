<?php
// database/seeders/LoreVideoSeeder.php

namespace Database\Seeders;

use App\Models\LoreVideo;
use Illuminate\Database\Seeder;

class LoreVideoSeeder extends Seeder
{
    public function run(): void
    {
        LoreVideo::query()->delete(); // limpio por si se vuelve a correr

        LoreVideo::insert([
            [
                'youtube_id' => 'sq29JTCMlGY',
                'title'      => 'Lore 1',
                'sort_order' => 1,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'youtube_id' => 'ACYrDPR_PRE',
                'title'      => 'Lore 2',
                'sort_order' => 2,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // --- Videos nuevos (Shorts + youtu.be) ---
            [
                'youtube_id' => 'Lrt4yCodhAs',
                'title'      => 'Lore 3 - Física 3',
                'sort_order' => 3,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'youtube_id' => 'vZ_rID6H-q0',
                'title'      => 'Lore 4',
                'sort_order' => 4,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'youtube_id' => '0qWLQFvF5Og',
                'title'      => 'Lore 5',
                'sort_order' => 5,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'youtube_id' => 'm8dRyPLy3wQ',
                'title'      => 'Lore 6',
                'sort_order' => 6,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'youtube_id' => 'KX_9fwnCgbQ',
                'title'      => 'Lore 7',
                'sort_order' => 7,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}