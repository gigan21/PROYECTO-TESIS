@props(['badges'])

{{-- Estilos solo para las insignias --}}
<style>
    .badge-glow {
        filter: drop-shadow(0 0 6px rgba(251, 191, 36, 0.55));
        transition: all 0.3s ease;
    }

    .badge-glow:hover {
        filter: drop-shadow(0 0 12px rgba(251, 191, 36, 0.85));
        transform: scale(1.08);
    }

    .badge-glow-strong {
        filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.7))
                drop-shadow(0 0 16px rgba(245, 158, 11, 0.45));
        animation: pulse-glow 2.8s ease-in-out infinite;
    }

    @keyframes pulse-glow {
        0%, 100% {
            filter: drop-shadow(0 0 6px rgba(251, 191, 36, 0.6))
                    drop-shadow(0 0 12px rgba(245, 158, 11, 0.35));
        }
        50% {
            filter: drop-shadow(0 0 12px rgba(251, 191, 36, 0.95))
                    drop-shadow(0 0 22px rgba(245, 158, 11, 0.6));
        }
    }
</style>

<div class="mt-5 w-full">
    <p class="mb-2 text-left text-sm font-semibold text-slate-300">🏅 Logros y medallas</p>
    
    <div class="grid grid-cols-3 gap-2">
        @foreach ($badges as $badge)
            <div class="flex aspect-square flex-col items-center justify-center rounded-lg border border-white/20 bg-black/20 p-1 text-center transition hover:bg-black/30">
                
                @if ($badge['is_placeholder'])
                    <img
                        src="{{ asset('images/Insignias/' . $badge['image']) }}"
                        alt="{{ $badge['name'] }}"
                        class="h-12 w-12 object-contain opacity-40 grayscale"
                    >
                    <span class="mt-0.5 text-[10px] font-semibold text-slate-500">Próximamente</span>

                @elseif ($badge['unlocked'])
                    <img
                        src="{{ asset('images/Insignias/' . $badge['image']) }}"
                        alt="{{ $badge['name'] }}"
                        class="h-14 w-14 object-contain 
                            {{ in_array($badge['slug'], ['level_4', 'level_5']) ? 'badge-glow-strong' : 'badge-glow' }}"
                        title="{{ $badge['name'] }}"
                    >

                @else
                    <p class="px-1 text-[10px] leading-tight text-slate-400">
                        {{ $badge['requirement_text'] }}
                    </p>
                @endif

            </div>
        @endforeach
    </div>
</div>