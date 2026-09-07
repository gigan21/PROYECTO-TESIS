@extends('layouts.guest')

@section('title', 'Recuperar Contraseña')

@section('content')
    <h1 class="mb-2 text-xl font-semibold text-slate-900">¿Olvidaste tu contraseña?</h1>
    <p class="mb-6 text-sm text-slate-500">Ingresa tu correo registrado y te enviaremos un PIN de 6 dígitos para restablecerla.</p>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 border border-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.pin.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @error('email')
                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
            Enviar PIN al Correo
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">← Volver al Inicio de Sesión</a>
    </p>
@endsection