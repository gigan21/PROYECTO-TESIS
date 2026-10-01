<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulador de Proyectiles — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <!-- MENÚ SUPERIOR -->
    <header class="border-b border-slate-200 bg-white shadow-xs">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <div>
                <a href="{{ route('estudiante.inicio') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Volver al Inicio</a>
                <h1 class="text-lg font-semibold text-slate-900">Simulador de Proyectiles</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('estudiante.preguntas.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Preguntas</a>
                <a href="{{ route('student.profile') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Mi Perfil</a>
            </div>
        </div>
    </header>

    <!-- CONTENEDOR DEL JUEGO -->
    <main class="mx-auto max-w-6xl space-y-6 px-6 py-10">
        <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-lg h-[700px] lg:h-[800px]">
            <!-- AQUÍ CARGAMOS TU JUEGO DESDE LA CARPETA PUBLIC -->
            <iframe src="{{ asset('JUEGO_PROYECTILES/index.html') }}" 
                    class="w-full h-full border-0 rounded-xl"
                    allowfullscreen>
            </iframe>
        </div>
    </main>
</body>
</html>