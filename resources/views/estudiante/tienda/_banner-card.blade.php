@php
    $borderColor = match($item->rarity) {
        'legendary' => 'border-amber-400 shadow-[0_0_25px_rgba(251,191,36,0.5)]',
        'epic'      => 'border-purple-400 shadow-[0_0_20px_rgba(168,85,247,0.4)]',
        'rare'      => 'border-blue-400 shadow-[0_0_15px_rgba(59,130,246,0.35)]',
        default     => 'border-slate-500',
    };
@endphp

<div class="relative overflow-hidden rounded-2xl border-2 {{ $borderColor }} bg-slate-900/60 shadow-lg transition hover:scale-[1.02]">

    {{-- Miniatura del banner --}}
    <div class="relative h-32 w-full overflow-hidden">
        <img src="{{ asset($item->media_path) }}"
             alt="{{ $item->name }}"
             class="h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-slate-950/80"></div>
    </div>

    {{-- Info --}}
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-bold text-white">{{ $item->name }}</h3>
            <span class="shrink-0 rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-300">
                {{ $item->rarity_label }}
            </span>
        </div>
        <p class="mt-1 line-clamp-2 text-xs text-slate-400">{{ $item->description }}</p>

        <div class="mt-3 flex items-center justify-between">
            <span class="flex items-center gap-1 font-bold text-amber-300">
                <span>🪙</span> {{ $item->price }}
            </span>

            {{-- Acciones --}}
            @if($equipped)
                <span class="rounded-lg bg-emerald-500/20 px-3 py-1.5 text-xs font-bold text-emerald-300">
                    ✓ Equipado
                </span>
            @elseif($owned)
                <button type="button"
                        data-equip="{{ route('estudiante.tienda.equip', $item) }}"
                        class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-violet-500">
                    Equipar
                </button>
            @elseif($canAfford)
                <button type="button"
                        data-buy="{{ route('estudiante.tienda.buy', $item) }}"
                        class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-900 transition hover:bg-amber-400">
                    Comprar
                </button>
            @else
                <span class="rounded-lg bg-rose-500/20 px-3 py-1.5 text-xs font-bold text-rose-300">
                    Sin monedas
                </span>
            @endif
        </div>
    </div>
</div>