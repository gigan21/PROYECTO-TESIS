<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Desafío — {{ $room->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <main class="mx-auto max-w-lg px-6 py-16 text-center">
        <h1 class="text-2xl font-bold text-slate-900">{{ $room->title }}</h1>
        <p class="mt-2 text-sm text-slate-600">Paralelo {{ $room->classroom }}</p>
        <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
            El docente aún no ha iniciado este desafío. Vuelve a abrir el enlace cuando esté activo.
        </div>
        <a href="{{ route('estudiante.inicio') }}" class="mt-6 inline-block text-sm font-semibold text-indigo-600">Volver al inicio</a>
    </main>
</body>
</html>
