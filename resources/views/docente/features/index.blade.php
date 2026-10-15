@extends('layouts.docente')

@section('title', 'Features ML — estudiantes')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Snapshots de features (C4.5)</h1>
            <p class="text-sm text-slate-500">Un registro por estudiante y día · versión v1</p>
        </div>
        <a href="{{ route('docente.features.export') }}"
           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            Exportar CSV (último por estudiante)
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="get" class="mb-6 flex flex-wrap items-center gap-3">
        <label class="text-sm font-medium text-slate-700" for="classroom">Paralelo</label>
        <select name="classroom" id="classroom" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach ($classrooms as $c)
                <option value="{{ \App\Support\Classroom::slugFor($c) }}" @selected($classroomFilter === \App\Support\Classroom::slugFor($c))>{{ $c }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Filtrar
        </button>
    </form>

    @if ($snapshots->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-600">
            No hay snapshots. Ejecuta <code class="rounded bg-slate-100 px-1">php artisan features:extract</code>.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Estudiante</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Paralelo</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Fecha</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Juegos</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">XP</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Sesiones</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Prec. medio preg.</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($snapshots as $snapshot)
                        @php $f = $snapshot->features_json ?? []; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $snapshot->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $snapshot->classroom ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $snapshot->snapshot_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $f['games_played_count'] ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $f['student_xp_points'] ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $f['total_sessions_all_games'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if (isset($f['questions_accuracy_medium']) && $f['questions_accuracy_medium'] !== null)
                                    {{ round((float) $f['questions_accuracy_medium'] * 100, 1) }}%
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="post" action="{{ route('docente.features.recalculate') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $snapshot->user_id }}">
                                    <button type="submit" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                        Recalcular
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $snapshots->links() }}
        </div>
    @endif
@endsection
