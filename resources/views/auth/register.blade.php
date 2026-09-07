@extends('layouts.guest')

@section('title', 'Registro de estudiante')

@section('content')
    <h1 class="mb-2 text-xl font-semibold text-slate-900">Crear cuenta de estudiante</h1>
    <p class="mb-6 text-sm text-slate-500">Regístrate para comenzar tu aprendizaje en cinemática.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre completo</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
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
                autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmar contraseña</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
        </div>
<!-- Selección de Avatar con Imágenes -->
<div>
    <label class="mb-2 block text-sm font-medium text-slate-700">Elige tu Avatar de Cinemática</label>
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
        @php
            $avatars = [
                'avatar1.jpg',
                'avatar2.jpg',
                'avatar3.jpg',
                'avatar4.jpg',
                'avatar5.jpg',
                'avatar6.jpg',
                'avatar7.jpg',
                'avatar8.jpg',
                'avatar9.jpg',
                'avatar10.jpg',
            ];
        @endphp

        @foreach ($avatars as $avatar)
            <label class="cursor-pointer">
                <input type="radio" name="avatar_name" value="{{ $avatar }}" class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                <div class="flex items-center justify-center rounded-2xl border-2 border-slate-200 p-2 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:bg-slate-50">
                    <img src="{{ asset('images/avatars/' . $avatar) }}" alt="Avatar" class="h-14 w-14 object-cover rounded-xl">
                </div>
            </label>
        @endforeach
    </div>
</div>

<div>
    <label for="classroom" class="mb-1 block text-sm font-medium text-slate-700">Paralelo / Curso</label>
    <select id="classroom" name="classroom" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm">
        <option value="" disabled selected>Selecciona tu paralelo</option>
        <option value="4A">4.º A de Secundaria</option>
        <option value="4B">4.º B de Secundaria</option>
        <option value="4C">4.º C de Secundaria</option>
    </select>
</div>

        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Registrarme
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Inicia sesión</a>
    </p>
@endsection
