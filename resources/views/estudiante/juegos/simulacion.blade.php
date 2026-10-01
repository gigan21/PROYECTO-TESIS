<!-- resources/views/estudiante/juegos/simulacion.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulación de Física — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white shadow-xs">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <h1 class="text-lg font-semibold text-slate-900">Simulación: Movimiento de Proyectiles</h1>
            <a href="{{ route('estudiante.inicio') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                ← Volver al Inicio
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-6">
        <div class="w-full bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden" style="height: 75vh;">
            <iframe 
                src="{{ asset('simulaciones/projectile-motion_es_PE.html') }}" 
                class="w-full h-full border-0" 
                allowfullscreen>
            </iframe>
        </div>
    </main>
</body>
</html>