<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class CureTopicSeeder extends Seeder
{
    public function run(): void
    {
        Topic::query()->firstOrCreate(
            ['name' => 'Cinemática aplicada'],
            [
                'description' => 'MRU, MRUV, caída libre y lanzamiento',
                'is_active' => true,
            ]
        );
    }
}
