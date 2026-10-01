<div id="asistencia-resumen" class="grid gap-3 sm:grid-cols-4">
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <p class="text-xs font-bold uppercase text-slate-500">Total</p>
        <p data-resumen="total" class="text-2xl font-extrabold text-slate-900">{{ $resumen['total'] }}</p>
    </div>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <p class="text-xs font-bold uppercase text-emerald-700">Presentes</p>
        <p data-resumen="presentes" class="text-2xl font-extrabold text-emerald-900">{{ $resumen['presentes'] }}</p>
    </div>
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
        <p class="text-xs font-bold uppercase text-rose-700">Ausentes</p>
        <p data-resumen="ausentes" class="text-2xl font-extrabold text-rose-900">{{ $resumen['ausentes'] }}</p>
    </div>
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
        <p class="text-xs font-bold uppercase text-indigo-700">Asistencia</p>
        <p class="text-2xl font-extrabold text-indigo-900"><span data-resumen="porcentaje">{{ $resumen['porcentaje'] }}</span>%</p>
    </div>
</div>
