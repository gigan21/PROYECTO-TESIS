<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preguntas — {{ config('app.name') }}</title>

    {{-- FUENTES ESTILO RPG (Las mismas de tu Dashboard) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/step-puzzle.js'])

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

    {{-- ===== HEADER: HUD DEL JUGADOR (Mismo estilo que el panel) ===== --}}
    <header class="sticky top-0 z-20 border-b border-white/5 bg-[#1a1d2d]/90 backdrop-blur-md shadow-sm">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            
            <!-- Logo / Título -->
            <a href="{{ route('estudiante.inicio') }}" class="flex items-center gap-3 group">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-xl transition-colors group-hover:border-amber-400/50 group-hover:text-amber-400">⚔️</span>
                <span class="font-epic text-lg font-extrabold text-amber-400 transition-colors group-hover:text-amber-300">
                    Cinemática
                    <span class="block font-hud text-xs font-semibold text-slate-400">Área del Estudiante</span>
                </span>
            </a>

            <!-- Botones de Navegación -->
            <div class="flex items-center gap-3">
               
                <a href="{{ route('estudiante.preguntas.index') }}" class="hidden rounded-lg bg-amber-500/10 border border-amber-500/30 px-4 py-2 text-sm font-semibold text-amber-400 sm:block">
                    Preguntas
                </a>
                
                <div class="h-6 w-px bg-white/10 mx-2 hidden sm:block"></div>

                <a href="{{ route('student.profile') }}" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/10">
                    Mi Perfil
                </a>
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-rose-500/20 hover:text-rose-400 hover:border-rose-500/30">
                        Salir
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6">
        
        @include('estudiante.gamification.widget-progreso')

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

        <p class="text-sm font-medium text-slate-400">
            @if ($question && $question->difficulty->value === 'Difícil')
                Resuelve el problema paso a paso. Si no estás seguro, puedes saltar la misión.
            @else
                Lee el enunciado y selecciona la respuesta correcta.
            @endif
        </p>

        @if ($question)
            <article class="overflow-hidden rounded-2xl border border-white/5 bg-[#1a1d2d] shadow-xl">
                
                <!-- CABECERA: ETIQUETAS DE LA MISIÓN -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/5 bg-white/5 p-5 sm:p-6">
                    <div class="flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wide">
                        <span class="rounded-md bg-slate-800 px-3 py-1.5 text-slate-300 border border-white/5">{{ $question->topic?->name }}</span>
                        <span class="rounded-md bg-indigo-500/20 px-3 py-1.5 text-indigo-300 border border-indigo-500/20">{{ $question->type->value }}</span>
                        <span class="rounded-md bg-amber-500/20 px-3 py-1.5 text-amber-400 border border-amber-500/20">{{ $question->difficulty->value }}</span>
                        <span class="flex items-center gap-1 rounded-md bg-emerald-500/20 px-3 py-1.5 text-emerald-400 border border-emerald-500/20">
                            +{{ $question->xp_reward }} XP
                        </span>
                    </div>

                    @if($question->difficulty->value === 'Difícil')
                        <div id="bonus-badge" class="rounded-lg border border-amber-500/50 bg-amber-500/10 px-3 py-1.5 text-xs font-bold text-amber-400 transition-colors">
                            ⚡ +50% XP en &lt; 15s
                        </div>
                    @endif
                </div>

                <!-- BARRA DE TIEMPO (Colores limpios) -->
                <div class="h-2 w-full bg-slate-800">
                    <div id="time-progress-bar" class="h-full bg-emerald-500 transition-all duration-1000 ease-linear" style="width: 100%;"></div>
                </div>

                <div class="p-6 sm:p-8">
                    <!-- CRONÓMETRO -->
                    <div class="mb-6 flex items-center justify-between rounded-xl border border-white/5 bg-white/5 px-4 py-3">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400">
                            Tiempo Restante
                        </h2>
                        <span id="countdown-timer" class="font-epic text-2xl font-bold text-emerald-400">{{ $question->time_limit_seconds }}s</span>
                    </div>

                    <!-- ENUNCIADO -->
                    <p class="mb-6 text-xl font-medium leading-relaxed text-white sm:text-2xl">
                        {{ $question->question_text }}
                    </p>

                    <!-- IMAGEN DE APOYO -->
                    @if ($question->image_path)
                        <div class="mb-8 flex justify-center">
                            <img src="{{ asset('storage/' . $question->image_path) }}" alt="Imagen de apoyo" class="max-h-80 w-auto rounded-xl border border-white/10 shadow-lg">
                        </div>
                    @endif

                    <!-- ORQUESTADOR DE VISTAS SEGÚN DIFICULTAD -->
                    @if ($question->difficulty->value === 'Medio')
                        @include('estudiante.partials.puzzle-component')
                    @elseif ($question->difficulty->value === 'Difícil')
                        @include('estudiante.partials.blackboard-component')
                    @else

                        <!-- FORMULARIO OPCIÓN MÚLTIPLE (Fácil) -->
                        <form method="POST" action="{{ route('estudiante.preguntas.responder', $question) }}" class="space-y-6">
                            @csrf
                            <input type="hidden" name="time_taken" id="time_taken_input" value="0">

                            <!-- TARJETAS DE OPCIONES ESTILO RPG -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($question->options as $index => $option)
                                    <label class="group relative block cursor-pointer">
                                        <input type="radio" name="question_option_id" value="{{ $option->id }}" required class="peer sr-only">
                                        
                                        <!-- Estilo de la tarjeta inactiva y hover/checked -->
                                        <div class="flex min-h-[5rem] items-center gap-4 rounded-xl border border-white/10 bg-white/5 p-4 transition-all duration-200 
                                                    hover:border-amber-400/50 hover:bg-white/10 
                                                    peer-checked:border-amber-400 peer-checked:bg-amber-400/10 peer-checked:shadow-[0_0_15px_rgba(251,191,36,0.15)]
                                                    peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500">
                                            
                                            <!-- Letra (A, B, C, D) -->
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#131521] font-epic text-lg font-bold text-slate-400 transition-colors group-hover:text-amber-300 peer-checked:bg-amber-400 peer-checked:text-[#131521]">
                                                {{ chr(65 + $index) }}
                                            </span>
                                            
                                            <!-- Texto de la opción -->
                                            <span class="text-lg font-medium text-slate-300 transition-colors peer-checked:text-amber-100">
                                                {{ $option->option_text }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- BOTONES DE ACCIÓN -->
                            <div class="mt-8 flex flex-col items-center gap-4 border-t border-white/5 pt-6 sm:flex-row sm:justify-end">
                                <a href="{{ route('estudiante.preguntas.index') }}" class="w-full rounded-xl border border-white/10 bg-transparent px-6 py-3.5 text-center text-sm font-bold text-slate-400 transition hover:bg-white/5 hover:text-white sm:w-auto">
                                    Saltar misión
                                </a>
                                
                                <button type="submit" class="w-full rounded-xl bg-amber-500 px-8 py-3.5 text-sm font-bold text-slate-900 transition hover:bg-amber-400 hover:scale-[1.02] active:scale-95 shadow-md shadow-amber-500/20 sm:w-auto">
                                    Aceptar misión
                                </button>
                            </div>
                        </form>

                    @endif
                </div>
            </article>
        @else
            <div class="rounded-2xl border border-white/5 bg-[#1a1d2d] p-10 text-center shadow-xl">
                <p class="text-lg font-medium text-slate-300">¡Gremio limpio! No tienes misiones pendientes.</p>
                <a href="{{ route('estudiante.inicio') }}" class="mt-6 inline-block rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">
                    Volver a la base
                </a>
            </div>
        @endif
    </main>

    @if ($question)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let maxTime = {{ $question->time_limit_seconds ?? 60 }};
            let timeLeft = maxTime;
            let timeTaken = 0; 
            let isHard = "{{ $question->difficulty->value }}" === "Difícil";
            
            const timerDisplay = document.getElementById('countdown-timer');
            const progressBar = document.getElementById('time-progress-bar');
            const timeTakenInput = document.getElementById('time_taken_input');
            const bonusBadge = document.getElementById('bonus-badge');

            const countdown = setInterval(() => {
                timeLeft--;
                timeTaken++;
                
                if(timeTakenInput) {
                    timeTakenInput.value = timeTaken; 
                }

                timerDisplay.innerText = timeLeft + 's';

                let percentage = (timeLeft / maxTime) * 100;
                progressBar.style.width = percentage + '%';

                if (isHard && timeTaken > 15 && bonusBadge) {
                    bonusBadge.classList.remove('bg-amber-500/10', 'text-amber-400', 'border-amber-500/50');
                    bonusBadge.classList.add('bg-slate-800', 'text-slate-500', 'border-white/5');
                    bonusBadge.innerText = 'Bono agotado';
                }

                // COLORES LIMPIOS: Verde -> Naranja -> Rojo
                if (percentage <= 25) {
                    progressBar.className = "h-full transition-all duration-1000 ease-linear bg-rose-500";
                    timerDisplay.className = "font-epic text-2xl font-bold text-rose-500 animate-pulse";
                } else if (percentage <= 50) {
                    progressBar.className = "h-full transition-all duration-1000 ease-linear bg-amber-500";
                    timerDisplay.className = "font-epic text-2xl font-bold text-amber-500";
                }

                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerDisplay.innerText = "0s";
                    window.location.href = "{{ route('estudiante.preguntas.index') }}"; 
                }
            }, 1000);
        });
    </script>
    @endif

    @if (session('gamification_status') && str_contains(session('gamification_status'), 'Ganaste'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof window.lanzarConfeti === 'function') {
                    window.lanzarConfeti();
                }
            });
        </script>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Bloquear todos los formularios después del primer clic para evitar doble envío
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.style.pointerEvents = 'none';
                    btn.style.opacity = '0.7';
                    btn.innerHTML = 'Enviando... ⏳';
                }
            });
        });
    });
</script>
</body>
</html>