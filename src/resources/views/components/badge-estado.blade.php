@props(['estado'])

@php
    $clases = match ($estado) {
        \App\Enums\EstadoSolicitud::Borrador => 'bg-gray-100 text-gray-700 ring-gray-300',
        \App\Enums\EstadoSolicitud::PendienteRevision => 'bg-amber-50 text-amber-800 ring-amber-300',
        \App\Enums\EstadoSolicitud::Aprobada => 'bg-green-50 text-green-800 ring-green-300',
        \App\Enums\EstadoSolicitud::Rechazada => 'bg-red-50 text-red-800 ring-red-300',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset', $clases]) }}>
    {{ $estado->etiqueta() }}
</span>