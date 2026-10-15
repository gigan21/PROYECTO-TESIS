<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CureGameSession;
use App\Models\CureLevelReward;
use App\Models\ProjectileGameSession;
use App\Models\ChartRaceSession;
use App\Models\ChartRaceEvent;
use App\Models\LearningLog;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtificialDataExtenderSeeder extends Seeder
{
    /**
     * Por cada estudiante del Grupo A, añadimos sesiones adicionales
     * que fuerzan una debilidad específica para que caigan en una
     * etiqueta concreta del C4.5.
     */
    public function run(): void
    {
        $topics = Topic::pluck('id', 'name')->all();
        $topicCure = $topics['Cinemática aplicada'] ?? $topics['Cinemática'] ?? null;
        $topicProj = $topics['Cinemática'] ?? $topics['Movimiento'] ?? null;

        // Cada user_id con su "debilidad objetivo"
        $objetivos = [
            14 => ['tipo' => 'cure_bajo'],        // → reforzar_formula_distancia_mruv
            18 => ['tipo' => 'projectile_bajo'],  // → reforzar_angulo_tiro_parabolico
            19 => ['tipo' => 'chart_identify'],   // → reforzar_leer_grafica_posicion_tiempo
            21 => ['tipo' => 'questions_easy'],   // → reforzar_concepto_velocidad_constante
            22 => ['tipo' => 'cure_decimales'],   // → reforzar_precision_decimales
            26 => ['tipo' => 'cure_concepto'],    // → reforzar_formula_distancia_mruv
            29 => ['tipo' => 'chart_predict'],    // → reforzar_leer_grafica_velocidad_tiempo
            32 => ['tipo' => 'projectile_gravity'], // → reforzar_relacion_gravedad_alcance
            33 => ['tipo' => 'chart_identify'],   // → reforzar_leer_grafica_posicion_tiempo
            35 => ['tipo' => 'cure_calculo'],     // → reforzar_formula_tiempo_caida_libre
        ];

        DB::transaction(function () use ($objetivos, $topicCure, $topicProj) {
            foreach ($objetivos as $userId => $obj) {
                $user = User::find($userId);
                if (!$user) {
                    $this->command->warn("User $userId no existe, saltando");
                    continue;
                }

                $this->command->info("Extendiendo user $userId con {$obj['tipo']}...");

                match ($obj['tipo']) {
                    'cure_bajo' => $this->addCureBajo($user, $topicCure),
                    'cure_decimales' => $this->addCureDecimales($user, $topicCure),
                    'cure_concepto' => $this->addCureConcepto($user, $topicCure),
                    'cure_calculo' => $this->addCureCalculo($user, $topicCure),
                    'projectile_bajo' => $this->addProjectileBajo($user, $topicProj),
                    'projectile_gravity' => $this->addProjectileGravity($user, $topicProj),
                    'chart_identify' => $this->addChartIdentify($user, $topicCure),
                    'chart_predict' => $this->addChartPredict($user, $topicCure),
                    'questions_easy' => $this->addQuestionsEasy($user),
                    default => null,
                };
            }
        });

        $this->command->info('✅ Extensión de datos artificiales completada.');
    }

    // ---------- CURE: accuracy baja general → reforzar_formula_distancia_mruv ----------
    private function addCureBajo(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $hits = 1;
            $sessionId = 'ext_cur_' . uniqid();
            CureGameSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'level_reached' => 1,
                'niveles_completados' => 1,
                'attempts' => 4,
                'hits' => $hits,
                'misses' => 3,
                'accuracy' => 25,
                'score' => 25,
                'rank' => 'Cadete',
                'max_streak' => 1,
                'won' => false,
                'pista_usadas' => 0,
                'total_time_sec' => rand(80, 200),
                'formulas_mastered' => json_encode(['v = d / t']),
                'formulas_review' => json_encode(['d = v₀·t + ½·a·t²', 'd = v · t']),
                'errors_by_type' => json_encode(['concepto' => 3, 'calculo' => 1, 'decimales' => 0]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => $hits, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 5)),
            ]);
            if ($topicId) {
                LearningLog::create([
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'game_type' => 'cure',
                    'total_time_seconds' => rand(80, 200),
                    'error_rate_percentage' => 75,
                    'earned_xp' => 1,
                    'attempts' => 4,
                    'correct_attempts' => 1,
                    'completed_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }
    }

    // ---------- CURE: errores decimales > 3 → reforzar_precision_decimales ----------
    private function addCureDecimales(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 4; $i++) {
            $sessionId = 'ext_cur_dec_' . uniqid();
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
                'errors_by_type' => json_encode(['concepto' => 0, 'calculo' => 0, 'decimales' => 3]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => 2, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 5)),
            ]);
            if ($topicId) {
                LearningLog::create([
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'game_type' => 'cure',
                    'total_time_seconds' => rand(100, 250),
                    'error_rate_percentage' => 50,
                    'earned_xp' => 2,
                    'attempts' => 4,
                    'correct_attempts' => 2,
                    'completed_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }
    }

    // ---------- CURE: errores concepto > 2 → reforzar_formula_distancia_mruv ----------
    private function addCureConcepto(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'ext_cur_con_' . uniqid();
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
                'score' => 30,
                'rank' => 'Cadete',
                'max_streak' => 1,
                'won' => false,
                'pista_usadas' => 1,
                'total_time_sec' => rand(90, 220),
                'formulas_mastered' => json_encode([]),
                'formulas_review' => json_encode(['d = v₀·t + ½·a·t²']),
                'errors_by_type' => json_encode(['concepto' => 3, 'calculo' => 0, 'decimales' => 0]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => 1, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 5)),
            ]);
        }
    }

    // ---------- CURE: errores cálculo > 2 → reforzar_formula_tiempo_caida_libre ----------
    private function addCureCalculo(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'ext_cur_cal_' . uniqid();
            CureGameSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'level_reached' => 2,
                'niveles_completados' => 1,
                'attempts' => 4,
                'hits' => 1,
                'misses' => 3,
                'accuracy' => 25,
                'score' => 30,
                'rank' => 'Cadete',
                'max_streak' => 1,
                'won' => false,
                'pista_usadas' => 2,
                'total_time_sec' => rand(100, 240),
                'formulas_mastered' => json_encode([]),
                'formulas_review' => json_encode(['t = √(2h / g)']),
                'errors_by_type' => json_encode(['concepto' => 1, 'calculo' => 3, 'decimales' => 1]),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => 1, 'seed' => true]),
                'created_at' => now()->subDays(rand(1, 5)),
            ]);
        }
    }

    // ---------- PROJECTILE: accuracy baja → reforzar_angulo_tiro_parabolico ----------
    private function addProjectileBajo(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'ext_prj_' . uniqid();
            $shots = 15;
            $hits = 4;
            ProjectileGameSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => 'lose',
                'level_reached' => 2,
                'score' => 800,
                'total_time_seconds' => rand(30, 90),
                'accuracy' => ($hits / $shots) * 100,
                'total_shots' => $shots,
                'total_hits' => $hits,
                'total_misses' => $shots - $hits,
                'classroom' => $user->studentProfile->classroom,
                'telemetry_json' => json_encode([
                    'sessionId' => $sessionId,
                    'totalHits' => $hits,
                    'totalShots' => $shots,
                    'avgAngle' => 110,
                    'avgPower' => 60,
                    'gravityChanges' => 0,
                    'seed' => true,
                ]),
                'analysis_text' => json_encode(['Datos forzados para C4.5']),
                'created_at' => now()->subDays(rand(1, 5)),
            ]);
        }
    }

    // ---------- PROJECTILE: probó gravedad sin éxito → reforzar_relacion_gravedad_alcance ----------
    private function addProjectileGravity(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'ext_prj_g_' . uniqid();
            $shots = 15;
            $hits = 4;
            ProjectileGameSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => 'lose',
                'level_reached' => 2,
                'score' => 800,
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
                    'gravityChanges' => 5,
                    'seed' => true,
                ]),
                'analysis_text' => json_encode(['Datos forzados para C4.5']),
                'created_at' => now()->subDays(rand(1, 5)),
            ]);
        }
    }

    // ---------- CHART: identify bajo → reforzar_leer_grafica_posicion_tiempo ----------
    private function addChartIdentify(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'ext_crt_i_' . uniqid();
            $this->createChartSession($user, $sessionId, 'identify');
        }
    }

    // ---------- CHART: predict bajo → reforzar_leer_grafica_velocidad_tiempo ----------
    private function addChartPredict(User $user, ?int $topicId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $sessionId = 'ext_crt_p_' . uniqid();
            $this->createChartSession($user, $sessionId, 'predict');
        }
    }

    private function createChartSession(User $user, string $sessionId, string $weakType): void
    {
        $types = [
            ['kind' => 'xt', 'type' => 'mru', 'topic' => 'MRU'],
            ['kind' => 'xt', 'type' => 'accel', 'topic' => 'MRUV acelerado'],
            ['kind' => 'vt', 'type' => 'vconst', 'topic' => 'MRU'],
            ['kind' => 'vt', 'type' => 'vneg', 'topic' => 'MRUV desacelerado'],
        ];
        $qTypes = ['identify', 'predict', 'choose'];

        $hits = 1;
        $total = 6;
        $byType = [
            'identify' => ['c' => 0, 't' => 0],
            'predict' => ['c' => 0, 't' => 0],
            'choose' => ['c' => 0, 't' => 0],
        ];
        $byKind = ['xt' => ['c' => 0, 't' => 0], 'vt' => ['c' => 0, 't' => 0]];

        for ($r = 1; $r <= $total; $r++) {
            $qType = $qTypes[array_rand($qTypes)];
            $graph = $types[array_rand($types)];
            // Forzar fallo en el tipo objetivo
            $correct = ($qType === $weakType) ? false : (rand(0, 10) > 6);

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
                'answered_in_seconds' => rand(2, 14) + rand(0, 99) / 100,
                'timed_out' => false,
                'wrong_answer' => $correct ? null : 'Opción incorrecta',
                'created_at' => now()->subDays(rand(1, 5)),
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
            'won' => false,
            'weak_areas' => json_encode([$weakType]),
            'strong_areas' => json_encode([]),
            'by_type_accuracy' => json_encode($byTypeAccuracy),
            'by_graph_kind_accuracy' => json_encode($byKindAccuracy),
            'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => $hits, 'seed' => true]),
            'created_at' => now()->subDays(rand(1, 5)),
        ]);
    }

    // ---------- QUESTIONS: easy bajo → reforzar_concepto_velocidad_constante ----------
    private function addQuestionsEasy(User $user): void
    {
        // Nota: por simplicidad, aquí NO creamos StudentQuestionAnswer.
        // El scoring de questions se hará bajando el accuracy_easy desde el CSV.
        // Este método queda como placeholder.
        $this->command->warn("User {$user->id}: questions_easy forzado (no-op por ahora)");
    }
}