<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CureGameSession;
use App\Models\CureLevelReward;
use App\Models\LearningLog;
use App\Models\ProjectileGameSession;
use App\Models\ProjectileLevelReward;
use App\Models\ChartRaceSession;
use App\Models\ChartRaceEvent;
use App\Models\CrosswordEvent;
use App\Models\CrosswordProgress;
use App\Models\CrosswordWord;
use App\Models\StudentProfile;
use App\Models\StudentQuestionAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ArtificialDataSeeder extends Seeder
{
    // Contraseñas repartidas
    private const PASSWORDS = ['password', '12345678', 'alumno2026'];

    // Nombres bolivianos variados
    private const NAMES = [
        'María Fernanda Quispe', 'Juan Pablo Mamani', 'Camila Rojas Vargas',
        'Diego Alejandro Flores', 'Valentina Condori Pérez', 'Mateo Sebastián Cruz',
        'Sofía Alejandra Limachi', 'Lucas Gabriel Chávez', 'Isabella María Huanca',
        'Santiago Nicolás Ríos', 'Daniela Rocío Apaza', 'Emilio Andrés Ticona',
        'Renata Valeria Choque', 'Tomás Ignacio Vedia', 'Antonella Pilar Siles',
        'Benjamín Eduardo Colque', 'Julieta Esperanza Calani', 'Facundo Rafael Nina',
        'Micaela Belén Yujra', 'Gonzalo Martín Cusi', 'Luciana Abril Laime',
        'Bruno Emanuel Callisaya', 'Catalina Pía Loza', 'Franco Iván Tórrez',
        'Agustina Soledad Blanco', 'Ramiro José Aguilar', 'Constanza Noemí Salazar',
        'Andrés Felipe Escobar', 'Delfina Milagros Cáceres', 'Ignacio Tomás Rocha',
        'Emilia Victoria Andrade', 'Leandro Nicolás Sosa', 'Renata Isabel Villca',
        'Thiago Benjamín Cárdenas', 'Julieta Antonella Llanos', 'Kevin Alexander Pinto',
        'Abril Guadalupe Chambi', 'Pablo Nicolás Calderón', 'Antonia Rocío Villarroel',
        'Franco Gabriel Ibáñez',
    ];

    // Avatares existentes
    private const AVATARS = [
        'avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 'avatar5.jpg',
        'avatar6.jpg', 'avatar7.jpg', 'avatar8.jpg', 'avatar9.jpg', 'avatar10.jpg',
    ];

    // Nicknames generados con nombres
    private const NICK_PREFIXES = ['el', 'la', 'pro', 'master', 'capitan', 'rey', 'reina', 'doctor', 'genio', 'alumno'];

    private array $credenciales = [];

    public function run(): void
    {
        $paralelos = array_merge(
            array_fill(0, 15, '4to A'),
            array_fill(0, 13, '4to B'),
            array_fill(0, 12, '4to C')
        );
        shuffle($paralelos);

        $perfiles = array_merge(
            array_fill(0, 10, 'bajo'),
            array_fill(0, 10, 'medio_bajo'),
            array_fill(0, 10, 'medio_alto'),
            array_fill(0, 10, 'alto')
        );
        shuffle($perfiles);

        $topics = Topic::pluck('id', 'name')->all();
        $crosswordWords = CrosswordWord::pluck('id', 'answer')->all();
        $questions = Question::where('is_active', 1)->get();

        DB::transaction(function () use ($paralelos, $perfiles, $topics, $crosswordWords, $questions) {
            foreach (self::NAMES as $i => $name) {
                $perfil = $perfiles[$i];
                $paralelo = $paralelos[$i];
                $password = self::PASSWORDS[$i % count(self::PASSWORDS)];
                $email = 'seed' . ($i + 1) . '@seed.test';

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
                    'nickname' => $this->makeNickname($name, $i),
                    'avatar_name' => self::AVATARS[$i % count(self::AVATARS)],
                    'xp_points' => $this->xpForPerfil($perfil),
                    'coins' => rand(0, 80),
                ]);

                $this->credenciales[] = [
                    'id' => $user->id,
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'paralelo' => $paralelo,
                    'perfil' => $perfil,
                ];

                // Generar datos de juegos
                $this->generarCrucigrama($user, $perfil, $crosswordWords);
                $this->generarCure($user, $perfil, $topics);
                $this->generarProyectiles($user, $perfil, $topics);
                $this->generarCarreraGraficas($user, $perfil, $topics);
                $this->generarPreguntas($user, $perfil, $questions, $topics);
            }
        });

        $this->guardarCredenciales();
        $this->command->info('✅ 40 estudiantes artificiales creados correctamente.');
        $this->command->info('📄 Credenciales guardadas en: storage/app/seed_credentials.txt');
    }

    private function makeNickname(string $fullName, int $i): string
    {
        $firstName = explode(' ', $fullName)[0];
        $prefix = self::NICK_PREFIXES[$i % count(self::NICK_PREFIXES)];
        return strtolower($prefix . '_' . $firstName) . ($i + 1);
    }

    private function xpForPerfil(string $perfil): int
    {
        return match ($perfil) {
            'bajo' => rand(0, 30),
            'medio_bajo' => rand(30, 80),
            'medio_alto' => rand(80, 200),
            'alto' => rand(200, 500),
            default => 0,
        };
    }

    private function pctForPerfil(string $perfil): float
    {
        return match ($perfil) {
            'bajo' => rand(15, 35) / 100,
            'medio_bajo' => rand(35, 55) / 100,
            'medio_alto' => rand(55, 75) / 100,
            'alto' => rand(75, 95) / 100,
            default => 0.5,
        };
    }

    private function shouldPlay(): bool
    {
        return rand(0, 10) < 7; // 70% jugará cada juego
    }

    // ---------- CRUCIGRAMA ----------
    private function generarCrucigrama(User $user, string $perfil, array $words): void
    {
        if (!$this->shouldPlay() || empty($words)) {
            return;
        }

        $pct = $this->pctForPerfil($perfil);
        $todas = array_keys($words);
        $cantidadAprender = (int) round(count($todas) * $pct * 0.3); // aprende hasta 30% del total
        $cantidadAprender = max(1, min($cantidadAprender, 15));

        $aprendidas = array_slice(array_values($todas), 0, $cantidadAprender);
        $wrongCount = (int) round($cantidadAprender * (1 - $pct) * 2);

        $progress = CrosswordProgress::create([
            'user_id' => $user->id,
            'current_level' => min(20, $cantidadAprender + rand(1, 3)),
            'max_level_reached' => min(20, $cantidadAprender + rand(1, 5)),
            'learned_words' => $aprendidas,
            'total_correct_attempts' => $cantidadAprender,
            'total_wrong_attempts' => $wrongCount,
            'coins_earned' => (int) floor($cantidadAprender / 2),
            'last_played_at' => now()->subDays(rand(0, 30)),
        ]);

        foreach ($aprendidas as $wordKey) {
            CrosswordEvent::create([
                'user_id' => $user->id,
                'crossword_word_id' => $words[$wordKey] ?? null,
                'level' => rand(1, 8),
                'word_attempted' => $wordKey,
                'was_correct' => true,
                'time_spent_seconds' => rand(15, 120),
                'coins_awarded' => rand(0, 1),
                'xp_awarded' => 2,
                'created_at' => now()->subDays(rand(0, 40))->subMinutes(rand(0, 500)),
            ]);
        }
    }

    // ---------- CURE ----------
    private function generarCure(User $user, string $perfil, array $topics): void
    {
        if (!$this->shouldPlay()) {
            return;
        }

        $topicId = $topics['Cinemática aplicada'] ?? $topics['Cinemática'] ?? null;
        $pct = $this->pctForPerfil($perfil);
        $sesiones = rand(2, 4);

        for ($i = 0; $i < $sesiones; $i++) {
            $hits = (int) round($pct * 4);
            $won = $hits === 4 && rand(0, 10) > 5;
            $misses = 4 - $hits;
            $sessionId = 'seed_cur_' . uniqid();

            $formulas = $this->pickFormulas($hits);
            $errores = [
                'concepto' => $misses > 0 ? rand(0, $misses) : 0,
                'calculo' => $misses > 1 ? rand(0, $misses - 1) : 0,
                'decimales' => rand(0, 1),
            ];

            CureGameSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'level_reached' => max(1, $hits),
                'niveles_completados' => $hits,
                'attempts' => 4,
                'hits' => $hits,
                'misses' => $misses,
                'accuracy' => ($hits / 4) * 100,
                'score' => $hits * 20,
                'rank' => $this->rankForPct($pct),
                'max_streak' => min(4, $hits + rand(0, 1)),
                'won' => $won,
                'pista_usadas' => rand(0, 2),
                'total_time_sec' => rand(60, 400),
                'formulas_mastered' => json_encode($formulas['mastered']),
                'formulas_review' => json_encode($formulas['review']),
                'errors_by_type' => json_encode($errores),
                'levels_json' => json_encode([]),
                'telemetry_json' => json_encode([
                    'sessionId' => $sessionId,
                    'hits' => $hits,
                    'misses' => $misses,
                    'won' => $won,
                    'seed' => true,
                ]),
                'created_at' => now()->subDays(rand(0, 45)),
            ]);

            // Level rewards
            for ($lvl = 1; $lvl <= $hits; $lvl++) {
                CureLevelReward::create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'level' => $lvl,
                    'coins_awarded' => 1,
                    'xp_awarded' => 1,
                    'created_at' => now()->subDays(rand(0, 45)),
                ]);
            }

            // LearningLog
            if ($topicId) {
                LearningLog::create([
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'game_type' => 'cure',
                    'total_time_seconds' => rand(60, 400),
                    'error_rate_percentage' => 100 - ($hits / 4 * 100),
                    'earned_xp' => $hits + ($won ? 5 : 0),
                    'attempts' => 4,
                    'correct_attempts' => $hits,
                    'completed_at' => now()->subDays(rand(0, 45)),
                ]);
            }
        }
    }

    private function pickFormulas(int $hits): array
    {
        $all = [
            'v = d / t', 'd = v · t', 't = d / v',
            'vf = v₀ + a·t', 'd = v₀·t + ½·a·t²',
            't = √(2h / g)', 'v = g · t', 'h = ½ · g · t²',
            'v₀ = d / t', 't = √(2h/g)',
        ];
        shuffle($all);
        return [
            'mastered' => array_slice($all, 0, $hits),
            'review' => array_slice($all, $hits, rand(0, 3)),
        ];
    }

    private function rankForPct(float $pct): string
    {
        return match (true) {
            $pct >= 0.9 => 'Leyenda',
            $pct >= 0.7 => 'Cirujano',
            $pct >= 0.4 => 'Residente',
            default => 'Cadete',
        };
    }

    // ---------- PROYECTILES ----------
    private function generarProyectiles(User $user, string $perfil, array $topics): void
    {
        if (!$this->shouldPlay()) {
            return;
        }

        $topicId = $topics['Cinemática'] ?? $topics['Movimiento'] ?? null;
        $pct = $this->pctForPerfil($perfil);
        $sesiones = rand(2, 4);

        for ($i = 0; $i < $sesiones; $i++) {
            $sessionId = 'seed_prj_' . uniqid();
            $hits = (int) round($pct * 10);
            $shots = 10 + rand(0, 5);
            $accuracy = $shots > 0 ? ($hits / $shots) * 100 : 0;

            ProjectileGameSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'result' => $hits >= 7 ? 'win' : 'lose',
                'level_reached' => rand(1, 5),
                'score' => $hits * 200,
                'total_time_seconds' => rand(30, 200),
                'accuracy' => $accuracy,
                'total_shots' => $shots,
                'total_hits' => $hits,
                'total_misses' => $shots - $hits,
                'classroom' => $user->studentProfile->classroom,
                'telemetry_json' => json_encode([
                    'sessionId' => $sessionId,
                    'totalHits' => $hits,
                    'totalShots' => $shots,
                    'avgAngle' => rand(45, 135),
                    'avgPower' => rand(40, 90),
                    'gravityChanges' => rand(0, 3),
                    'seed' => true,
                ]),
                'analysis_text' => json_encode(['Sesión simulada para dataset']),
                'created_at' => now()->subDays(rand(0, 45)),
            ]);

            if ($topicId) {
                LearningLog::create([
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'game_type' => 'projectiles',
                    'total_time_seconds' => rand(30, 200),
                    'error_rate_percentage' => 100 - $accuracy,
                    'earned_xp' => $hits * 5,
                    'attempts' => $shots,
                    'correct_attempts' => $hits,
                    'completed_at' => now()->subDays(rand(0, 45)),
                ]);
            }
        }
    }

    // ---------- CARRERA DE GRÁFICAS ----------
    private function generarCarreraGraficas(User $user, string $perfil, array $topics): void
    {
        if (!$this->shouldPlay()) {
            return;
        }

        $topicId = $topics['Cinemática aplicada'] ?? $topics['Cinemática'] ?? null;
        $pct = $this->pctForPerfil($perfil);
        $sesiones = rand(2, 4);

        $types = [
            ['kind' => 'xt', 'type' => 'mru', 'topic' => 'MRU'],
            ['kind' => 'xt', 'type' => 'accel', 'topic' => 'MRUV acelerado'],
            ['kind' => 'xt', 'type' => 'decel', 'topic' => 'MRUV desacelerado'],
            ['kind' => 'vt', 'type' => 'vconst', 'topic' => 'MRU'],
            ['kind' => 'vt', 'type' => 'vpos', 'topic' => 'MRUV acelerado'],
            ['kind' => 'vt', 'type' => 'vneg', 'topic' => 'MRUV desacelerado'],
        ];
        $qTypes = ['identify', 'predict', 'choose'];

        for ($i = 0; $i < $sesiones; $i++) {
            $sessionId = 'seed_cr_' . uniqid();
            $totalRounds = 6;

            $weakAreas = [];
            $strongAreas = [];
            $byType = ['identify' => ['c' => 0, 't' => 0], 'predict' => ['c' => 0, 't' => 0], 'choose' => ['c' => 0, 't' => 0]];
            $byKind = ['xt' => ['c' => 0, 't' => 0], 'vt' => ['c' => 0, 't' => 0]];
            $hits = 0;

            for ($r = 1; $r <= $totalRounds; $r++) {
                $qType = $qTypes[array_rand($qTypes)];
                $graph = $types[array_rand($types)];
                $correct = rand(0, 100) < ($pct * 100);

                if ($correct) $hits++;
                else $weakAreas[$graph['topic']] = true;

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
                    'answered_in_seconds' => rand(1, 14) + rand(0, 99) / 100,
                    'timed_out' => false,
                    'wrong_answer' => $correct ? null : 'Opción incorrecta',
                    'created_at' => now()->subDays(rand(0, 45)),
                ]);
            }

            foreach ($byType as $t => $d) {
                $pctT = $d['t'] > 0 ? ($d['c'] / $d['t']) * 100 : 0;
                if ($pctT < 50) $byType[$t]['pct'] = round($pctT, 1);
                else $strongAreas[] = $t;
            }

            $byTypeAccuracy = [];
            foreach ($byType as $t => $d) {
                $byTypeAccuracy[$t] = $d['t'] > 0 ? round(($d['c'] / $d['t']) * 100, 1) : 0;
            }
            $byKindAccuracy = [];
            foreach ($byKind as $k => $d) {
                $byKindAccuracy[$k] = $d['t'] > 0 ? round(($d['c'] / $d['t']) * 100, 1) : 0;
            }

            $session = ChartRaceSession::create([
                'user_id' => $user->id,
                'classroom' => $user->studentProfile->classroom,
                'session_id' => $sessionId,
                'hits' => $hits,
                'misses' => $totalRounds - $hits,
                'accuracy' => round(($hits / $totalRounds) * 100, 2),
                'total_time_sec' => rand(30, 120),
                'exploded' => false,
                'completed' => true,
                'won' => $hits >= 5,
                'weak_areas' => json_encode(array_keys($weakAreas)),
                'strong_areas' => json_encode(array_values(array_unique($strongAreas))),
                'by_type_accuracy' => json_encode($byTypeAccuracy),
                'by_graph_kind_accuracy' => json_encode($byKindAccuracy),
                'telemetry_json' => json_encode(['sessionId' => $sessionId, 'hits' => $hits, 'seed' => true]),
                'created_at' => now()->subDays(rand(0, 45)),
            ]);

            if ($topicId) {
                LearningLog::create([
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'game_type' => 'chart_race',
                    'total_time_seconds' => rand(30, 120),
                    'error_rate_percentage' => 100 - (($hits / $totalRounds) * 100),
                    'earned_xp' => $hits,
                    'attempts' => $totalRounds,
                    'correct_attempts' => $hits,
                    'completed_at' => now()->subDays(rand(0, 45)),
                ]);
            }
        }
    }

    // ---------- PREGUNTAS ----------
    private function generarPreguntas(User $user, string $perfil, $questions, array $topics): void
    {
        if (!$this->shouldPlay() || $questions->isEmpty()) {
            return;
        }

        $pct = $this->pctForPerfil($perfil);
        $cantidad = rand(5, 15);

        $topicsMap = $topics;

        for ($i = 0; $i < $cantidad; $i++) {
            $question = $questions->random();
            $options = QuestionOption::where('question_id', $question->id)->get();
            $correctOption = $options->firstWhere('is_correct', true) ?? $options->first();
            if (!$correctOption) continue;

            $isCorrect = rand(0, 100) < ($pct * 100);
            $chosenOption = $isCorrect
                ? $correctOption
                : ($options->where('is_correct', false)->first() ?? $correctOption);

            StudentQuestionAnswer::create([
                'student_id' => $user->id,
                'question_id' => $question->id,
                'question_option_id' => $chosenOption->id,
                'is_correct' => $isCorrect,
                'is_skipped' => false,
                'xp_earned' => $isCorrect ? $question->xp_reward : 0,
                'answered_at' => now()->subDays(rand(0, 45)),
            ]);

            $topicId = $question->topic_id;
            if ($topicId) {
                $log = LearningLog::firstOrNew([
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'game_type' => 'challenge',
                ]);

                $log->attempts = ($log->attempts ?? 0) + 1;
                if ($isCorrect) {
                    $log->correct_attempts = ($log->correct_attempts ?? 0) + 1;
                } else {
                    $log->skipped_attempts = ($log->skipped_attempts ?? 0) + 1;
                }
                $log->error_rate_percentage = $log->attempts > 0
                    ? round((($log->attempts - $log->correct_attempts) / $log->attempts) * 100, 2)
                    : 0;
                $log->earned_xp = ($log->earned_xp ?? 0) + ($isCorrect ? $question->xp_reward : 0);
                $log->total_time_seconds = ($log->total_time_seconds ?? 0) + rand(10, 60);
                $log->completed_at = now()->subDays(rand(0, 45));
                $log->save();
            }
        }
    }

    // ---------- GUARDAR CREDENCIALES ----------
    private function guardarCredenciales(): void
    {
        $path = storage_path('app/seed_credentials.txt');
        $lines = ["CREDENCIALES DE USUARIOS ARTIFICIALES", str_repeat('=', 60), ''];

        foreach ($this->credenciales as $c) {
            $lines[] = sprintf(
                'ID: %d | %s | %s | %s | %s | %s',
                $c['id'],
                str_pad($c['name'], 32),
                str_pad($c['email'], 25),
                str_pad($c['password'], 15),
                str_pad($c['paralelo'], 8),
                $c['perfil']
            );
        }

        file_put_contents($path, implode("\n", $lines));
    }
}