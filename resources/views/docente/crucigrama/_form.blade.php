@php
    $isEdit = isset($word);
@endphp

<div class="space-y-4">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Respuesta</label>
        <input type="text" name="answer" required maxlength="30"
               value="{{ old('answer', $isEdit ? $word->answer : '') }}"
               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm uppercase">
        @error('answer')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Pista</label>
        <textarea name="clue" rows="3" required maxlength="500"
                  class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">{{ old('clue', $isEdit ? $word->clue : '') }}</textarea>
        @error('clue')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Dificultad</label>
            <select name="difficulty" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
                @foreach (['facil', 'medio', 'dificil'] as $difficulty)
                    <option value="{{ $difficulty }}" @selected(old('difficulty', $isEdit ? $word->difficulty : 'facil') === $difficulty)>{{ ucfirst($difficulty) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Nivel (1-20)</label>
            <input type="number" name="level" min="1" max="20" required
                   value="{{ old('level', $isEdit ? $word->level : 1) }}"
                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
        </div>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Tema (opcional)</label>
        <select name="topic_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm">
            <option value="">Sin tema</option>
            @foreach ($topics as $topic)
                <option value="{{ $topic->id }}" @selected((string) old('topic_id', $isEdit ? $word->topic_id : '') === (string) $topic->id)>{{ $topic->name }}</option>
            @endforeach
        </select>
    </div>
    @if ($isEdit)
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $word->is_active))>
            Palabra activa
        </label>
    @endif
</div>
