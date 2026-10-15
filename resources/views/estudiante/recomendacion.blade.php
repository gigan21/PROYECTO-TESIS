@extends('layouts.estudiante')

@section('title', 'Tu recomendación')

@section('content')
@php
    $slug = $recommendation?->reason;
@endphp
<div class="mx-auto max-w-2xl rounded-2xl border border-white/20 bg-white/10 p-6 text-white shadow-xl backdrop-blur-md">
    <a href="{{ route('estudiante.inicio') }}" class="text-sm font-semibold text-indigo-200">← Inicio</a>
    @if (! $recommendation)
        <p class="mt-4 text-slate-200">Sigue jugando para recibir recomendaciones personalizadas.</p>
    @else
        <h1 class="mt-3 text-2xl font-bold">{{ __('recommendations.'.$slug.'.label', [], 'es') }}</h1>
        <p class="mt-2 text-sm text-slate-300">{{ $recommendation->created_at?->diffForHumans() }}</p>
        @if ($recommendation->confidence !== null)
            <p class="mt-1 text-sm text-slate-300">Confianza del modelo: {{ number_format($recommendation->confidence * 100, 0) }}%</p>
        @endif
        <p class="mt-4 leading-relaxed text-slate-100">{{ __('recommendations.'.$slug.'.detail', [], 'es') }}</p>
    @endif
</div>

<div class="mx-auto mt-6 max-w-2xl">
    @include('partials.struggle-alerts', ['alerts' => $struggleAlerts ?? []])
</div>
@endsection
