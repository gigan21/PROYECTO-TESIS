@php
    $user    = auth()->user();
    $profile = $user->studentProfile;
    $xp      = $profile->xp_points ?? 0;
    $xpBar   = $xp % 100; // barra de progreso al siguiente nivel (ajusta a tu fórmula real)
@endphp

<!-- Columna Izquierda: Tarjeta de Perfil RPG -->
<div class="lg:col-span-4">
    <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">

        <!-- Línea de neón superior -->
        <div class="absolute inset-x-0 top-0 z-20 h-px bg-gradient-to-r from-transparent via-pink-400 to-transparent"></div>

                  <!-- ===== BANNER ===== -->
        @php
            $banner = $profile->banner;
            $bannerPath = $banner?->media_path;
            $bannerExists = $bannerPath && file_exists(public_path($bannerPath));
        @endphp

        <div class="relative h-36 w-full overflow-hidden bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
            @if($bannerExists)
                @if($banner?->media_type === 'video')
                    <video autoplay muted loop playsinline class="absolute inset-0 h-full w-full object-cover">
                        <source src="{{ asset($bannerPath) }}">
                    </video>
                @else
                    <img src="{{ asset($bannerPath) }}"
                         alt="{{ $banner->name ?? 'Banner' }}"
                         class="absolute inset-0 h-full w-full object-cover">
                @endif
            @endif

            <!-- Rejilla cyberpunk sutil -->
            <div class="pointer-events-none absolute inset-0 opacity-20"
                 style="background-image:linear-gradient(rgba(255,255,255,.35) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.35) 1px,transparent 1px);background-size:24px 24px;"></div>

            <!-- Degradado inferior para fundir con el card -->
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white to-transparent dark:from-slate-900"></div>
        </div>
        <!-- ===== AVATAR (sube sobre el banner, sin position absolute) ===== -->
        <div class="relative z-10 -mt-14 flex justify-center">
            <div class="relative">
                <img src="{{ asset('images/avatars/' . ($profile->avatar_name ?? 'avatar1.jpg')) }}"
                     alt="Avatar actual"
                     class="h-28 w-28 rounded-2xl border-4 border-white object-cover shadow-2xl ring-4 ring-indigo-500/40 transition-transform duration-300 hover:scale-105 dark:border-slate-900">

                <!-- Punto de estado -->
                <span class="absolute -bottom-1 -right-1 block h-6 w-6 rounded-full border-4 border-white dark:border-slate-900 {{ $user->is_active ? 'bg-green-500' : 'bg-gray-400' }}"
                      title="{{ $user->is_active ? 'En línea' : 'Desconectado' }}"></span>
            </div>
        </div>

        <!-- ===== INFORMACIÓN ===== -->
        <div class="px-6 pb-7 pt-4 text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ $profile->nickname ?? $user->name }}
            </h2>

            <!-- Chip de paralelo -->
            <span class="mt-2 inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50/80 px-3 py-1 text-xs font-bold text-indigo-600 dark:border-indigo-900/50 dark:bg-indigo-900/20 dark:text-indigo-400">
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                Paralelo: {{ $profile->classroom ?? 'Sin asignar' }}
            </span>

            <!-- Datos personales -->
            <div class="mt-4 space-y-0.5 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 dark:border-slate-800 dark:bg-slate-800/40">
                <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $user->name }}</p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
            </div>

            <!-- Separador -->
            <div class="my-5 flex items-center gap-3">
                <span class="h-px flex-1 bg-gradient-to-r from-transparent to-slate-300 dark:to-slate-700"></span>
                <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Estadísticas</span>
                <span class="h-px flex-1 bg-gradient-to-l from-transparent to-slate-300 dark:to-slate-700"></span>
            </div>

            <!-- Estadísticas RPG -->
            <div class="grid grid-cols-3 gap-3">

                <!-- Nivel -->
                <div class="flex min-h-[84px] flex-col items-center justify-center rounded-2xl border border-amber-200 bg-amber-50/80 p-3 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-500/10 dark:border-amber-900/50 dark:bg-amber-900/20">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-600 dark:text-amber-400">Nivel</span>
                    <span class="mt-1 text-2xl font-black text-amber-900 dark:text-amber-300">{{ $profile->level ?? 1 }}</span>
                </div>

                <!-- Rango -->
                <div class="flex min-h-[84px] flex-col items-center justify-center rounded-2xl border border-indigo-200 bg-indigo-50/80 p-3 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-indigo-500/10 dark:border-indigo-900/50 dark:bg-indigo-900/20">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Rango</span>
                    <span class="mt-1 text-xs font-bold leading-tight text-indigo-950 dark:text-indigo-200">{{ $profile->rank_title ?? '🌱 Novato' }}</span>
                </div>

                <!-- XP -->
                <div class="flex min-h-[84px] flex-col items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50/80 p-3 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-500/10 dark:border-emerald-900/50 dark:bg-emerald-900/20">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">XP</span>
                    <span class="mt-1 text-2xl font-black text-emerald-900 dark:text-emerald-300">{{ $xp }}</span>
                </div>
            </div>

            <!-- Barra de progreso de XP -->
            <div class="mt-4">
                <div class="mb-1 flex justify-between text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    <span>Progreso</span>
                    <span>{{ $xpBar }}/100</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 shadow-[0_0_10px_rgba(236,72,153,0.5)]"
                         style="width: {{ $xpBar }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>