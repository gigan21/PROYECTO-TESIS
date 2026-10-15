@extends('layouts.estudiante')

@section('title', 'Carrera de Gráficas')

@section('content')
<div class="relative -mx-4 -my-8 md:-mx-6" style="height: calc(100vh - 64px);">
    <iframe
        id="carrera-graficas-frame"
        src="{{ asset('carrera-graficas/index.html') }}?csrf={{ urlencode(csrf_token()) }}&reward_url={{ urlencode(route('estudiante.chart_race.api.reward')) }}&status_url={{ urlencode(route('estudiante.chart_race.api.status')) }}"
        class="h-full w-full border-0"
        title="Carrera de Gráficas"
        allow="fullscreen"
    ></iframe>
</div>

<script>
    (function () {
        const statusUrl = @json(route('estudiante.chart_race.api.status'));

        async function refreshStatus() {
            try {
                const res = await fetch(statusUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                await res.json();
            } catch (e) {
                console.warn('Carrera de Gráficas: no se pudo refrescar estado', e);
            }
        }

        window.addEventListener('message', (event) => {
            if (event.origin !== window.location.origin) return;
            if (event.data && event.data.type === 'chart_race:reward') {
                refreshStatus();
            }
        });
    })();
</script>
@endsection
