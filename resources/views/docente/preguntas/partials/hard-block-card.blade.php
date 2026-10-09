@php
    $idx = $index;
    $isTemplate = $isTemplate ?? false;
    $type = data_get($block, 'block_type', 'text');
    $blockId = data_get($block, 'id');
    $sortOrderVal = data_get($block, 'sort_order', $idx);

    $contentVal = $isTemplate
        ? (string) data_get($block, 'content', '')
        : old("options.$idx.content", data_get($block, 'content', ''));
    $unitVal = $isTemplate
        ? (string) data_get($block, 'unit', '')
        : old("options.$idx.unit", data_get($block, 'unit', ''));
    $tolVal = $isTemplate
        ? data_get($block, 'tolerance', '')
        : old("options.$idx.tolerance", data_get($block, 'tolerance', ''));
    $labelVal = $isTemplate
        ? (string) data_get($block, 'option_text', '')
        : old("options.$idx.option_text", data_get($block, 'option_text', ''));

    $badgeConfig = match ($type) {
        'formula' => ['title' => 'Fórmula del procedimiento', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
        'input' => ['title' => 'Respuesta final', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
        default => ['title' => 'Texto / Instrucción', 'bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
    };
@endphp

<div class="hard-block-card relative rounded-2xl border {{ $badgeConfig['border'] }} bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md" data-block-type="{{ $type }}">
    <!-- Cabecera del bloque -->
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2">
        <div class="flex items-center gap-2">
            <!-- Icono Drag Handle -->
            <span class="hard-drag-handle cursor-grab text-slate-400 hover:text-slate-600 active:cursor-grabbing text-base" title="Arrastrar para reordenar">⣿</span>
            <!-- Número de bloque dinámico -->
            <span class="hard-block-number rounded-lg bg-slate-800 px-2 py-0.5 text-xs font-bold text-white">#{{ is_numeric($idx) ? $idx + 1 : 1 }}</span>
            <span class="rounded-lg {{ $badgeConfig['bg'] }} px-2 py-0.5 text-xs font-bold uppercase tracking-wide {{ $badgeConfig['text'] }}">
                {{ $badgeConfig['title'] }}
            </span>
        </div>

        <div class="flex items-center gap-1">
            <button type="button" class="hard-move-up rounded-lg p-1.5 text-xs text-slate-500 hover:bg-slate-100" title="Subir">⬆️</button>
            <button type="button" class="hard-move-down rounded-lg p-1.5 text-xs text-slate-500 hover:bg-slate-100" title="Bajar">⬇️</button>
            <button type="button" class="hard-duplicate-block rounded-lg p-1.5 text-xs text-indigo-600 hover:bg-indigo-50" title="Duplicar este bloque">📋</button>
            <button type="button" class="hard-toggle-collapse rounded-lg p-1.5 text-xs text-slate-500 hover:bg-slate-100" title="Minimizar / Expandir">🔽</button>
            <button type="button" class="hard-remove-block rounded-lg p-1.5 text-xs text-red-500 hover:bg-red-50" title="Eliminar bloque">🗑️</button>
        </div>
    </div>

    <input type="hidden" name="options[{{ $idx }}][block_type]" value="{{ $type }}" class="hard-field-type">
    <input type="hidden" name="options[{{ $idx }}][sort_order]" value="{{ $sortOrderVal }}" class="hard-field-sort">
    @if (! $isTemplate && filled($blockId))
        <input type="hidden" name="options[{{ $idx }}][id]" value="{{ $blockId }}">
    @endif

    <!-- Cuerpo del bloque (Colapsable) -->
    <div class="hard-block-body space-y-3">
        @if ($type === 'text')
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Contenido del párrafo</label>
                <textarea name="options[{{ $idx }}][content]" rows="2"
                          class="hard-field hard-field-content w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                          data-hard-required placeholder="Explica el razonamiento...">{{ $contentVal }}</textarea>
            </div>
        @elseif ($type === 'formula')
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Plantillas de apoyo LaTeX</label>
                <!-- RESTAURADOS TODOS TUS BOTONES ORIGINALES -->
                <div class="flex flex-wrap gap-2 mb-2">
                    <button type="button" class="hard-latex-snippet rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold hover:bg-slate-100" data-snippet="\frac{}{}">Fracción Normal</button>
                    <button type="button" class="hard-latex-snippet rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold hover:bg-slate-100" data-snippet="\frac{[ ]}{[ ]}">Fracción Rellenable</button>
                    <button type="button" class="hard-latex-snippet rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold hover:bg-slate-100" data-snippet="\sqrt{[ ]}">Raíz Rellenable</button>
                    <button type="button" class="hard-latex-snippet rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold hover:bg-slate-100" data-snippet="^{[ ]}">Exponente Rellenable</button>
                    
                    <!-- NUEVOS BOTONES MÁGICOS -->
                    <button type="button" class="hard-latex-snippet rounded-lg border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100" data-snippet="[ ]">Hueco Simple</button>
                    <button type="button" class="hard-latex-snippet rounded-lg border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100" data-snippet="[ ] \times [ ]">Multiplicación</button>
                    <button type="button" class="hard-latex-snippet rounded-lg border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100" data-snippet="[ ]\text{m/s} \times [ ]\text{s}">Mult. con Unidad</button>
                    <button type="button" class="hard-latex-snippet rounded-lg border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100" data-snippet="[ ] \times \frac{[ ]}{[ ]}">Conversión</button>
                    <button type="button" class="hard-latex-snippet rounded-lg border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100" data-snippet="\frac{[ ]}{[ ] + [ ]}">Suma Denominador</button>
                </div>
                
                <textarea name="options[{{ $idx }}][content]" rows="2"
                          class="hard-field hard-field-content hard-latex-input w-full rounded-xl border border-slate-300 px-3 py-2 font-mono text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                          data-hard-required placeholder="v = \frac{[120]}{[15]}">{{ $contentVal }}</textarea>
                
                <p class="mt-1.5 text-xs font-medium text-slate-500">Vista previa (sin corchetes):</p>
                <div class="hard-katex-preview mt-1 min-h-[2.2rem] rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-1.5 text-slate-800 text-sm overflow-x-auto" aria-live="polite"></div>
                
                <div class="mt-2">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Tolerancia para huecos (opcional)</label>
                    <input type="number" step="0.0001" min="0" name="options[{{ $idx }}][tolerance]" value="{{ $tolVal }}"
                           class="hard-field w-full rounded-xl border border-slate-300 px-3 py-1.5 text-sm"
                           placeholder="0">
                </div>
            </div>
        @else
        <div class="mb-3">
                <label class="mb-1 block text-xs font-medium text-slate-600">Título / Etiqueta visible para el estudiante</label>
                <input type="text" name="options[{{ $idx }}][option_text]" value="{{ $labelVal }}"
                       class="hard-field w-full rounded-xl border border-slate-300 px-3 py-1.5 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                       placeholder="Ej: Tiempo de encuentro (te) --- (Déjalo en blanco para usar 'Respuesta final')">
            </div>
            <div class="grid gap-2 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Resultado</label>
                    <input type="text" name="options[{{ $idx }}][content]" value="{{ $contentVal }}"
                           class="hard-field hard-field-content w-full rounded-xl border border-slate-300 px-3 py-1.5 text-sm"
                           data-hard-required placeholder="Ej: 8">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Unidad</label>
                    <input type="text" name="options[{{ $idx }}][unit]" value="{{ $unitVal }}"
                           class="hard-field w-full rounded-xl border border-slate-300 px-3 py-1.5 text-sm"
                           placeholder="Ej: m/s">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Tolerancia</label>
                    <input type="number" step="0.0001" min="0" name="options[{{ $idx }}][tolerance]" value="{{ $tolVal }}"
                           class="hard-field w-full rounded-xl border border-slate-300 px-3 py-1.5 text-sm"
                           placeholder="0">
                </div>
            </div>
        @endif
    </div>
</div>