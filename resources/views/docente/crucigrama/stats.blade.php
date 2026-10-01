@extends('layouts.docente')

@section('title', 'Estadísticas crucigrama')

@section('content')
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Estadísticas de estudiantes</h1>
            <p class="text-sm text-slate-500">Progreso, monedas y actividad reciente.</p>
        </div>
        <a href="{{ route('docente.crucigrama.index') }}" class="text-sm font-semibold text-indigo-600">← Palabras</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o email" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
        <select name="sort" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="level" @selected(request('sort', 'level') === 'level')>Ordenar por nivel</option>
            <option value="coins" @selected(request('sort') === 'coins')>Ordenar por monedas</option>
        </select>
        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Aplicar</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Estudiante</th>
                    <th class="px-4 py-3">Nivel</th>
                    <th class="px-4 py-3">Aprendidas</th>
                    <th class="px-4 py-3">Monedas</th>
                    <th class="px-4 py-3">Último juego</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($students as $student)
                    @php $p = $student->crosswordProgress; @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $student->email }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $p->current_level ?? 1 }}</td>
                        <td class="px-4 py-3">{{ $p ? count($p->learned_words ?? []) : 0 }}</td>
                        <td class="px-4 py-3">{{ $p->coins_earned ?? 0 }}</td>
                        <td class="px-4 py-3">{{ $p?->last_played_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('docente.crucigrama.stats.show', $student) }}" class="text-indigo-600 hover:underline">Detalle</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
@endsection
