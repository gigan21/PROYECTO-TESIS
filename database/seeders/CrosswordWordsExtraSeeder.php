<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CrosswordLevel;
use App\Models\CrosswordWord;
use App\Models\User;
use Illuminate\Database\Seeder;

class CrosswordWordsExtraSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::query()->where('role', 'docente')->orderBy('id')->first();
        if (!$teacher) {
            $this->command->warn('No hay docente. Seeder omitido.');
            return;
        }

        $words = $this->getWords();

        foreach ($words as $word) {
            CrosswordWord::updateOrCreate(
                ['answer' => $word['answer'], 'level' => $word['level']],
                [
                    'user_id'    => $teacher->id,
                    'clue'       => $word['clue'],
                    'difficulty' => CrosswordLevel::difficultyForLevel($word['level']),
                    'is_active'  => true,
                ]
            );
        }

        $this->command->info('Palabras extra cargadas: ' . count($words));
    }

    private function getWords(): array
    {
        return [
            // ==================== NIVEL 1 (5 palabras) ====================
            ['answer' => 'MRU',     'level' => 1, 'clue' => 'Movimiento con velocidad constante'],
            ['answer' => 'MASA',    'level' => 1, 'clue' => 'Cantidad de materia de un cuerpo'],
            ['answer' => 'EJE',     'level' => 1, 'clue' => 'Línea imaginaria de referencia'],
            ['answer' => 'TIEMPO',  'level' => 1, 'clue' => 'Magnitud que mide la duración de un fenómeno'],
            ['answer' => 'PUNTO',   'level' => 1, 'clue' => 'Lugar geométrico sin dimensiones'],

            // ==================== NIVEL 2 (5 palabras) ====================
            ['answer' => 'MRUV',    'level' => 2, 'clue' => 'Movimiento con aceleración constante'],
            ['answer' => 'RECTA',   'level' => 2, 'clue' => 'Trayectoria en línea recta'],
            ['answer' => 'RAPIDEZ', 'level' => 2, 'clue' => 'Módulo de la velocidad sin dirección'],
            ['answer' => 'CUERPO',  'level' => 2, 'clue' => 'Objeto físico con masa'],
            ['answer' => 'METRO',   'level' => 2, 'clue' => 'Unidad SI de longitud'],

            // ==================== NIVEL 3 (5 palabras) ====================
            ['answer' => 'GRAVEDAD', 'level' => 3, 'clue' => 'Aceleración de la atracción terrestre'],
            ['answer' => 'ESPACIO',  'level' => 3, 'clue' => 'Extensión donde ocurre el movimiento'],
            ['answer' => 'CINEMA',   'level' => 3, 'clue' => 'Prefijo relacionado con el movimiento'],
            ['answer' => 'SEGUNDO',  'level' => 3, 'clue' => 'Unidad SI de tiempo'],
            ['answer' => 'VELOZ',    'level' => 3, 'clue' => 'Que se mueve con gran rapidez'],

            // ==================== NIVEL 4 (5 palabras) ====================
            ['answer' => 'PARABOLA',   'level' => 4, 'clue' => 'Trayectoria de un proyectil'],
            ['answer' => 'CURVA',      'level' => 4, 'clue' => 'Trayectoria no rectilínea'],
            ['answer' => 'MOVIL',      'level' => 4, 'clue' => 'Cuerpo que se mueve'],
            ['answer' => 'POSICION',   'level' => 4, 'clue' => 'Ubicación de un cuerpo en el espacio'],
            ['answer' => 'SENTIDO',    'level' => 4, 'clue' => 'Orientación del vector velocidad'],

            // ==================== NIVEL 5 (5 palabras) ====================
            ['answer' => 'FRECUENCIA',  'level' => 5, 'clue' => 'Número de vueltas por unidad de tiempo'],
            ['answer' => 'MAGNITUD',    'level' => 5, 'clue' => 'Cantidad física medible'],
            ['answer' => 'VECTOR',      'level' => 5, 'clue' => 'Magnitud con dirección y sentido'],
            ['answer' => 'ESCALAR',     'level' => 5, 'clue' => 'Magnitud sin dirección'],
            ['answer' => 'MODULO',      'level' => 5, 'clue' => 'Longitud de un vector'],

            // ==================== NIVEL 6 (4 palabras) ====================
            ['answer' => 'CENTRIPETA',  'level' => 6, 'clue' => 'Aceleración hacia el centro de una curva'],
            ['answer' => 'CIRCULAR',    'level' => 6, 'clue' => 'Movimiento en trayectoria de círculo'],
            ['answer' => 'ORBITA',      'level' => 6, 'clue' => 'Trayectoria de un cuerpo alrededor de otro'],
            ['answer' => 'RADIO',       'level' => 6, 'clue' => 'Distancia del centro a la circunferencia'],

            // ==================== NIVEL 7 (4 palabras) ====================
            ['answer' => 'DESPLAZAMIENTO', 'level' => 7, 'clue' => 'Cambio de posición de un cuerpo'],
            ['answer' => 'DISTANCIA',      'level' => 7, 'clue' => 'Longitud total recorrida'],
            ['answer' => 'INTERVALO',      'level' => 7, 'clue' => 'Diferencia entre dos instantes'],
            ['answer' => 'INSTANTE',       'level' => 7, 'clue' => 'Momento específico en el tiempo'],

            // ==================== NIVEL 8 (4 palabras) ====================
            ['answer' => 'VELOCIDAD',    'level' => 8, 'clue' => 'Cambio de posición por unidad de tiempo'],
            ['answer' => 'MEDIA',        'level' => 8, 'clue' => 'Promedio entre dos valores'],
            ['answer' => 'CONSTANTE',    'level' => 8, 'clue' => 'Que no cambia en el tiempo'],
            ['answer' => 'VARIABLE',     'level' => 8, 'clue' => 'Que cambia en el tiempo'],

            // ==================== NIVEL 9 (4 palabras) ====================
            ['answer' => 'ACELERACION',  'level' => 9, 'clue' => 'Cambio de velocidad por unidad de tiempo'],
            ['answer' => 'NEGATIVA',     'level' => 9, 'clue' => 'Aceleración que reduce la velocidad'],
            ['answer' => 'POSITIVA',     'level' => 9, 'clue' => 'Aceleración que aumenta la velocidad'],
            ['answer' => 'VARIACION',    'level' => 9, 'clue' => 'Cambio de una magnitud'],

            // ==================== NIVEL 10 (4 palabras) ====================
            ['answer' => 'TRAYECTORIA',  'level' => 10, 'clue' => 'Línea descrita por un cuerpo en movimiento'],
            ['answer' => 'CAMINO',       'level' => 10, 'clue' => 'Ruta seguida por un móvil'],
            ['answer' => 'RECORRIDO',    'level' => 10, 'clue' => 'Espacio total atravesado'],
            ['answer' => 'LINEA',        'level' => 10, 'clue' => 'Sucesión continua de puntos'],

            // ==================== NIVEL 11 (3 palabras) ====================
            ['answer' => 'INERCIA',      'level' => 11, 'clue' => 'Propiedad de mantener el estado de reposo o movimiento'],
            ['answer' => 'FUERZA',       'level' => 11, 'clue' => 'Interacción que modifica el movimiento'],
            ['answer' => 'EQUILIBRIO',   'level' => 11, 'clue' => 'Estado sin aceleración neta'],

            // ==================== NIVEL 12 (3 palabras) ====================
            ['answer' => 'MOMENTO',      'level' => 12, 'clue' => 'Producto de masa por velocidad'],
            ['answer' => 'LINEAL',       'level' => 12, 'clue' => 'Relativo a la línea recta'],
            ['answer' => 'CHOQUE',       'level' => 12, 'clue' => 'Interacción brusca entre dos cuerpos'],

            // ==================== NIVEL 13 (3 palabras) ====================
            ['answer' => 'NEWTON',       'level' => 13, 'clue' => 'Unidad SI de fuerza'],
            ['answer' => 'DINA',         'level' => 13, 'clue' => 'Unidad de fuerza en el sistema CGS'],
            ['answer' => 'PESO',         'level' => 13, 'clue' => 'Fuerza con que la Tierra atrae a un cuerpo'],

            // ==================== NIVEL 14 (3 palabras) ====================
            ['answer' => 'ENERGIA',      'level' => 14, 'clue' => 'Capacidad para realizar un trabajo'],
            ['answer' => 'CINETICA',     'level' => 14, 'clue' => 'Energía asociada al movimiento'],
            ['answer' => 'POTENCIAL',    'level' => 14, 'clue' => 'Energía asociada a la posición'],

            // ==================== NIVEL 15 (3 palabras) ====================
            ['answer' => 'TRABAJO',      'level' => 15, 'clue' => 'Producto de fuerza por distancia'],
            ['answer' => 'JOULE',        'level' => 15, 'clue' => 'Unidad SI de energía y trabajo'],
            ['answer' => 'CALORIA',      'level' => 15, 'clue' => 'Unidad de energía en calor'],

            // ==================== NIVEL 16 (2 palabras) ====================
            ['answer' => 'POTENCIA',     'level' => 16, 'clue' => 'Rapidez con que se realiza un trabajo'],
            ['answer' => 'VATIO',        'level' => 16, 'clue' => 'Unidad SI de potencia'],

            // ==================== NIVEL 17 (2 palabras) ====================
            ['answer' => 'IMPULSO',      'level' => 17, 'clue' => 'Producto de fuerza por tiempo'],
            ['answer' => 'CANTIDAD',     'level' => 17, 'clue' => 'Magnitud medible expresada con número y unidad'],

            // ==================== NIVEL 18 (2 palabras) ====================
            ['answer' => 'GALILEO',      'level' => 18, 'clue' => 'Científico que estudió la caída de los cuerpos'],
            ['answer' => 'ARISTOTELES',  'level' => 18, 'clue' => 'Filósofo griego que estudió el movimiento'],

            // ==================== NIVEL 19 (2 palabras) ====================
            ['answer' => 'CORIOLIS',     'level' => 19, 'clue' => 'Efecto de desviación por rotación terrestre'],
            ['answer' => 'ELIPTICA',     'level' => 19, 'clue' => 'Trayectoria en forma de elipse'],

            // ==================== NIVEL 20 (2 palabras) ====================
            ['answer' => 'CINEMATICA',   'level' => 20, 'clue' => 'Rama de la mecánica que estudia el movimiento'],
            ['answer' => 'MECANICA',     'level' => 20, 'clue' => 'Rama de la física que estudia el movimiento y las fuerzas'],
        ];
    }
}