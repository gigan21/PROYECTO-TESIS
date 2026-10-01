@php
    $orderedOptions = $question->options->sortBy('id')->values();
@endphp

<form method="POST" action="{{ route('estudiante.preguntas.responder', $question) }}" class="space-y-5">
    @csrf
    <input type="hidden" name="time_taken" id="time_taken_input" value="0">

    <p class="text-sm text-slate-600">
        Completa cada paso del laboratorio con el resultado numérico y su unidad.
    </p>

    <div class="space-y-4">
        @foreach ($orderedOptions as $index => $option)
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <label for="step_answer_{{ $option->id }}" class="mb-2 block text-sm font-bold text-slate-800">
                    {{ $option->step_label ?: 'Paso '.($index + 1) }}
                </label>
                <input type="text"
                       id="step_answer_{{ $option->id }}"
                       name="step_answers[{{ $option->id }}]"
                       required
                       placeholder="Ej: 12.5 s"
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        @endforeach
    </div>

    <div class="mt-8 flex flex-col items-center gap-4 border-t border-slate-100 pt-6 sm:flex-row">
        <button type="submit"
                class="w-full rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white transition-all hover:-translate-y-1 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 sm:w-auto">
            Enviar procedimiento
        </button>
        <a href="{{ route('estudiante.preguntas.index') }}"
           class="w-full rounded-xl px-6 py-3.5 text-center text-sm font-bold text-slate-500 transition-colors hover:bg-slate-100 sm:w-auto">
            Saltar pregunta
        </a>
    </div>
</form>
