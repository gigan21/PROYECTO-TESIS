@extends('layouts.docente')

@section('title', 'Detalle estudiante')

@section('content')
    <a href="{{ route('docente.crucigrama.stats') }}" class="text-sm font-semibold text-indigo-600">← Estadísticas</a>
    <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $student->name }}</h1>
    <p class="text-sm text-slate-500">{{ $student->email }}</p>

    @php $progress = $statistics['progress'] ?? null; @endphp
    <div class="mt-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Nivel actual</p><p class="text-2xl font-bold">{{ $progress['current_level'] ?? 1 }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Palabras aprendidas</p><p class="text-2xl font-bold">{{ $progress['learned_count'] ?? 0 }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Monedas</p><p class="text-2xl font-bold">{{ $progress['coins_earned'] ?? 0 }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Tiempo prom. (s)</p><p class="text-2xl font-bold">{{ $statistics['avg_time_seconds'] ?: '—' }}</p></div>
    </div>

    <h2 class="mt-8 text-lg font-bold text-slate-900">Eventos recientes</h2>
    <ul class="mt-3 space-y-2 rounded-2xl border border-slate-200 bg-white p-4 text-sm">
        @forelse ($statistics['recent_events'] as $event)
            <li class="flex justify-between border-b border-slate-100 pb-2 last:border-0">
                <span>{{ $event['was_correct'] ? '✅' : '❌' }} {{ strtoupper($event['word']) }} (nivel {{ $event['level'] }})</span>
                <span class="text-slate-500">{{ $event['created_at'] }}</span>
            </li>
        @empty
            <li class="text-slate-500">Sin eventos aún.</li>
        @endforelse
    </ul>
@endsection
