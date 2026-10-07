<?php

declare(strict_types=1);

return [

    'base_path' => 'images/pets',

    /*
     * animations: estado => archivo
     *   - string  → un solo GIF; se espeja con scaleX(-1) según la dirección
     *   - array   → ['right' => 'x.gif', 'left' => 'y.gif']
     * faces: hacia dónde mira el GIF original ('right' | 'left')
     * price: 0 = gratis (viene desbloqueada)
     * rarity: common | rare | epic | legendary
     */
    'catalog' => [
        'dog' => [
            'name' => 'Perro',
            'description' => 'Tu fiel compañero desde el primer día.',
            'rarity' => 'common',
            'price' => 0,
            'size' => 110,
            'speed' => 50,
            'faces' => 'right',
            'animations' => [
                'walking' => 'dog.gif',
                'resting' => 'dogReposo.gif',
            ],
        ],
        'cat' => [
            'name' => 'Gato',
            'description' => 'Independiente, curioso y muy elegante.',
            'rarity' => 'common',
            'price' => 50,
            'size' => 100,
            'speed' => 40,
            'faces' => 'right',
            'animations' => ['walking' => 'gato.gif'],
        ],
        'chicken' => [
            'name' => 'Pollo',
            'description' => 'Un compañero de granja que pica sin parar.',
            'rarity' => 'rare',
            'price' => 100,
            'size' => 90,
            'speed' => 30,
            'faces' => 'right',
            'animations' => [
                'walking' => 'polloReposo.gif',
                'resting' => 'polloReposo.gif',
                'eating'  => 'polloComiendo.gif',
            ],
        ],
        'crab' => [
            'name' => 'Cangrejo',
            'description' => 'Camina de lado y no le importa.',
            'rarity' => 'rare',
            'price' => 150,
            'size' => 70,
            'speed' => 35,
            'faces' => 'right',
            'animations' => [
                'walking' => 'cangrejoCaminando.gif',
                'resting' => 'cangrejoReposo.gif',
            ],
        ],
        'fox' => [
            'name' => 'Zorro',
            'description' => 'Rápido, astuto y legendario.',
            'rarity' => 'legendary',
            'price' => 250,
            'size' => 100,
            'speed' => 90,
            'faces' => 'right',
            'animations' => [
                'walking' => 'zorroCorriendo.gif',
                'resting' => 'zorroReposoParado.gif',
                'eating'  => 'zorroComiendo.gif',
            ],
        ],
    ],

    'phrases' => [
        'La velocidad es el cambio de posición entre el tiempo: v = Δx / Δt.',
        'En un MRU la aceleración es cero.',
        'En caída libre (sin aire) todos los cuerpos aceleran a g ≈ 9,8 m/s².',
        'En un tiro parabólico, el alcance máximo en terreno plano se logra con 45°.',
        'Rapidez y velocidad no son lo mismo: la velocidad tiene dirección.',
        'En el punto más alto de un lanzamiento vertical, v = 0 pero a sigue siendo g.',
        'En un MRUA: v = v₀ + a·t.',
        'En un proyectil, la componente horizontal de la velocidad es constante (sin rozamiento).',
        'La pendiente de la gráfica x-t es la velocidad.',
        'El área bajo la gráfica v-t es el desplazamiento.',
    ],
];