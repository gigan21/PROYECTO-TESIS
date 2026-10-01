@extends('layouts.docente')

@section('title', 'Detalle de pregunta')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('docente.preguntas.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Volver al banco</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Detalle de la pregunta</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('docente.preguntas.edit', $question) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Editar</a>
            <form method="POST" action="{{ route('docente.preguntas.toggle', $question) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                    {{ $question->is_active ? 'Desactivar' : 'Activar' }}
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-3xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{{ $question->topic?->name }}</span>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">{{ $question->difficulty->value }}</span>
                <span class="rounded-full bg-violet-50 px-3 py-1 text-violet-700">{{ $question->type->value }}</span>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">{{ $question->xp_reward }} XP</span>
                <span class="rounded-full bg-orange-50 px-3 py-1 text-orange-700">⏱ {{ $question->time_limit_seconds }} seg</span>
                <span class="rounded-full px-3 py-1 {{ $question->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                    {{ $question->is_active ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
            <p class="text-base leading-relaxed text-slate-800">{{ $question->question_text }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">Opciones de respuesta</h2>
            <ul class="space-y-3">
                @foreach ($question->options as $index => $option)
                    <li class="flex items-start gap-3 rounded-xl border px-4 py-3 {{ $option->is_correct ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                        <span class="mt-0.5 text-sm font-bold text-slate-500">{{ chr(65 + $index) }}.</span>
                        <div class="flex-1 text-sm text-slate-800">{{ $option->option_text }}</div>
                        @if ($option->is_correct)
                            <span class="text-xs font-bold uppercase text-emerald-700">Correcta</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection