@extends('layouts.guest')

@section('title', 'Verificar PIN')

@section('content')
    <h1 class="mb-2 text-xl font-semibold text-slate-900">Ingresa tu PIN de Verificación</h1>
    <p class="mb-6 text-sm text-slate-500">Revisa tu correo e ingresa el código PIN de 6 dígitos junto a tu nueva contraseña.</p>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-indigo-50 p-4 text-sm text-indigo-700 border border-indigo-200">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.pin.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="email" value="{{ request('email', $email) }}">

        <div>
            <label for="pin" class="mb-1 block text-sm font-medium text-slate-700">PIN de 6 dígitos</label>
            <input id="pin" type="text" name="pin" maxlength="6" required autofocus placeholder="123456"
                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-center text-lg tracking-widest font-bold text-indigo-600 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @error('pin')
                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Nueva Contraseña</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @error('password')
                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmar Nueva Contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
            Cambiar Contraseña
        </button>
    </form>
@endsection