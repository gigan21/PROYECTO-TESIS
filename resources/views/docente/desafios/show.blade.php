@extends('layouts.docente')

@section('title', $room->title)

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('docente.desafios.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Desafíos</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $room->title }}</h1>
            <p class="text-sm text-slate-500">Paralelo: <strong>{{ $room->classroom }}</strong> · Estado: <strong>{{ $room->status->value }}</strong></p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($room->status->value !== 'finished')
                <form method="POST" action="{{ route('docente.desafios.start', $room) }}">
                    @csrf
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">Iniciar desafío</button>
                </form>
            @endif
            @if ($room->status->value !== 'finished')
                <form method="POST" action="{{ route('docente.desafios.finish', $room) }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Finalizar</button>
                </form>
            @endif
        </div>
    </div>

    <div class="mb-8 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6">
            <p class="text-xs font-bold uppercase text-indigo-800">Código de acceso</p>
            <p class="mt-1 font-mono text-2xl font-black text-indigo-950">{{ $room->code }}</p>
            <p class="mt-4 text-xs font-bold uppercase text-indigo-800">Enlace para estudiantes</p>
            <p class="mt-1 break-all text-sm text-indigo-900">{{ $joinUrl }}</p>
            <p class="mt-2 text-xs text-indigo-700">Los estudiantes deben iniciar sesión y pertenecer al paralelo {{ $room->classroom }}.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-800">Preguntas incluidas ({{ $room->questions->count() }})</p>
            <ul class="mt-3 max-h-48 space-y-2 overflow-y-auto text-sm text-slate-600">
                @foreach ($room->questions as $question)
                    <li>{{ $loop->iteration }}. {{ \Illuminate\Support\Str::limit($question->question_text, 70) }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="font-semibold text-slate-900">Participantes</h2>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-2">Estudiante</th>
                    <th class="px-4 py-2">Respondidas</th>
                    <th class="px-4 py-2">Correctas</th>
                    <th class="px-4 py-2">XP</th>
                    <th class="px-4 py-2">Tiempo prom.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($participants as $row)
                    <tr>
                        <td class="px-4 py-2">{{ $row['student']->name }}</td>
                        <td class="px-4 py-2">{{ $row['answered'] }}</td>
                        <td class="px-4 py-2">{{ $row['correct'] }}</td>
                        <td class="px-4 py-2">{{ $row['xp'] }}</td>
                        <td class="px-4 py-2">{{ $row['avg_time'] }}s</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Sin respuestas aún.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="font-semibold text-slate-900">Detalle de respuestas</h2>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-2">Estudiante</th>
                    <th class="px-4 py-2">Pregunta</th>
                    <th class="px-4 py-2">Resultado</th>
                    <th class="px-4 py-2">Tiempo</th>
                    <th class="px-4 py-2">XP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($answers as $answer)
                    <tr>
                        <td class="px-4 py-2">{{ $answer->student->name }}</td>
                        <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($answer->question->question_text, 50) }}</td>
                        <td class="px-4 py-2">{{ $answer->is_correct ? 'Correcta' : 'Incorrecta' }}</td>
                        <td class="px-4 py-2">{{ $answer->response_time_seconds }}s</td>
                        <td class="px-4 py-2">{{ $answer->xp_earned }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Sin registros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
