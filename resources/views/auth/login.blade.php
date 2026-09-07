@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
    <h1 class="mb-2 text-xl font-semibold text-slate-900">Iniciar sesión</h1>
    <p class="mb-6 text-sm text-slate-500">Accede a tu cuenta de estudiante o docente.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
        </div>

        <div class="flex items-center">
            <input
                id="remember"
                type="checkbox"
                name="remember"
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            >
            <label for="remember" class="ml-2 text-sm text-slate-600">Recordarme</label>
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Entrar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        ¿Eres estudiante y no tienes cuenta?
        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Regístrate aquí</a>
    </p>
    <div class="flex items-center justify-between mb-4">
    <a href="{{ route('password.pin.request') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">
        ¿Olvidaste tu contraseña?
    </a>
</div>
@endsection
