{{-- resources/views/estudiante/profile.blade.php (ajusta la ruta a tu archivo) --}}
@extends('layouts.estudiante')

@section('title', 'Mi Perfil')

@push('styles')
<style>
    /* Restyle de los inputs del formulario existente sin tocar el partial */
    .rpg-form label {
        font-family: 'Rajdhani', system-ui, sans-serif;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        font-size: .8rem;
        color: rgb(252 211 77) !important; /* amber-300 */
    }
    .rpg-form input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    .rpg-form select,
    .rpg-form textarea {
        background-color: rgba(15, 23, 42, .6) !important; /* slate-900 */
        border: 1px solid rgba(255, 255, 255, .15) !important;
        color: #f1f5f9 !important;
        border-radius: .75rem !important;
        transition: border-color .2s, box-shadow .2s;
    }
    .rpg-form input:focus,
    .rpg-form select:focus,
    .rpg-form textarea:focus {
        outline: none !important;
        border-color: rgb(251 191 36) !important;
        box-shadow: 0 0 0 3px rgba(251, 191, 36, .25) !important;
    }
    .rpg-form select option { background: #0f172a; color: #f1f5f9; }
    .rpg-form button[type="submit"] {
        background: linear-gradient(to bottom, #fbbf24, #f59e0b) !important;
        color: #1e1b4b !important;
        font-family: 'Cinzel', Georgia, serif;
        font-weight: 800;
        border: 0 !important;
        border-radius: .75rem !important;
        box-shadow: 0 0 18px rgba(251, 191, 36, .35);
        transition: transform .15s, box-shadow .15s;
    }
    .rpg-form button[type="submit"]:hover {
        transform: translateY(-1px);
        box-shadow: 0 0 26px rgba(251, 191, 36, .55);
    }
    .rpg-form p.text-red-600, .rpg-form .text-red-500 { color: #fda4af !important; }

    /* Estilo para el partial de la tarjeta */
    .rpg-card h1, .rpg-card h2, .rpg-card h3 { font-family: 'Cinzel', Georgia, serif; }
</style>
@endpush

@section('content')

    {{-- ===== ENCABEZADO DE PÁGINA ===== --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('estudiante.inicio') }}"
               class="inline-flex items-center gap-1 text-sm font-semibold text-slate-400 transition hover:text-amber-300">
                ← Volver al Inicio
            </a>
            <h1 class="font-epic mt-1 text-3xl font-extrabold text-amber-300 md:text-4xl">
                Hoja de Personaje
            </h1>
            <p class="text-sm text-slate-400">Edita tu identidad de héroe y personaliza tu aventura.</p>
        </div>

        
    </div>

    {{-- ===== ALERTA DE ÉXITO ===== --}}
    @if (session('status') === 'profile-updated')
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-400/40 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-200 shadow-[0_0_20px_rgba(16,185,129,0.15)]"
             role="status">
            <span class="text-xl">⚡</span>
            <span>¡Tu información, apodo y avatar se actualizaron correctamente!</span>
        </div>
    @endif

    {{-- ===== GRID PRINCIPAL ===== --}}
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

        {{-- Columna izquierda: tarjeta RPG --}}
        <div class="rpg-card lg:col-span-4">
            <div class="relative overflow-hidden rounded-2xl border border-amber-400/30 bg-white/5 p-1 shadow-[0_0_30px_rgba(251,191,36,0.12)] backdrop-blur-md lg:sticky lg:top-28">
                {{-- Brillo decorativo (solo visual) --}}
                <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-amber-400/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-indigo-500/20 blur-3xl"></div>

                <div class="relative rounded-xl p-5 text-slate-100">
                    @include('estudiante.partials.profile-card')
                </div>
            </div>
        </div>

        {{-- Columna derecha: formulario --}}
        <div class="lg:col-span-8">
            <section class="rpg-form relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 shadow-xl backdrop-blur-md">
                {{-- Barra superior estilo panel --}}
                <div class="flex items-center gap-3 border-b border-white/10 bg-gradient-to-r from-indigo-500/20 via-transparent to-amber-400/10 px-5 py-4 md:px-8">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-amber-400/40 bg-amber-400/10 text-lg">🛡️</span>
                    <div>
                        <h2 class="font-epic text-lg font-extrabold text-amber-300">Editar Perfil</h2>
                        <p class="text-xs text-slate-400">Actualiza tus datos, apodo y avatar</p>
                    </div>
                </div>

                <div class="p-5 text-slate-100 md:p-8">
                    @include('estudiante.partials.profile-form', ['classrooms' => $classrooms])
                </div>
            </section>

            {{-- Consejo decorativo (sin función) --}}
            <div class="mt-6 flex items-center gap-3 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-100/80">
                <span class="text-xl">💡</span>
                <p>Consejo del gremio: un buen avatar y un apodo épico hacen tu leyenda inolvidable.</p>
            </div>
        </div>

    </div>
@endsection