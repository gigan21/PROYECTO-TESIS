@extends('layouts.docente')

@section('title', 'Proyectiles por paralelo')

@section('content')
    <div class="mb-6 flex items-end justify-between">
        <div>
            <a href="{{ route('docente.juegos.index') }}" class="text-sm font-semibold text-indigo-600">← Juegos</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Simulador de proyectiles</h1>
            <p class="text-sm text-slate-500">Resumen por paralelo.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($paralelos as $p)
            <a href="{{ route('docente.juegos.projectiles.students', $p['slug']) }}"
               class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                <h2 class="text-xl font-bold text-slate-900">{{ $p['nombre'] }}</h2>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-slate-500">Sesiones</dt>
                        <dd class="font-bold text-slate-800">{{ $p['stats']['sessions'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Victorias</dt>
                        <dd class="font-bold text-emerald-600">{{ $p['stats']['wins'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Precisión</dt>
                        <dd class="font-bold">{{ $p['stats']['avg_accuracy'] }}%</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Mejor score</dt>
                        <dd class="font-bold">{{ number_format($p['stats']['best_score']) }}</dd>
                    </div>
                </dl>
            </a>
        @endforeach
    </div>
@endsection
