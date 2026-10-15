<?php

declare(strict_types=1);

return [
    'max_alerts' => 2,
    'session_limit' => 5,
    'crossword_event_limit' => 200,
    'crossword_session_gap_minutes' => 30,

    'thresholds' => [
        'consecutive_failures' => 3,
        'low_accuracy' => 30,
        'no_wins_recent' => 5,
        'stuck_level_failures' => 3,
        'hints_sessions' => 3,
        'chart_type_accuracy_low' => 40,
        'projectile_power_low' => 50,
        'projectile_angle_low' => 45,
        'projectile_angle_high' => 135,
        'projectile_gravity_changes' => 2,
    ],

    'games' => [
        'cure' => [
            'label' => 'Lanza la Cura',
            'icon' => '💉',
        ],
        'projectile' => [
            'label' => 'Proyectiles',
            'icon' => '🎯',
        ],
        'chart_race' => [
            'label' => 'Carrera de Gráficas',
            'icon' => '📈',
        ],
        'crossword' => [
            'label' => 'Crucigrama',
            'icon' => '🧩',
        ],
        'generic' => [
            'label' => 'tus juegos',
            'icon' => '⚠️',
        ],
    ],

    'messages' => [
        'situation' => [
            'consecutive_failures' => 'Llevas :count fallos seguidos en :game.',
            'low_accuracy' => 'Tu precisión en :game bajó mucho (promedio reciente :accuracy%).',
            'no_wins_recent' => 'No has ganado :game en :count intentos recientes.',
            'stuck_level' => 'Estás atascado en el nivel :level de :game.',
            'hints_overuse' => 'Estás usando muchas pistas en :game.',
        ],
        'suggestions' => [
            'cure' => [
                'concepto' => 'Repasa cómo identificar cada variable (velocidad, distancia, tiempo) en los problemas de cinemática. Antes de calcular, escribe qué datos tienes y qué te piden.',
                'calculo' => 'Cuida el orden de las operaciones. Despeja la variable paso a paso y verifica las unidades antes de calcular.',
                'decimales' => 'Revisa los decimales de tu respuesta. Un pequeño error de redondeo puede cambiar el resultado.',
                'default' => 'Repasa las fórmulas de MRU y MRUV antes de tu próxima partida. Lee el enunciado con calma y anota las unidades.',
            ],
            'projectile' => [
                'angle_low' => 'Usa ángulos más cercanos a 45° para llegar más lejos. El ángulo afecta directamente cuánto avanza el proyectil.',
                'angle_high' => 'Usa ángulos más cercanos a 45° para llegar más lejos. Ángulos muy abiertos reducen el alcance horizontal.',
                'power_low' => 'Aumenta la potencia para que el proyectil llegue más lejos. Recuerda que el alcance depende de la velocidad inicial al cuadrado.',
                'gravity' => 'Practica con la gravedad normal (320) antes de experimentar. Así afinas tu intuición sobre la trayectoria.',
                'default' => 'Repasa cómo se separa la velocidad inicial en vx (horizontal) y vy (vertical). La trayectoria parabólica depende de ambas.',
            ],
            'chart_race' => [
                'predict' => 'Repasa cómo predecir la continuación de una curva. Si la pendiente sube, la velocidad aumenta; si baja, disminuye.',
                'identify' => 'Practica identificar MRU vs MRUV. MRU = línea recta; MRUV = curva.',
                'choose' => 'Revisa qué representa cada eje: en x-t, la pendiente es velocidad; en v-t, la pendiente es aceleración.',
                'default' => 'Observa la forma de la gráfica antes de responder: pendiente, curvatura y qué magnitud representa cada eje.',
            ],
            'crossword' => [
                'default' => 'Repasa los conceptos básicos de cinemática: desplazamiento, velocidad, aceleración. El glosario al inicio de cada nivel puede ayudarte.',
            ],
            'generic' => [
                'default' => 'Repasa los conceptos fundamentales de cinemática antes de continuar. Descansar y volver con la mente fresca también ayuda.',
            ],
        ],
    ],
];
