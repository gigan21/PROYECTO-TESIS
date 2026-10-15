@extends('layouts.docente')

@section('title', 'Carrera de Gráficas — Análisis')

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.juegos.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Datos de juegos</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Carrera de Gráficas — Análisis</h1>
        <p class="text-sm text-slate-500">Sesiones por paralelo.</p>
    </div>

    @if (count($rows) === 0)
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-600">
            Aún no hay sesiones registradas.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Paralelo</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Estudiantes</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Sesiones</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Precisión media</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Tiempo medio</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $row['classroom'] }}</td>
                            <td class="px-4 py-3">{{ $row['students'] }}</td>
                            <td class="px-4 py-3">{{ $row['sessions'] }}</td>
                            <td class="px-4 py-3">{{ $row['avg_accuracy'] }}%</td>
                            <td class="px-4 py-3">{{ $row['avg_time'] }} s</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('docente.juegos.chart_race.students', $row['slug']) }}"
                                   class="font-semibold text-indigo-600 hover:text-indigo-800">Ver estudiantes →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
