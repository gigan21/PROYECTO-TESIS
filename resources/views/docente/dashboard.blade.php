@extends('layouts.docente')

@section('title', 'Dashboard docente')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-bold text-indigo-700">Dashboard de Analítica Gamificada</h2>
            <p class="mt-2 text-slate-600">Monitoreo de rendimiento para el modelo de recomendaciones (C4.5)</p>
        </div>

        @if (($summary['total_sessions'] ?? 0) === 0)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <p class="text-lg font-medium text-slate-700">Aún no hay registros en learning_logs</p>
                <p class="mt-2 text-sm text-slate-500">Cuando los juegos escriban sesiones, verás métricas y gráficos aquí.</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sesiones</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($summary['total_sessions']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Error promedio</p>
                    <p class="mt-2 text-3xl font-bold text-rose-600">{{ $summary['avg_error_rate'] }}%</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tiempo promedio</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-700">{{ number_format($summary['avg_time_seconds'] / 60, 1) }} min</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">XP en logs</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($summary['total_xp']) }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-800">Errores por paralelo</h3>
                    <p class="mt-1 text-xs text-slate-500">Promedio de error por paralelo</p>
                    <div class="mt-4 h-72">
                        <canvas id="chartClassroomErrors" aria-label="Errores por paralelo"></canvas>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-800">Tiempo por tipo de juego</h3>
                    <p class="mt-1 text-xs text-slate-500">Segundos promedio Tiempo (Crucigramas, Salas de Retos, etc.)</p>
                    <div class="mt-4 h-72">
                        <canvas id="chartGameTime" aria-label="Tiempo por juego"></canvas>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-800">Detalle por tema y juego</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Tema</th>
                                <th class="px-3 py-2">Juego</th>
                                <th class="px-3 py-2">Error %</th>
                                <th class="px-3 py-2">Tiempo (s)</th>
                                <th class="px-3 py-2">Sesiones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topicGameRows as $row)
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2">{{ $row['topic'] }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $row['game_type'] }}</td>
                                    <td class="px-3 py-2">{{ $row['avg_error_rate'] }}%</td>
                                    <td class="px-3 py-2">{{ $row['avg_time_seconds'] }}</td>
                                    <td class="px-3 py-2">{{ $row['sessions'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@if (($summary['total_sessions'] ?? 0) > 0)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const charts = @json($charts);
                const palette = ['#6366f1', '#64748b', '#0ea5e9', '#14b8a6', '#f43f5e'];

                new Chart(document.getElementById('chartClassroomErrors'), {
                    type: 'bar',
                    data: {
                        labels: charts.classroom_errors.labels,
                        datasets: [{
                            label: 'Error %',
                            data: charts.classroom_errors.data,
                            backgroundColor: '#6366f1',
                            borderRadius: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { color: '#64748b' } },
                            x: { ticks: { color: '#64748b' } },
                        },
                    },
                });

                new Chart(document.getElementById('chartGameTime'), {
                    type: 'doughnut',
                    data: {
                        labels: charts.game_time.labels,
                        datasets: [{
                            data: charts.game_time.data,
                            backgroundColor: palette,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { color: '#475569' } } },
                    },
                });
            });
        </script>
    @endpush
@endif
