@extends('layouts.docente')

@section('title', 'Mis Estudiantes')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('docente.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:underline">← Dashboard</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Mis Estudiantes</h2>
                <p class="mt-1 text-sm text-slate-600">Análisis por paralelo — cartas de rendimiento</p>
            </div>
            <form method="GET" action="{{ route('docente.estudiantes') }}" class="flex items-center gap-2">
                <label for="classroom" class="text-sm font-medium text-slate-600">Paralelo</label>
                <select name="classroom" id="classroom"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        onchange="this.form.submit()">
                    @foreach ($classroomOptions as $option)
                        <option value="{{ $option['value'] }}" @selected($option['value'] === $selectedClassroom)>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if (count($cards) === 0)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <p class="text-slate-600">No hay estudiantes en este paralelo.</p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($cards as $card)
                    <button type="button"
                            class="student-card group relative overflow-hidden rounded-2xl border border-slate-200 bg-linear-to-br from-slate-800 via-indigo-900 to-slate-900 p-4 text-left text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400"
                            data-student="{{ (json_encode($card['modal'])) }}">
                        <div class="absolute right-3 top-3 rounded-lg bg-amber-400/90 px-2 py-1 text-lg font-black text-slate-900">
                            {{ $card['rating'] }}
                        </div>
                        <img src="{{ asset('images/avatars/' . $card['avatar']) }}"
                             alt=""
                             class="mx-auto mt-6 h-20 w-20 rounded-full border-2 border-white/30 object-cover">
                        <h3 class="mt-3 truncate text-center text-base font-bold">{{ $card['name'] }}</h3>
                        <p class="text-center text-xs text-indigo-200">{{ $card['classroom'] }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="rounded-lg bg-white/10 px-2 py-2">
                                <p class="text-white/60">Precisión</p>
                                <p class="font-bold text-emerald-300">{{ $card['precision'] }}%</p>
                            </div>
                            <div class="rounded-lg bg-white/10 px-2 py-2">
                                <p class="text-white/60">Tiempo prom.</p>
                                <p class="font-bold">{{ $card['avg_time_seconds'] }}s</p>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div id="studentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4" aria-hidden="true">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 id="modalStudentName" class="text-lg font-bold text-slate-900"></h3>
                <button type="button" id="modalClose" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Cerrar">✕</button>
            </div>
            <div class="px-6 py-4">
                <div class="h-64">
                    <canvas id="radarChart" aria-label="Dominio por dificultad"></canvas>
                </div>
                <h4 class="mt-6 text-sm font-semibold text-rose-600">Lista roja de fallos</h4>
                <ul id="modalFailureList" class="mt-2 space-y-2 text-sm text-slate-700"></ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('studentModal');
            const modalName = document.getElementById('modalStudentName');
            const failureList = document.getElementById('modalFailureList');
            const closeBtn = document.getElementById('modalClose');
            let radarInstance = null;

            function openModal(payload) {
                modalName.textContent = payload.name;
                failureList.innerHTML = '';
                if (payload.failures.length === 0) {
                    failureList.innerHTML = '<li class="text-slate-500">Sin fallos registrados.</li>';
                } else {
                    payload.failures.forEach(function (item) {
                        const li = document.createElement('li');
                        li.className = 'rounded-lg border border-rose-100 bg-rose-50 px-3 py-2';
                        li.innerHTML = '<span class="font-medium text-rose-700">' + item.count + '×</span> ' + escapeHtml(item.text);
                        failureList.appendChild(li);
                    });
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');

                const ctx = document.getElementById('radarChart');
        const chartExistente = Chart.getChart(ctx); // Busca si ya existe un gráfico en el canvas
        if (chartExistente) {
            chartExistente.destroy(); // Lo destruye de forma segura
        }
                radarInstance = new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: payload.radar.labels,
                        datasets: [{
                            label: 'Dominio',
                            data: payload.radar.values,
                            backgroundColor: 'rgba(99, 102, 241, 0.2)',
                            borderColor: '#6366f1',
                            pointBackgroundColor: '#6366f1',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 100,
                                ticks: { stepSize: 20, color: '#64748b' },
                                pointLabels: { color: '#334155', font: { size: 11 } },
                            },
                        },
                        plugins: { legend: { display: false } },
                    },
                });
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            document.querySelectorAll('.student-card').forEach(function (card) {
                card.addEventListener('click', function () {
                    openModal(JSON.parse(card.getAttribute('data-student')));
                });
            });

            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
@endpush
