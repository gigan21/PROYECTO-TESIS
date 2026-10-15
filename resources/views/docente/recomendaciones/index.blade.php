@extends('layouts.docente')

@section('title', 'Recomendaciones')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Recomendaciones del modelo</h1>
            <p class="text-sm text-slate-500">Última recomendación por estudiante · feedback de calidad</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Total</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Revisadas</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['reviewed'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Correctas</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['correct'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Precisión percibida</p>
            <p class="mt-1 text-2xl font-bold text-indigo-700">{{ $stats['accuracy'] !== null ? $stats['accuracy'].'%' : '—' }}</p>
        </div>
    </div>

    <form method="get" class="mb-4 flex flex-wrap items-center gap-3">
        <label class="text-sm font-medium text-slate-700" for="classroom">Paralelo</label>
        <select name="classroom" id="classroom" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach ($classrooms as $c)
                <option value="{{ \App\Support\Classroom::slugFor($c) }}" @selected($classroomFilter === \App\Support\Classroom::slugFor($c))>{{ $c }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Filtrar</button>
    </form>

    @if ($classroomFilter !== '')
        <form method="post" action="{{ route('docente.recomendaciones.refresh_classroom') }}" class="mb-6">
            @csrf
            <input type="hidden" name="classroom" value="{{ $classroomFilter }}">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Recalcular paralelo
            </button>
        </form>
    @endif

    @if ($rows->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-600">
            Aún no hay recomendaciones. Un estudiante debe abrir su inicio, o recalcula un paralelo.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Estudiante</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Paralelo</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Recomendación</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Confianza</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Fecha</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Avisos</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rows as $row)
                        @php
                            $studentAlerts = $alertsByUserId[$row->user_id] ?? [];
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $row->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $row->user?->studentProfile?->classroom ?? '—' }}</td>
                            <td class="px-4 py-3">{{ __('recommendations.'.$row->reason.'.label', [], 'es') }}</td>
                            <td class="px-4 py-3">
                                {{ $row->confidence !== null ? number_format($row->confidence * 100, 0).'%' : '—' }}
                            </td>
                            <td class="px-4 py-3">{{ $row->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 align-top">
                                @if (count($studentAlerts) === 0)
                                    <span class="text-slate-400">—</span>
                                @else
                                    <details class="text-sm">
                                        <summary class="cursor-pointer font-semibold text-amber-800">
                                            ⚠️ {{ count($studentAlerts) }} aviso{{ count($studentAlerts) > 1 ? 's' : '' }}
                                        </summary>
                                        <ul class="mt-2 max-w-md space-y-2 text-slate-700">
                                            @foreach ($studentAlerts as $alert)
                                                <li class="rounded border border-amber-200 bg-amber-50 p-2">
                                                    <p class="font-semibold">{{ $alert['icon'] ?? '' }} {{ $alert['title'] ?? '' }}</p>
                                                    @if (! empty($alert['suggestion']))
                                                        <p class="mt-1 text-xs leading-relaxed">{{ $alert['suggestion'] }}</p>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <form method="post" action="{{ route('docente.recomendaciones.feedback', $row) }}">
                                        @csrf
                                        <input type="hidden" name="is_correct" value="1">
                                        <button type="submit" class="rounded border px-2 py-1 text-xs font-semibold {{ $row->teacher_feedback === true ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-300 text-slate-700' }}">Correcta</button>
                                    </form>
                                    <form method="post" action="{{ route('docente.recomendaciones.feedback', $row) }}">
                                        @csrf
                                        <input type="hidden" name="is_correct" value="0">
                                        <button type="submit" class="rounded border px-2 py-1 text-xs font-semibold {{ $row->teacher_feedback === false ? 'border-red-600 bg-red-50 text-red-800' : 'border-slate-300 text-slate-700' }}">Incorrecta</button>
                                    </form>
                                    <form method="post" action="{{ route('docente.recomendaciones.refresh', $row->user_id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-indigo-600">Recalcular</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
