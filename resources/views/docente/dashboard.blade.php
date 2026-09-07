<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Docente — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-lg font-semibold text-slate-900">Panel Docente</h1>
                <p class="text-sm text-slate-500">Bienvenido, {{ auth()->user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-12">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-bold text-indigo-700">Dashboard del Docente</h2>
            <p class="mt-2 text-slate-600">
                Bienvenido al panel de administración docente. Aquí podrás gestionar contenidos, actividades y el progreso de tus estudiantes.
            </p>
            <div class="mt-6 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                Vista provisional — el módulo completo se implementará próximamente.
            </div>
        </div>
    </main>
</body>
</html>
