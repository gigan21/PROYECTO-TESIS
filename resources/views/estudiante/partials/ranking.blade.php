<div
    x-data="{
        tab: 'local',

        switchTab(newTab) {
            this.tab = newTab;
        }
    }"
    class="rounded-2xl border border-white/15 bg-white/5 p-5 backdrop-blur-md"
>

    {{-- ============================================================
         ENCABEZADO
    ============================================================ --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        {{-- Título --}}
        <div class="flex items-center gap-3">

            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-500/20">
                <img
                    src="{{ asset('images/trofeo.png') }}"
                    alt="Ranking"
                    class="h-6 w-6 object-contain"
                >
            </div>

            <div>
                <h4 class="font-epic text-lg font-bold text-white">
                    Ranking
                </h4>

                <p class="text-xs text-slate-400">
                    Compite con tu gremio
                </p>
            </div>

        </div>

        {{-- ========================================================
             BOTONES LOCAL / GLOBAL
        ========================================================= --}}
        <div class="flex rounded-xl bg-black/30 p-1">

            {{-- Local --}}
            <button
                type="button"
                @click="switchTab('local')"
                :class="tab === 'local'
                    ? 'bg-indigo-600 text-white shadow'
                    : 'text-slate-400 hover:text-white'"
                class="rounded-lg px-4 py-1.5 text-sm font-semibold transition-all duration-200"
            >
                Local
            </button>

            {{-- Global --}}
            <button
                type="button"
                @click="switchTab('global')"
                :class="tab === 'global'
                    ? 'bg-indigo-600 text-white shadow'
                    : 'text-slate-400 hover:text-white'"
                class="rounded-lg px-4 py-1.5 text-sm font-semibold transition-all duration-200"
            >
                Global
            </button>

        </div>

    </div>


    {{-- ============================================================
         CONTENIDO
    ============================================================ --}}
    <div class="relative max-h-[420px] overflow-hidden">


        {{-- ========================================================
             RANKING LOCAL
             Solo estudiantes del mismo paralelo
        ========================================================= --}}
        <div
            x-show="tab === 'local'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
        >

            @php
                $top3Local = $localRanking->take(3);
                $restLocal = $localRanking->slice(3);
            @endphp


            {{-- Sin estudiantes --}}
            @if($localRanking->isEmpty())

                <div class="flex min-h-[250px] items-center justify-center text-center">

                    <div>
                        <div class="mb-3 text-4xl">
                            🏆
                        </div>

                        <p class="text-sm text-slate-400">
                            Todavía no hay estudiantes en este ranking.
                        </p>
                    </div>

                </div>

            @else

                {{-- =================================================
                     PODIO LOCAL
                ================================================== --}}
                <div class="mb-6 grid grid-cols-3 items-end gap-2">

                    {{-- =========================
                         SEGUNDO
                    ========================== --}}
                    <div class="flex flex-col items-center pt-5">

                        @if(isset($top3Local[1]))

                            <div class="relative">

                                <img
                                    src="{{ asset('images/avatars/' . $top3Local[1]['avatar']) }}"
                                    class="h-12 w-12 rounded-full border-2 border-slate-400 object-cover"
                                    alt="{{ $top3Local[1]['nickname'] }}"
                                >

                                <span
                                    class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-slate-500 text-xs font-bold text-white"
                                >
                                    2
                                </span>

                            </div>

                            <p class="mt-1.5 max-w-[80px] truncate text-center text-xs font-semibold text-white">
                                {{ $top3Local[1]['nickname'] }}
                            </p>

                            <p class="text-center text-[10px] text-slate-400">
                                Nv.{{ $top3Local[1]['level'] }}
                                ·
                                {{ $top3Local[1]['xp_points'] }} XP
                            </p>

                        @endif

                    </div>


                    {{-- =========================
                         PRIMERO
                    ========================== --}}
                    <div class="flex flex-col items-center">

                        @if(isset($top3Local[0]))

                            <div class="relative">

                                <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-xl">
                                    🥇
                                </div>

                                <img
                                    src="{{ asset('images/avatars/' . $top3Local[0]['avatar']) }}"
                                    class="h-16 w-16 rounded-full border-2 border-amber-400 object-cover shadow-lg shadow-amber-500/20"
                                    alt="{{ $top3Local[0]['nickname'] }}"
                                >

                                <span
                                    class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-black"
                                >
                                    1
                                </span>

                            </div>

                            <p class="mt-1.5 max-w-[90px] truncate text-center text-xs font-bold text-amber-300">
                                {{ $top3Local[0]['nickname'] }}
                            </p>

                            <p class="text-center text-[10px] text-slate-400">
                                Nv.{{ $top3Local[0]['level'] }}
                                ·
                                {{ $top3Local[0]['xp_points'] }} XP
                            </p>

                        @endif

                    </div>


                    {{-- =========================
                         TERCERO
                    ========================== --}}
                    <div class="flex flex-col items-center pt-7">

                        @if(isset($top3Local[2]))

                            <div class="relative">

                                <img
                                    src="{{ asset('images/avatars/' . $top3Local[2]['avatar']) }}"
                                    class="h-11 w-11 rounded-full border-2 border-amber-700 object-cover"
                                    alt="{{ $top3Local[2]['nickname'] }}"
                                >

                                <span
                                    class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-amber-700 text-xs font-bold text-white"
                                >
                                    3
                                </span>

                            </div>

                            <p class="mt-1.5 max-w-[80px] truncate text-center text-xs font-semibold text-white">
                                {{ $top3Local[2]['nickname'] }}
                            </p>

                            <p class="text-center text-[10px] text-slate-400">
                                Nv.{{ $top3Local[2]['level'] }}
                                ·
                                {{ $top3Local[2]['xp_points'] }} XP
                            </p>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     RESTO DEL RANKING LOCAL
                ================================================== --}}
                @if($restLocal->isNotEmpty())

                <div class="max-h-48 space-y-2 overflow-y-auto pr-1 scrollbar-thin">

                        @foreach($restLocal as $student)

                            <div
                                class="flex items-center justify-between rounded-xl bg-white/10 px-4 py-3"
                            >

                                <div class="flex min-w-0 items-center gap-3">

                                    {{-- Posición --}}
                                    <span class="w-5 text-center text-sm font-bold text-slate-400">
                                        {{ $student['position'] }}
                                    </span>

                                    {{-- Avatar --}}
                                    <img
                                        src="{{ asset('images/avatars/' . $student['avatar']) }}"
                                        class="h-9 w-9 rounded-full border border-white/20 object-cover"
                                        alt="{{ $student['nickname'] }}"
                                    >

                                    {{-- Datos --}}
                                    <div class="min-w-0">

                                        <p class="truncate text-sm font-semibold text-white">
                                            {{ $student['nickname'] }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ $student['classroom'] }}
                                            ·
                                            Nv.{{ $student['level'] }}
                                        </p>

                                    </div>

                                </div>

                                {{-- XP --}}
                                <div class="ml-3 shrink-0 text-right">

                                    <p class="text-sm font-bold text-indigo-300">
                                        {{ $student['xp_points'] }} XP
                                    </p>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            @endif

        </div>


        {{-- ========================================================
             RANKING GLOBAL
             Todos los paralelos
        ========================================================= --}}
        <div
            x-show="tab === 'global'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
        >

            @php
                $top3Global = $globalRanking->take(3);
                $restGlobal = $globalRanking->slice(3);
            @endphp


            {{-- Sin estudiantes --}}
            @if($globalRanking->isEmpty())

                <div class="flex min-h-[250px] items-center justify-center text-center">

                    <div>
                        <div class="mb-3 text-4xl">
                            🌎
                        </div>

                        <p class="text-sm text-slate-400">
                            Todavía no hay estudiantes en el ranking global.
                        </p>
                    </div>

                </div>

            @else

                {{-- =================================================
                     PODIO GLOBAL
                ================================================== --}}
                <div class="mb-6 grid grid-cols-3 items-end gap-2">

                    {{-- SEGUNDO --}}
                    <div class="flex flex-col items-center pt-5">

                        @if(isset($top3Global[1]))

                            <div class="relative">

                                <img
                                    src="{{ asset('images/avatars/' . $top3Global[1]['avatar']) }}"
                                    class="h-12 w-12 rounded-full border-2 border-slate-400 object-cover"
                                    alt="{{ $top3Global[1]['nickname'] }}"
                                >

                                <span
                                    class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-slate-500 text-xs font-bold text-white"
                                >
                                    2
                                </span>

                            </div>

                            <p class="mt-1.5 max-w-[80px] truncate text-center text-xs font-semibold text-white">
                                {{ $top3Global[1]['nickname'] }}
                            </p>

                            <p class="text-center text-[10px] text-slate-400">
                                {{ $top3Global[1]['classroom'] }}
                                ·
                                {{ $top3Global[1]['xp_points'] }} XP
                            </p>

                        @endif

                    </div>


                    {{-- PRIMERO --}}
                    <div class="flex flex-col items-center">

                        @if(isset($top3Global[0]))

                            <div class="relative">

                                <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-xl">
                                    🥇
                                </div>

                                <img
                                    src="{{ asset('images/avatars/' . $top3Global[0]['avatar']) }}"
                                    class="h-16 w-16 rounded-full border-2 border-amber-400 object-cover shadow-lg shadow-amber-500/20"
                                    alt="{{ $top3Global[0]['nickname'] }}"
                                >

                                <span
                                    class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-black"
                                >
                                    1
                                </span>

                            </div>

                            <p class="mt-1.5 max-w-[90px] truncate text-center text-xs font-bold text-amber-300">
                                {{ $top3Global[0]['nickname'] }}
                            </p>

                            <p class="text-center text-[10px] text-slate-400">
                                {{ $top3Global[0]['classroom'] }}
                                ·
                                {{ $top3Global[0]['xp_points'] }} XP
                            </p>

                        @endif

                    </div>


                    {{-- TERCERO --}}
                    <div class="flex flex-col items-center pt-7">

                        @if(isset($top3Global[2]))

                            <div class="relative">

                                <img
                                    src="{{ asset('images/avatars/' . $top3Global[2]['avatar']) }}"
                                    class="h-11 w-11 rounded-full border-2 border-amber-700 object-cover"
                                    alt="{{ $top3Global[2]['nickname'] }}"
                                >

                                <span
                                    class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-amber-700 text-xs font-bold text-white"
                                >
                                    3
                                </span>

                            </div>

                            <p class="mt-1.5 max-w-[80px] truncate text-center text-xs font-semibold text-white">
                                {{ $top3Global[2]['nickname'] }}
                            </p>

                            <p class="text-center text-[10px] text-slate-400">
                                {{ $top3Global[2]['classroom'] }}
                                ·
                                {{ $top3Global[2]['xp_points'] }} XP
                            </p>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     RESTO DEL RANKING GLOBAL
                ================================================== --}}
                @if($restGlobal->isNotEmpty())

                <div class="max-h-48 space-y-2 overflow-y-auto pr-1 scrollbar-thin">
                        @foreach($restGlobal as $student)

                            <div
                                class="flex items-center justify-between rounded-xl bg-white/10 px-4 py-3"
                            >

                                <div class="flex min-w-0 items-center gap-3">

                                    {{-- Posición --}}
                                    <span class="w-5 text-center text-sm font-bold text-slate-400">
                                        {{ $student['position'] }}
                                    </span>

                                    {{-- Avatar --}}
                                    <img
                                        src="{{ asset('images/avatars/' . $student['avatar']) }}"
                                        class="h-9 w-9 rounded-full border border-white/20 object-cover"
                                        alt="{{ $student['nickname'] }}"
                                    >

                                    {{-- Datos --}}
                                    <div class="min-w-0">

                                        <p class="truncate text-sm font-semibold text-white">
                                            {{ $student['nickname'] }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ $student['classroom'] }}
                                            ·
                                            Nv.{{ $student['level'] }}
                                        </p>

                                    </div>

                                </div>

                                {{-- XP --}}
                                <div class="ml-3 shrink-0 text-right">

                                    <p class="text-sm font-bold text-indigo-300">
                                        {{ $student['xp_points'] }} XP
                                    </p>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            @endif

        </div>

    </div>


    {{-- ============================================================
         POSICIONES DEL ESTUDIANTE
    ============================================================ --}}
    <div class="mt-6 rounded-xl bg-black/20 p-4">

        <div class="grid grid-cols-2 gap-4">

            {{-- POSICIÓN LOCAL --}}
            <div>

                <p class="text-xs text-slate-400">
                    Tu posición Local
                </p>

                <p class="mt-1 text-lg font-bold text-white">
                    #{{ $myLocal['position'] ?? '-' }}

                    @if(isset($myLocal['total']))
                        <span class="text-xs font-normal text-slate-500">
                            de {{ $myLocal['total'] }}
                        </span>
                    @endif
                </p>

            </div>


            {{-- POSICIÓN GLOBAL --}}
            <div>

                <p class="text-xs text-slate-400">
                    Tu posición Global
                </p>

                <p class="mt-1 text-lg font-bold text-white">
                    #{{ $myGlobal['position'] ?? '-' }}

                    @if(isset($myGlobal['total']))
                        <span class="text-xs font-normal text-slate-500">
                            de {{ $myGlobal['total'] }}
                        </span>
                    @endif
                </p>

            </div>

        </div>


        {{-- ========================================================
             MENSAJE DE POSICIÓN
        ========================================================= --}}
        <div class="mt-4 rounded-xl bg-amber-500/10 px-4 py-3 text-center">

            @if(($myLocal['position'] ?? 0) === 1)

                <p class="text-sm font-semibold text-amber-300">
                    👑 ¡Eres el #1 de tu gremio!
                </p>

            @elseif($xpToNext !== null)

                <p class="text-sm text-slate-300">
                    ⚔️ Te faltan
                    <span class="font-bold text-amber-300">
                        {{ $xpToNext }} XP
                    </span>
                    para alcanzar al siguiente estudiante.
                </p>

            @else

                <p class="text-sm text-slate-400">
                    Sigue ganando XP para subir posiciones.
                </p>

            @endif

        </div>

    </div>

</div>