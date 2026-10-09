{{-- resources/views/estudiante/inicio.blade.php --}}

@extends('layouts.estudiante')

@section('title', 'Inicio Estudiante')

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

    <!-- ---------- COLUMNA IZQUIERDA: ESTADO DEL HÉROE ---------- -->
        <!-- ---------- COLUMNA IZQUIERDA: ESTADO DEL HÉROE ---------- -->
        <aside class="lg:col-span-1">
        @php
            $banner = auth()->user()->studentProfile?->banner;
            $bannerPath = $banner?->media_path;
            $bannerExists = $bannerPath && file_exists(public_path($bannerPath));
        @endphp

        <div class="relative flex h-full flex-col items-center overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-6 text-center shadow-xl backdrop-blur-md">

            {{-- 🎨 Banner de fondo --}}
            @if($bannerExists)
                <div class="pointer-events-none absolute inset-x-0 top-0 h-72">
                    @if($banner?->media_type === 'video')
                        <video autoplay muted loop playsinline class="h-full w-full object-cover">
                            <source src="{{ asset($bannerPath) }}">
                        </video>
                    @else
                        <img src="{{ asset($bannerPath) }}"
                             alt="{{ $banner->name ?? 'Banner' }}"
                             class="h-full w-full object-cover">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-slate-950/40 to-slate-950/95"></div>
                </div>
            @endif

            {{-- Contenido encima del banner --}}
            <div class="relative z-10 flex w-full flex-col items-center">

                <div class="relative">
                    <img src="{{ asset('images/avatars/' . (auth()->user()->studentProfile->avatar_name ?? 'default_avatar.png')) }}"
                         alt="Avatar de {{ auth()->user()->name }}"
                         class="h-36 w-36 rounded-full border-4 border-amber-400 object-cover shadow-[0_0_25px_rgba(251,191,36,0.35)]">
                    <span class="absolute bottom-2 right-2 block h-5 w-5 rounded-full border-4 border-slate-900 {{ auth()->user()->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                </div>

                <h2 class="font-epic mt-4 text-2xl font-extrabold text-white">{{ auth()->user()->name }}</h2>

                @if(auth()->user()->is_active)
                    <p class="mt-1 inline-flex items-center gap-2 text-sm font-semibold text-green-400">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span> En línea
                    </p>
                @else
                    <p class="mt-1 inline-flex items-center gap-2 text-sm font-semibold text-slate-400">
                        <span class="h-2 w-2 rounded-full bg-gray-400"></span> Desconectado
                    </p>
                @endif

                <div class="mt-5 w-full rounded-xl border border-indigo-400/30 bg-indigo-500/10 p-4">
                    <p class="text-xs font-semibold text-indigo-300">🏫 Gremio</p>
                    <p class="font-epic mt-1 text-lg font-bold text-indigo-100">
                        {{ auth()->user()->studentProfile->classroom ?? 'Sin asignar' }}
                    </p>
                    <a href="{{ route('student.profile') }}" class="mt-1 inline-block text-xs font-bold text-amber-300 hover:underline">
                        Cambiar
                    </a>
                </div>

                <x-student-progress :progress="$progress" />

                <x-student-badges :badges="$badges" />
                <a href="{{ route('estudiante.tienda') }}"
   class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-amber-400/60 bg-gradient-to-b from-amber-500 to-amber-700 px-4 py-3 text-sm font-bold text-slate-900 shadow-lg transition hover:brightness-110 hover:shadow-[0_0_20px_rgba(251,191,36,0.4)] focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300">
    <span class="text-lg">🛒</span>
    TIENDA
