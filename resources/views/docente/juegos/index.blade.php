@extends('layouts.docente')

@section('title', 'Juegos — analítica')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Analítica de juegos</h1>
        <p class="text-sm text-slate-500">Telemetría gamificada por actividad.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Sesiones proyectiles</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">{{ $overview['total_sessions'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">% victorias</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $overview['win_rate'] }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Precisión media</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $overview['avg_accuracy'] }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Jugadores únicos</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $overview['unique_players'] }}</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('docente.juegos.projectiles.classrooms') }}"
           class="flex items-center justify-between rounded-2xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-white p-6 shadow-sm transition hover:border-indigo-300">
            <div>
                <h2 class="text-lg font-bold text-indigo-800">Simulador de proyectiles</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $overview['total_sessions'] }} sesiones · Precisión media {{ $overview['avg_accuracy'] }}%</p>
            </div>
            <span class="text-indigo-600 font-semibold text-sm">Ver análisis →</span>
        </a>
        <a href="{{ route('docente.juegos.cure.classrooms') }}"
           class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-white p-6 shadow-sm transition hover:border-emerald-300">
            <div>
                <h2 class="text-lg font-bold text-emerald-800">💉 Lanza la Cura</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $cureOverview['total_sessions'] }} sesiones · Promedio precisión: {{ $cureOverview['avg_accuracy'] }}%</p>
            </div>
            <span class="text-emerald-700 font-semibold text-sm">Ver análisis →</span>
        </a>
        <a href="{{ route('docente.juegos.chart_race.classrooms') }}"
           class="flex items-center justify-between rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-50 to-white p-6 shadow-sm transition hover:border-orange-300">
            <div>
                <h2 class="text-lg font-bold text-orange-800">🏎️ Carrera de Gráficas</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $chartRaceOverview['total_sessions'] }} sesiones · Promedio precisión: {{ $chartRaceOverview['avg_accuracy'] }}%</p>
            </div>
            <span class="text-orange-700 font-semibold text-sm">Ver análisis →</span>
        </a>
        <a href="{{ route('docente.recomendaciones.index') }}"
           class="flex items-center justify-between rounded-2xl border border-violet-200 bg-gradient-to-r from-violet-50 to-white p-6 shadow-sm transition hover:border-violet-300 md:col-span-2 lg:col-span-3">
            <div>
                <h2 class="text-lg font-bold text-violet-800">Recomendaciones C4.5</h2>
                <p class="mt-1 text-sm text-slate-600">Última sugerencia por estudiante y checklist de calidad.</p>
            </div>
            <span class="text-violet-700 font-semibold text-sm">Ver recomendaciones →</span>
        </a>
    </div>
@endsection
