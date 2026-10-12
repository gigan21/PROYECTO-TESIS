@extends('layouts.docente')

@section('title', 'Lanza la Cura — ' . $student->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.juegos.cure.students', $classroomSlug) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Estudiantes de {{ $classroom }}</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $student->name }}</h1>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Precisión promedio</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $aggregate['avg_accuracy'] }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Rango más común</p>
            <p class="mt-1 text-lg font-bold text-indigo-800">{{ $aggregate['common_rank'] }}</p>
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
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Precisión — últimas 10 sesiones</h2>
        @if ($timeline->isEmpty())
            <p class="text-sm text-slate-500">Sin sesiones.</p>
        @else
            <div class="space-y-2">
                @foreach ($timeline as $session)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-24 shrink-0 text-slate-500">{{ $session->created_at->format('d/m H:i') }}</span>
                        <div class="h-3 flex-1 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, (float) $session->accuracy) }}%"></div>
                        </div>
                        <span class="w-12 text-right font-semibold text-slate-800">{{ $session->accuracy }}%</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">Fórmulas dominadas</h2>
            @if (count($formulas['mastered']) === 0)
                <p class="text-sm text-slate-500">—</p>
            @else
                <ul class="list-inside list-disc text-sm text-slate-700">
                    @foreach ($formulas['mastered'] as $f)
                        <li>✓ {{ $f }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">Fórmulas para repasar</h2>
            @if (count($formulas['review']) === 0)
                <p class="text-sm text-slate-500">—</p>
            @else
                <ul class="list-inside list-disc text-sm text-slate-700">
                    @foreach ($formulas['review'] as $f)
                        <li>✗ {{ $f }} @if(isset($formulas['review_counts'][$f])) ({{ $formulas['review_counts'][$f] }}) @endif</li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-2 text-lg font-semibold text-slate-900">Errores por tipo</h2>
        <p class="text-sm text-slate-700">
            Errores de concepto: <strong>{{ $errorsByType['concepto'] }}</strong>
            · Errores de cálculo: <strong>{{ $errorsByType['calculo'] }}</strong>
            · Errores de decimales: <strong>{{ $errorsByType['decimales'] }}</strong>
        </p>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Últimas 10 sesiones</h2>
        @if ($timeline->isEmpty())
            <p class="text-sm text-slate-500">Sin datos.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Nivel</th>
                            <th class="px-3 py-2 text-left">Resultado</th>
                            <th class="px-3 py-2 text-left">Tiempo</th>
                            <th class="px-3 py-2 text-left">Precisión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($timeline as $session)
                            <tr>
                                <td class="px-3 py-2">{{ $session->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2">{{ $session->level_reached }}</td>
                                <td class="px-3 py-2">{{ $session->won ? 'Victoria' : 'Derrota' }}</td>
                                <td class="px-3 py-2">{{ $session->total_time_sec }} s</td>
                                <td class="px-3 py-2">{{ $session->accuracy }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
