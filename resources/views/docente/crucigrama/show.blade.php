@extends('layouts.docente')

@section('title', 'Detalle palabra')

@section('content')
    <a href="{{ route('docente.crucigrama.index') }}" class="text-sm font-semibold text-indigo-600">← Volver al listado</a>
    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold uppercase text-slate-900">{{ $crosswordWord->answer }}</h1>
        <p class="mt-4 text-slate-700">{{ $crosswordWord->clue }}</p>
        <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="font-semibold text-slate-500">Dificultad</dt><dd>{{ ucfirst($crosswordWord->difficulty) }}</dd></div>
            <div><dt class="font-semibold text-slate-500">Nivel</dt><dd>{{ $crosswordWord->level }}</dd></div>
            <div><dt class="font-semibold text-slate-500">Estado</dt><dd>{{ $crosswordWord->is_active ? 'Activa' : 'Inactiva' }}</dd></div>
            <div><dt class="font-semibold text-slate-500">Aciertos registrados</dt><dd>{{ $stats['success_count'] }}</dd></div>
            <div><dt class="font-semibold text-slate-500">Intentos totales</dt><dd>{{ $stats['attempt_count'] }}</dd></div>
            <div><dt class="font-semibold text-slate-500">Tiempo promedio (s)</dt><dd>{{ $stats['avg_time'] ?: '—' }}</dd></div>
        </dl>
        <div class="mt-8 flex gap-3">
            <a href="{{ route('docente.crucigrama.edit', $crosswordWord) }}" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">Editar</a>
            <form method="POST" action="{{ route('docente.crucigrama.destroy', $crosswordWord) }}" onsubmit="return confirm('¿Eliminar palabra?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-xl border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700">Eliminar</button>
            </form>
        </div>
    </div>
@endsection
