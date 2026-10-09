@php
    $hardBlocks =$hardBlocks ?? [];
@endphp

<!-- DEPENDENCIAS (Todas agrupadas arriba) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.min.js"></script>

<div id="hard-lab-panel" class="hidden space-y-5 rounded-2xl border-2 border-dashed border-indigo-200 bg-indigo-50/40 p-6">
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Recuerda subir la imagen del enunciado en la pestaña principal. Aquí construirás la resolución paso a paso.
        Usa los botones de plantilla en las fórmulas para insertar huecos con corchetes; no hace falta escribirlos a mano.
    </div>

    <!-- Botones de acción sticky para tenerlos siempre a mano si el panel se alarga -->
    <div class="sticky top-2 z-10 flex flex-wrap gap-2 rounded-xl bg-white/80 p-2 backdrop-blur-md shadow-sm border border-slate-200/60">
        <button type="button" data-add-block="text"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            + Añadir Texto
        </button>
        <button type="button" id="btn-preview-board"
        class="rounded-xl border border-purple-200 bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-700 transition-colors ml-auto">
            👁️ Ver Pizarra Completa
        </button>
        <button type="button" data-add-block="formula"
                class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-800 shadow-sm hover:bg-indigo-100">
            + Fórmula del Procedimiento
        </button>
        <button type="button" data-add-block="input"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm hover:bg-emerald-100">
            + Respuesta Final
        </button>
    </div>

    <!-- Grid de 2 columnas -->
    <div id="hard-blocks-list" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
        @foreach ($hardBlocks as $index =>$block)
            @include('docente.preguntas.partials.hard-block-card', [
                'index' => $index,
                'block' => $block,
            ])
        @endforeach
    </div>

    <p id="hard-blocks-empty" class="{{ count($hardBlocks) > 0 ? 'hidden' : '' }} text-sm text-slate-500">
        Aún no hay bloques. Usa los botones de arriba para construir la resolución guiada.
    </p>
</div>

<template id="hard-block-template-text">
    @include('docente.preguntas.partials.hard-block-card', [
        'index' => '__INDEX__',
        'block' => ['block_type' => 'text', 'content' => '', 'unit' => null, 'tolerance' => null, 'sort_order' => 0],
        'isTemplate' => true,
    ])
</template>
<template id="hard-block-template-formula">
    @include('docente.preguntas.partials.hard-block-card', [
        'index' => '__INDEX__',
        'block' => ['block_type' => 'formula', 'content' => '', 'unit' => null, 'tolerance' => null, 'sort_order' => 0],
        'isTemplate' => true,
    ])
</template>
<template id="hard-block-template-input">
    @include('docente.preguntas.partials.hard-block-card', [
        'index' => '__INDEX__',
        'block' => ['block_type' => 'input', 'content' => '', 'unit' => '', 'tolerance' => '', 'sort_order' => 0],
        'isTemplate' => true,
    ])
</template>

