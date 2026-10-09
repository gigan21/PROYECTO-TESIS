@php
    $question = $questions->first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $room->title }} — Desafío</title>

    {{-- FUENTES ESTILO RPG (mismas del dashboard) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/step-puzzle.js', 'resources/js/blackboard-quiz.js'])

    <style>
        .font-epic { font-family: 'Cinzel', Georgia, serif; }
        .font-hud  { font-family: 'Rajdhani', system-ui, sans-serif; }
    </style>
</head>
<body class="font-hud min-h-screen bg-[#131521] text-slate-200 antialiased">

    {{-- FONDO OSCURO ESTILO DASHBOARD --}}
    <div class="fixed inset-0 -z-10 bg-[#131521]">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(99,102,241,0.08),transparent_50%),radial-gradient(ellipse_at_bottom_right,rgba(251,191,36,0.05),transparent_50%)]"></div>
    </div>

    {{-- HEADER HUD --}}
    <header class="sticky top-0 z-20 border-b border-white/5 bg-[#1a1d2d]/90 backdrop-blur-md shadow-sm">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <a href="{{ route('estudiante.inicio') }}" class="flex items-center gap-3 group">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-xl transition-colors group-hover:border-amber-400/50 group-hover:text-amber-400">⚔️</span>
                <span class="font-epic text-lg font-extrabold text-amber-400 transition-colors group-hover:text-amber-300">
                    {{ $room->title }}
                    <span class="block font-hud text-xs font-semibold text-slate-400">Desafío · {{ $room->classroom }}</span>
                </span>
            </a>

            <a href="{{ route('estudiante.inicio') }}" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/10">
                Inicio
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6">

        @include('estudiante.gamification.widget-progreso')

        <div class="rounded-xl border border-white/5 bg-[#1a1d2d] px-4 py-3 text-sm text-slate-400">
            Progreso: <span class="font-bold text-amber-400">{{ $answeredCount }}</span> / {{ $totalCount }} preguntas respondidas en esta sala.
        </div>

        @if (session('gamification_status'))
            <div class="flex items-center gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-400">
                <span>✨</span>
                {{ session('gamification_status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($question)
            @php
                $isHard = $question->difficulty->value === 'Difícil';
                $isMedium = $question->difficulty->value === 'Medio';
            @endphp

            <article class="overflow-hidden rounded-2xl border border-white/5 bg-[#1a1d2d] shadow-xl">

                <!-- CABECERA: ETIQUETAS -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/5 bg-white/5 p-5 sm:p-6">
                    <div class="flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wide">
                        <span class="rounded-md bg-slate-800 px-3 py-1.5 text-slate-300 border border-white/5">{{ $question->topic?->name }}</span>
                        <span class="rounded-md bg-indigo-500/20 px-3 py-1.5 text-indigo-300 border border-indigo-500/20">{{ $question->type->value }}</span>
                        <span class="rounded-md bg-amber-500/20 px-3 py-1.5 text-amber-400 border border-amber-500/20">{{ $question->difficulty->value }}</span>
                        <span class="flex items-center gap-1 rounded-md bg-emerald-500/20 px-3 py-1.5 text-emerald-400 border border-emerald-500/20">
                            +{{ $question->xp_reward }} XP
                        </span>
                    </div>

                    @if ($isHard)
                        <div id="bonus-badge" class="rounded-lg border border-amber-500/50 bg-amber-500/10 px-3 py-1.5 text-xs font-bold text-amber-400 transition-colors">
                            ⚡ +50% XP en &lt; 15s
                        </div>
                    @endif
                </div>

                <!-- BARRA DE TIEMPO -->
                <div class="h-2 w-full bg-slate-800">
                    <div id="time-progress-bar" class="h-full bg-emerald-500 transition-all duration-1000 ease-linear" style="width: 100%;"></div>
                </div>

                <div class="p-6 sm:p-8">
                    <!-- CRONÓMETRO -->
                    <div class="mb-6 flex items-center justify-between rounded-xl border border-white/5 bg-white/5 px-4 py-3">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400">Tiempo Restante</h2>
                        <span id="countdown-timer" class="font-epic text-2xl font-bold text-emerald-400">{{ $question->time_limit_seconds }}s</span>
                    </div>

                    <!-- ENUNCIADO -->
                    <p class="mb-6 text-xl font-medium leading-relaxed text-white sm:text-2xl">
                        {{ $question->question_text }}
                    </p>

                    <!-- IMAGEN DE APOYO (solo si NO es Difícil, porque el hard-steps-form ya la incluye) -->
                    @if ($question->image_path && ! $isHard)
                        <div class="mb-8 flex justify-center">
                            <img src="{{ asset('storage/' . $question->image_path) }}"
                                 alt="Imagen de apoyo"
                                 class="max-h-80 w-auto rounded-xl border border-white/10 shadow-lg">
                        </div>
                    @endif

                    {{-- ORQUESTADOR DE VISTAS SEGÚN DIFICULTAD --}}
                    @if ($isMedium)
                        {{-- Puzzle: el componente espera un action de quiz libre, hay que sobreescribirlo --}}
                        @include('estudiante.partials.puzzle-component', [
                            'action' => route('estudiante.desafio.responder', ['code' => $room->code, 'question' => $question]),
                            'timeField' => 'response_time_seconds',
                        ])
                    @elseif ($isHard)
                        @include('estudiante.partials.hard-steps-form', [
                            'action' => route('estudiante.desafio.responder', ['code' => $room->code, 'question' => $question]),
                            'timeField' => 'response_time_seconds',
                        ])
                    @else
                        {{-- FÁCIL: opciones A/B/C/D (solo las opciones reales, sin block_type) --}}
                        @php
                            $realOptions = $question->options->filter(fn ($o) => $o->block_type === null)->values();
                        @endphp

                        <form id="challenge-answer-form" method="POST"
                              action="{{ route('estudiante.desafio.responder', ['code' => $room->code, 'question' => $question]) }}"
                              class="space-y-6">
                            @csrf
                            <input type="hidden" name="response_time_seconds" id="response_time_seconds" value="0">

                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($realOptions as $index => $option)
                                    <label class="group relative block cursor-pointer">
                                        <input type="radio" name="question_option_id" value="{{ $option->id }}" required class="peer sr-only">

                                        <div class="flex min-h-[5rem] items-center gap-4 rounded-xl border border-white/10 bg-white/5 p-4 transition-all duration-200
                                                    hover:border-amber-400/50 hover:bg-white/10
                                                    peer-checked:border-amber-400 peer-checked:bg-amber-400/10 peer-checked:shadow-[0_0_15px_rgba(251,191,36,0.15)]
                                                    peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#131521] font-epic text-lg font-bold text-slate-400 transition-colors group-hover:text-amber-300 peer-checked:bg-amber-400 peer-checked:text-[#131521]">
                                                {{ chr(65 + $index) }}
                                            </span>
                                            <span class="text-lg font-medium text-slate-300 transition-colors peer-checked:text-amber-100">
                                                {{ $option->option_text }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-8 flex justify-end border-t border-white/5 pt-6">
                                <button type="submit" class="rounded-xl bg-amber-500 px-8 py-3.5 text-sm font-bold text-slate-900 transition hover:bg-amber-400 hover:scale-[1.02] active:scale-95 shadow-md shadow-amber-500/20">
                                    Aceptar misión
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </article>

            {{-- CRONÓMETRO GLOBAL (para todas las dificultades) --}}
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    let maxTime = {{ (int) $question->time_limit_seconds }};
                    let timeLeft = maxTime;
                    let timeTaken = 0;
                    let isHard = @json($isHard);

                    const timerDisplay = document.getElementById('countdown-timer');
                    const progressBar = document.getElementById('time-progress-bar');
                    const bonusBadge = document.getElementById('bonus-badge');

                    // Inputs de tiempo en los formularios visibles
                    const timeInputs = document.querySelectorAll('input[name="response_time_seconds"], input[name="time_taken"]');
                    // Inputs de tiempo en los formularios de "Saltar" (si los hay)
                    const skipTimeInputs = document.querySelectorAll('.skip_time_taken_input');

                    const countdown = setInterval(() => {
                        timeLeft--;
                        timeTaken++;

                        timeInputs.forEach(inp => { inp.value = timeTaken; });
                        skipTimeInputs.forEach(inp => { inp.value = timeTaken; });

                        if (timerDisplay) timerDisplay.innerText = timeLeft + 's';

                        if (progressBar) {
                            let percentage = (timeLeft / maxTime) * 100;
                            progressBar.style.width = percentage + '%';

                            if (percentage <= 25) {
                                progressBar.className = "h-full transition-all duration-1000 ease-linear bg-rose-500";
                                if (timerDisplay) timerDisplay.className = "font-epic text-2xl font-bold text-rose-500 animate-pulse";
                            } else if (percentage <= 50) {
                                progressBar.className = "h-full transition-all duration-1000 ease-linear bg-amber-500";
                                if (timerDisplay) timerDisplay.className = "font-epic text-2xl font-bold text-amber-500";
                            }
                        }

                        if (isHard && timeTaken > 15 && bonusBadge) {
                            bonusBadge.classList.remove('bg-amber-500/10', 'text-amber-400', 'border-amber-500/50');
                            bonusBadge.classList.add('bg-slate-800', 'text-slate-500', 'border-white/5');
                            bonusBadge.innerText = 'Bono agotado';
                        }

                        if (timeLeft <= 0) {
                            clearInterval(countdown);
                            if (timerDisplay) timerDisplay.innerText = "0s";

                            // Auto-envío si hay un form con opción seleccionada
                            const easyForm = document.getElementById('challenge-answer-form');
                            if (easyForm) {
                                const selected = easyForm.querySelector('input[name="question_option_id"]:checked');
                                if (selected) {
                                    easyForm.submit();
                                } else {
                                    window.location.href = "{{ route('estudiante.desafio.show', $room->code) }}";
                                }
                            } else {
                                // Para Medio/Difícil, solo recargamos
                                window.location.href = "{{ route('estudiante.desafio.show', $room->code) }}";
                            }
                        }
                    }, 1000);
                });
            </script>
        @else
            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-10 text-center shadow-xl">
                <p class="text-lg font-medium text-emerald-300">¡Completaste todas las preguntas de este desafío!</p>
                <a href="{{ route('estudiante.inicio') }}" class="mt-6 inline-block rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">
                    Volver al inicio
                </a>
            </div>
        @endif
    </main>
    @stack('scripts')
</body>
</html>