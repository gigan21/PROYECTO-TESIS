<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-base font-bold text-slate-800 mb-2">🔄 Restablecer esta pregunta a un estudiante</h3>
    <p class="text-xs text-slate-500 mb-4">Selecciona un alumno para volver a habilitarle únicamente esta pregunta.</p>

    <form action="{{ route('docente.preguntas.reset.single', $question) }}" method="POST" class="flex gap-3">
        @csrf
        <select name="student_id" required class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">-- Seleccionar Estudiante --</option>

            {{-- Asegúrate de que $students esté disponible o cárgalos en el método show del controlador --}}
            @foreach (\App\Models\User::where('role', 'estudiante')->orderBy('name')->get() as $student)
                <option value="{{ $student->id }}">{{ $student->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700">
            Restablecer Pregunta
        </button>
    </form>
</div>