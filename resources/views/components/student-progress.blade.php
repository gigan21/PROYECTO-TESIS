@props(['progress'])

<div class="mt-5 w-full rounded-xl border border-white/20 bg-white/10 p-4 text-left backdrop-blur-md">
    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Progreso</p>
    <p class="font-epic mt-1 text-lg font-bold text-white">Nivel {{ $progress['level'] }}</p>
    <p class="mt-0.5 text-xs text-indigo-200">{{ $progress['rank_title'] }}</p>

    <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-black/30">
        <div
            class="h-full rounded-full bg-linear-to-r from-amber-400 via-indigo-500 to-violet-500 transition-all duration-700"
            style="width: {{ min(100, max(0, $progress['percent'])) }}%"
        ></div>
    </div>
    <p class="mt-1.5 text-xs text-slate-400">
        {{ $progress['xp_in_level'] }} / {{ $progress['xp_per_level'] }} XP hacia el siguiente nivel
    </p>

    <p class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-amber-300">
        <span aria-hidden="true">🪙</span>
        {{ number_format($progress['coins']) }} monedas
    </p>
</div>
