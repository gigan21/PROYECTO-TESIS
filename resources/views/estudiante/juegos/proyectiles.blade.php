<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulador de Proyectiles — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <!-- MENÚ SUPERIOR -->
    <header class="border-b border-slate-200 bg-white shadow-xs">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <div>
                <a href="{{ route('estudiante.inicio') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Volver al Inicio</a>
                <h1 class="text-lg font-semibold text-slate-900">Simulador de Proyectiles</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('estudiante.preguntas.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Preguntas</a>
                <a href="{{ route('student.profile') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Mi Perfil</a>
            </div>
        </div>
    </header>

    <!-- PANEL DE MONEDAS -->
    <section class="mx-auto max-w-6xl px-6 pt-6">
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-amber-300/60 bg-gradient-to-r from-amber-50 to-yellow-50 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-400/30 text-2xl">🪙</div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Tu saldo</p>
                    <p class="text-2xl font-extrabold text-amber-900">
                        <span id="coins-balance">—</span> <span class="text-base font-bold">monedas</span>
                    </p>
                </div>
            </div>

            <div class="flex-1 min-w-[220px]">
                <div class="flex items-center justify-between text-xs font-semibold text-amber-800">
                    <span>Ganadas hoy en este juego</span>
                    <span><b id="daily-earned">0</b> / <span id="daily-cap">—</span></span>
                </div>
                <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-amber-200/60">
                    <div id="daily-bar" class="h-full rounded-full bg-amber-500 transition-all" style="width: 0%"></div>
                </div>
                <p id="daily-msg" class="mt-1 text-xs text-amber-700">Cargando…</p>
            </div>
        </div>
    </section>

    <!-- CONTENEDOR DEL JUEGO -->
    <main class="mx-auto max-w-6xl space-y-6 px-6 py-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-lg h-[700px] lg:h-[800px]">
            <iframe
                id="game-frame"
                src="{{ asset('JUEGO_PROYECTILES/index.html') }}?csrf={{ urlencode(csrf_token()) }}&reward_url={{ urlencode(route('estudiante.proyectiles.api.reward')) }}&status_url={{ urlencode(route('estudiante.proyectiles.api.status')) }}"
                class="w-full h-full border-0 rounded-xl"
                allowfullscreen>
            </iframe>
        </div>
    </main>

    <!-- SCRIPT: actualiza el panel de monedas y escucha mensajes del iframe -->
    <script>
        (function () {
            const statusUrl = @json(route('estudiante.proyectiles.api.status'));
            const csrfToken = @json(csrf_token());

            const els = {
                balance: document.getElementById('coins-balance'),
                dailyEarned: document.getElementById('daily-earned'),
                dailyCap: document.getElementById('daily-cap'),
                dailyBar: document.getElementById('daily-bar'),
                dailyMsg: document.getElementById('daily-msg'),
            };

            function renderStatus(data) {
                els.balance.textContent = data.coins;
                els.dailyEarned.textContent = data.daily_earned;
                els.dailyCap.textContent = data.daily_cap;

                const pct = data.daily_cap > 0
                    ? Math.min(100, Math.round((data.daily_earned / data.daily_cap) * 100))
                    : 0;
                els.dailyBar.style.width = pct + '%';

                if (data.daily_remaining <= 0) {
                    els.dailyMsg.textContent = '⛔ Tope diario alcanzado. Vuelve mañana.';
                    els.dailyBar.classList.remove('bg-amber-500');
                    els.dailyBar.classList.add('bg-rose-500');
                } else {
                    els.dailyMsg.textContent = `Te quedan ${data.daily_remaining} monedas disponibles hoy.`;
                    els.dailyBar.classList.add('bg-amber-500');
                    els.dailyBar.classList.remove('bg-rose-500');
                }
            }

            async function refreshStatus() {
                try {
                    const res = await fetch(statusUrl, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    renderStatus(data);
                } catch (e) {
                    console.warn('No se pudo actualizar el panel de monedas:', e);
                }
            }

            // 1) Cargar al inicio
            refreshStatus();

            // 2) Escuchar mensajes del iframe (lo usaremos en el 2.D)
            window.addEventListener('message', (event) => {
                // Solo aceptar mensajes de nuestro propio origen
                if (event.origin !== window.location.origin) return;

                const msg = event.data || {};
                if (msg.type === 'projectiles:refresh-coins') {
                    refreshStatus();
                }
                if (msg.type === 'projectiles:reward') {
                    // Refresco inmediato del panel cuando el juego avisa que ganó monedas
                    refreshStatus();
                }
            });

            // 3) Exponer el token por si en el futuro lo necesitamos aquí
            window.__projectiles_csrf = csrfToken;
        })();
    </script>
</body>
</html>