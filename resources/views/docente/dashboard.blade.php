@extends('layouts.docente')

@section('title', 'Dashboard docente')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-bold text-indigo-700">Dashboard del Docente</h2>
            <p class="mt-2 text-slate-600">
                Bienvenido al panel de administración docente. Gestiona la asistencia de Física por paralelo.
            </p>
        </div>

        <a href="{{ route('docente.crucigrama.index') }}" class="block rounded-2xl border border-violet-200 bg-white p-8 shadow-sm transition hover:border-violet-400 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-violet-600">Juego</p>
            <h3 class="mt-1 text-xl font-bold text-slate-900">Crucigrama</h3>
            <p class="mt-2 text-sm text-slate-600">Administra palabras, niveles y revisa el progreso de tus estudiantes.</p>
        </a>

        <a href="{{ route('docente.asistencia.index') }}" class="block rounded-2xl border border-indigo-200 bg-white p-8 shadow-sm transition hover:border-indigo-400 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-indigo-600">Módulo</p>
            <h3 class="mt-1 text-xl font-bold text-slate-900">Control de Asistencia</h3>
            <p class="mt-2 text-sm text-slate-600">
                Crea sesiones, registra presentes y ausentes, y consulta el historial de 4to A, 4to B y 4to C.
            </p>
        </a>
    </div>
@endsection
