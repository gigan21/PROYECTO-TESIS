<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Panel de Administración')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100">

    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        <aside class="w-64 bg-slate-900 text-white">

            <div class="flex h-20 items-center px-6 border-b border-slate-700">
                <div>
                    <div class="text-xl font-bold">
                        Sistema Gamificado
                    </div>

                    <div class="text-xs text-slate-400">
                        Panel del Director
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-2">

                <a href="{{ route('admin.dashboard') }}"
                   class="block rounded-lg px-4 py-3 text-sm hover:bg-slate-800">
                    📊 Dashboard
                </a>

                <a href="{{ route('admin.usuarios.index') }}"
                   class="block rounded-lg px-4 py-3 text-sm hover:bg-slate-800">
                    👥 Usuarios
                </a>

                <a href="{{ route('admin.usuarios.index', ['role' => 'docente']) }}"
                   class="block rounded-lg px-4 py-3 text-sm hover:bg-slate-800">
                    👨‍🏫 Docentes
                </a>

                <a href="{{ route('admin.usuarios.index', ['role' => 'estudiante']) }}"
                   class="block rounded-lg px-4 py-3 text-sm hover:bg-slate-800">
                    🎓 Estudiantes
                </a>

                <a href="{{ route('admin.usuarios.index', ['classroom' => '4A']) }}"
                   class="block rounded-lg px-4 py-3 text-sm hover:bg-slate-800">
                    📚 4º A
                </a>

                <a href="{{ route('admin.usuarios.index', ['classroom' => '4B']) }}"
                   class="block rounded-lg px-4 py-3 text-sm hover:bg-slate-800">
                    📚 4º B
                </a>

                <a href="{{ route('admin.usuarios.index', ['classroom' => '4C']) }}"
                   class="block rounded-lg px-4 py-3 text-sm hover:bg-slate-800">
                    📚 4º C
                </a>

            </nav>

        </aside>


        {{-- CONTENIDO --}}
        <div class="flex-1">

            {{-- HEADER --}}
            <header class="flex h-20 items-center justify-between border-b bg-white px-8">

                <div>
                    <span class="text-sm text-slate-500">
                        Administrador
                    </span>
                </div>

                <div class="flex items-center gap-4">

                    <div class="text-right">
                        <div class="text-sm font-semibold text-slate-800">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="text-xs text-slate-500">
                            Director
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            Cerrar sesión
                        </button>
                    </form>

                </div>

            </header>


            {{-- MAIN --}}
            <main class="p-8">

                @if(session('success'))
                    <div class="mb-6 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')

            </main>

        </div>

    </div>

</body>
</html>