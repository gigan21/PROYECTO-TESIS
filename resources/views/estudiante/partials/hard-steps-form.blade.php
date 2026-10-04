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
         <!-- BOTÓN DE SALTAR CON CAPTURA DE TIEMPO -->
         <form method="POST" action="{{ route('estudiante.preguntas.skip', $question) }}" class="w-full sm:w-auto m-0 p-0">
            @csrf
            <!-- Aquí se inyectarán los segundos de abandono -->
            <input type="hidden" name="time_taken" class="skip_time_taken_input" value="0">
            
            <button type="submit" class="w-full rounded-xl border border-slate-400 bg-transparent px-6 py-3 text-center text-sm font-bold text-slate-600 transition hover:bg-slate-200">
                Saltar misión
            </button>
        </form>
    </div>
</form>
