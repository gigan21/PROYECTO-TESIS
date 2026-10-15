<?php

declare(strict_types=1);

/**
 * Integración con la API Flask C4.5 (Fase 6).
 * feature_keys: 42 columnas pedagógicas (sin XP ni monedas).
 */
return [
    'api_url' => env('ML_API_URL', 'http://127.0.0.1:5000'),
    'timeout' => 3,
    'cache_hours' => 24,
    'model_version' => 'v2',
    'feature_keys' => [
        'crossword_total_words_learned',
        'crossword_current_level',
        'crossword_max_level_reached',
        'crossword_wrong_rate',
        'crossword_avg_time_per_word',
        'crossword_words_level_1_3',
        'crossword_words_level_4_7',
        'cure_total_sessions',
        'cure_avg_accuracy',
        'cure_won_rate',
        'cure_avg_hits',
        'cure_errors_concepto',
        'cure_errors_calculo',
        'cure_errors_decimales',
        'cure_formulas_mastered_count',
        'cure_formulas_review_count',
        'projectile_total_sessions',
        'projectile_avg_accuracy',
        'projectile_avg_angle',
        'projectile_avg_power',
        'projectile_gravity_changes_total',
        'projectile_won_rate',
        'questions_attempts_easy',
        'questions_attempts_medium',
        'questions_attempts_hard',
        'questions_accuracy_easy',
        'questions_accuracy_medium',
        'questions_accuracy_hard',
        'chart_race_total_sessions',
        'chart_race_avg_accuracy',
        'chart_race_accuracy_identify',
        'chart_race_accuracy_predict',
        'chart_race_accuracy_choose',
        'chart_race_exploded_rate',
        'student_level',
        'total_sessions_all_games',
        'has_played_crossword',
        'has_played_cure',
        'has_played_projectile',
        'has_played_questions',
        'has_played_chart_race',
        'games_played_count',
    ],
];
