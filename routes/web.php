<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controllers_Estudiantes\StudentProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ResetPasswordPinController;
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
Route::middleware(['auth', 'teacher'])->prefix('docente')->group(function () {
    Route::get('/dashboard', function () {
        return view('docente.dashboard');
    })->name('docente.dashboard');
});

// Rutas para ESTUDIANTES (Perfil + Inicio)
Route::middleware(['auth', 'student'])->prefix('estudiante')->group(function () {
    
    Route::get('/inicio', function () {
        return view('estudiante.inicio');
    })->name('estudiante.inicio');

    // --- RUTAS DEL PERFIL (Movidas aquí correctamente) ---
    Route::get('/perfil', [StudentProfileController::class, 'edit'])
        ->name('student.profile');

    Route::patch('/perfil', [StudentProfileController::class, 'update'])
        ->name('student.profile.update');
});