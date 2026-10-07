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
    
        // ===== TUS 20 PALABRAS ORIGINALES (1 por nivel) =====
        $entries = [
            1  => ['answer' => 'masa',          'clue' => 'Cantidad de materia de un cuerpo'],
            2  => ['answer' => 'metro',         'clue' => 'Unidad SI de longitud'],
            3  => ['answer' => 'tiempo',        'clue' => 'Magnitud que mide la duración de un fenómeno'],
            4  => ['answer' => 'velocidad',     'clue' => 'Rapidez con dirección en el movimiento'],
            5  => ['answer' => 'aceleracion',   'clue' => 'Cambio de velocidad por unidad de tiempo'],
            6  => ['answer' => 'fuerza',        'clue' => 'Magnitud capaz de modificar el movimiento'],
            7  => ['answer' => 'newton',        'clue' => 'Unidad SI de fuerza'],
            8  => ['answer' => 'mru',           'clue' => 'Movimiento rectilíneo uniforme'],
            9  => ['answer' => 'mruv',          'clue' => 'Movimiento con aceleración constante'],
            10 => ['answer' => 'desplazamiento','clue' => 'Vector que une posición inicial y final'],
            11 => ['answer' => 'trayectoria',   'clue' => 'Línea que sigue un móvil en el espacio'],
            12 => ['answer' => 'referencial',   'clue' => 'Sistema desde el cual se observa el movimiento'],
            13 => ['answer' => 'inercia',       'clue' => 'Tendencia a mantener el estado de movimiento'],
            14 => ['answer' => 'friccion',      'clue' => 'Fuerza que se opone al movimiento relativo'],
            15 => ['answer' => 'peso',          'clue' => 'Fuerza gravitacional sobre un cuerpo'],
            16 => ['answer' => 'gravedad',      'clue' => 'Aceleración aproximada de 9,8 m/s² en la Tierra'],
            17 => ['answer' => 'caida',         'clue' => 'Movimiento vertical bajo la gravedad'],
            18 => ['answer' => 'componente',    'clue' => 'Parte de un vector en un eje'],
            19 => ['answer' => 'parabolico',    'clue' => 'Tipo de tiro con trayectoria curva'],
            20 => ['answer' => 'cinematica',    'clue' => 'Rama que describe el movimiento sin causas'],
        ];
    
        foreach ($entries as $level => $data) {
            $answer = CrosswordWord::normalizeAnswer($data['answer']);
    
            CrosswordWord::query()->updateOrCreate(
                ['answer' => $answer, 'level' => $level],
                [
                    'user_id'    => $teacher->id,
                    'topic_id'   => null,
                    'clue'       => $data['clue'],
                    'difficulty' => CrosswordLevel::difficultyForLevel($level),
                    'is_active'  => true,
                ]
            );
        }
    
        // ===== NUEVAS PALABRAS (4 por nivel del 1 al 10) =====
        $extraWords = [
    
            // Nivel 1
            ['answer' => 'espacio',      'clue' => 'Lugar que ocupa un cuerpo o donde ocurre el movimiento', 'level' => 1],
            ['answer' => 'posicion',     'clue' => 'Lugar que ocupa un móvil respecto a un sistema de referencia', 'level' => 1],
            ['answer' => 'distancia',    'clue' => 'Longitud del camino recorrido por un móvil', 'level' => 1],
            ['answer' => 'rapidez',      'clue' => 'Magnitud escalar que indica qué tan rápido se mueve un cuerpo', 'level' => 1],
    
            // Nivel 2
            ['answer' => 'vector',       'clue' => 'Magnitud que tiene módulo, dirección y sentido', 'level' => 2],
            ['answer' => 'escalar',      'clue' => 'Magnitud que solo tiene valor numérico, sin dirección', 'level' => 2],
            ['answer' => 'origen',       'clue' => 'Punto de partida o cero del sistema de referencia', 'level' => 2],
            ['answer' => 'instante',     'clue' => 'Momento preciso en el tiempo', 'level' => 2],
    
            // Nivel 3
            ['answer' => 'intervalo',    'clue' => 'Diferencia de tiempo entre dos instantes', 'level' => 3],
            ['answer' => 'modulo',       'clue' => 'Valor numérico o longitud de un vector', 'level' => 3],
            ['answer' => 'direccion',    'clue' => 'Línea sobre la cual actúa un vector', 'level' => 3],
            ['answer' => 'sentido',      'clue' => 'Orientación de un vector sobre su dirección', 'level' => 3],
    
            // Nivel 4
            ['answer' => 'recta',        'clue' => 'Trayectoria más simple del movimiento uniforme', 'level' => 4],
            ['answer' => 'curva',        'clue' => 'Trayectoria que no es una línea recta', 'level' => 4],
            ['answer' => 'uniforme',     'clue' => 'Movimiento con velocidad constante', 'level' => 4],
            ['answer' => 'variado',      'clue' => 'Movimiento en el que cambia la velocidad', 'level' => 4],
    
            // Nivel 5
            ['answer' => 'promedio',     'clue' => 'Velocidad calculada como desplazamiento total entre tiempo total', 'level' => 5],
            ['answer' => 'instantanea',  'clue' => 'Velocidad en un instante determinado', 'level' => 5],
            ['answer' => 'positiva',     'clue' => 'Aceleración que aumenta la velocidad', 'level' => 5],
            ['answer' => 'negativa',     'clue' => 'Aceleración que disminuye la velocidad (frenado)', 'level' => 5],
    
            // Nivel 6
            ['answer' => 'grafica',      'clue' => 'Representación visual de posición, velocidad o aceleración vs tiempo', 'level' => 6],
            ['answer' => 'pendiente',    'clue' => 'Inclinación de una recta en una gráfica de movimiento', 'level' => 6],
            ['answer' => 'area',         'clue' => 'En gráfica v-t representa el desplazamiento', 'level' => 6],
            ['answer' => 'ecuacion',     'clue' => 'Fórmula matemática que describe el movimiento', 'level' => 6],
    
            // Nivel 7
            ['answer' => 'caidalibre',   'clue' => 'Movimiento vertical solo bajo la acción de la gravedad', 'level' => 7],
            ['answer' => 'tirovertical', 'clue' => 'Lanzamiento hacia arriba o abajo en línea recta', 'level' => 7],
            ['answer' => 'altura',       'clue' => 'Distancia vertical alcanzada por un proyectil', 'level' => 7],
            ['answer' => 'alcance',      'clue' => 'Distancia horizontal máxima de un proyectil', 'level' => 7],
    
            // Nivel 8
            ['answer' => 'proyectil',    'clue' => 'Cuerpo lanzado que sigue una trayectoria parabólica', 'level' => 8],
            ['answer' => 'angulo',       'clue' => 'Inclinación con la que se lanza un proyectil', 'level' => 8],
            ['answer' => 'horizontal',   'clue' => 'Componente de la velocidad paralela al suelo', 'level' => 8],
            ['answer' => 'vertical',     'clue' => 'Componente de la velocidad perpendicular al suelo', 'level' => 8],
    
            // Nivel 9
            ['answer' => 'composicion',  'clue' => 'Suma de dos o más movimientos simultáneos', 'level' => 9],
            ['answer' => 'independencia','clue' => 'Principio que dice que los movimientos vertical y horizontal no se afectan', 'level' => 9],
            ['answer' => 'relativo',     'clue' => 'Movimiento observado desde un sistema de referencia en movimiento', 'level' => 9],
            ['answer' => 'absoluto',     'clue' => 'Movimiento respecto a un sistema de referencia fijo', 'level' => 9],
    
            // Nivel 10
            ['answer' => 'acelerometro', 'clue' => 'Instrumento que mide la aceleración', 'level' => 10],
            ['answer' => 'cronometro',   'clue' => 'Instrumento usado para medir intervalos de tiempo', 'level' => 10],
            ['answer' => 'cinemometro',  'clue' => 'Dispositivo que mide la velocidad de un vehículo', 'level' => 10],
            ['answer' => 'odometro',     'clue' => 'Instrumento que mide la distancia recorrida', 'level' => 10],
        ];
    
        foreach ($extraWords as $word) {
            $answer = CrosswordWord::normalizeAnswer($word['answer']);
    
            CrosswordWord::query()->updateOrCreate(
                ['answer' => $answer, 'level' => $word['level']],
                [
                    'user_id'    => $teacher->id,
                    'topic_id'   => null,
                    'clue'       => $word['clue'],
                    'difficulty' => CrosswordLevel::difficultyForLevel($word['level']),
                    'is_active'  => true,
                ]
            );
        }
    }
}
