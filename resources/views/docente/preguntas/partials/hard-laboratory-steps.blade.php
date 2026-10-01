@php
    $hardSteps = $hardSteps ?? [['step_label' => '', 'option_text' => '', 'hint_formula' => '']];
@endphp

<div id="hard-lab-panel" class="hidden space-y-4 rounded-2xl border-2 border-dashed border-amber-200 bg-amber-50/50 p-6">
    <div>
        <h3 class="text-base font-bold text-amber-900">Pasos de Laboratorio</h3>
        <p class="mt-1 text-sm text-amber-800/90">
            Define el procedimiento paso a paso. En cada paso indica la <strong>respuesta numérica con unidad</strong> (ej. <code class="rounded bg-white px-1">12.5 s</code>).
            La fórmula es opcional (útil para pistas con XP más adelante).
        </p>
    </div>

    <div id="hard-steps-list" class="space-y-4">
        @foreach ($hardSteps as $index => $step)
            <div class="hard-step-card rounded-xl border border-amber-200 bg-white p-4 shadow-sm" data-step-index="{{ $index }}">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <span class="hard-step-number text-sm font-bold text-indigo-700">Paso {{ $index + 1 }}</span>
                    <button type="button"
                            class="hard-remove-step text-xs font-semibold text-red-600 hover:text-red-800 {{ count($hardSteps) <= 1 ? 'hidden' : '' }}">
                        Quitar paso
                    </button>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Etiqueta del paso</label>
                        <input type="text"
                               name="options[{{ $index }}][step_label]"
                               value="{{ old("options.$index.step_label", $step['step_label'] ?? '') }}"
                               placeholder="Ej: Tiempo de encuentro"
                               class="hard-field w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"
                               data-hard-required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Respuesta exacta (número + unidad)</label>
                        <input type="text"
                               name="options[{{ $index }}][option_text]"
                               value="{{ old("options.$index.option_text", $step['option_text'] ?? '') }}"
                               placeholder="Ej: 12.5 s"
                               class="hard-field w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"
                               data-hard-required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Fórmula (opcional)</label>
                        <input type="text"
                               name="options[{{ $index }}][hint_formula]"
                               value="{{ old("options.$index.hint_formula", $step['hint_formula'] ?? '') }}"
                               placeholder="Ej: t = d / v"
                               class="hard-field w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button"
            id="hard-add-step"
            class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-100">
        + Añadir paso numérico
    </button>
</div>

<template id="hard-step-template">
    <div class="hard-step-card rounded-xl border border-amber-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-2">
            <span class="hard-step-number text-sm font-bold text-indigo-700"></span>
            <button type="button" class="hard-remove-step text-xs font-semibold text-red-600 hover:text-red-800">
                Quitar paso
            </button>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">Etiqueta del paso</label>
                <input type="text" name="" placeholder="Ej: Tiempo de encuentro"
                       class="hard-field w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm" data-hard-required>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Respuesta exacta (número + unidad)</label>
                <input type="text" name="" placeholder="Ej: 12.5 s"
                       class="hard-field w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm" data-hard-required>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Fórmula (opcional)</label>
                <input type="text" name="" placeholder="Ej: t = d / v"
                       class="hard-field w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
            </div>
        </div>
    </div>
</template>
