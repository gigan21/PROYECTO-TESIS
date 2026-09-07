<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Perfil — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased font-sans">
    <!-- Navegación -->
    <header class="border-b border-slate-200 bg-white shadow-xs">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('estudiante.inicio') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    ← Volver al Inicio
                </a>
                <h1 class="text-lg font-bold text-slate-900">Perfil del Estudiante</h1>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-6 py-10 space-y-8">
        
        <!-- Alerta de éxito -->
        @if (session('status') === 'profile-updated')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 shadow-xs">
                ¡Tu información, apodo y avatar se actualizaron correctamente!
            </div>
        @endif

        <!-- Card 1: Tarjeta Gamificada del Estudiante -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <!-- Imagen del Avatar Actual -->
                <img src="{{ asset('images/avatars/' . (auth()->user()->studentProfile->avatar_name ?? 'default_avatar.png')) }}" 
                     alt="Avatar" 
                     class="h-16 w-16 rounded-2xl border-2 border-indigo-500 object-cover shadow-sm">
                
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ auth()->user()->studentProfile->nickname ?? auth()->user()->name }}
                    </h2>
                    <p class="text-sm text-slate-500">{{ auth()->user()->name }} — {{ auth()->user()->email }}</p>
                </div>
            </div>

            <!-- Stats Gamificadas -->
            <div class="flex gap-4">
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-center">
                    <p class="text-xs font-semibold uppercase text-amber-600">Nivel</p>
                    <p class="text-xl font-extrabold text-amber-900">
                        {{ floor((auth()->user()->studentProfile->xp_points ?? 0) / 100) + 1 }}
                    </p>
                </div>
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-center">
                    <p class="text-xs font-semibold uppercase text-emerald-600">Puntos XP</p>
                    <p class="text-xl font-extrabold text-emerald-900">
                        {{ auth()->user()->studentProfile->xp_points ?? 0 }} XP
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Formulario de Edición -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Editar Perfil Gamificado</h3>
            <p class="mb-6 text-sm text-slate-500">Actualiza tu apodo, cambia tu avatar o rectifica tu paralelo de 4.º de secundaria.</p>

            <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Nombre Completo -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-1">Nombre Completo</label>
                    <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required 
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>

                <!-- Nickname / Apodo -->
                <div>
                    <label for="nickname" class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-1">Apodo (Nickname)</label>
                    <input id="nickname" name="nickname" type="text" value="{{ old('nickname', auth()->user()->studentProfile->nickname ?? '') }}" required 
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>

              <!-- Selector de Avatar con Imágenes JPG -->
<div>
    <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Selecciona un nuevo Avatar</label>
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
            // Si el usuario en la BD aún tiene guardado algo con .png o null, forzamos avatar1.jpg por defecto
            $currentAvatar = auth()->user()->studentProfile->avatar_name ?? 'avatar1.jpg';
            if (str_contains($currentAvatar, '.png')) {
                $currentAvatar = str_replace('.png', '.jpg', $currentAvatar);
            }
        @endphp

        @foreach ($avatars as $avatar)
            <label class="cursor-pointer">
                <input type="radio" name="avatar_name" value="{{ $avatar }}" class="peer hidden" {{ $currentAvatar == $avatar ? 'checked' : '' }}>
                <div class="flex items-center justify-center rounded-2xl border-2 border-slate-200 p-2 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:bg-slate-50">
                    <img src="{{ asset('images/avatars/' . $avatar) }}" alt="Avatar" class="h-14 w-14 object-cover rounded-xl">
                </div>
            </label>
        @endforeach
    </div>
</div>


                <!-- Paralelo (4.º de Secundaria) -->
                <div>
                    <label for="classroom" class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-1">Paralelo (4.º de Secundaria)</label>
                    <select id="classroom" name="classroom" required 
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 bg-white">
                        <option value="" disabled>Selecciona tu paralelo...</option>
                        @foreach ($classrooms as $item)
                            <option value="{{ $item }}" 
                                {{ (auth()->user()->studentProfile->classroom ?? '') == $item ? 'selected' : '' }}>
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" 
                            class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-indigo-700 active:scale-[0.98]">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>