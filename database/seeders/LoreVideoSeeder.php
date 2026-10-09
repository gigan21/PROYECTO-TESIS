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
        ]);
    }
}