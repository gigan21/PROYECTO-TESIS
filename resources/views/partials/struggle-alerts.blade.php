@php
    $alerts = $alerts ?? [];
@endphp
@if (count($alerts) > 0)
    <section class="mb-6 space-y-3" aria-label="Avisos pedagógicos">
        @foreach ($alerts as $alert)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-slate-900 shadow-sm">
                <p class="flex items-start gap-2 font-bold">
                    <span aria-hidden="true">{{ $alert['icon'] ?? '⚠️' }}</span>
                    <span>{{ $alert['title'] ?? '' }}</span>
                </p>
                @if (! empty($alert['suggestion']))
                    <p class="mt-2 text-sm leading-relaxed">{{ $alert['suggestion'] }}</p>
                @endif
            </div>
        @endforeach
    </section>
@endif
