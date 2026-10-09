@php
    $isEdit = isset($question);
    $options = old('options', $isEdit ? $question->options->values()->map(fn ($option) => ['option_text' => $option->option_text])->all() : [['option_text' => ''], ['option_text' => ''], ['option_text' => ''], ['option_text' => '']]);
    $correct = (string) old('correct_option', $isEdit ? (string) $question->options->values()->search(fn ($option) => $option->is_correct) : '0');

    $hardBlocksFromQuestion = $isEdit && $question->difficulty->value === 'Difícil'
        ? $question->options->sortBy('sort_order')->values()->map(fn ($option) => [
            'id' => $option->id,
            'block_type' => $option->block_type?->value ?? 'text',
            'content' => $option->content ?? '',
            'option_text' => $option->option_text ?? '',
            'unit' => $option->unit,
            'tolerance' => $option->tolerance,
            'sort_order' => $option->sort_order ?? 0,
        ])->all()
        : [];

    $oldOptions = old('options');
    $hardBlocks = (is_array($oldOptions) && isset($oldOptions[0]['block_type']))
        ? $oldOptions
        : $hardBlocksFromQuestion;
@endphp

@if ($topics->isEmpty())
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        No hay temas. Ejecuta <code class="font-mono">php artisan questions:import</code> para cargar Movimiento, MRU y MRUV.
    </div>
@endif

<form method="POST"
      action="{{ $isEdit ? route('docente.preguntas.update', $question) : route('docente.preguntas.store') }}"
      enctype="multipart/form-data"
      class="max-w-6xl w-full space-y-5 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="topic_id" class="mb-1.5 block text-sm font-medium text-slate-700">Tema</label>
        <select id="topic_id" name="topic_id" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
            <option value="">Selecciona un tema</option>
            @foreach ($topics as $topic)
                <option value="{{ $topic->id }}" @selected((string) old('topic_id', $isEdit ? $question->topic_id : '') === (string) $topic->id)>{{ $topic->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="difficulty" class="mb-1.5 block text-sm font-medium text-slate-700">Dificultad</label>
            <select id="difficulty" name="difficulty" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                @foreach ($difficulties as $difficulty)
                    <option value="{{ $difficulty->value }}" @selected(old('difficulty', $isEdit ? $question->difficulty->value : '') === $difficulty->value)>{{ $difficulty->value }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="type" class="mb-1.5 block text-sm font-medium text-slate-700">Tipo</label>
            <select id="type" name="type" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $isEdit ? $question->type->value : '') === $type->value)>{{ $type->value }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="xp_reward" class="mb-1.5 block text-sm font-medium text-slate-700">XP</label>
            <input id="xp_reward" name="xp_reward" type="number" min="0" max="1000" required
                   value="{{ old('xp_reward', $isEdit ? $question->xp_reward : 10) }}"
                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label for="time_limit_seconds" class="mb-1.5 block text-sm font-medium text-slate-700">Tiempo (segundos)</label>
            <input id="time_limit_seconds" name="time_limit_seconds" type="number" min="10" max="600" required
                   value="{{ old('time_limit_seconds', $isEdit ? $question->time_limit_seconds : 60) }}"
                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
        </div>
    </div>

    <div>
        <label for="question_text" class="mb-1.5 block text-sm font-medium text-slate-700">Enunciado</label>
        <textarea id="question_text" name="question_text" rows="4" required
                  class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">{{ old('question_text', $isEdit ? $question->question_text : '') }}</textarea>
    </div>

    <div>
        <label for="image" class="mb-1.5 block text-sm font-medium text-slate-700">Imagen de apoyo (Opcional)</label>
        
        <!-- Si está editando y ya tiene imagen, se la mostramos pequeñita -->
        @if($isEdit && $question->image_path)
            <div class="mb-3">
                <p class="text-xs text-slate-500 mb-1">Imagen actual:</p>
                <img src="{{ asset('storage/' . $question->image_path) }}" class="h-32 w-auto rounded-lg border border-slate-200 shadow-sm">
            </div>
        @endif
        
        <input type="file" id="image" name="image" accept="image/*" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm bg-white file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
    </div>

    <div class="space-y-3" id="opciones-container">
        <p id="titulo-opciones" class="text-sm font-medium text-slate-700">Opciones (marca la correcta)</p>
        @foreach (['A', 'B', 'C', 'D'] as $index => $letter)
            <div class="flex items-start gap-3">
                <label class="radio-wrapper mt-2.5 flex items-center gap-2 text-sm font-semibold text-slate-600">
                    <input type="radio" name="correct_option" value="{{ $index }}" @checked($correct === (string) $index) required class="radio-input">
                    <span class="letra-opcion">{{ $letter }}</span>
                </label>
                <input type="text" name="options[{{ $index }}][option_text]" required
                       value="{{ old("options.$index.option_text", $options[$index]['option_text'] ?? '') }}"
                       placeholder="Escribe la opción..."
                       class="input-opcion w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
            </div>
        @endforeach
    </div>

    @include('docente.preguntas.partials.hard-laboratory-steps', ['hardBlocks' => $hardBlocks])

    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $isEdit ? $question->is_active : true))>
        Pregunta activa (visible para estudiantes)
    </label>

    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700">
        {{ $isEdit ? 'Guardar cambios' : 'Crear pregunta' }}
    </button>
</form>

@vite(['resources/js/docente-question-hard-lab.js'])

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const difficultySelect = document.getElementById('difficulty');
        const tituloOpciones = document.getElementById('titulo-opciones');
        const radioWrappers = document.querySelectorAll('.radio-wrapper');
        const radioInputs = document.querySelectorAll('.radio-input');
        const letrasOpcion = document.querySelectorAll('.letra-opcion');
        const inputsOpcion = document.querySelectorAll('.input-opcion');

        function restaurarVistaNormal() {
            tituloOpciones.innerText = 'Opciones (marca la correcta)';
            const letras = ['A', 'B', 'C', 'D'];

            radioWrappers.forEach((wrapper, index) => {
                radioInputs[index].style.display = 'inline-block';
                letrasOpcion[index].innerText = letras[index];
                inputsOpcion[index].placeholder = 'Escribe la opción...';
            });
        }

        function actualizarFormulario() {
            const dificultad = difficultySelect.value;

            if (dificultad === 'Difícil') {
                if (window.DocenteHardLab) {
                    window.DocenteHardLab.activate();
                }
                return;
            }

            if (window.DocenteHardLab) {
                window.DocenteHardLab.deactivate();
            }

            if (dificultad === 'Medio') {
                tituloOpciones.innerHTML = 'Pasos del Rompecabezas <span class="ml-2 text-xs font-normal text-indigo-500">(Escribe los pasos en orden cronológico. El sistema los desordenará para el estudiante)</span>';

                radioWrappers.forEach((wrapper, index) => {
                    radioInputs[index].style.display = 'none';
                    if (index === 0) {
                        radioInputs[index].checked = true;
                    }
                    letrasOpcion[index].innerText = 'Paso ' + (index + 1);
                    inputsOpcion[index].placeholder = 'Ej: ' + (index === 0 ? 'Identificar los datos...' : 'Aplicar la fórmula...');
                });
            } else {
                restaurarVistaNormal();
            }
        }

        difficultySelect.addEventListener('change', actualizarFormulario);

        function bootDifficultyUi() {
            actualizarFormulario();
        }

        if (window.DocenteHardLab) {
            bootDifficultyUi();
        } else {
            document.addEventListener('docente-hard-lab-ready', bootDifficultyUi, { once: true });
        }
    });
</script>
