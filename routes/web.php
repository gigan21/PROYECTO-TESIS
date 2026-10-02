<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordPinController;
use App\Http\Controllers\Controllers_Docentes\AttendanceExclusionController;
use App\Http\Controllers\Controllers_Docentes\AttendanceHubController;
use App\Http\Controllers\Controllers_Docentes\AttendanceRecordController;
use App\Http\Controllers\Controllers_Docentes\AttendanceSessionController;
use App\Http\Controllers\Controllers_Docentes\ChallengeRoomController;
use App\Http\Controllers\Controllers_Docentes\CrosswordStatsController;
use App\Http\Controllers\Controllers_Docentes\CrosswordWordController;
use App\Http\Controllers\Controllers_Docentes\QuestionController;
use App\Http\Controllers\Controllers_Estudiantes\ChallengePlayController;
use App\Http\Controllers\Controllers_Estudiantes\CrosswordPlayController;
use App\Http\Controllers\Controllers_Estudiantes\GamificationController;
use App\Http\Controllers\Controllers_Estudiantes\StudentHomeController;
use App\Http\Controllers\Controllers_Estudiantes\StudentProfileController;
use App\Http\Controllers\Controllers_Estudiantes\StudentQuizController;

use App\Support\Classroom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirección inicial según rol
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'docente'
            ? redirect()->route('docente.dashboard')
            : redirect()->route('estudiante.inicio');
    }
    return redirect()->route('login');
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
    Route::get('/dashboard', function () {
        return view('docente.dashboard');
    })->name('dashboard');

    Route::prefix('asistencia')->name('asistencia.')->group(function () {
        Route::get('/', [AttendanceHubController::class, 'index'])->name('index');

        Route::get('/paralelo/{classroom}', [AttendanceHubController::class, 'showClassroom'])
            ->where('classroom', Classroom::slugPattern())
            ->name('classroom');

        Route::get('/paralelo/{classroom}/crear', [AttendanceSessionController::class, 'create'])
            ->where('classroom', Classroom::slugPattern())
            ->name('sesiones.create');

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

    Route::get('/preguntas', [StudentQuizController::class, 'index'])
        ->name('estudiante.preguntas.index');

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
});