@php
    use App\Support\PetCatalog;

    $user = auth()->user();
    $ownedPets = PetCatalog::ownedIdsFor($user);
    $activePet = PetCatalog::activeFor($user);
    $catalog = PetCatalog::all();

    // Mascotas que el estudiante puede equipar (solo las que tiene)
    $pets = collect($ownedPets)
        ->filter(fn ($id) => isset($catalog[$id]))
        ->map(fn ($id) => ['id' => $id] + $catalog[$id])
        ->values();
@endphp

<div>
    <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
        Tus Mascotas
    </label>

    @if($pets->isEmpty())
        {{-- Sin mascotas (raro, porque dog es gratis) --}}
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/30">
            <span class="text-3xl">🐾</span>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                No tienes mascotas todavía.
            </p>
            <a href="{{ route('estudiante.tienda') }}"
               class="mt-3 inline-block rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-slate-900 transition hover:bg-amber-400">
                Ir a la Tienda
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach($pets as $pet)
                @php
                    $isActive = $activePet === $pet['id'];

                    // GIF de preview (el de "walking")
                    $walkAnim = $pet['animations']['walking'] ?? null;
                    $walkFile = is_array($walkAnim)
                        ? ($walkAnim['right'] ?? reset($walkAnim))
                        : $walkAnim;
                    $previewUrl = asset(config('pets.base_path') . '/' . $walkFile);
                @endphp

                <div class="group relative overflow-hidden rounded-2xl border-2 transition
                            {{ $isActive
                                ? 'border-emerald-400 bg-emerald-500/10 shadow-[0_0_20px_rgba(16,185,129,0.35)]'
                                : 'border-slate-300 bg-slate-800/40 hover:border-amber-400/60 dark:border-slate-700' }}">

                    {{-- Preview de la mascota --}}
                    <div class="flex h-24 items-center justify-center bg-slate-900/40">
                        <img src="{{ $previewUrl }}"
                             alt="{{ $pet['name'] }}"
                             class="h-20 w-20 object-contain drop-shadow-[0_0_10px_rgba(251,191,36,0.4)]">
                    </div>

                    {{-- Info + Acción --}}
                    <div class="p-3 text-center">
                        <h4 class="text-sm font-bold text-white">{{ $pet['name'] }}</h4>

                        @if($isActive)
                            <span class="mt-2 block rounded-lg bg-emerald-500/20 px-3 py-1.5 text-xs font-bold text-emerald-300">
                                ✓ Equipada
                            </span>
                        @else
                            <button type="button"
                                    data-equip-pet="{{ $pet['id'] }}"
                                    class="mt-2 w-full rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-violet-500">
                                Equipar
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($activePet)
            <div class="mt-3 text-center">
                <button type="button"
                        id="unequip-pet-btn"
                        class="text-xs font-semibold text-slate-500 hover:text-rose-400 hover:underline transition">
                    Ocultar mi mascota
                </button>
            </div>
        @endif
    @endif
</div>

{{-- Script: equipar/quitar mascota --}}
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

        // --- EQUIPAR ---
        document.querySelectorAll('[data-equip-pet]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const petId = btn.dataset.equipPet;
                btn.disabled = true;
                btn.textContent = '...';

                const data = await post(`/estudiante/tienda/mascotas/${petId}/equipar`);
                if (data.ok) window.location.reload();
                else {
                    alert('⚠️ ' + (data.message ?? 'Error'));
                    btn.disabled = false;
                    btn.textContent = 'Equipar';
                }
            });
        });

        // --- QUITAR ---
        const unequipBtn = document.getElementById('unequip-pet-btn');
        if (unequipBtn) {
            unequipBtn.addEventListener('click', async () => {
                if (!confirm('¿Ocultar tu mascota?')) return;
                unequipBtn.disabled = true;
                unequipBtn.textContent = '...';

                const data = await post('/estudiante/tienda/mascotas/desequipar');
                if (data.ok) window.location.reload();
                else {
                    alert('⚠️ ' + (data.message ?? 'Error'));
                    unequipBtn.disabled = false;
                    unequipBtn.textContent = 'Ocultar mi mascota';
                }
            });
        }
    })();
</script>