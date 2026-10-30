@props(['prioridad'])

@php
    $clases = match ($prioridad) {
        \App\Enums\Prioridad::Baja => 'bg-slate-100 text-slate-700',
        \App\Enums\Prioridad::Media => 'bg-sky-100 text-sky-800',
        \App\Enums\Prioridad::Alta => 'bg-orange-100 text-orange-800',
        \App\Enums\Prioridad::Urgente => 'bg-red-600 text-white',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded px-2 py-0.5 text-xs font-bold', $clases]) }}>
    @if ($prioridad === \App\Enums\Prioridad::Urgente)
        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
    @endif
    {{ $prioridad === \App\Enums\Prioridad::Urgente ? 'URGENTE' : $prioridad->etiqueta() }}
</span>