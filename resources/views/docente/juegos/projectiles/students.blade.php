@extends('layouts.docente')

@section('title', 'Estudiantes — proyectiles')

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.juegos.projectiles.classrooms') }}" class="text-sm font-semibold text-indigo-600">← Paralelos</a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $classroom }}</h1>
        <p class="text-sm text-slate-500">Tarjetas de rendimiento en el simulador.</p>
    </div>

    <form method="GET" class="mb-6 flex gap-2">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar estudiante"
               class="rounded-xl border border-slate-300 px-3 py-2 text-sm w-full max-w-md">
        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Buscar</button>
    </form>

    @if (count($cards) === 0)
        <p class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
            No hay estudiantes o sesiones en este paralelo todavía.
        </p>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($cards as $card)
                @php
                    $user = $card['user'];
                    $trend = $card['accuracy_trend'];
                    $trendIcon = match ($trend) {
                        'up' => '↑',
                        'down' => '↓',
                        default => '→',
                    };
                    $trendClass = match ($trend) {
                        'up' => 'text-emerald-600',
                        'down' => 'text-rose-600',
                        default => 'text-slate-500',
                    };
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 to-indigo-950 p-5 text-white shadow-lg">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h2 class="text-lg font-bold leading-tight">{{ $user->name }}</h2>
                            <p class="text-xs text-indigo-200/80">{{ $user->email }}</p>
                        </div>
                        <span class="rounded-lg bg-white/10 px-2 py-1 text-xs font-bold">🔥 {{ $card['win_streak'] }}</span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-indigo-200/70 text-xs">Sesiones</dt>
                            <dd class="text-xl font-extrabold">{{ $card['sessions'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-indigo-200/70 text-xs">Precisión</dt>
                            <dd class="text-xl font-extrabold flex items-center gap-1">
                                {{ $card['avg_accuracy'] }}%
                                <span class="{{ $trendClass }} text-base" title="Tendencia precisión">{{ $trendIcon }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-indigo-200/70 text-xs">Tiempo medio</dt>
                            <dd class="font-semibold">{{ round($card['avg_time_seconds'] / 60, 1) }} min</dd>
                        </div>
                        <div>
                            <dt class="text-indigo-200/70 text-xs">Mejor score</dt>
                            <dd class="font-semibold">{{ number_format($card['best_score']) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-4 text-xs text-indigo-200/90">
                        Última sesión:
                        <strong>{{ $card['last_played_human'] ?? 'Sin jugar' }}</strong>
                    </p>

                    <a href="{{ route('docente.juegos.projectiles.student', [$classroomSlug, $user]) }}"
                       class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-indigo-500 py-2 text-sm font-bold text-white hover:bg-indigo-400">
                        Ver detalle
                    </a>
                </article>
            @endforeach
        </div>
    @endif
@endsection
