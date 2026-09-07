<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Autenticación') — {{ config('app.name', 'Cinemática Gamificada') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans text-slate-800">
    <!-- Contenedor principal con imagen de fondo local -->
    <div class="relative flex min-h-screen flex-col items-center justify-center bg-cover bg-center bg-no-repeat px-4 py-12"
         style="background-image: url('{{ asset('images/imagen_login1.png') }}');">
        
        <!-- Capa oscura (Overlay) -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        <!-- Encabezado del Sistema -->
        <div class="relative z-10 mb-6 text-center">
            <a href="{{ url('/') }}" class="text-3xl font-extrabold tracking-tight text-white drop-shadow-md transition hover:text-indigo-200">
                {{ config('app.name', 'Cinemática Gamificada') }}
            </a>
            <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-indigo-200 drop-shadow">
                U.E. República de Francia B
            </p>
        </div>

        <!-- Tarjeta Flotante Transparente (Glassmorphism) -->
        <!-- bg-white/80 le da 80% de opacidad y backdrop-blur-lg desenfoca el fondo -->
        <div class="relative z-10 w-full max-w-md rounded-2xl border border-white/40 bg-white/80 p-8 shadow-2xl backdrop-blur-lg">
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50/90 p-4 text-sm text-red-700 shadow-sm">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Pie de página -->
        <p class="relative z-10 mt-8 text-center text-xs font-medium text-slate-200 drop-shadow-sm">
            &copy; {{ date('Y') }} Sistema Gamificado de Cinemática
        </p>
    </div>
</body>
</html>