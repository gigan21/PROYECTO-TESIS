{{--
    Selector de Avatar
    ------------------
    Muestra:
      - Avatares base (siempre disponibles)
      - Avatares comprados en la tienda (con ⭐)
--}}

@php
    // 1. Avatares BASE (siempre disponibles)
    $baseAvatars = [
        'avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 'avatar5.jpg',
        'avatar6.jpg', 'avatar7.jpg', 'avatar8.jpg', 'avatar9.jpg', 'avatar10.jpg',
    ];

    // 2. Avatares COMPRADOS en la tienda
    $ownedShopAvatars = \App\Models\ShopItem::query()
        ->where('category', 'avatar')
        ->whereIn('id', \App\Models\StudentItem::query()
            ->where('user_id', auth()->id())
            ->pluck('shop_item_id')
        )
        ->ordered()
        ->get()
        ->map(fn ($item) => basename($item->media_path))
        ->all();

    // 3. Combinados (base + comprados, sin duplicados)
    $avatars = array_values(array_unique(array_merge($baseAvatars, $ownedShopAvatars)));

    $currentAvatar = auth()->user()->studentProfile->avatar_name ?? 'avatar1.jpg';
    if (str_contains($currentAvatar, '.png')) {
        $currentAvatar = str_replace('.png', '.jpg', $currentAvatar);
    }
@endphp

<div>
    <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
        Selecciona tu Avatar
    </label>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5">
        @foreach ($avatars as $avatar)
            @php
                $isPurchased = in_array($avatar, $ownedShopAvatars, true);
            @endphp

            <label class="group relative cursor-pointer">
                <input type="radio" name="avatar_name" value="{{ $avatar }}"
                       class="peer hidden" {{ $currentAvatar == $avatar ? 'checked' : '' }}>

                <div class="flex flex-col items-center rounded-2xl border-2 border-slate-200 bg-slate-50/50 p-3 transition-all group-hover:border-indigo-300 group-hover:bg-indigo-50/30 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/80 peer-checked:shadow-md dark:border-slate-700 dark:bg-slate-800/50 dark:group-hover:border-indigo-500 dark:peer-checked:border-indigo-500 dark:peer-checked:bg-indigo-900/30">

                    <div class="relative">
                        <img src="{{ asset('images/avatars/' . $avatar) }}"
                             alt="Avatar option"
                             class="h-16 w-16 rounded-xl object-cover shadow-sm transition duration-200 group-hover:scale-105 peer-checked:scale-105">

                        @if($isPurchased)
                            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-amber-400 text-[10px] shadow-md"
                                  title="Comprado en la tienda">
                                ⭐
                            </span>
                        @endif
                    </div>

                    <span class="mt-2 text-[11px] font-semibold text-slate-500 peer-checked:text-indigo-700 dark:text-slate-400 dark:peer-checked:text-indigo-300">
                        {{ $isPurchased ? 'Comprado' : 'Elegir' }}
                    </span>
                </div>
            </label>
        @endforeach
    </div>

    @if(count($ownedShopAvatars) === 0)
        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
            💡 Compra nuevos avatares en la
            <a href="{{ route('estudiante.tienda') }}" class="font-bold text-indigo-500 hover:underline">
                Tienda
            </a>
            para desbloquear más opciones.
        </p>
    @endif
</div>