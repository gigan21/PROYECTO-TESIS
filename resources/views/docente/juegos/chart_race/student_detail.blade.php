@extends('layouts.docente')

@section('title', 'Carrera de Gráficas — ' . $student->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.juegos.chart_race.students', $classroomSlug) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Estudiantes de {{ $classroom }}</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $student->name }}</h1>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Precisión promedio</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $aggregate['avg_accuracy'] }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Victorias (meta)</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $aggregate['wins'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Tiempo promedio</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $aggregate['avg_time'] }} s</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Monedas ganadas (juego)</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ $coins_earned }}</p>
        </div>
    </div>

    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Precisión por tipo de pregunta</h2>
        @php
            $typeLabels = ['identify' => 'Identificar', 'predict' => 'Predecir', 'choose' => 'Elegir gráfica'];
        @endphp
        @if (count($byType) === 0)
            <p class="text-sm text-slate-500">Sin datos de rondas aún.</p>
        @else
            <div class="space-y-3">
                @foreach ($typeLabels as $key => $label)
                    @php $pct = $byType[$key] ?? null; @endphp
                    @if ($pct !== null)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-32 shrink-0 font-medium text-slate-700">{{ $label }}</span>
                            <div class="h-3 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ min(100, (float) $pct) }}%"></div>
                            </div>
                            <span class="w-12 text-right font-semibold text-slate-800">{{ $pct }}%</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </section>

    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold text-slate-900">Áreas débiles (temas)</h2>
        @if (count($weakAreas) === 0)
            <p class="text-sm text-slate-500">Sin temas marcados como débiles.</p>
        @else
            <ul class="list-inside list-disc text-sm text-slate-700">
                @foreach (array_slice($weakAreas, 0, 12) as $topic)
                    <li>{{ $topic }}</li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Últimas 10 sesiones</h2>
        @if ($timeline->isEmpty())
            <p class="text-sm text-slate-500">Sin sesiones.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-600">
                            <th class="py-2 pr-4">Fecha</th>
                            <th class="py-2 pr-4">Aciertos</th>
                            <th class="py-2 pr-4">Precisión</th>
                            <th class="py-2 pr-4">Tiempo</th>
                            <th class="py-2">Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($timeline as $session)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-4 text-slate-500">{{ $session->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2 pr-4">{{ $session->hits }}/6</td>
                                <td class="py-2 pr-4">{{ $session->accuracy }}%</td>
                                <td class="py-2 pr-4">{{ $session->total_time_sec }} s</td>
                                <td class="py-2">
                                    @if ($session->exploded)
                                        <span class="text-red-600">Explosión</span>
                                    @elseif ($session->won)
                                        <span class="text-emerald-600">Meta</span>
                                    @else
                                        <span class="text-slate-600">Parcial</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
