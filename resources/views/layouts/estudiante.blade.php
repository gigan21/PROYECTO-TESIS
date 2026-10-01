{{-- resources/views/layouts/estudiante.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Área del Estudiante') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-epic { font-family: 'Cinzel', Georgia, serif; }
        .font-hud  { font-family: 'Rajdhani', system-ui, sans-serif; }
    </style>

    @stack('styles')
</head>
<body class="font-hud min-h-screen bg-slate-900 text-slate-100 antialiased">

    {{-- FONDO --}}
    <div class="fixed inset-0 -z-10 bg-slate-900">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(99,102,241,0.25),transparent_55%),radial-gradient(ellipse_at_bottom_right,rgba(245,158,11,0.12),transparent_50%)]"></div>
    </div>

    <!-- ============ HEADER: HUD DEL JUGADOR ============ -->
    <header class="sticky top-0 z-20 border-b border-white/10 bg-slate-900/70 backdrop-blur-md">
        <div class="mx-auto grid max-w-7xl grid-cols-2 items-center gap-4 px-4 py-3 md:grid-cols-[auto_1fr_auto] md:px-6">

            <a href="{{ route('estudiante.inicio') }}" class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-400/40 bg-amber-400/10 text-2xl shadow-[0_0_15px_rgba(251,191,36,0.25)]">⚔️</span>
                <span class="font-epic text-xl font-extrabold leading-tight text-amber-300">
                    Cinemática
                    <span class="block text-xs font-semibold text-slate-400">Área del Estudiante</span>
                </span>
            </a>

            <div class="order-3 col-span-2 md:order-none md:col-span-1">
                <div class="mx-auto w-full max-w-xl">
                    @include('estudiante.gamification.widget-progreso')
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <div class="relative hidden sm:block">
                    <img src="{{ asset('images/avatars/' . (auth()->user()->studentProfile->avatar_name ?? 'default_avatar.png')) }}"
                         alt="Avatar de {{ auth()->user()->name }}"
                         class="h-11 w-11 rounded-full border-2 border-amber-400 object-cover shadow-[0_0_12px_rgba(251,191,36,0.4)]">
                    <span class="absolute bottom-0 right-0 block h-3.5 w-3.5 rounded-full border-2 border-slate-900 {{ auth()->user()->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                </div>

                
                <a href="{{ route('student.profile') }}"
                   class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm font-semibold text-slate-100 transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                    👤 <span class="hidden lg:inline">Mi Perfil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-white/20 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:border-rose-400/60 hover:text-rose-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                        Salir
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- ============ CONTENIDO VARIABLE ============ -->
    <main class="mx-auto max-w-7xl px-4 py-8 md:px-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>