</a>
            </div>{{-- cierre del contenedor relative z-10 --}}
        </div>{{-- cierre de la tarjeta --}}
    </aside>

    <!-- ---------- COLUMNA CENTRAL: TABLÓN DE MISIONES ---------- -->
    <section class="relative space-y-6 pb-24 lg:col-span-2">
     
    @include('estudiante.partials.lore-player', ['videos' => $loreVideos])

        <div class="flex flex-col gap-4 rounded-2xl border-2 border-amber-400/40 bg-white/10 p-6 shadow-xl backdrop-blur-md sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-400/20 text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-epic text-xl font-extrabold text-amber-300">Misiones Regulares</h3>
                    <p class="text-sm text-slate-300">Responde preguntas de cinemática y gana XP.</p>
                </div>
            </div>
            <a href="{{ route('estudiante.preguntas.index') }}"
               class="shrink-0 rounded-xl border border-amber-300 bg-linear-to-b from-amber-400 to-amber-600 px-6 py-3 text-center text-sm font-bold text-slate-900 shadow-md transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300">
                Aceptar misión
            </a>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border-2 border-rose-500/40 bg-white/10 p-6 shadow-xl backdrop-blur-md sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="shrink-0">
                    <img src="{{ asset('images/dragon.png') }}"
                         alt="Jefe Dragón"
                         class="h-16 w-16 object-contain drop-shadow-[0_0_10px_rgba(244,63,94,0.6)] transition-transform hover:scale-110">
                </div>
                <div>
                    <h3 class="font-epic text-xl font-extrabold text-rose-400">Batallas de Jefe</h3>
                    <p class="text-sm text-slate-300">Ingresa a un desafío y enfrenta a tus compañeros.</p>
                </div>
            </div>
            <a href="{{ route('estudiante.desafio.ingresar') }}"
               class="shrink-0 rounded-xl border border-rose-400 bg-linear-to-b from-rose-500 to-rose-700 px-6 py-3 text-center text-sm font-bold text-white shadow-md transition hover:shadow-[0_0_15px_rgba(244,63,94,0.5)] hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-300">
                🎯 Entrar al desafío
            </a>
        </div>
       
    </section>

    <!-- ---------- COLUMNA DERECHA: CAMPO DE ENTRENAMIENTO ---------- -->
    <aside class="space-y-6 lg:col-span-1">
        <h3 class="font-epic text-lg font-extrabold text-slate-200">Campo de entrenamiento</h3>

        {{-- Crucigrama --}}
        <div class="flex flex-col items-center rounded-2xl border border-white/20 bg-white/10 p-5 text-center shadow-xl backdrop-blur-md">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-violet-400/20 text-violet-400">
                <img src="{{ asset('images/crucigrama.jpg') }}" alt="Crucigrama" class="h-12 w-12 object-contain">
            </div>
            <h4 class="font-epic mt-2 text-lg font-bold text-violet-300">Crucigrama</h4>
            <p class="mt-1 text-sm text-slate-400">Resuelve el crucigrama y gana XP.</p>
            <a href="{{ route('estudiante.crucigrama') }}"
               class="mt-4 w-full rounded-lg bg-violet-600 px-4 py-2 text-sm font-bold text-white transition hover:scale-105 hover:bg-violet-500 hover:shadow-[0_0_15px_rgba(139,92,246,0.5)] focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-300">
                Jugar crucigrama
            </a>
        </div>
        {{-- Lanza la Cura --}}
        <div class="flex flex-col items-center rounded-2xl border border-white/20 bg-white/10 p-5 text-center shadow-xl backdrop-blur-md">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-purple-400/20 text-purple-400">
                <img src="{{ asset('images/lanza-la-cura.png') }}" alt="Lanza la Cura" class="h-29 w-29 object-contain">
            </div>
            <h4 class="font-epic mt-2 text-lg font-bold text-purple-300">Lanza la Cura</h4>
            <p class="mt-1 text-sm text-slate-400">Lanza la cura y gana XP.</p>
            <a href="{{ route('estudiante.juego.lanza-la-cura') }}"
               class="mt-4 w-full rounded-lg bg-purple-600 px-4 py-2 text-sm font-bold text-white transition hover:scale-105 hover:bg-purple-500 hover:shadow-[0_0_15px_rgba(139,92,246,0.5)] focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-300">
                Jugar lanza la cura
            </a>
        </div>
        {{-- Simulador --}}
        <div class="flex flex-col items-center rounded-2xl border border-white/20 bg-white/10 p-5 text-center shadow-xl backdrop-blur-md">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-400/20 text-emerald-400">
                <img src="{{ asset('images/cohete.png') }}" alt="Simulador de Proyectiles" class="h-12 w-12 object-contain">
            </div>
            <h4 class="font-epic mt-2 text-lg font-bold text-emerald-300">Simulador de Proyectiles</h4>
            <p class="mt-1 text-sm text-slate-400">Lanza, ajusta el ángulo y apunta al blanco.</p>
            <a href="{{ route('estudiante.juego_proyectiles') }}"
               class="mt-4 w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:scale-105 hover:bg-emerald-500 hover:shadow-[0_0_15px_rgba(16,185,129,0.5)] focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300">
                Jugar simulador
            </a>
            <a href="{{ route('estudiante.simulaciones') }}"
               class="mt-2 w-full rounded-lg border border-amber-400/40 bg-amber-400/10 px-4 py-2 text-xs font-semibold text-amber-300 transition hover:bg-amber-400/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300">
                Ver simulación de movimiento
            </a>
        </div>

        {{-- Ranking --}}
<div class="flex flex-col items-center rounded-2xl border border-dashed border-white/20 bg-white/5 p-5 text-center backdrop-blur-md">
    @include('estudiante.partials.ranking')
</div>
    </aside>

</div>

@include('estudiante.partials.pets')
@endsection