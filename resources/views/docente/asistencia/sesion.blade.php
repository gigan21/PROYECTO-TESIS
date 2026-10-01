@extends('layouts.docente')

@section('title', $session->title)

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('docente.asistencia.classroom', \App\Support\Classroom::slugFor($session->classroom)) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                ← Historial {{ $session->classroom }}
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $session->title }}</h1>
            <p class="text-sm text-slate-500">
                Paralelo: <strong>{{ $session->classroom }}</strong>
                · Fecha: {{ $session->session_date->format('d/m/Y') }}
                · {{ substr($session->starts_at, 0, 5) }} - {{ substr($session->ends_at, 0, 5) }}
            </p>
            @if ($session->description)
                <p class="mt-2 text-sm text-slate-600">{{ $session->description }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $session->isOpen() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $session->status->value }}
            </span>
            @if ($session->isOpen())
                <form method="POST" action="{{ route('docente.asistencia.sesiones.close', $session) }}"
                      onsubmit="return confirm('Al cerrar, no se aceptarán más registros. Los estudiantes sin marcar quedarán como ausentes.');">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cerrar asistencia
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('docente.asistencia.partials.resumen')

    <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm"
               @if ($session->isOpen())
                   data-attendance-table
                   data-upsert-url="{{ route('docente.asistencia.registros.upsert', $session) }}"
               @endif
        >
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-3">Avatar</th>
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">Nickname</th>
                    <th class="px-3 py-3 text-right">Nivel</th>
                    <th class="px-3 py-3 text-right">XP</th>
                    <th class="px-3 py-3">Correo</th>
                    <th class="px-3 py-3">Paralelo</th>
                    <th class="px-3 py-3">Asistencia</th>
                    <th class="px-3 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($estudiantes as $estudiante)
                    @include('docente.asistencia.partials.fila-estudiante')
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                            No hay estudiantes para este paralelo en tu gestión de asistencia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection