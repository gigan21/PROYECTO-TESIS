<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Docente') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <div>
                <a href="{{ route('docente.dashboard') }}" class="text-lg font-semibold text-slate-900">Panel Docente</a>
                <p class="text-sm text-slate-500">{{ auth()->user()->name }}</p>
            </div>
            <nav class="flex items-center gap-3">
                <a href="{{ route('docente.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Inicio</a>
                <a href="{{ route('docente.asistencia.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">Asistencia</a>
                <a href="{{ route('docente.preguntas.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Preguntas</a>
                <a href="{{ route('docente.desafios.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">Desafíos</a>
                <a href="{{ route('docente.crucigrama.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Crucigrama</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cerrar sesión
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
