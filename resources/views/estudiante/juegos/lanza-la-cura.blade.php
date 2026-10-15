@extends('layouts.estudiante')

@section('title', 'Lanza la Cura')

@section('content')
<div class="relative -mx-4 -my-8 md:-mx-6" style="height: calc(100vh - 64px);">
    <iframe
        id="lanza-cura-frame"
        src="{{ asset('lanza-la-cura/index.html') }}?csrf={{ urlencode(csrf_token()) }}&reward_url={{ urlencode(route('estudiante.cure.api.reward')) }}&status_url={{ urlencode(route('estudiante.cure.api.status')) }}&home_url={{ urlencode(route('estudiante.inicio')) }}"
        class="h-full w-full border-0"
        title="Lanza la Cura"
        allow="fullscreen"
    ></iframe>
</div>

<script>
    (function () {
        const statusUrl = @json(route('estudiante.cure.api.status'));

        async function refreshStatus() {
            try {
                const res = await fetch(statusUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                await res.json();
            } catch (e) {
                console.warn('Lanza la Cura: no se pudo refrescar estado', e);
            }
        }

        window.addEventListener('message', (event) => {
            if (event.origin !== window.location.origin) return;
            if (event.data && event.data.type === 'cure:reward') {
                refreshStatus();
            }
        });

        const frame = document.getElementById('lanza-cura-frame');
        function notifyIframeAbandon() {
            if (!frame || !frame.contentWindow) return;
            frame.contentWindow.postMessage({ type: 'cure:abandon' }, window.location.origin);
        }
        window.addEventListener('pagehide', notifyIframeAbandon);
        window.addEventListener('beforeunload', notifyIframeAbandon);
    })();
</script>
@endsection
