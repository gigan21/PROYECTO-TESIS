<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tipos de actividad gamificada registrados en learning_logs.
 * Añadir nuevos cases al integrar juegos (p. ej. quiz, projectile_sim).
 */
enum LearningGameType: string
{
    case Crossword = 'crossword';
    case Challenge = 'challenge';
    case Projectiles = 'projectiles';
    /** Lanza la Cura — MRU, MRUV, caída libre y lanzamiento. */
    case Cure = 'cure';
    case ChartRace = 'chart_race';
    case Other = 'other';
}
