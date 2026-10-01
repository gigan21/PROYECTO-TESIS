@extends('layouts.docente')

@section('title', 'Asistencia '.$classroom)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('docente.asistencia.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Control de Asistencia</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Historial — {{ $classroom }}</h1>
            <p class="text-sm text-slate-500">{{ $totalEstudiantes }} estudiantes considerados en este paralelo</p>
        </div>
        <a href="{{ route('docente.asistencia.sesiones.create', $classroomSlug) }}"
           class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            Crear sesión
        </a>
    </div>

    <div class="space-y-3">
        @forelse ($sesiones as $item)
            @php
                $sesion = $item['session'];
                $resumen = $item['resumen'];
            @endphp
            <a href="{{ route('docente.asistencia.sesiones.show', $sesion) }}"
               class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">{{ $sesion->session_date->format('d/m/Y') }} · {{ substr($sesion->starts_at, 0, 5) }} - {{ substr($sesion->ends_at, 0, 5) }}</p>
                        <h2 class="text-lg font-bold text-slate-900">{{ $sesion->title }}</h2>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $sesion->isOpen() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $sesion->status->value }}
                    </span>
                </div>
                <p class="mt-3 text-sm text-slate-600">
                    Total: {{ $resumen['total'] }} | Presentes: {{ $resumen['presentes'] }} | Ausentes: {{ $resumen['ausentes'] }} | {{ $resumen['porcentaje'] }}%
                </p>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                No hay sesiones registradas para {{ $classroom }}.
            </div>
        @endforelse
    </div>
@endsection
