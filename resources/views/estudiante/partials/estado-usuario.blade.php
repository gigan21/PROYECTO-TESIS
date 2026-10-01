@props(['user'])

@if($user->is_active)
    <span class="inline-flex items-center gap-1" title="En línea">
        <span style="display:inline-block; width:10px; height:10px; background:#22c55e; border-radius:50%; box-shadow:0 0 4px #22c55e;"></span>
        <span class="text-xs text-green-600">En línea</span>
    </span>
@else
    <span class="inline-flex items-center gap-1" title="Desconectado">
        <span style="display:inline-block; width:10px; height:10px; background:#9ca3af; border-radius:50%;"></span>
        <span class="text-xs text-gray-500">Desconectado</span>
    </span>
@endif