@extends('layouts.docente')

@section('title', 'Banco de preguntas')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Banco de preguntas</h1>
            <p class="text-sm text-slate-500">Administra preguntas de Física por tema, dificultad y tipo.</p>
        </div>
        <a href="{{ route('docente.preguntas.create') }}" class="inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
            Nueva pregunta
        </a>
    </div>

    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
        <select name="topic_id" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos los temas</option>
            @foreach ($topics as $topic)
                <option value="{{ $topic->id }}" @selected((string) request('topic_id') === (string) $topic->id)>{{ $topic->name }}</option>
            @endforeach
        </select>
        <select name="difficulty" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todas las dificultades</option>
            @foreach (\App\Enums\QuestionDifficulty::cases() as $difficulty)
                <option value="{{ $difficulty->value }}" @selected(request('difficulty') === $difficulty->value)>{{ $difficulty->value }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Filtrar</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Pregunta</th>
                    <th class="px-4 py-3">Tema</th>
                    <th class="px-4 py-3">Dificultad</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">XP</th>
                    <th class="px-4 py-3">Tiempo</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($questions as $question)
                    <tr>
                        <td class="max-w-md px-4 py-3 text-slate-800">{{ \Illuminate\Support\Str::limit($question->question_text, 90) }}</td>
                        <td class="px-4 py-3">{{ $question->topic?->name }}</td>
                        <td class="px-4 py-3">{{ $question->difficulty->value }}</td>
                        <td class="px-4 py-3">{{ $question->type->value }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $question->xp_reward }}</td>
                        <td class="px-4 py-3">{{ $question->time_limit_seconds }}s</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $question->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $question->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('docente.preguntas.show', $question) }}" class="text-indigo-600 hover:underline">Ver</a>
                            <a href="{{ route('docente.preguntas.edit', $question) }}" class="ml-3 text-slate-600 hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                            Aún no hay preguntas. Importa el CSV o crea una nueva.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>
@endsection
