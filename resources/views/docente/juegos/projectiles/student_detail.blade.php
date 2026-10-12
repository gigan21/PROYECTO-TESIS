@extends('layouts.docente')

@section('title', $student->name . ' — proyectiles')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.juegos.projectiles.students', $classroomSlug) }}" class="text-sm font-semibold text-indigo-600">← {{ $classroom }}</a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $student->name }}</h1>
        <p class="text-sm text-slate-500">Historial del simulador de proyectiles</p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500 uppercase">Sesiones</p>
            <p class="text-2xl font-bold">{{ $aggregate['sessions'] }}</p>
        </div>
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500 uppercase">Precisión media</p>
            <p class="text-2xl font-bold">{{ $aggregate['avg_accuracy'] }}%</p>
        </div>
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500 uppercase">Racha victorias</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $win_streak }}</p>
        </div>
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500 uppercase">Tendencia</p>
            <p class="text-2xl font-bold">
                @if ($accuracy_trend === 'up') ↑ Mejora
                @elseif ($accuracy_trend === 'down') ↓ Baja
                @else → Estable
                @endif
            </p>
        </div>
    </div>

    @if (count($chart['labels']) > 0)
        <div class="mb-8 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-800">Precisión por sesión</h2>
                <div class="mt-4 h-64"><canvas id="chartAccuracy"></canvas></div>
            </div>
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-800">Tiempo por sesión (min)</h2>
                <div class="mt-4 h-64"><canvas id="chartTime"></canvas></div>
            </div>
        </div>
    @endif

    @if (count($analysis) > 0)
        <section class="mb-8 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-6">
            <h2 class="text-sm font-semibold text-indigo-900">Análisis pedagógico (última sesión)</h2>
            @foreach ($analysis as $paragraph)
                <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $paragraph }}</p>
            @endforeach
        </section>
    @endif

    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Resultado</th>
                    <th class="px-4 py-3">Nivel</th>
                    <th class="px-4 py-3">Score</th>
                    <th class="px-4 py-3">Precisión</th>
                    <th class="px-4 py-3">Tiempo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sessions as $session)
                    <tr>
                        <td class="px-4 py-3">{{ $session->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if ($session->result === 'win')
                                <span class="text-emerald-600 font-semibold">Victoria</span>
                            @else
                                <span class="text-rose-600 font-semibold">Derrota</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $session->level_reached }}</td>
                        <td class="px-4 py-3">{{ number_format($session->score) }}</td>
                        <td class="px-4 py-3">{{ $session->accuracy }}%</td>
                        <td class="px-4 py-3">{{ round($session->total_time_seconds / 60, 1) }} min</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Sin sesiones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection

@if (count($chart['labels']) > 0)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const labels = @json($chart['labels']);
                const accuracy = @json($chart['accuracy']);
                const timeMin = @json($chart['time_minutes']);

                new Chart(document.getElementById('chartAccuracy'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Precisión %',
                            data: accuracy,
                            borderColor: '#4f46e5',
                            tension: 0.25,
                            fill: false
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } } }
                });

                new Chart(document.getElementById('chartTime'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Minutos',
                            data: timeMin,
                            backgroundColor: '#818cf8'
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            });
        </script>
    @endpush
@endif
