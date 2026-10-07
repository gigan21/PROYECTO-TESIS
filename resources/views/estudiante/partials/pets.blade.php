@php
    $catalog = config('pets.catalog');
    $activeId = $activePetId ?? null;

    $pets = collect();
    if ($activeId && isset($catalog[$activeId])) {
        $pets->push(['id' => $activeId] + $catalog[$activeId]);
    }

    $payload = [
        'userId'   => auth()->id(),
        'basePath' => asset(config('pets.base_path')),
        'phrases'  => config('pets.phrases'),
        'pets'     => $pets,
    ];
@endphp

<link rel="stylesheet" href="{{ asset('css/pets.css') }}">

<div class="pet-layer" data-pet-layer data-config="{{ json_encode($payload) }}" aria-hidden="true"></div>

<script src="{{ asset('js/pets/pet-system.js') }}" defer></script>