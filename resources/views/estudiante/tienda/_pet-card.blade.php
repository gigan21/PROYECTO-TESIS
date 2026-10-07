@php
    $borderColor = match($item['rarity'] ?? 'common') {
        'legendary' => 'border-amber-400 shadow-[0_0_25px_rgba(251,191,36,0.5)]',
        'epic'      => 'border-purple-400 shadow-[0_0_20px_rgba(168,85,247,0.4)]',
        'rare'      => 'border-blue-400 shadow-[0_0_15px_rgba(59,130,246,0.35)]',
        default     => 'border-slate-500',
    };

    $previewGif = $item['animations']['walking']
        ?? (is_array($item['animations']) ? reset($item['animations']) : null);
    $previewGif = is_array($previewGif) ? ($previewGif['right'] ?? reset($previewGif)) : $previewGif;
    $previewUrl = asset(config('pets.base_path') . '/' . $previewGif);
@endphp

<div class="flex flex-col items-center rounded-2xl border-2 {{ $borderColor }} bg-slate-900/60 p-4 text-center shadow-lg transition hover:scale-[1.02]">

    <div class="relative flex h-24 w-24 items-center justify-center">
        <img src="{{ $previewUrl }}"
             alt="{{ $item['name'] }}"
             class="h-20 w-20 object-contain drop-shadow-[0_0_12px_rgba(251,191,36,0.4)]">
    </div>

    <h3 class="mt-3 text-sm font-bold text-white">{{ $item['name'] }}</h3>

    <span class="mt-1 rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-300">
        {{ ucfirst($item['rarity'] ?? 'common') }}
    </span>

    <p class="mt-1 line-clamp-2 text-[11px] text-slate-400">{{ $item['description'] ?? '' }}</p>

    <span class="mt-2 flex items-center gap-1 text-sm font-bold text-amber-300">
        <span>🪙</span> {{ $item['price'] }}
    </span>

    <div class="mt-3 w-full">
        @if($equipped)
            <span class="block rounded-lg bg-emerald-500/20 px-3 py-1.5 text-xs font-bold text-emerald-300">
                ✓ Equipada
            </span>
        @elseif($owned)
            <button type="button"
                    data-equip-pet="{{ $item['id'] }}"
                    class="w-full rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-violet-500">
                Equipar
            </button>
        @elseif($canAfford)
            <button type="button"
                    data-buy-pet="{{ $item['id'] }}"
                    class="w-full rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-900 transition hover:bg-amber-400">
                Comprar
            </button>
        @else
            <span class="block rounded-lg bg-rose-500/20 px-3 py-1.5 text-xs font-bold text-rose-300">
                Sin monedas
            </span>
        @endif
    </div>
</div>