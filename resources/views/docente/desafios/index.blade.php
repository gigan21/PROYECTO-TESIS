@extends('layouts.docente')

@section('title', 'Salas de desafío')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Salas de desafío</h1>
            <p class="text-sm text-slate-500">Desafíos gamificados por paralelo.</p>
        </div>
        <a href="{{ route('docente.desafios.create') }}" class="inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
            Nuevo desafío
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Paralelo</th>
                    <th class="px-4 py-3">Código</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Preguntas</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rooms as $room)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $room->title }}</td>
                        <td class="px-4 py-3">{{ $room->classroom }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $room->code }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match ($room->status->value) {
                                    'active' => 'bg-emerald-100 text-emerald-800',
                                    'finished' => 'bg-slate-200 text-slate-600',
                                    default => 'bg-amber-100 text-amber-800',
                                };
                            @endphp
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $badge }}">{{ $room->status->value }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $room->questions_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('docente.desafios.show', $room) }}" class="text-indigo-600 hover:underline">Gestionar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aún no has creado desafíos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rooms->links() }}</div>
@endsection
