@php
    $question = $questions->first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $room->title }} — Desafío</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white shadow-xs">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <div>
                <p class="text-xs font-bold uppercase text-indigo-600">Desafío · {{ $room->classroom }}</p>
                <h1 class="text-lg font-semibold text-slate-900">{{ $room->title }}</h1>
            </div>
            <a href="{{ route('estudiante.inicio') }}" class="text-sm font-semibold text-indigo-600">Inicio</a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-6 px-6 py-10">
        @include('estudiante.gamification.widget-progreso')

        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
            Progreso: {{ $answeredCount }} / {{ $totalCount }} preguntas respondidas en esta sala.
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($question)
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-3 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1">{{ $question->topic?->name }}</span>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">+{{ $question->xp_reward }} XP si aciertas</span>
                </div>

                <div class="mb-5 flex items-center justify-between rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                    <span class="font-bold text-red-700">Tiempo restante:</span>
                    <span id="countdown-timer" class="text-xl font-black text-red-700">{{ $question->time_limit_seconds }}s</span>
                </div>

                <p class="mb-6 text-base font-medium text-slate-900">{{ $question->question_text }}</p>

                <form id="challenge-answer-form" method="POST" action="{{ route('estudiante.desafio.responder', ['code' => $room->code, 'question' => $question]) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="response_time_seconds" id="response_time_seconds" value="{{ $question->time_limit_seconds }}">
                    @foreach ($question->options as $index => $option)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:border-indigo-300 hover:bg-indigo-50/40">
                            <input type="radio" name="question_option_id" value="{{ $option->id }}" required class="mt-1">
                            <span class="text-sm"><span class="font-bold text-slate-500">{{ chr(65 + $index) }}.</span> {{ $option->option_text }}</span>
                        </label>
                    @endforeach
                    <button type="submit" class="mt-4 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
                        Enviar respuesta
                    </button>
                </form>
            </article>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const initial = {{ (int) $question->time_limit_seconds }};
                    let timeLeft = initial;
                    const timerDisplay = document.getElementById('countdown-timer');
                    const timeInput = document.getElementById('response_time_seconds');
                    const form = document.getElementById('challenge-answer-form');

                    const countdown = setInterval(() => {
                        timeLeft--;
                        timerDisplay.innerText = timeLeft + 's';
                        if (timeLeft <= 0) {
                            clearInterval(countdown);
                            timeInput.value = initial;
                            form.submit();
                        }
                    }, 1000);

                    form.addEventListener('submit', function () {
                        clearInterval(countdown);
                        const elapsed = Math.max(1, initial - timeLeft);
                        timeInput.value = elapsed;
                    });
                });
            </script>
        @else
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center">
                <p class="font-semibold text-emerald-900">Completaste todas las preguntas de este desafío.</p>
                <a href="{{ route('estudiante.inicio') }}" class="mt-4 inline-block text-sm font-bold text-indigo-600">Volver al inicio</a>
            </div>
        @endif
    </main>
</body>
</html>
