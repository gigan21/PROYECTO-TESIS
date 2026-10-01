@extends('layouts.docente')

@section('title', 'Editar pregunta')

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.preguntas.show', $question) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Volver al detalle</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Editar pregunta</h1>
    </div>

    @include('docente.preguntas._form')
@endsection