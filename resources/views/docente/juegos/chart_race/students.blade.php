@extends('layouts.docente')

@section('title', 'Carrera de Gráficas — ' . $classroom)

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.juegos.chart_race.classrooms') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Cambiar paralelo</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Estudiantes de {{ $classroom }}</h1>
    </div>

    @if (count($rows) === 0)
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-600">
            No hay estudiantes con sesiones en este paralelo.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Estudiante</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Sesiones</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Precisión</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Victorias</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Última sesión</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Tend.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rows as $row)
                        <tr class="cursor-pointer hover:bg-indigo-50/50"
                            onclick="window.location='{{ route('docente.juegos.chart_race.student', [$classroomSlug, $row['user']->id]) }}'">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $row['user']->name }}</td>
                            <td class="px-4 py-3">{{ $row['sessions'] }}</td>
                            <td class="px-4 py-3">{{ $row['avg_accuracy'] }}%</td>
                            <td class="px-4 py-3">{{ $row['wins'] }}</td>
                            <td class="px-4 py-3">{{ $row['last_played_human'] ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $row['trend'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
