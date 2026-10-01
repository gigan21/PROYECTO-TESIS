@extends('layouts.estudiante')

@section('title', 'Crucigrama')

@push('styles')
    <link rel="stylesheet" href="{{ asset('crucigrama/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('crucigrama/niveles.css') }}">
@endpush

@section('content')
    <div id="crossword-app" class="crossword-blade-wrap"
         data-level-url="{{ route('estudiante.crucigrama.api.nivel') }}"
         data-progress-url="{{ route('estudiante.crucigrama.api.progreso') }}"
         data-save-url="{{ route('estudiante.crucigrama.api.guardar') }}"
         data-reset-url="{{ route('estudiante.crucigrama.api.reiniciar') }}">

        <div class="container">
            <header>
                <h1>Crucigrama de Cinemática</h1>
                <p>Resuelve el crucigrama usando las pistas. Haz clic en una pista para resaltar la palabra.</p>
            </header>

            <div class="nivel-header">
                <div class="nivel-info">
                    <span class="nivel-label">Nivel</span>
                    <span class="nivel-number" id="nivelActual">1</span>
                    <span class="nivel-max">/ <span id="nivelMaximo">20</span></span>
                </div>
                <span id="statCoins" class="text-sm font-bold text-amber-300">Monedas: 0</span>
                <button id="btnReiniciarProgreso" class="btn-reiniciar" title="Borrar progreso" style="display:none;">Reiniciar progreso</button>
            </div>

            <div class="message" id="message"></div>

            <div class="stats">
                <span>Palabras: <strong id="statWords">0</strong></span>
                <span>Resueltas: <strong id="statSolved">0</strong></span>
                <span>Progreso: <strong id="statProgress">0%</strong></span>
            </div>

            <div class="toolbar">
                <button type="button" onclick="checkAnswers()">Comprobar</button>
                {{-- Botón "Mostrar solución" deshabilitado por ahora
    /*<button type="button" class="secondary" onclick="revealSolutions()">Mostrar solución</button>*/
    --}}
                <button type="button" class="danger" onclick="resetCrossword()">Reiniciar cuadrícula</button>
            </div>

            <div class="main-layout">
                <div class="grid-wrapper"
                     @if (file_exists(public_path('images/crucigrama-fondo.jpg')))
                         style="--crucigrama-bg: url('{{ asset('images/crucigrama-fondo.jpg') }}');"
                     @endif>
                    <div id="crossword"></div>
                </div>
                <div class="clues">
                    <div class="clue-box">
                        <h3>Horizontal</h3>
                        <ul class="clue-list" id="acrossClues"></ul>
                    </div>
                    <div class="clue-box">
                        <h3>Vertical</h3>
                        <ul class="clue-list" id="downClues"></ul>
                    </div>
                </div>
            </div>

            <h3 class="section-title">Palabras aprendidas</h3>
            <ul class="word-list" id="wordList"></ul>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('crucigrama/api.js') }}"></script>
    <script src="{{ asset('crucigrama/script.js') }}"></script>
    <script src="{{ asset('crucigrama/niveles.js') }}"></script>
@endpush
