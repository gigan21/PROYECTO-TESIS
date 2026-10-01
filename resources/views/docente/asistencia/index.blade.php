@extends('layouts.docente')

@section('title', 'Control de Asistencia')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Control de Asistencia</h1>
        <p class="mt-1 text-sm text-slate-500">Administra cada paralelo de forma independiente. Los datos no se mezclan.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        @foreach ($paralelos as $paralelo)
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-indigo-700">{{ $paralelo['nombre'] }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $paralelo['estudiantes'] }} estudiantes en gestión</p>

                <div class="mt-6 flex flex-col gap-2">
                    <a href="{{ route('docente.asistencia.sesiones.create', $paralelo['slug']) }}"
                       class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-700">
                        Crear sesión
                    </a>
                    <a href="{{ route('docente.asistencia.classroom', $paralelo['slug']) }}"
                       class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Ver asistencia
                    </a>
                </div>

                <div class="mt-6 space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Últimas sesiones</p>
                    @forelse ($paralelo['sesiones'] as $item)
                        @php
                            $sesion = $item['session'];
                            $resumen = $item['resumen'];
                        @endphp
                        <a href="{{ route('docente.asistencia.sesiones.show', $sesion) }}" class="block rounded-lg bg-slate-50 px-3 py-2 text-sm hover:bg-indigo-50">
                            <span class="font-medium text-slate-800">{{ $sesion->session_date->format('d/m/Y') }} — {{ $sesion->title }}</span>
                            <span class="mt-1 block text-xs text-slate-500">
                                Total: {{ $resumen['total'] }} | Presentes: {{ $resumen['presentes'] }} | Ausentes: {{ $resumen['ausentes'] }} | {{ $resumen['porcentaje'] }}%
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400">Aún no hay sesiones en este paralelo.</p>
                    @endforelse
                </div>
            </article>
        @endforeach
    </div>
@endsection
