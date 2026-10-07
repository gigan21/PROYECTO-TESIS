<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordPinController;
use App\Http\Controllers\Controllers_Docentes\AttendanceExclusionController;
use App\Http\Controllers\Controllers_Docentes\AttendanceHubController;
use App\Http\Controllers\Controllers_Docentes\AttendanceRecordController;
use App\Http\Controllers\Controllers_Docentes\AttendanceSessionController;
use App\Http\Controllers\Controllers_Docentes\ChallengeRoomController;
use App\Http\Controllers\Controllers_Docentes\DocenteDashboardController;
use App\Http\Controllers\Controllers_Docentes\DocenteStudentsController;
use App\Http\Controllers\Controllers_Docentes\CrosswordStatsController;
use App\Http\Controllers\Controllers_Docentes\CrosswordWordController;
use App\Http\Controllers\Controllers_Docentes\QuestionController;
use App\Http\Controllers\Controllers_Estudiantes\ChallengePlayController;
use App\Http\Controllers\Controllers_Estudiantes\CrosswordPlayController;
use App\Http\Controllers\Controllers_Estudiantes\GamificationController;
use App\Http\Controllers\Controllers_Estudiantes\StudentHomeController;
use App\Http\Controllers\Controllers_Estudiantes\StudentProfileController;
use App\Http\Controllers\Controllers_Estudiantes\StudentQuizController;
use App\Http\Controllers\Controllers_Admin\AdminDashboardController;
use App\Http\Controllers\Controllers_Admin\AdminUserController;
use App\Http\Controllers\Controllers_Estudiantes\ProjectileGameController;
use App\Http\Controllers\Estudiante\TiendaController;
use App\Http\Controllers\Controllers_Estudiantes\PetShopController;
use App\Http\Controllers\Controllers_Estudiantes\ShopController;
use App\Support\Classroom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirección inicial según rol
Route::get('/', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'docente' => redirect()->route('docente.dashboard'),
        'estudiante' => redirect()->route('estudiante.inicio'),
        default => abort(403),
    };
});

// Rutas para visitantes NO AUTENTICADOS (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    
    Route::get('/recuperar-contrasena', [ResetPasswordPinController::class, 'showEmailForm'])->name('password.pin.request');
    Route::post('/recuperar-contrasena', [ResetPasswordPinController::class, 'sendPin'])->name('password.pin.email');

    Route::get('/verificar-pin', [ResetPasswordPinController::class, 'showVerifyForm'])->name('password.pin.verify.form');
    Route::post('/restablecer-contrasena', [ResetPasswordPinController::class, 'resetPassword'])->name('password.pin.update');
});

// Cerrar sesión (Usuarios autenticados)
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Rutas para DOCENTES
Route::middleware(['auth', 'teacher'])->prefix('docente')->name('docente.')->group(function () {
    Route::get('/dashboard', [DocenteDashboardController::class, 'index'])->name('dashboard');
    Route::get('/estudiantes', [DocenteStudentsController::class, 'index'])->name('estudiantes');

    Route::prefix('asistencia')->name('asistencia.')->group(function () {
        Route::get('/', [AttendanceHubController::class, 'index'])->name('index');

        Route::get('/paralelo/{classroom}', [AttendanceHubController::class, 'showClassroom'])
            ->where('classroom', Classroom::slugPattern())
            ->name('classroom');

        Route::get('/paralelo/{classroom}/crear', [AttendanceSessionController::class, 'create'])
            ->where('classroom', Classroom::slugPattern())
            ->name('sesiones.create');

   
    Route::get('/{question}', [QuestionController::class, 'show'])->name('show');
    Route::get('/{question}/editar', [QuestionController::class, 'edit'])->name('edit');
    Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
    Route::patch('/{question}/estado', [QuestionController::class, 'toggle'])->name('toggle');

        Route::post('/paralelo/{classroom}/sesiones', [AttendanceSessionController::class, 'store'])
            ->where('classroom', Classroom::slugPattern())
            ->name('sesiones.store');

        Route::get('/sesiones/{attendance_session}', [AttendanceSessionController::class, 'show'])->name('sesiones.show');
        Route::post('/sesiones/{attendance_session}/cerrar', [AttendanceSessionController::class, 'close'])->name('sesiones.close');
        Route::post('/sesiones/{attendance_session}/registros', [AttendanceRecordController::class, 'upsert'])->name('registros.upsert');
        Route::post('/sesiones/{attendance_session}/excluir', [AttendanceExclusionController::class, 'store'])->name('exclusiones.store');
    });

    Route::prefix('preguntas')->name('preguntas.')->group(function () {
        Route::get('/', [QuestionController::class, 'index'])->name('index');
        Route::get('/crear', [QuestionController::class, 'create'])->name('create');
        Route::post('/', [QuestionController::class, 'store'])->name('store');

        // Página principal de restablecimiento
    Route::get('/restablecer', [QuestionController::class, 'resetIndex'])
    ->name('reset.index');

// Restablecer preguntas de un estudiante
Route::post('/restablecer/estudiante/{student}', [QuestionController::class, 'resetStudent'])
    ->name('reset.student');

// Restablecer preguntas masivamente
Route::post('/restablecer/masivo', [QuestionController::class, 'resetBulk'])
    ->name('reset.bulk');

// Restablecer una pregunta específica
Route::post('/{question}/restablecer', [QuestionController::class, 'resetSingleQuestion'])
    ->name('reset.single');
        Route::get('/{question}', [QuestionController::class, 'show'])->name('show');
        Route::get('/{question}/editar', [QuestionController::class, 'edit'])->name('edit');
        Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
        Route::patch('/{question}/estado', [QuestionController::class, 'toggle'])->name('toggle');
    });

    Route::resource('crucigrama', CrosswordWordController::class)
        ->parameters(['crucigrama' => 'crosswordWord']);

    Route::get('crucigrama-stats', [CrosswordStatsController::class, 'index'])->name('crucigrama.stats');
    Route::get('crucigrama-stats/{student}', [CrosswordStatsController::class, 'show'])->name('crucigrama.stats.show');

    Route::prefix('desafios')->name('desafios.')->group(function () {
        Route::get('/', [ChallengeRoomController::class, 'index'])->name('index');
        Route::get('/crear', [ChallengeRoomController::class, 'create'])->name('create');
        Route::post('/', [ChallengeRoomController::class, 'store'])->name('store');
        Route::get('/{challenge_room}', [ChallengeRoomController::class, 'show'])->name('show');
        Route::post('/{challenge_room}/iniciar', [ChallengeRoomController::class, 'start'])->name('start');
        Route::post('/{challenge_room}/finalizar', [ChallengeRoomController::class, 'finish'])->name('finish');
    });
});

