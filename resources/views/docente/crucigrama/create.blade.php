@extends('layouts.docente')

@section('title', 'Nueva palabra')

@section('content')
    <a href="{{ route('docente.crucigrama.index') }}" class="text-sm font-semibold text-indigo-600">← Volver</a>
    <h1 class="mt-2 text-2xl font-bold text-slate-900">Nueva palabra</h1>
    <form method="POST" action="{{ route('docente.crucigrama.store') }}" class="mt-6 max-w-2xl space-y-6 rounded-2xl border border-slate-200 bg-white p-8">
        @csrf
        @include('docente.crucigrama._form', ['topics' => $topics])
        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white">Guardar</button>
            <a href="{{ route('docente.crucigrama.index') }}" class="rounded-xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700">Cancelar</a>
        </div>
    </form>
@endsection
