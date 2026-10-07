{{--
    Selector de Banner
    ------------------
    Muestra todos los banners que el estudiante posee.
    Al hacer click, se equipa automáticamente (vía AJAX).
--}}

@php
    // 1. Banners comprados por el usuario
    $ownedBanners = \App\Models\ShopItem::query()
        ->where('category', 'banner')
        ->whereIn('id', \App\Models\StudentItem::query()
            ->where('user_id', auth()->id())
            ->pluck('shop_item_id')
        )
        ->ordered()
        ->get();

    // 2. Banner actualmente equipado
    $currentBannerId = auth()->user()->studentProfile->banner_id ?? null;
@endphp

<div>
    <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
        Tus Banners
    </label>

    @if($ownedBanners->isEmpty())
        {{-- Sin banners comprados --}}
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/30">
            <span class="text-3xl">🖼️</span>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                Todavía no tienes banners.
            </p>
            <a href="{{ route('estudiante.tienda') }}"
               class="mt-3 inline-block rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-slate-900 transition hover:bg-amber-400">
                Ir a la Tienda
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($ownedBanners as $banner)
                @php
                    $isEquipped = $currentBannerId === $banner->id;
                @endphp

                <div class="group relative overflow-hidden rounded-2xl border-2 transition
                            {{ $isEquipped ? 'border-emerald-400 shadow-[0_0_20px_rgba(16,185,129,0.35)]' : 'border-slate-300 dark:border-slate-700 hover:border-amber-400/60' }}">

                    {{-- Miniatura del banner --}}
                    <div class="relative h-24 w-full overflow-hidden bg-slate-800">
                        @if($banner->media_type === 'video')
                            <video autoplay muted loop playsinline class="h-full w-full object-cover">
                                <source src="{{ asset($banner->media_path) }}">
                            </video>
                        @else
                            <img src="{{ asset($banner->media_path) }}"
                                 alt="{{ $banner->name }}"
                                 class="h-full w-full object-cover">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-slate-950/60"></div>
                    </div>

                    {{-- Info --}}
                    <div class="p-3">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-sm font-bold text-white">{{ $banner->name }}</h4>
                            <span class="shrink-0 rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-300">
                                {{ $banner->rarity_label }}
                            </span>
                        </div>

                        {{-- Estado / Acción --}}
                        <div class="mt-2">
                            @if($isEquipped)
                                <span class="block rounded-lg bg-emerald-500/20 px-3 py-1.5 text-center text-xs font-bold text-emerald-300">
                                    ✓ Equipado
                                </span>
                            @else
                                <button type="button"
                                        data-equip-banner="{{ $banner->id }}"
                                        class="w-full rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-violet-500">
                                    Equipar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Script: equipar banner vía AJAX --}}
<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        document.querySelectorAll('[data-equip-banner]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const bannerId = btn.dataset.equipBanner;
                btn.disabled = true;
                btn.textContent = '...';

                try {
                    const res = await fetch(`/estudiante/tienda/${bannerId}/equipar`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    const data = await res.json();

                    if (data.ok) {
                        window.location.reload();
                    } else {
                        alert('⚠️ ' + (data.message ?? 'Error al equipar'));
                        btn.disabled = false;
                        btn.textContent = 'Equipar';
                    }
                } catch (e) {
                    alert('⚠️ Error de red');
                    btn.disabled = false;
                    btn.textContent = 'Equipar';
                }
            });
        });
    })();
</script>