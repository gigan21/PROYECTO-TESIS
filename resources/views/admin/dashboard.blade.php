@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">
            Dashboard
        </h1>

        <p class="mt-1 text-slate-500">
            Administración del sistema gamificado
        </p>
    </div>


    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">
                Usuarios
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900">
                {{ $totalUsuarios }}
            </p>
        </div>


        <div class="rounded-xl bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">
                Docentes
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900">
                {{ $totalDocentes }}
            </p>
        </div>


        <div class="rounded-xl bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">
                Estudiantes
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900">
                {{ $totalEstudiantes }}
            </p>
        </div>

    </div>


    <div class="mt-8">

        <h2 class="mb-4 text-xl font-semibold text-slate-900">
            Estudiantes por paralelo
        </h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

            <a href="{{ route('admin.usuarios.index', ['classroom' => '4A']) }}"
               class="rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md">

                <p class="text-sm text-slate-500">
                    Paralelo
                </p>

                <p class="mt-2 text-2xl font-bold">
                    4º A
                </p>

                <p class="mt-2 text-slate-600">
                    {{ $estudiantes4A }} estudiantes
                </p>

            </a>


            <a href="{{ route('admin.usuarios.index', ['classroom' => '4B']) }}"
               class="rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md">

                <p class="text-sm text-slate-500">
                    Paralelo
                </p>

                <p class="mt-2 text-2xl font-bold">
                    4º B
                </p>

                <p class="mt-2 text-slate-600">
                    {{ $estudiantes4B }} estudiantes
                </p>

            </a>


            <a href="{{ route('admin.usuarios.index', ['classroom' => '4C']) }}"
               class="rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md">

                <p class="text-sm text-slate-500">
                    Paralelo
                </p>

                <p class="mt-2 text-2xl font-bold">
                    4º C
                </p>

                <p class="mt-2 text-slate-600">
                    {{ $estudiantes4C }} estudiantes
                </p>

            </a>

        </div>

    </div>

@endsection