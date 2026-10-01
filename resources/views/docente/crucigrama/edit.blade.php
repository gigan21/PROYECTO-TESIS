@extends('layouts.docente')

@section('title', 'Editar palabra')

@section('content')
    <a href="{{ route('docente.crucigrama.show', $word) }}" class="text-sm font-semibold text-indigo-600">← Volver</a>
    <h1 class="mt-2 text-2xl font-bold text-slate-900">Editar palabra</h1>
    <form method="POST" action="{{ route('docente.crucigrama.update', $word) }}" class="mt-6 max-w-2xl space-y-6 rounded-2xl border border-slate-200 bg-white p-8">
        @csrf @method('PUT')
        @include('docente.crucigrama._form', ['topics' => $topics, 'word' => $word])
        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white">Guardar cambios</button>
            <a href="{{ route('docente.crucigrama.index') }}" class="rounded-xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700">Cancelar</a>
        </div>
    </form>
@endsection
