@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')

@section('content')

<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Gestión de Usuarios
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Administración de docentes y estudiantes
            </p>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
            ← Volver al dashboard
        </a>

    </div>


    {{-- MENSAJE DE ERROR --}}
    @if(session('error'))

        <div class="rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>

    @endif


    {{-- FILTROS --}}
    <div class="rounded-xl bg-white p-6 shadow-sm">

        <form method="GET"
              action="{{ route('admin.usuarios.index') }}">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                {{-- ROL --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Tipo de usuario
                    </label>

                    <select name="role"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">

                        <option value="">
                            Todos
                        </option>

                        <option value="docente"
                            {{ request('role') === 'docente' ? 'selected' : '' }}>
                            Docentes
                        </option>

                        <option value="estudiante"
                            {{ request('role') === 'estudiante' ? 'selected' : '' }}>
                            Estudiantes
                        </option>

                    </select>

                </div>


                {{-- PARALELO --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Paralelo
                    </label>

                    <select name="classroom"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">

                        <option value="">
                            Todos
                        </option>

                        <option value="4A"
                            {{ request('classroom') === '4A' ? 'selected' : '' }}>
                            4º A
                        </option>

                        <option value="4B"
                            {{ request('classroom') === '4B' ? 'selected' : '' }}>
                            4º B
                        </option>

                        <option value="4C"
                            {{ request('classroom') === '4C' ? 'selected' : '' }}>
                            4º C
                        </option>

                    </select>

                </div>


                {{-- BUSCAR --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Buscar
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nombre o correo..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">

                </div>

            </div>


            <div class="mt-4 flex gap-2">

                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    🔎 Buscar
                </button>

                <a
                    href="{{ route('admin.usuarios.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    Limpiar
                </a>

            </div>

        </form>

    </div>


    {{-- TABLA --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm">

        <div class="flex items-center justify-between border-b px-6 py-4">

            <div>
                <h2 class="font-semibold text-slate-800">
                    Usuarios registrados
                </h2>

                <p class="text-sm text-slate-500">
                    Total: {{ $usuarios->total() }}
                </p>
            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            ID
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Nombre
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Correo
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Rol
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Paralelo
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Estado
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-200 bg-white">

                    @forelse($usuarios as $usuario)

                        <tr class="hover:bg-slate-50">

                            {{-- ID --}}
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $usuario->id }}
                            </td>


                            {{-- NOMBRE --}}
                            <td class="whitespace-nowrap px-6 py-4">

                                <div class="font-medium text-slate-800">
                                    {{ $usuario->name }}
                                </div>

                            </td>


                            {{-- EMAIL --}}
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $usuario->email }}
                            </td>


                            {{-- ROL --}}
                            <td class="whitespace-nowrap px-6 py-4">

                                @if($usuario->role === 'docente')

                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Docente
                                    </span>

                                @elseif($usuario->role === 'estudiante')

                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Estudiante
                                    </span>

                                @elseif($usuario->role === 'admin')

                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        Administrador
                                    </span>

                                @else

                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ $usuario->role }}
                                    </span>

                                @endif

                            </td>


                            {{-- PARALELO --}}
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">

                                @if($usuario->role === 'estudiante')

                                    {{ $usuario->studentProfile?->classroom ?? 'Sin asignar' }}

                                @else

                                    —

                                @endif

                            </td>


                            {{-- ESTADO --}}
                            <td class="whitespace-nowrap px-6 py-4">

                                @if($usuario->is_active)

                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Activo
                                    </span>

                                @else

                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        Inactivo
                                    </span>

                                @endif

                            </td>


                            {{-- ACCIONES --}}
                            <td class="whitespace-nowrap px-6 py-4">

                                <div class="flex items-center gap-2">

                                    {{-- DOCENTE --}}
                                    @if($usuario->role === 'docente')

                                        @if($usuario->is_active)

                                            <form method="POST"
                                                  action="{{ route('admin.usuarios.desactivar', $usuario->id) }}">

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    onclick="return confirm('¿Deseas desactivar este docente?')"
                                                    class="rounded-lg bg-yellow-100 px-3 py-2 text-xs font-semibold text-yellow-700 hover:bg-yellow-200">

                                                    Desactivar

                                                </button>

                                            </form>

                                        @else

                                            <form method="POST"
                                                  action="{{ route('admin.usuarios.activar', $usuario->id) }}">

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-green-100 px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-200">

                                                    Activar

                                                </button>

                                            </form>

                                        @endif

                                    @endif


                                    {{-- ESTUDIANTE --}}
                                    @if($usuario->role === 'estudiante')

                                        <form method="POST"
                                              action="{{ route('admin.usuarios.destroy', $usuario->id) }}">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                onclick="return confirm('¿Seguro que deseas eliminar este estudiante? Se eliminarán también sus datos y progreso.')"
                                                class="rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-200">

                                                Eliminar

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="px-6 py-10 text-center">

                                <div class="text-slate-500">
                                    No se encontraron usuarios.
                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINACIÓN --}}
        @if($usuarios->hasPages())

            <div class="border-t px-6 py-4">

                {{ $usuarios->links() }}

            </div>

        @endif

    </div>

</div>

@endsection