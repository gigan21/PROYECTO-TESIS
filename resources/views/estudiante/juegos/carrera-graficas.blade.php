@extends('layouts.estudiante')

@section('title', 'Carrera de Gráficas')

@section('content')
<div class="relative -mx-4 -my-8 md:-mx-6" style="height: calc(100vh - 64px);">
    <iframe
        id="carrera-graficas-frame"
        src="{{ asset('carrera-graficas/index.html') }}"
        class="h-full w-full border-0"
        title="Carrera de Gráficas"
        allow="fullscreen"
    ></iframe>
</div>
@endsection