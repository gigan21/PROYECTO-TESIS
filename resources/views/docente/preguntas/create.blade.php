@extends('layouts.docente')

@section('title', 'Nueva pregunta')

@section('content')
    <div class="mb-6">
        <a href="{{ route('docente.preguntas.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Volver al banco</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Crear pregunta</h1>
    </div>

    @include('docente.preguntas._form')
@endsection