<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChartRaceEvent;
use App\Models\ChartRaceSession;
use App\Models\CureGameSession;
use App\Models\CureLevelReward;
use App\Models\LearningLog;
use App\Models\ProjectileGameSession;
use App\Models\ProjectileLevelReward;
use App\Models\StudentProfile;
use App\Models\StudentQuestionAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ArtificialDataBalancedSeeder extends Seeder
{
    /**
     * Seeder inteligente: crea 40 estudiantes adicionales, cada uno con un perfil
     * específico para balancear las etiquetas del C4.5.
     *
     * Cobertura de etiquetas:
     *  - G1 (5): reforzar_angulo_tiro_parabolico
     *  - G2 (5): reforzar_componentes_vx_vy
     *  - G3 (5): reforzar_relacion_gravedad_alcance
     *  - G4 (4): reforzar_concepto_velocidad_constante
     *  - G5 (4): reforzar_formula_distancia_mruv
     *  - G6 (4): reforzar_formula_tiempo_caida_libre
     *  - G7 (4): reforzar_leer_grafica_posicion_tiempo
     *  - G8 (4): reforzar_precision_decimales
     *  - G9 (5): avanzar_a_leyes_newton
     */

    private const PASSWORDS = ['password', '12345678', 'alumno2026'];

    // 40 nombres nuevos bolivianos (no colisionan con el seeder anterior)
    private const NAMES = [
        'Ana Lucía Rojas Paredes', 'Bruno Alexander Vargas', 'Carla Jimena Orellana',
        'David Santiago Muñoz', 'Elena Rocío Álvarez', 'Fabián Eduardo Ponce',
        'Gabriela Sofía Nieva', 'Hugo Manuel Céspedes', 'Irene Valeria Bustamante',
        'Javier Alberto Roca', 'Karen Marisol Oviedo', 'Luis Fernando Torrico',
        'Mariana Belén Pedraza', 'Nicolás Esteban Zapata', 'Olga Patricia Villca',
        'Pedro Antonio Salvatierra', 'Quimey Lucía Huanca', 'Rodrigo Emilio Quiroga',
        'Silvia Natalia Gutiérrez', 'Tomás Nicolás Medina', 'Úrsula Camila Ferrufino',
        'Víctor Hugo Cuéllar', 'Wendy Alejandra Oropeza', 'Xavier Ignacio Patiño',
        'Yamilka Rocío Escalante', 'Zoe Amanda Montaño', 'Alonso Rafael Guzmán',
        'Beatriz Elena Céspedes', 'Cristóbal Matías Arana', 'Diana Carolina Landívar',
        'Eduardo Joaquín Villarroel', 'Fernanda Nicole Ríos', 'Gustavo Adolfo Siles',
        'Helena Mercedes Terrazas', 'Iván Rodrigo Arévalo', 'Josefina Clara Antelo',
        'Karla Verónica Menacho', 'Leonardo Fabio Suárez', 'Mónica Paola Barriga',
        'Norberto Iván Pinto',
    ];

    private const AVATARS = [
        'avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 'avatar5.jpg',
        'avatar6.jpg', 'avatar7.jpg', 'avatar8.jpg', 'avatar9.jpg', 'avatar10.jpg',
    ];

    private array $credenciales = [];

    public function run(): void
    {
        $topics = Topic::pluck('id', 'name')->all();
        $topicCure = $topics['Cinemática aplicada'] ?? $topics['Cinemática'] ?? null;
        $topicProj = $topics['Cinemática'] ?? $topics['Movimiento'] ?? null;
        $topicChart = $topics['Cinemática'] ?? $topics['Movimiento'] ?? null;
        $topicQ = $topics['Movimiento'] ?? null;

        // Definición de los 9 grupos: cada uno apunta a una etiqueta específica
        $groups = [
            ['count' => 5, 'profile' => 'g1_angulo_tiro'],
            ['count' => 5, 'profile' => 'g2_componentes_vxvy'],
            ['count' => 5, 'profile' => 'g3_gravedad_alcance'],
            ['count' => 4, 'profile' => 'g4_concepto_velocidad'],
            ['count' => 4, 'profile' => 'g5_formula_distancia_mruv'],
            ['count' => 4, 'profile' => 'g6_formula_caida_libre'],
            ['count' => 4, 'profile' => 'g7_grafica_posicion'],
            ['count' => 4, 'profile' => 'g8_precision_decimales'],
            ['count' => 5, 'profile' => 'g9_avanzar_newton'],
        ];

        $paralelos = ['4to A', '4to B', '4to C'];
        $nameIndex = 0;
        $seq = 41; // Empezamos en seed41

        DB::transaction(function () use ($groups, $topics, $topicCure, $topicProj, $topicChart, $topicQ, $paralelos, &$nameIndex, &$seq) {
            foreach ($groups as $group) {
                for ($i = 0; $i < $group['count']; $i++) {
                    $name = self::NAMES[$nameIndex];
                    $email = "seed{$seq}@seed.test";
                    $password = self::PASSWORDS[$nameIndex % count(self::PASSWORDS)];
                    $paralelo = $paralelos[$nameIndex % 3];

                    // XP y coins según perfil
                    $xp = match (true) {
                        str_starts_with($group['profile'], 'g9') => rand(250, 450),
                        str_starts_with($group['profile'], 'g1'),
                        str_starts_with($group['profile'], 'g2'),
                        str_starts_with($group['profile'], 'g3') => rand(30, 120),
                        default => rand(50, 200),
                    };

                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => 'estudiante',
                        'is_active' => 1,
                        'email_verified_at' => now(),
                    ]);

                    StudentProfile::create([
                        'user_id' => $user->id,
                        'classroom' => $paralelo,
                        'nickname' => strtolower('b_' . explode(' ', $name)[0]) . $seq,
                        'avatar_name' => self::AVATARS[$nameIndex % count(self::AVATARS)],
                        'xp_points' => $xp,
                        'coins' => rand(5, 60),
                    ]);

                    // Generar datos según perfil
                    match ($group['profile']) {
                        'g1_angulo_tiro' => $this->seedG1AnguloTiro($user, $topicProj),
                        'g2_componentes_vxvy' => $this->seedG2Componentes($user, $topicProj),
                        'g3_gravedad_alcance' => $this->seedG3Gravedad($user, $topicProj),
                        'g4_concepto_velocidad' => $this->seedG4ConceptoVelocidad($user, $topicQ),
                        'g5_formula_distancia_mruv' => $this->seedG5FormulaDistancia($user, $topicCure),
                        'g6_formula_caida_libre' => $this->seedG6FormulaCaida($user, $topicCure),
                        'g7_grafica_posicion' => $this->seedG7GraficaPosicion($user, $topicChart),
                        'g8_precision_decimales' => $this->seedG8PrecisionDecimales($user, $topicCure),
                        'g9_avanzar_newton' => $this->seedG9Avanzar($user, $topicCure, $topicProj, $topicQ),
                        default => null,
                    };

                    $this->credenciales[] = [
                        'id' => $user->id,
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'paralelo' => $paralelo,
                        'profile' => $group['profile'],
                    ];

                    $nameIndex++;
                    $seq++;
                }
            }
        });

        $this->guardarCredenciales();
        $this->command->info('✅ 40 estudiantes balanceados creados.');
        $this->command->info('📄 Credenciales en: storage/app/seed_balanced_credentials.txt');
    }

    // =========================================================================
    // G1: reforzar_angulo_tiro_parabolico
    // Requisito: projectile_avg_accuracy < 40% Y gravity_changes < 2
    // =========================================================================
    private function seedG1AnguloTiro(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g1_' . uniqid();
            $shots = 15;
            $hits = 3; // 20% accuracy
            ProjectileGameSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => 'lose',
                'level_reached' => 1,
                'score' => 500,
                'total_time_seconds' => rand(20, 60),
                'accuracy' => ($hits / $shots) * 100,
                'total_shots' => $shots,
                'total_hits' => $hits,
                'total_misses' => $shots - $hits,
                'classroom' => $user->studentProfile->classroom,
                'telemetry_json' => json_encode([
                    'sessionId' => $sessionId,
                    'totalHits' => $hits,
                    'totalShots' => $shots,
                    'avgAngle' => 30, // ángulo muy bajo
                    'avgPower' => 50,
                    'gravityChanges' => 0, // NUNCA usó laboratorio
                    'seed' => true,
                ]),
                'analysis_text' => json_encode(['Perfil G1 - ángulo bajo sin laboratorio']),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);

            if ($topicId) {
                LearningLog::create([
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'game_type' => 'projectiles',
                    'total_time_seconds' => rand(20, 60),
                    'error_rate_percentage' => 80,
                    'earned_xp' => 15,
                    'attempts' => $shots,
                    'correct_attempts' => $hits,
                    'completed_at' => now()->subDays(rand(1, 10)),
                ]);
            }
        }
    }

    // =========================================================================
    // G2: reforzar_componentes_vx_vy
    // Requisito: projectile_avg_accuracy < 40% Y gravity_changes >= 2 Y < 4
    // =========================================================================
    private function seedG2Componentes(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g2_' . uniqid();
            $shots = 15;
            $hits = 4; // 26% accuracy
            ProjectileGameSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => 'lose',
                'level_reached' => 2,
                'score' => 800,
                'total_time_seconds' => rand(30, 80),
                'accuracy' => ($hits / $shots) * 100,
                'total_shots' => $shots,
                'total_hits' => $hits,
                'total_misses' => $shots - $hits,
                'classroom' => $user->studentProfile->classroom,
                'telemetry_json' => json_encode([
                    'sessionId' => $sessionId,
                    'totalHits' => $hits,
                    'totalShots' => $shots,
                    'avgAngle' => 90,
                    'avgPower' => 60,
                    'gravityChanges' => 2, // Probó el laboratorio
                    'seed' => true,
                ]),
                'analysis_text' => json_encode(['Perfil G2 - componentes vx/vy']),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    // =========================================================================
    // G3: reforzar_relacion_gravedad_alcance
    // Requisito: projectile_avg_accuracy < 40% Y gravity_changes >= 4
    // =========================================================================
    private function seedG3Gravedad(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g3_' . uniqid();
            $shots = 15;
            $hits = 3; // 20%
            ProjectileGameSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => 'lose',
                'level_reached' => 2,
                'score' => 500,
                'total_time_seconds' => rand(40, 100),
                'accuracy' => ($hits / $shots) * 100,
                'total_shots' => $shots,
                'total_hits' => $hits,
                'total_misses' => $shots - $hits,
                'classroom' => $user->studentProfile->classroom,
                'telemetry_json' => json_encode([
                    'sessionId' => $sessionId,
                    'totalHits' => $hits,
                    'totalShots' => $shots,
                    'avgAngle' => 100,
                    'avgPower' => 65,
                    'gravityChanges' => 5, // Probó mucho el laboratorio
                    'seed' => true,
                ]),
                'analysis_text' => json_encode(['Perfil G3 - probó gravedad sin éxito']),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    // =========================================================================
    // G4: reforzar_concepto_velocidad_constante
    // Requisito: questions_easy < 40% O chart_identify < 30%
    // =========================================================================
    private function seedG4ConceptoVelocidad(User $user, ?int $topicId): void
    {
        // Datos de preguntas fáciles con bajo accuracy
        $questions = Question::where('difficulty', 'Fácil')->where('is_active', 1)->take(10)->get();
        foreach ($questions as $q) {
            $options = QuestionOption::where('question_id', $q->id)->get();
            $wrongOption = $options->where('is_correct', false)->first() ?? $options->first();
            if (!$wrongOption) continue;

            StudentQuestionAnswer::create([
                'student_id' => $user->id,
                'question_id' => $q->id,
                'question_option_id' => $wrongOption->id,
                'is_correct' => false,
                'is_skipped' => false,
                'xp_earned' => 0,
                'answered_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        // LearningLog con accuracy baja en fácil
        if ($topicId) {
            LearningLog::create([
                'user_id' => $user->id,
                'topic_id' => $topicId,
                'game_type' => 'challenge',
                'total_time_seconds' => rand(60, 180),
                'error_rate_percentage' => 80,
                'earned_xp' => 5,
                'attempts' => 10,
                'correct_attempts' => 1,
                'completed_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    // =========================================================================
    // G5: reforzar_formula_distancia_mruv
    // Requisito: cure_errors_concepto > 2 Y cure_avg_accuracy <= 55%
    // =========================================================================
    private function seedG5FormulaDistancia(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g5_' . uniqid();
            CureGameSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'level_reached' => 1,
                'niveles_completados' => 1,
                'attempts' => 4,
                'hits' => 1,
                'misses' => 3,
                'accuracy' => 25,
                'score' => 25,
                'rank' => 'Cadete',
                'max_streak' => 1,
                'won' => false,
                'pista_usadas' => 0,
                'total_time_sec' => rand(80, 200),
                'formulas_mastered' => json_encode(['v = d / t']),
                'formulas_review' => json_encode(['d = v₀·t + ½·a·t²']),
                'errors_by_type' => json_encode(['concepto' => 4, 'calculo' => 0, 'decimales' => 0]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => 1, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    // =========================================================================
    // G6: reforzar_formula_tiempo_caida_libre
    // Requisito: cure_errors_calculo > 2 Y cure_avg_accuracy < 50%
    // =========================================================================
    private function seedG6FormulaCaida(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g6_' . uniqid();
            CureGameSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'level_reached' => 1,
                'niveles_completados' => 1,
                'attempts' => 4,
                'hits' => 1,
                'misses' => 3,
                'accuracy' => 25,
                'score' => 25,
                'rank' => 'Cadete',
                'max_streak' => 1,
                'won' => false,
                'pista_usadas' => 1,
                'total_time_sec' => rand(100, 220),
                'formulas_mastered' => json_encode([]),
                'formulas_review' => json_encode(['t = √(2h / g)']),
                'errors_by_type' => json_encode(['concepto' => 0, 'calculo' => 4, 'decimales' => 0]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => 1, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    // =========================================================================
    // G7: reforzar_leer_grafica_posicion_tiempo
    // Requisito: chart_identify < 40%
    // =========================================================================
    private function seedG7GraficaPosicion(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g7_' . uniqid();
            $this->createChartSessionForced(
                $user,
                $sessionId,
                'identify',
                'xt', // forzar a gráficas de posición-tiempo
                0.0    // 0% accuracy en identify
            );
        }
    }

    // =========================================================================
    // G8: reforzar_precision_decimales
    // Requisito: cure_errors_decimales > 2
    // =========================================================================
    private function seedG8PrecisionDecimales(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g8_' . uniqid();
            CureGameSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'level_reached' => 2,
                'niveles_completados' => 2,
                'attempts' => 4,
                'hits' => 2,
                'misses' => 2,
                'accuracy' => 50,
                'score' => 40,
                'rank' => 'Residente',
                'max_streak' => 1,
                'won' => false,
                'pista_usadas' => 0,
                'total_time_sec' => rand(100, 250),
                'formulas_mastered' => json_encode(['v = d / t', 'd = v · t']),
                'formulas_review' => json_encode([]),
                'errors_by_type' => json_encode(['concepto' => 0, 'calculo' => 0, 'decimales' => 4]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => 2, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    // =========================================================================
    // G9: avanzar_a_leyes_newton
    // Requisito: games_played >= 4 Y cure > 75% Y questions_hard > 60%
    // =========================================================================
    private function seedG9Avanzar(User $user, ?int $topicCure, ?int $topicProj, ?int $topicQ): void
    {
        // 1) Cure exitoso
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'bal_g9_cur_' . uniqid();
            CureGameSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'level_reached' => 4,
                'niveles_completados' => 4,
                'attempts' => 4,
                'hits' => 4,
                'misses' => 0,
                'accuracy' => 100,
                'score' => 110,
                'rank' => 'Leyenda',
                'max_streak' => 4,
                'won' => true,
                'pista_usadas' => 0,
                'total_time_sec' => rand(60, 150),
                'formulas_mastered' => json_encode(['v = d / t', 'd = v · t', 't = √(2h/g)', 'vf = v₀ + a·t']),
                'formulas_review' => json_encode([]),
                'errors_by_type' => json_encode(['concepto' => 0, 'calculo' => 0, 'decimales' => 0]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => 4, 'won' => true, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        // 2) Proyectiles exitoso
        for ($i = 0; $i < 2; $i++) {
            $sessionId = 'bal_g9_prj_' . uniqid();
            ProjectileGameSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => 'win',
                'level_reached' => 5,
                'score' => 10000,
                'total_time_seconds' => rand(60, 150),
                'accuracy' => 80,
                'total_shots' => 10,
                'total_hits' => 8,
                'total_misses' => 2,
                'classroom' => $user->studentProfile->classroom,
                'telemetry_json' => json_encode([
                    'sessionId' => $sessionId,
                    'totalHits' => 8,
                    'totalShots' => 10,
                    'avgAngle' => 90,
                    'avgPower' => 70,
                    'gravityChanges' => 2,
                    'seed' => true,
                ]),
                'analysis_text' => json_encode(['Perfil G9 - avanzado']),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        // 3) Preguntas difíciles con buen accuracy
        $questions = Question::where('difficulty', 'Difícil')->where('is_active', 1)->take(10)->get();
        foreach ($questions as $q) {
            $options = QuestionOption::where('question_id', $q->id)->get();
            $correctOption = $options->where('is_correct', true)->first();
            if (!$correctOption) continue;

            StudentQuestionAnswer::create([
                'student_id' => $user->id,
                'question_id' => $q->id,
                'question_option_id' => $correctOption->id,
                'is_correct' => true,
                'is_skipped' => false,
                'xp_earned' => $q->xp_reward ?? 20,
                'answered_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        // 4) Crucigrama completado
        $progress = \App\Models\CrosswordProgress::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_level' => 8,
                'max_level_reached' => 10,
                'learned_words' => json_encode(['masa', 'metro', 'tiempo', 'velocidad', 'aceleracion', 'fuerza']),
                'total_correct_attempts' => 6,
                'total_wrong_attempts' => 0,
                'coins_earned' => 3,
                'last_played_at' => now()->subDays(rand(1, 10)),
            ]
        );

        // 5) Chart race exitoso
        for ($i = 0; $i < 2; $i++) {
            $sessionId = 'bal_g9_cr_' . uniqid();
            $this->createChartSessionForced($user, $sessionId, 'all_ok', 'vt', 0.9);
        }
    }

    // =========================================================================
    // Helper: Chart race con accuracy forzado
    // =========================================================================
    private function createChartSessionForced(User $user, string $sessionId, string $weakType, string $preferredKind, float $accuracy): void
    {
        $types = [
            ['kind' => 'xt', 'type' => 'mru', 'topic' => 'MRU'],
            ['kind' => 'xt', 'type' => 'accel', 'topic' => 'MRUV acelerado'],
            ['kind' => 'vt', 'type' => 'vconst', 'topic' => 'MRU'],
            ['kind' => 'vt', 'type' => 'vneg', 'topic' => 'MRUV desacelerado'],
        ];

        $qTypes = ['identify', 'predict', 'choose'];
        $total = 6;
        $hits = 0;

        $byType = ['identify' => ['c' => 0, 't' => 0], 'predict' => ['c' => 0, 't' => 0], 'choose' => ['c' => 0, 't' => 0]];
        $byKind = ['xt' => ['c' => 0, 't' => 0], 'vt' => ['c' => 0, 't' => 0]];

        for ($r = 1; $r <= $total; $r++) {
            $qType = $qTypes[array_rand($qTypes)];
            $graph = $types[array_rand($types)];

            if ($weakType === 'identify' && $qType === 'identify') {
                $correct = false;
            } elseif ($weakType === 'predict' && $qType === 'predict') {
                $correct = false;
            } elseif ($weakType === 'all_ok') {
                $correct = (rand(0, 100) < ($accuracy * 100));
            } else {
                $correct = (rand(0, 100) < 60);
            }

            if ($correct) $hits++;
            $byType[$qType]['t']++;
            if ($correct) $byType[$qType]['c']++;
            $byKind[$graph['kind']]['t']++;
            if ($correct) $byKind[$graph['kind']]['c']++;

            ChartRaceEvent::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'round_number' => $r,
                'question_type' => $qType,
                'graph_kind' => $graph['kind'],
                'graph_type' => $graph['type'],
                'topic' => $graph['topic'],
                'correct' => $correct,
                'answered_in_seconds' => rand(2, 12) + rand(0, 99) / 100,
                'timed_out' => false,
                'wrong_answer' => $correct ? null : 'Opción incorrecta',
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        $byTypeAccuracy = [];
        foreach ($byType as $t => $d) {
            $byTypeAccuracy[$t] = $d['t'] > 0 ? round(($d['c'] / $d['t']) * 100, 1) : 0;
        }
        $byKindAccuracy = [];
        foreach ($byKind as $k => $d) {
            $byKindAccuracy[$k] = $d['t'] > 0 ? round(($d['c'] / $d['t']) * 100, 1) : 0;
        }

        ChartRaceSession::create([
            'user_id' => $user->id,
            'classroom' => $user->studentProfile->classroom,
            'session_id' => $sessionId,
            'hits' => $hits,
            'misses' => $total - $hits,
            'accuracy' => round(($hits / $total) * 100, 2),
            'total_time_sec' => rand(30, 90),
            'exploded' => false,
            'completed' => true,
            'won' => $hits >= 5,
            'weak_areas' => json_encode($weakType === 'all_ok' ? [] : [$weakType]),
            'strong_areas' => json_encode($weakType === 'all_ok' ? ['MRU', 'MRUV'] : []),
            'by_type_accuracy' => json_encode($byTypeAccuracy),
            'by_graph_kind_accuracy' => json_encode($byKindAccuracy),
            'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => $hits, 'seed' => true]),
            'created_at' => now()->subDays(rand(1, 10)),
        ]);
    }

    private function guardarCredenciales(): void
    {
        $path = storage_path('app/seed_balanced_credentials.txt');
        $lines = ["CREDENCIALES - 40 ESTUDIANTES BALANCEADOS", str_repeat('=', 70), ''];

        foreach ($this->credenciales as $c) {
            $lines[] = sprintf(
                'ID: %d | %-32s | %-22s | %-15s | %-8s | %s',
                $c['id'],
                $c['name'],
                $c['email'],
                $c['password'],
                $c['paralelo'],
                $c['profile']
            );
        }

        file_put_contents($path, implode("\n", $lines));
    }
}   