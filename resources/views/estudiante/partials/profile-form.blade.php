<div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
    <div class="border-b border-slate-100 pb-5 mb-6 dark:border-slate-800">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Editar Perfil Gamificado</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">Personaliza tu personaje y asegura tus datos de curso.</p>
    </div>

    <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Nombre Completo y Apodo -->
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="name" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nombre Completo</label>
                <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required 
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-900">
            </div>

            <div>
                <label for="nickname" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Apodo (Nickname)</label>
                <input id="nickname" name="nickname" type="text" value="{{ old('nickname', auth()->user()->studentProfile->nickname ?? '') }}" required 
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-900">
            </div>
        </div>

        <!-- Selector de Avatar -->
         @include('estudiante.partials.items.avatar-selector')
        <!-- Selector de Banner -->
        @include('estudiante.partials.items.banner-selector')
         <!-- Selector de Mascota -->
         @include('estudiante.partials.items.pet-selector')
        <!-- Paralelo -->
        <div>
            <label for="classroom" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Paralelo (4.º de Secundaria)</label>
            <select id="classroom" name="classroom" required 
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500 dark:focus:ring-indigo-900">
                <option value="" disabled>Selecciona tu paralelo...</option>
                @foreach ($classrooms as $item)
                    <option value="{{ $item }}" 
                        {{ (auth()->user()->studentProfile->classroom ?? '') == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Botón Guardar Cambios -->
        <div class="flex justify-end pt-6 border-t border-slate-100 dark:border-slate-800">
            <button type="submit" 
                    class="rounded-xl bg-indigo-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-700 active:scale-95 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>