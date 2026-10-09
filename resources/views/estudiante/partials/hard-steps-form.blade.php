@php
    use App\Enums\QuestionBlockType;
    use App\Services\Questions\HardFormulaClozeParser;

    $clozeParser = app(HardFormulaClozeParser::class);

    // Acción configurable (quiz libre o desafío)
    $formAction = $action ?? route('estudiante.preguntas.responder', $question);
    $timeField = $timeField ?? 'time_taken';
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
<style>
    .katex-cloze-block .katex {
        color: rgb(15 23 42);
    }
</style>

<form method="POST" action="{{ $formAction }}" class="space-y-6" id="hard-answer-form">
    @csrf
    <input type="hidden" name="{{ $timeField }}" id="time_taken_input" value="0">

    @if ($question->image_path)
        <div class="flex justify-center">
            <img src="{{ asset('storage/' . $question->image_path) }}"
                 alt="Enunciado visual"
                 class="max-h-80 w-full max-w-2xl rounded-xl border border-white/10 object-contain shadow-lg">
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200/80 bg-slate-50 p-6 text-slate-800 shadow-lg sm:p-10">
        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-2 text-xl">
            @foreach ($question->options->sortBy('sort_order') as $option)
                @php
                    $type = $option->block_type?->value ?? 'text';
                @endphp

                @if ($type === 'text')
                    <p class="my-4 w-full text-lg leading-relaxed text-slate-800">{{ $option->content }}</p>
                @elseif ($type === 'formula')
                    @php
                        $holeCount = $clozeParser->holeCount((string) $option->content);
                        $studentLatex = $holeCount > 0
                            ? $clozeParser->buildStudentLatex((string) $option->content, (int) $option->id)
                            : (string) $option->content;
                    @endphp
                    <div class="katex-cloze-block flex w-full overflow-x-auto py-2 items-center"
                         data-option-id="{{ $option->id }}"
                         data-hole-count="{{ $holeCount }}"
                         data-latex="{{ $studentLatex }}"></div>
                @elseif ($type === 'input')
                    <div class="mt-6 w-full border-t border-slate-200/80 pt-6">
                        <p class="mb-2 text-base font-semibold tracking-tight text-slate-800">
                            {{ $option->option_text ?: 'Respuesta final' }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="text"
                                   id="step_answer_{{ $option->id }}"
                                   name="step_answers[{{ $option->id }}]"
                                   required
                                   placeholder="Resultado..."
                                   class="w-32 rounded-md border border-slate-300 bg-white px-3 py-2 text-center text-xl font-bold text-blue-700 shadow-inner outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            @if ($option->unit)
                                <span class="text-lg font-semibold text-slate-600">{{ $option->unit }}</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-10 flex justify-center border-t border-slate-100 pt-6">
            <button type="submit"
                    class="rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-indigo-500">
                Enviar procedimiento
            </button>
        </div>
    </div>
</form>

@if (! isset($action))
    <form method="POST" action="{{ route('estudiante.preguntas.skip', $question) }}" class="mt-4 flex justify-center">
        @csrf
        <input type="hidden" name="time_taken" class="skip_time_taken_input" value="0">
        <button type="submit" class="rounded-xl border border-slate-500/50 bg-transparent px-6 py-3 text-center text-sm font-bold text-slate-400 transition hover:bg-white/5 hover:text-white">
            Saltar misión
        </button>
    </form>
@endif

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof katex === 'undefined') {
                console.error('KaTeX cloze: katex.min.js no está cargado.');
                document.querySelectorAll('.katex-cloze-block').forEach(function (block) {
                    block.textContent = 'No se pudo cargar el visor matemático.';
                });
                return;
            }

            const formulaBlocks = document.querySelectorAll('.katex-cloze-block');

            formulaBlocks.forEach(function (block) {
                let rawLatex = '';

                try {
                    rawLatex = block.getAttribute('data-latex') || '';
                } catch (parseError) {
                    console.error('KaTeX cloze: data-latex inválido', parseError);
                    block.textContent = 'Error al leer la fórmula.';
                    return;
                }

                if (!rawLatex) {
                    return;
                }

                try {
                    katex.render(rawLatex, block, {
                        strict: false,
                        throwOnError: true,
                        displayMode: true,
                    });

                   // Usamos ORDERED_NODE_SNAPSHOT_TYPE para mantener el orden exacto de izquierda a derecha
                  // XPath: ahora buscamos ZHOLEZ (sin guiones, sin posibilidad de partirse)
const textNodes = document.evaluate(
    "//span[contains(text(), 'ZHOLEZ')]",
    block,
    null,
    XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
    null
);

let nodesArray = [];
for (let i = 0; i < textNodes.snapshotLength; i++) {
    nodesArray.push(textNodes.snapshotItem(i));
}

nodesArray.sort((a, b) => {
    const matchA = a.textContent.match(/ZHOLEZ\d+Z(\d+)Z/);
    const matchB = b.textContent.match(/ZHOLEZ\d+Z(\d+)Z/);
    return (matchA ? parseInt(matchA[1], 10) : 0) - (matchB ? parseInt(matchB[1], 10) : 0);
});

nodesArray.forEach((span, index) => {
    const match = span.textContent.match(/ZHOLEZ(\d+)Z(\d+)Z/);
    if (!match) return;

    const optionId = match[1];

    const input = document.createElement('input');
    input.type = 'number';
    input.step = 'any';
    input.name = 'step_answers[' + optionId + '][' + match[2] + ']';
    input.className = 'align-middle min-w-[4.5rem] w-auto max-w-[7rem] mx-1 rounded-md border border-slate-300 bg-white px-2 py-0.5 text-center font-bold text-blue-700 shadow-inner outline-none appearance-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200';
    input.required = true;
    input.setAttribute('aria-label', 'Hueco ' + (index + 1));

    span.replaceWith(input);
});
                } catch (error) {
                    console.error('Error renderizando KaTeX:', error);
                    block.innerHTML = '<p class="text-red-500 text-sm">Error matemático: ' + error.message + '</p>';
                }
            });
        });
        const form = document.getElementById('hard-answer-form');
form?.addEventListener('submit', function () {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});
    </script>
@endpush
