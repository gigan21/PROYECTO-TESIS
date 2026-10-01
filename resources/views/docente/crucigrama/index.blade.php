@extends('layouts.docente')

@section('title', 'Crucigrama — palabras')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Palabras del crucigrama</h1>
            <p class="text-sm text-slate-500">Gestiona respuestas, pistas y niveles.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('docente.crucigrama.stats') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Estadísticas</a>
            <a href="{{ route('docente.crucigrama.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Nueva palabra</a>
        </div>
    </div>

    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..." class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2">
        <select name="difficulty" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Dificultad</option>
            @foreach (['facil', 'medio', 'dificil'] as $d)
                <option value="{{ $d }}" @selected(request('difficulty') === $d)>{{ ucfirst($d) }}</option>
            @endforeach
        </select>
        <select name="is_active" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Estado</option>
            <option value="1" @selected(request('is_active') === '1')>Activas</option>
            <option value="0" @selected(request('is_active') === '0')>Inactivas</option>
        </select>
        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Filtrar</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Respuesta</th>
                    <th class="px-4 py-3">Pista</th>
                    <th class="px-4 py-3">Dif.</th>
                    <th class="px-4 py-3">Nivel</th>
                    <th class="px-4 py-3">Activa</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($words as $word)
                    <tr>
                        <td class="px-4 py-3 font-semibold uppercase">{{ $word->answer }}</td>
                        <td class="max-w-xs px-4 py-3">{{ \Illuminate\Support\Str::limit($word->clue, 60) }}</td>
                        <td class="px-4 py-3">{{ ucfirst($word->difficulty) }}</td>
                        <td class="px-4 py-3">{{ $word->level }}</td>
                        <td class="px-4 py-3">{{ $word->is_active ? 'Sí' : 'No' }}</td>
                        <td class="space-x-2 px-4 py-3 text-right">
                            <a href="{{ route('docente.crucigrama.show', $word) }}" class="text-indigo-600 hover:underline">Ver</a>
                            <a href="{{ route('docente.crucigrama.edit', $word) }}" class="text-slate-600 hover:underline">Editar</a>
                            <form action="{{ route('docente.crucigrama.destroy', $word) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay palabras.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $words->links() }}</div>
@endsection
