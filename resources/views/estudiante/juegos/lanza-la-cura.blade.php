@extends('layouts.estudiante')

@section('title', 'Lanza la Cura')

@section('content')
<div class="relative -mx-4 -my-8 md:-mx-6" style="height: calc(100vh - 64px);">
    <iframe
        id="lanza-cura-frame"
        src="{{ asset('lanza-la-cura/index.html') }}"
        class="h-full w-full border-0"
        title="Lanza la Cura"
        allow="fullscreen"
    ></iframe>

    
</div>
@endsection