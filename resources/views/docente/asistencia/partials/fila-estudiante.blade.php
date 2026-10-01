@php
    $profile = $estudiante->studentProfile;
    $registro = $registros->get($estudiante->id);
    $estado = $registro?->status?->value;
    $avatar = $profile->avatar_name ?? 'avatar1.jpg';
    $abierta = $session->isOpen();
    try {
        $paraleloVisible = \App\Support\Classroom::normalize((string) $profile->classroom);
    } catch (\InvalidArgumentException) {
        $paraleloVisible = $profile->classroom;
    }
@endphp

<tr data-student-row="{{ $estudiante->id }}" class="border-t border-slate-100">
    <td class="px-3 py-3">
    <div class="relative inline-block">
        <img src="{{ asset('images/avatars/' . $avatar) }}" 
             alt="Avatar de {{ $estudiante->name }}" 
             class="h-10 w-10 rounded-full object-cover ring-2 ring-indigo-100">
        
        {{-- Punto de estado superpuesto al avatar --}}
        <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full border-2 border-white {{ $estudiante->is_active ? 'bg-green-500' : 'bg-gray-400' }}"
              title="{{ $estudiante->is_active ? 'En línea' : 'Desconectado' }}">
        </span>
    </div>
</td>
    <td class="px-3 py-3 font-semibold text-slate-900">{{ $estudiante->name }}</td>
    <td class="px-3 py-3 text-slate-600">{{ $profile->nickname ?? '—' }}</td>
    <td class="px-3 py-3 text-right font-bold text-amber-800">{{ $profile->level ?? 1 }}</td>
    <td class="px-3 py-3 text-right font-bold text-emerald-800">{{ $profile->xp_points ?? 0 }}</td>
    <td class="px-3 py-3 text-sm text-slate-500">{{ $estudiante->email }}</td>
    <td class="px-3 py-3 text-sm font-medium">{{ $paraleloVisible }}</td>
    <td class="px-3 py-3">
        <span data-attendance-label class="rounded-full px-2.5 py-1 text-xs font-bold uppercase
            {{ $estado === 'presente' ? 'bg-emerald-100 text-emerald-800' : ($estado === 'ausente' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600') }}">
            {{ $estado ? ucfirst($estado) : 'Sin marcar' }}
        </span>
        @if ($registro)
            <p data-attendance-time class="mt-1 text-[11px] text-slate-400">{{ $registro->marked_at->format('d/m/Y H:i') }}</p>
        @else
            <p data-attendance-time class="mt-1 text-[11px] text-slate-400"></p>
        @endif
    </td>
    <td class="px-3 py-3">
        @if ($abierta)
            <div class="flex flex-wrap gap-2">
                <button type="button"
                        data-attendance-action="presente"
                        data-student-id="{{ $estudiante->id }}"
                        class="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">
                    Presente
                </button>
                <button type="button"
                        data-attendance-action="ausente"
                        data-student-id="{{ $estudiante->id }}"
                        class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-rose-700">
                    Ausente
                </button>
                <form method="POST" action="{{ route('docente.asistencia.exclusiones.store', $session) }}"
                      onsubmit="return confirm('Esta cuenta seguirá existiendo en el sistema. Solo se ocultará de tu gestión de asistencia. ¿Continuar?');">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $estudiante->id }}">
                    <button type="submit" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                        Excluir
                    </button>
                </form>
            </div>
        @endif
    </td>
</tr>
