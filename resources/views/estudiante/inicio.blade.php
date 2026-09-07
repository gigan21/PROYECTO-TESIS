<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio Estudiante — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white shadow-xs">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <div>  
                <h1 class="text-lg font-semibold text-slate-900">Área del Estudiante</h1>
                <p class="text-sm text-slate-500">Hola, {{ auth()->user()->name }}</p>
            </div>

            <!-- Botones de Acción (Perfil + Logout) -->
            <div class="flex items-center gap-3">
                <a href="{{ route('student.profile') }}" 
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    👤 Mi Perfil
                </a>

<!-- Avatar con Imagen Real -->
<img src="{{ asset('images/avatars/' . (auth()->user()->studentProfile->avatar_name ?? 'default_avatar.png')) }}" 
     alt="Avatar de {{ auth()->user()->name }}" 
     class="h-12 w-12 rounded-full border-2 border-indigo-500 object-cover shadow-sm">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-12 space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xs">
            <h2 class="text-2xl font-bold text-indigo-700">¡Bienvenido a Cinemática!</h2>
            <p class="mt-2 text-slate-600">
                Comienza tu aventura de aprendizaje. Gana XP completando lecciones y desafíos.
            </p>

            <!-- Sección Informativa de Paralelo y Estado -->
            @if (auth()->user()->studentProfile)
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- XP Actual -->
                    <div class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 p-4">
                        <span class="text-3xl">⭐</span>
                        <div>
                            <p class="text-xs font-semibold text-emerald-800 uppercase">Puntos de Experiencia</p>
                            <p class="text-xl font-bold text-emerald-900">{{ auth()->user()->studentProfile->xp_points }} XP</p>
                        </div>
                    </div>

                    <!-- Paralelo Registrado -->
                    <div class="flex items-center justify-between rounded-xl bg-indigo-50 border border-indigo-200 p-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">🏫</span>
                            <div>
                                <p class="text-xs font-semibold text-indigo-800 uppercase">Curso y Paralelo</p>
                                <p class="text-base font-bold text-indigo-950">
                                    {{ auth()->user()->studentProfile->classroom->name ?? 'Sin asignar' }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('student.profile') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                            Cambiar
                        </a>
                    </div>

                </div>
            @endif

            <div class="mt-6 rounded-lg bg-slate-50 border border-slate-200 p-4 text-sm text-slate-600">
                Vista provisional — los módulos de física se habilitarán según tu paralelo.
            </div>
        </div>
    </main>
</body>
</html>