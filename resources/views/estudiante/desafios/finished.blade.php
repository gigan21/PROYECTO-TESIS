<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Desafío finalizado</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <main class="mx-auto max-w-lg px-6 py-16 text-center">
        <h1 class="text-2xl font-bold text-slate-900">{{ $room->title }}</h1>
        <p class="mt-2 text-sm text-slate-600">Este desafío ya finalizó.</p>
        @if ($summary)
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm">
                <p class="text-sm text-slate-600">Tu resultado:</p>
                <ul class="mt-3 space-y-1 text-sm font-medium text-slate-800">
                    <li>Respondidas: {{ $summary['answered'] }}</li>
                    <li>Correctas: {{ $summary['correct'] }}</li>
                    <li>XP obtenido: {{ $summary['xp'] }}</li>
                </ul>
            </div>
        @endif
        <a href="{{ route('estudiante.inicio') }}" class="mt-6 inline-block text-sm font-semibold text-indigo-600">Volver al inicio</a>
    </main>
</body>
</html>
