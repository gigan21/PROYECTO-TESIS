<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CrosswordLevel;
use App\Models\CrosswordWord;
use App\Models\User;
use Illuminate\Database\Seeder;

class CrosswordWordsSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::query()->where('role', 'docente')->orderBy('id')->first();

        if ($teacher === null) {
            $this->command?->warn('CrosswordWordsSeeder: no hay usuario docente.');

            return;
        }

        $entries = [
            1 => ['answer' => 'masa', 'clue' => 'Cantidad de materia de un cuerpo'],
            2 => ['answer' => 'metro', 'clue' => 'Unidad SI de longitud'],
            3 => ['answer' => 'tiempo', 'clue' => 'Magnitud que mide la duración de un fenómeno'],
            4 => ['answer' => 'velocidad', 'clue' => 'Rapidez con dirección en el movimiento'],
            5 => ['answer' => 'aceleracion', 'clue' => 'Cambio de velocidad por unidad de tiempo'],
            6 => ['answer' => 'fuerza', 'clue' => 'Magnitud capaz de modificar el movimiento'],
            7 => ['answer' => 'newton', 'clue' => 'Unidad SI de fuerza'],
            8 => ['answer' => 'mru', 'clue' => 'Movimiento rectilíneo uniforme'],
            9 => ['answer' => 'mruv', 'clue' => 'Movimiento con aceleración constante'],
            10 => ['answer' => 'desplazamiento', 'clue' => 'Vector que une posición inicial y final'],
            11 => ['answer' => 'trayectoria', 'clue' => 'Línea que sigue un móvil en el espacio'],
            12 => ['answer' => 'referencial', 'clue' => 'Sistema desde el cual se observa el movimiento'],
            13 => ['answer' => 'inercia', 'clue' => 'Tendencia a mantener el estado de movimiento'],
            14 => ['answer' => 'friccion', 'clue' => 'Fuerza que se opone al movimiento relativo'],
            15 => ['answer' => 'peso', 'clue' => 'Fuerza gravitacional sobre un cuerpo'],
            16 => ['answer' => 'gravedad', 'clue' => 'Aceleración aproximada de 9,8 m/s² en la Tierra'],
            17 => ['answer' => 'caida', 'clue' => 'Movimiento vertical bajo la gravedad'],
            18 => ['answer' => 'componente', 'clue' => 'Parte de un vector en un eje'],
            19 => ['answer' => 'parabolico', 'clue' => 'Tipo de tiro con trayectoria curva'],
            20 => ['answer' => 'cinematica', 'clue' => 'Rama que describe el movimiento sin causas'],
        ];

        foreach ($entries as $level => $data) {
            $answer = CrosswordWord::normalizeAnswer($data['answer']);

            CrosswordWord::query()->updateOrCreate(
                ['answer' => $answer, 'level' => $level],
                [
                    'user_id' => $teacher->id,
                    'topic_id' => null,
                    'clue' => $data['clue'],
                    'difficulty' => CrosswordLevel::difficultyForLevel($level),
                    'is_active' => true,
                ]
            );
        }
    }
}
