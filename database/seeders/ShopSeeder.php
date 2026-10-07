<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShopItem;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $items = config('shop.items', []);

        foreach ($items as $item) {
            ShopItem::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'category' => $item['category'],
                    'rarity' => $item['rarity'] ?? 'common',
                    'price' => $item['price'] ?? 0,
                    'media_type' => $item['media_type'] ?? 'image',
                    'media_path' => $item['media_path'],
                    'is_active' => $item['is_active'] ?? true,
                    'sort_order' => $item['sort_order'] ?? 0,
                ]
            );
        }
    }
}