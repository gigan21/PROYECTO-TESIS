@extends('layouts.estudiante')

@section('title', 'Tienda')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">

    {{-- ===== ENCABEZADO ===== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('estudiante.inicio') }}"
               class="inline-flex items-center gap-1 text-sm font-semibold text-slate-400 transition hover:text-amber-300">
                ← Volver al Inicio
            </a>
            <h1 class="font-epic mt-1 text-3xl font-extrabold text-amber-300 md:text-4xl">
                🛒 Mercado de las Estrellas
            </h1>
            <p class="text-sm text-slate-400">Personaliza tu héroe con banners, avatares e insignias.</p>
        </div>

        {{-- Monedas actuales --}}
        <div class="flex items-center gap-3 rounded-2xl border border-amber-300/40 bg-amber-400/10 px-5 py-3 shadow-lg">
            <span class="text-2xl">🪙</span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-300">Tu saldo</p>
                <p class="text-2xl font-extrabold text-amber-100" id="coins-balance">
                    {{ $user->studentProfile->coins ?? 0 }}
                </p>
            </div>
        </div>
    </div>

    {{-- ===== SECCIÓN: BANNERS ===== --}}
    <section>
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-violet-400/40 bg-violet-400/10 text-lg">🖼️</span>
            <div>
                <h2 class="font-epic text-2xl font-extrabold text-violet-300">Banners</h2>
                <p class="text-xs text-slate-400">Personaliza el fondo de tu perfil y de tu inicio.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($banners as $item)
                @include('estudiante.tienda._banner-card', [
                    'item' => $item,
                    'owned' => in_array($item->id, $ownedIds, true),
                    'equipped' => $equippedBanner && $equippedBanner->id === $item->id,
                    'canAfford' => ($user->studentProfile->coins ?? 0) >= $item->price,
                ])
            @endforeach
        </div>
    </section>

    {{-- ===== SECCIÓN: AVATARES ===== --}}
    <section>
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-indigo-400/40 bg-indigo-400/10 text-lg">🧑</span>
            <div>
                <h2 class="font-epic text-2xl font-extrabold text-indigo-300">Avatares</h2>
                <p class="text-xs text-slate-400">Elige la cara con la que te verán tus compañeros.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-5">
            @foreach($avatars as $item)
                @include('estudiante.tienda._avatar-card', [
                    'item' => $item,
                    'owned' => in_array($item->id, $ownedIds, true),
                    'canAfford' => ($user->studentProfile->coins ?? 0) >= $item->price,
                ])
            @endforeach
        </div>
    </section>

</div>
{{-- ===== SECCIÓN: MASCOTAS ===== --}}
    <section>
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-emerald-400/40 bg-emerald-400/10 text-lg">🐾</span>
            <div>
                <h2 class="font-epic text-2xl font-extrabold text-emerald-300">Mascotas Virtuales</h2>
                <p class="text-xs text-slate-400">Compañeros que te acompañan en tu aventura.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-5">
            @foreach($pets as $item)
                @include('estudiante.tienda._pet-card', [
                    'item' => $item,
                    'owned' => in_array($item['id'], $ownedPets, true),
                    'equipped' => $activePet === $item['id'],
                    'canAfford' => ($user->studentProfile->coins ?? 0) >= $item['price'],
                ])
            @endforeach
        </div>
    </section>
{{-- ===== SCRIPT: comprar + equipar ===== --}}
<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        async function post(url) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            return res.json();
        }

        // --- COMPRAR ---
        document.querySelectorAll('[data-buy]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = btn.dataset.buy;
                btn.disabled = true;
                btn.textContent = '...';

                const data = await post(url);
                if (data.ok) {
                    // Refrescar la página para ver el nuevo estado
                    window.location.reload();
                } else {
                    alert('⚠️ ' + (data.message ?? 'Error al comprar'));
                    btn.disabled = false;
                    btn.textContent = 'Comprar';
                }
            });
        });

        // --- EQUIPAR ---
        document.querySelectorAll('[data-equip]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = btn.dataset.equip;
                btn.disabled = true;

                const data = await post(url);
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('⚠️ ' + (data.message ?? 'Error al equipar'));
                    btn.disabled = false;
                }
            });
        });

        // --- DESEQUIPAR ---
        document.querySelectorAll('[data-unequip]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = btn.dataset.unequip;
                btn.disabled = true;

                const data = await post(url);
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('⚠️ ' + (data.message ?? 'Error al quitar'));
                    btn.disabled = false;
                }
            });
        });
                // --- COMPRAR MASCOTA ---
                document.querySelectorAll('[data-buy-pet]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const petId = btn.dataset.buyPet;
                btn.disabled = true;
                btn.textContent = '...';

                const res = await fetch(`/estudiante/tienda/mascotas/${petId}/comprar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (data.ok) window.location.reload();
                else { alert('⚠️ ' + (data.message ?? 'Error')); btn.disabled = false; btn.textContent = 'Comprar'; }
            });
        });

        // --- EQUIPAR MASCOTA ---
        document.querySelectorAll('[data-equip-pet]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const petId = btn.dataset.equipPet;
                btn.disabled = true;
                btn.textContent = '...';

                const res = await fetch(`/estudiante/tienda/mascotas/${petId}/equipar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (data.ok) window.location.reload();
                else { alert('⚠️ ' + (data.message ?? 'Error')); btn.disabled = false; btn.textContent = 'Equipar'; }
            });
        });
    })();
</script>
@endsection