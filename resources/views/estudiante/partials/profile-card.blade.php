<!-- Columna Izquierda: Tarjeta de Perfil RPG -->
<div class="lg:col-span-4">
    <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
        
        <!-- Banner de fondo (Opcional, le da el toque RPG) -->
        <div class="h-32 w-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500"></div>
        
        <!-- Avatar Grande -->
        <div class="absolute top-16 left-1/2 -translate-x-1/2">
            <img src="{{ asset('images/avatars/' . (auth()->user()->studentProfile->avatar_name ?? 'avatar1.jpg')) }}" 
                 alt="Avatar actual" 
                 class="h-32 w-32 rounded-2xl border-4 border-white object-cover shadow-2xl dark:border-slate-900 ring-4 ring-indigo-500/30 transition-transform hover:scale-105">
                 {{-- Punto de estado grande --}}
    <span class="absolute bottom-2 right-2 block h-6 w-6 rounded-full border-4 border-white {{ auth()->user()->is_active ? 'bg-green-500' : 'bg-gray-400' }}"
          title="{{ auth()->user()->is_active ? 'En línea' : 'Desconectado' }}">
    </span>
        </div>

        <!-- Información del Estudiante -->
        <div class="mt-20 px-6 pb-8 text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ auth()->user()->studentProfile->nickname ?? auth()->user()->name }}
            </h2>
            <p class="mt-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                Paralelo: {{ auth()->user()->studentProfile->classroom ?? 'Sin asignar' }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ auth()->user()->name }} • {{ auth()->user()->email }}
            </p>

            <!-- Estadísticas RPG (Nivel, Rango, XP) -->
            <div class="mt-8 grid grid-cols-3 gap-3">
                
                <!-- Nivel -->
                <div class="flex flex-col items-center justify-center rounded-2xl border border-amber-200 bg-amber-50/80 p-3 dark:border-amber-900/50 dark:bg-amber-900/20">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-600 dark:text-amber-400">Nivel</span>
                    <span class="mt-1 text-2xl font-black text-amber-900 dark:text-amber-300">
                        {{ auth()->user()->studentProfile->level ?? 1 }}
                    </span>
                </div>

                <!-- Rango -->
                <div class="flex flex-col items-center justify-center rounded-2xl border border-indigo-200 bg-indigo-50/80 p-3 dark:border-indigo-900/50 dark:bg-indigo-900/20">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Rango</span>
                    <span class="mt-1 text-xs font-bold text-indigo-950 dark:text-indigo-200">
                        {{ auth()->user()->studentProfile->rank_title ?? '🌱 Novato' }}
                    </span>
                </div>

                <!-- XP -->
                <div class="flex flex-col items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50/80 p-3 dark:border-emerald-900/50 dark:bg-emerald-900/20">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">XP</span>
                    <span class="mt-1 text-2xl font-black text-emerald-900 dark:text-emerald-300">
                        {{ auth()->user()->studentProfile->xp_points ?? 0 }}
                    </span>
                </div>

            </div>
        </div>
    </div>
</div>