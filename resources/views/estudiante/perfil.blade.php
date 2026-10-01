<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Perfil — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    
    <!-- Navegación Encabezado -->
    <header class="border-b border-slate-200 bg-white shadow-xs sticky top-0 z-50">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('estudiante.inicio') }}" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-800">
                    ← Volver al Inicio
                </a>
                <h1 class="text-lg font-bold text-slate-900">Perfil del Estudiante</h1>
                <a href="{{ route('estudiante.preguntas.index') }}"
                   class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition">
                    Preguntas
                </a>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-10">
        
        <!-- Alerta de éxito -->
        @if (session('status') === 'profile-updated')
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 shadow-xs">
                ⚡ ¡Tu información, apodo y avatar se actualizaron correctamente!
            </div>
        @endif

        <!-- Grid Principal del Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Columna Izquierda: Tarjeta de Perfil RPG -->
            @include('estudiante.partials.profile-card')

            <!-- Columna Derecha: Formulario de Edición -->
            <div class="lg:col-span-8">
                @include('estudiante.partials.profile-form', ['classrooms' => $classrooms])
            </div>

        </div>
    </main>
</body>
</html>