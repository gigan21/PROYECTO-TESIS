<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Recompensas por rango de puntos (por nivel superado)
    |--------------------------------------------------------------------------
    | El rango se evalúa en orden, se aplica el primero donde encaje.
    | "min" inclusive, "max" inclusive.
    */
    'rewards' => [
        ['min' => 0,     'max' => 999,   'coins' => 1],
        ['min' => 1000,  'max' => 2499,  'coins' => 2],
        ['min' => 2500,  'max' => 4999,  'coins' => 3],
        ['min' => 5000,  'max' => 9999,  'coins' => 5],
        ['min' => 10000, 'max' => null,  'coins' => 8], // null = sin tope superior
    ],

    /*
    |--------------------------------------------------------------------------
    | Bonus por terminar la partida completa (los 5 niveles)
    |--------------------------------------------------------------------------
    */
    'win_bonus' => 3,

    /*
    |--------------------------------------------------------------------------
    | Anti-abuso: tope diario de monedas por este juego
    |--------------------------------------------------------------------------
    | Se cuentan las transacciones de hoy con source = 'projectiles'.
    | Si el usuario alcanza el tope, no se le dan más monedas este día.
    */
    'daily_cap' => 20,
];