// Rutas para ESTUDIANTES (Un solo bloque limpio)
Route::middleware(['auth', 'student'])->prefix('estudiante')->group(function () {
    
    Route::get('/inicio', [StudentHomeController::class, 'index'])->name('estudiante.inicio');

    // --- RUTAS DEL PERFIL ---
    Route::get('/perfil', [StudentProfileController::class, 'edit'])
        ->name('student.profile');

    Route::patch('/perfil', [StudentProfileController::class, 'update'])
        ->name('student.profile.update');

    // --- RUTA DE GAMIFICACIÓN ---
    Route::post('/claim-reading-xp', [GamificationController::class, 'claimReadingXp'])
        ->name('student.claim.reading.xp');
        Route::get('/desafio', [ChallengePlayController::class, 'enter'])
        ->name('estudiante.desafio.ingresar');
    Route::get('/desafio/{code}', [ChallengePlayController::class, 'show'])
        ->name('estudiante.desafio.show');

    Route::post('/desafio/{code}/preguntas/{question}/responder', [ChallengePlayController::class, 'answer'])
        ->name('estudiante.desafio.responder');

    // --- RUTA DE TIENDA ---
        
        Route::get('/tienda', [ShopController::class, 'index'])->name('estudiante.tienda');
        Route::prefix('tienda')->name('estudiante.tienda.')->group(function () {
            Route::post('{item}/comprar',     [ShopController::class, 'buy'])->name('buy');
            Route::post('{item}/equipar',     [ShopController::class, 'equip'])->name('equip');
            Route::post('{item}/desequipar',  [ShopController::class, 'unequip'])->name('unequip');
        });

          // --- TIENDA: mascotas ---
    Route::prefix('tienda/mascotas')->name('estudiante.tienda.pets.')->group(function () {
        Route::post('{petId}/comprar',    [PetShopController::class, 'buy'])->name('buy');
        Route::post('{petId}/equipar',    [PetShopController::class, 'equip'])->name('equip');
        Route::post('desequipar',         [PetShopController::class, 'unequip'])->name('unequip');
    });

    Route::get('/preguntas', [StudentQuizController::class, 'index'])
        ->name('estudiante.preguntas.index');
    Route::post('/preguntas/{question}/skip', [StudentQuizController::class, 'skip'])
    ->name('estudiante.preguntas.skip');
    
    Route::post('/preguntas/{question}/responder', [StudentQuizController::class, 'answer'])
        ->name('estudiante.preguntas.responder');
     
    Route::get('/crucigrama', [CrosswordPlayController::class, 'index'])->name('estudiante.crucigrama');
    Route::prefix('crucigrama/api')->name('estudiante.crucigrama.api.')->group(function () {
        Route::get('nivel', [CrosswordPlayController::class, 'getLevelWords'])->name('nivel');
        Route::get('progreso', [CrosswordPlayController::class, 'getProgress'])->name('progreso');
        Route::post('guardar', [CrosswordPlayController::class, 'saveProgress'])->name('guardar');
        Route::post('reiniciar', [CrosswordPlayController::class, 'resetProgress'])->name('reiniciar');
    });

    Route::get('/estudiante/simulaciones', function () {
        return view('estudiante.juegos.simulacion');
    })->name('estudiante.simulaciones');
    
    Route::get('/estudiante/simulador-proyectiles', [GamificationController::class, 'juegoProyectiles'])->name('estudiante.juego_proyectiles');
    // --- API del juego de proyectiles (recompensas con monedas) ---
    Route::prefix('proyectiles/api')->name('estudiante.proyectiles.api.')->group(function () {
        Route::post('reward', [ProjectileGameController::class, 'reward'])->name('reward');
        Route::get('status',  [ProjectileGameController::class, 'status'])->name('status');
    });
});  
    


Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/usuarios', [AdminUserController::class, 'index'])
            ->name('usuarios.index');

        Route::patch('/usuarios/{usuario}/desactivar', [AdminUserController::class, 'desactivar'])
            ->name('usuarios.desactivar');

        Route::patch('/usuarios/{usuario}/activar', [AdminUserController::class, 'activar'])
            ->name('usuarios.activar');

        Route::delete('/usuarios/{usuario}', [AdminUserController::class, 'destroy'])
            ->name('usuarios.destroy');
    });