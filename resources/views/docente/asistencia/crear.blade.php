@extends('layouts.docente')

@section('title', 'Nueva sesión '.$classroom)

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.asistencia.classroom', $classroomSlug) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Volver a {{ $classroom }}</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Crear sesión de asistencia</h1>
        <p class="text-sm text-slate-500">Paralelo: <strong>{{ $classroom }}</strong> · {{ $totalEstudiantes }} estudiantes</p>
    </div>

    <form method="POST" action="{{ route('docente.asistencia.sesiones.store', $classroomSlug) }}" class="max-w-2xl space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        @csrf

        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-slate-700">Título de la clase</label>
            <input id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="255"
                   placeholder="Movimiento Rectilíneo Uniforme"
                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción (opcional)</label>
            <textarea id="description" name="description" rows="3" maxlength="2000"
                      class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="session_date" class="mb-1.5 block text-sm font-medium text-slate-700">Fecha</label>
                <input id="session_date" name="session_date" type="date" value="{{ old('session_date', now()->toDateString()) }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label for="starts_at" class="mb-1.5 block text-sm font-medium text-slate-700">Hora de inicio</label>
                <input id="starts_at" name="starts_at" type="time" value="{{ old('starts_at', '18:30') }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label for="ends_at" class="mb-1.5 block text-sm font-medium text-slate-700">Hora de cierre</label>
                <input id="ends_at" name="ends_at" type="time" value="{{ old('ends_at', '19:30') }}" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
        </div>

        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700">
            Crear y registrar asistencia
        </button>
    </form>
@endsection
