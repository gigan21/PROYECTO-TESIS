@extends('layouts.docente')

@section('title', 'Crear desafío')

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.desafios.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Volver</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Nuevo desafío</h1>
    </div>

    <form method="POST" action="{{ route('docente.desafios.store') }}" class="max-w-3xl space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        @csrf

        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-slate-700">Título</label>
            <input id="title" name="title" type="text" required maxlength="255" value="{{ old('title') }}"
                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
        </div>

        <div>
            <label for="classroom" class="mb-1.5 block text-sm font-medium text-slate-700">Paralelo</label>
            <select id="classroom" name="classroom" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                <option value="">Selecciona paralelo</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom }}" @selected(old('classroom') === $classroom)>{{ $classroom }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <p class="mb-2 text-sm font-medium text-slate-700">Preguntas del desafío (activas)</p>
            <div class="max-h-80 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-4">
                @forelse ($questions as $question)
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 px-3 py-2 hover:bg-slate-50">
                        <input type="checkbox" name="question_ids[]" value="{{ $question->id }}"
                               @checked(collect(old('question_ids', []))->contains($question->id))>
                        <span class="text-sm text-slate-800">
                            <span class="font-semibold text-indigo-700">{{ $question->topic?->name }}</span> —
                            {{ \Illuminate\Support\Str::limit($question->question_text, 80) }}
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-slate-500">No hay preguntas activas. Importa o crea preguntas primero.</p>
                @endforelse
            </div>
        </div>

        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700">
            Crear desafío
        </button>
    </form>
@endsection
