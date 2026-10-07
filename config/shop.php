<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Catálogo de ítems de la tienda
    |--------------------------------------------------------------------------
    |
    | category:  banner | avatar | badge
    | rarity:    common | rare | epic | legendary
    | media_path: ruta relativa a public/
    |
    */

    'items' => [

        // ============ BANNERS ============
        [
            'slug' => 'banner-nebulosa',
            'name' => 'Nebulosa Violeta',
            'description' => 'Un cielo teñido de morado y estrellas.',
            'category' => 'banner',
            'rarity' => 'common',
            'price' => 100,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner1.gif',
            'sort_order' => 10,
        ],
        [
            'slug' => 'banner-ciudad',
            'name' => 'Ciudad Cyberpunk',
            'description' => 'Neones y lluvia en una metrópolis futurista.',
            'category' => 'banner',
            'rarity' => 'rare',
            'price' => 200,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner2.jpg',
            'sort_order' => 20,
        ],
        [
            'slug' => 'banner-bosque',
            'name' => 'Bosque Encantado',
            'description' => 'Naturaleza mágica con luces flotantes.',
            'category' => 'banner',
            'rarity' => 'rare',
            'price' => 200,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner3.gif',
            'sort_order' => 30,
        ],
        [
            'slug' => 'banner-tormenta',
            'name' => 'Tormenta Espacial',
            'description' => 'Una tempestad de energía cósmica.',
            'category' => 'banner',
            'rarity' => 'epic',
            'price' => 400,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner4.png',
            'sort_order' => 40,
        ],
        [
            'slug' => 'banner-galaxia',
            'name' => 'Galaxia Dorada',
            'description' => 'Polvo de estrellas dorado brillando.',
            'category' => 'banner',
            'rarity' => 'legendary',
            'price' => 700,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner5.gif',
            'sort_order' => 50,
        ],
        [
            'slug' => 'banner-aurora',
            'name' => 'Aurora Boreal',
            'description' => 'Luces danzantes sobre un cielo nocturno.',
            'category' => 'banner',
            'rarity' => 'epic',
            'price' => 450,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner6.gif',
            'sort_order' => 60,
        ],
        [
            'slug' => 'banner-fuego',
            'name' => 'Corazón de Fuego',
            'description' => 'Llamas ardientes que no consumen.',
            'category' => 'banner',
            'rarity' => 'legendary',
            'price' => 650,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner7.gif',
            'sort_order' => 70,
        ],
        [
            'slug' => 'banner-agua',
            'name' => 'Océano Profundo',
            'description' => 'Olas suaves bajo el mar.',
            'category' => 'banner',
            'rarity' => 'common',
            'price' => 80,
            'media_type' => 'image',
            'media_path' => 'images/banners/banner8.gif',
            'sort_order' => 80,
        ],

        // ============ AVATARES ============
        [
            'slug' => 'avatar-tienda-1',
            'name' => 'Avatar Aprendiz',
            'description' => 'El héroe que todos llevamos dentro.',
            'category' => 'avatar',
            'rarity' => 'common',
            'price' => 50,
            'media_type' => 'image',
            'media_path' => 'images/avatars/avatartienda1.jpg',
            'sort_order' => 10,
        ],
        [
            'slug' => 'avatar-tienda-2',
            'name' => 'Avatar Científico',
            'description' => 'Con bata y gafas, listo para experimentar.',
            'category' => 'avatar',
            'rarity' => 'rare',
            'price' => 150,
            'media_type' => 'image',
            'media_path' => 'images/avatars/avatartienda2.jpg',
            'sort_order' => 20,
        ],
        [
            'slug' => 'avatar-tienda-3',
            'name' => 'Avatar Físico',
            'description' => 'Domina las fuerzas del universo.',
            'category' => 'avatar',
            'rarity' => 'epic',
            'price' => 350,
            'media_type' => 'image',
            'media_path' => 'images/avatars/avatartienda3.jpg',
            'sort_order' => 30,
        ],

    ],

];