<!-- Modal de Vista Previa -->
<div id="modal-preview-board" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-5xl rounded-3xl bg-[#f8fafc] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center bg-white px-6 py-4 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-lg">Vista Previa del Estudiante</h3>
            <button type="button" id="close-preview" class="text-slate-400 hover:text-red-500 font-bold text-xl">&times;</button>
        </div>
        
        <!-- Pizarra (Aquí se inyectará el contenido) -->
        <div class="p-8 overflow-y-auto w-full">
        <div id="preview-content"
     class="md:columns-2 gap-8 text-lg text-slate-800 leading-relaxed"
     style="column-rule: 1px solid #cbd5e1;">
     </div>
     </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('hard-blocks-list');

    // 1. Funciones base
    window.reindexHardBlocks = function() {
        const blocks = list.querySelectorAll('.hard-block-card');
        blocks.forEach((block, index) => {
            const numberBadge = block.querySelector('.hard-block-number');
            if (numberBadge) numberBadge.textContent = `#${index + 1}`;

            block.querySelectorAll('[name^="options["]').forEach(input => {
                input.name = input.name.replace(/options\[\d+\]/, `options[${index}]`);
            });

            const sortInput = block.querySelector('.hard-field-sort');
            if (sortInput) sortInput.value = index;
        });

        const emptyMsg = document.getElementById('hard-blocks-empty');
        if (emptyMsg) {
            emptyMsg.classList.toggle('hidden', blocks.length > 0);
        }
    };

    // 2. Event Delegation
    list.addEventListener('click', (e) => {
        const card = e.target.closest('.hard-block-card');
        if (!card) return;

        if (e.target.closest('.hard-move-up')) {
            if (card.previousElementSibling) {
                card.parentNode.insertBefore(card, card.previousElementSibling);
                window.reindexHardBlocks();
            }
        }
        if (e.target.closest('.hard-move-down')) {
            if (card.nextElementSibling) {
                card.parentNode.insertBefore(card.nextElementSibling, card);
                window.reindexHardBlocks();
            }
        }
        if (e.target.closest('.hard-remove-block')) {
            card.remove();
            window.reindexHardBlocks();
        }
        if (e.target.closest('.hard-toggle-collapse')) {
            const body = card.querySelector('.hard-block-body');
            body.classList.toggle('hidden');
            e.target.textContent = body.classList.contains('hidden') ? '▶️' : '🔽';
        }
        if (e.target.closest('.hard-duplicate-block')) {
            const clone = card.cloneNode(true);
            card.parentNode.insertBefore(clone, card.nextElementSibling);
            window.reindexHardBlocks();
        }
    });

    window.reindexHardBlocks();

    // 3. Drag & Drop
    if (list) {
        new Sortable(list, {
            handle: '.hard-drag-handle',
            animation: 150,
            onEnd: function () {
                window.reindexHardBlocks();
            }
        });
    }

    // 4. Vista Previa — AQUÍ FALTABA LA DECLARACIÓN DE btnPreview
    const btnPreview = document.getElementById('btn-preview-board');
    if (btnPreview) {
        btnPreview.addEventListener('click', () => {
            const previewContainer = document.getElementById('preview-content');
            previewContainer.innerHTML = '';

            const blocks = document.querySelectorAll('.hard-block-card');

            if (blocks.length === 0) {
                previewContainer.innerHTML = '<p class="text-slate-500 italic text-center w-full">Agrega bloques para ver la previsualización.</p>';
            }

            blocks.forEach((block, index) => {
                const type = block.getAttribute('data-block-type');
                const contentInput = block.querySelector('.hard-field-content');
                const content = contentInput ? contentInput.value.trim() : '';

                // Datos extra
                const labelInput = block.querySelector('input[name*="[option_text]"]');
                const unitInput  = block.querySelector('input[name*="[unit]"]');
                const tolInput   = block.querySelector('input[name*="[tolerance]"]');
                const label      = labelInput ? labelInput.value.trim() : '';
                const unit       = unitInput ? unitInput.value.trim() : '';
                const tolerance  = tolInput ? tolInput.value.trim() : '';

                let blockHtml = `<div class="break-inside-avoid mb-6 border-l-4 border-slate-200 pl-4">`;

                blockHtml += `
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                        <span>#${index + 1}</span>
                        <span>${type === 'text' ? 'Texto' : type === 'formula' ? 'Fórmula' : 'Respuesta Final'}</span>
                    </div>
                `;

                if (type === 'text') {
                    if (content) {
                        blockHtml += `<p class="text-slate-700 whitespace-pre-wrap">${content.replace(/\n/g, '<br>')}</p>`;
                    } else {
                        blockHtml += `<p class="text-slate-400 italic">(Texto vacío)</p>`;
                    }
                }
                else if (type === 'formula') {
    // Limpiar espacios invisibles (zero-width space)
    let cleanLatex = content.replace(/[\u200B-\u200D\uFEFF]/g, '');

    // 👇 VISTA DEL PROFESOR: mostrar el contenido real entre corchetes
    // [120]  →  120
    // [ ]    →  \square  (hueco vacío)
    cleanLatex = cleanLatex.replace(/\[([^\]]*)\]/g, (match, inner) => {
        const value = inner.trim();
        // Si el hueco tiene contenido → lo mostramos tal cual
        if (value !== '') {
            return value;
        }
        // Si está vacío → mostramos el cuadro
        return '\\square ';
    });

    if (cleanLatex.trim()) {
        try {
            const htmlLatex = katex.renderToString(cleanLatex, {
                displayMode: true,
                throwOnError: false,
                strict: false
            });
            blockHtml += `<div class="text-slate-900 py-2 overflow-x-auto">${htmlLatex}</div>`;
        } catch (e) {
            blockHtml += `<p class="text-red-500 text-sm">Error en LaTeX: ${e.message}</p>`;
        }
    } else {
        blockHtml += `<p class="text-slate-400 italic">(Fórmula vacía)</p>`;
    }

    if (tolerance) {
        blockHtml += `<p class="text-xs text-slate-500 mt-1">Tolerancia: ±${tolerance}</p>`;
    }
}
                else if (type === 'input') {
                    const displayLabel = label || 'RESPUESTA FINAL';
                    blockHtml += `
                        <div class="mt-4 p-4 border-2 border-dashed border-emerald-300 bg-emerald-50 rounded-xl w-full text-center">
                            <span class="text-sm font-bold text-emerald-800 block mb-2">${displayLabel}</span>
                            <span class="bg-white border border-slate-300 px-4 py-2 rounded-lg font-bold shadow-inner text-slate-400">???</span>
                            ${unit ? `<span class="ml-2 font-medium text-slate-600">${unit}</span>` : ''}
                            ${tolerance ? `<p class="text-xs text-slate-500 mt-2">Tolerancia: ±${tolerance}</p>` : ''}
                        </div>`;
                }

                blockHtml += `</div>`;
                previewContainer.insertAdjacentHTML('beforeend', blockHtml);
            });

            document.getElementById('modal-preview-board').classList.remove('hidden');
        });
    }

    // 5. Cerrar modal
    const btnClosePreview = document.getElementById('close-preview');
    if (btnClosePreview) {
        btnClosePreview.addEventListener('click', () => {
            document.getElementById('modal-preview-board').classList.add('hidden');
        });
    }
});
</script>