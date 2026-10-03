@extends('layouts.docente')

@section('title', 'Restablecer preguntas')

@section('content')

```
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            Restablecer Preguntas de Estudiantes
        </h1>
        <p class="text-sm text-slate-500">
            Permite que los alumnos vuelvan a responder preguntas resueltas sin perder sus XP ni insignias.
        </p>
    </div>

    <a href="{{ route('docente.preguntas.index') }}"
       class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
        Volver al Banco
    </a>
</div>

@if (session('status'))
    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
        {{ session('status') }}
    </div>
@endif


{{-- ========================================================= --}}
{{-- RESTABLECER PARA UN SOLO ESTUDIANTE --}}
{{-- ========================================================= --}}

<div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

    <h2 class="mb-2 text-lg font-bold text-slate-800">
        👤 Restablecer para un solo estudiante
    </h2>

    <p class="mb-5 text-sm text-slate-500">
        Selecciona un paralelo para mostrar sus estudiantes y restablecer sus preguntas individualmente.
    </p>

    {{-- Seleccionar paralelo --}}
    <form method="GET" class="mb-6">

        <label class="mb-2 block text-sm font-semibold text-slate-700">
            Seleccionar paralelo
        </label>

        <select
            name="classroom"
            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm sm:w-80"
            onchange="this.form.submit()"
        >
            <option value="">
                -- Seleccionar paralelo --
            </option>

            @foreach ($classrooms as $c)
                <option
                    value="{{ $c }}"
                    @selected(request('classroom') === $c)
                >
                    Paralelo {{ $c }}
                </option>
            @endforeach
        </select>

    </form>


    {{-- Lista de estudiantes --}}
    @if (request()->filled('classroom'))

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200 text-sm">

                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">

                    <tr>
                        <th class="px-4 py-3">Estudiante</th>
                        <th class="px-4 py-3">Paralelo</th>
                        <th class="px-4 py-3">XP</th>
                        <th class="px-4 py-3 text-right">Acción</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($students as $student)

                        <tr>

                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $student->name }}

                                <span class="block text-xs text-slate-400">
                                    {{ $student->email }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                {{ $student->studentProfile?->classroom ?? 'Sin paralelo' }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-indigo-600">
                                {{ $student->studentProfile?->xp_points ?? 0 }} XP
                            </td>

                            <td class="px-4 py-3 text-right">

                            <details class="inline-block ml-3 relative">
    <summary class="list-none cursor-pointer text-amber-600 hover:underline text-xs font-semibold">
        🔄 Restablecer
    </summary>

    <div class="absolute right-0 z-20 mt-2 w-80 rounded-xl border border-slate-200 bg-white p-4 shadow-lg">

        <p class="mb-3 text-sm font-bold text-slate-800">
            Restablecer preguntas
        </p>

        <p class="mb-4 text-xs text-slate-500">
            Se volverán a habilitar las preguntas respondidas por este estudiante.
            Sus XP e insignias no se modificarán.
        </p>

        <form
            action="{{ route('docente.preguntas.reset.student', $student) }}"
            method="POST"
            onsubmit="return confirm('⚠️ ¿Estás seguro de restablecer TODAS las preguntas de {{ $student->name }}? Sus XP e insignias no serán modificados.');"
        >
            @csrf

            <button
                type="submit"
                class="w-full rounded-lg bg-amber-600 px-3 py-2 text-xs font-bold text-white hover:bg-amber-700"
            >
                👤 Restablecer preguntas de este estudiante
            </button>
        </form>

    </div>
</details>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                No se encontraron estudiantes en el paralelo seleccionado.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    @else

        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
            Selecciona un paralelo para ver sus estudiantes.
        </div>

    @endif

</div>


{{-- ========================================================= --}}
{{-- RESTABLECER PARA TODOS --}}
{{-- ========================================================= --}}

<div class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm">

    <h2 class="mb-2 text-lg font-bold text-slate-800">
        ⚠️ Restablecer para todos los estudiantes
    </h2>

    <p class="mb-5 text-sm text-slate-500">
        Esta opción eliminará el registro de preguntas respondidas de todos los estudiantes del sistema.
        Sus XP e insignias no serán modificados.
    </p>

    <form
        action="{{ route('docente.preguntas.reset.bulk') }}"
        method="POST"
        onsubmit="return confirm('⚠️ ATENCIÓN: ¿Estás seguro de restablecer las preguntas para TODOS los estudiantes?');"
    >
        @csrf

        <input type="hidden" name="classroom" value="">

        <button
            type="submit"
            class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700"
        >
            🔄 Restablecer para todos los estudiantes
        </button>

    </form>

</div>
```

@endsection
