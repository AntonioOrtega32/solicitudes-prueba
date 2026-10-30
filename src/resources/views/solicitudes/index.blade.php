<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-gray-800">Reporte de solicitudes</h2>

            @can('create', App\Models\Solicitud::class)
                <a href="{{ route('solicitudes.create') }}"
                   class="rounded bg-[#4f7ea6] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3f6a8f]">
                    Nueva solicitud
                </a>
            @endcan
        </div>
    </x-slot>

    @php
        $estadoActual = $filtros['estado'] ?? null;
        $porResolver = request()->boolean('por_resolver');
        $tab = fn (bool $activo) => $activo ? 'bg-[#4f7ea6] text-white' : 'text-[#3f6a8f] hover:bg-[#eef4f9]';
        $campo = 'w-full rounded border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]';
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">

                {{-- Pestañas --}}
                <nav class="mb-6 flex flex-wrap gap-2">
                    <a href="{{ route('solicitudes.index') }}"
                       class="rounded px-4 py-2 text-sm font-medium {{ $tab(! $estadoActual && ! $porResolver) }}">
                        Todas <span class="ml-1 text-xs opacity-80">({{ $conteos->sum() }})</span>
                    </a>

                    @if (auth()->user()->esAprobador())
                        <a href="{{ route('solicitudes.index', ['por_resolver' => 1]) }}"
                           class="rounded px-4 py-2 text-sm font-medium {{ $tab($porResolver) }}">
                            Por resolver
                        </a>
                    @endif

                    @foreach ($estados as $estado)
                        <a href="{{ route('solicitudes.index', ['estado' => $estado->value]) }}"
                           class="rounded px-4 py-2 text-sm font-medium {{ $tab($estadoActual === $estado->value) }}">
                            {{ $estado->etiqueta() }}
                            <span class="ml-1 text-xs opacity-80">({{ $conteos->get($estado->value, 0) }})</span>
                        </a>
                    @endforeach
                </nav>

                {{-- Formulario de filtros (los inputs de la tabla se enlazan con form="filtros") --}}
                <form id="filtros" method="GET" action="{{ route('solicitudes.index') }}">
                    @if ($estadoActual)
                        <input type="hidden" name="estado" value="{{ $estadoActual }}">
                    @endif
                    @if ($porResolver)
                        <input type="hidden" name="por_resolver" value="1">
                    @endif
                </form>

                @if ($errors->any() && ! $errors->has('regla'))
                    <p class="mb-3 text-sm text-red-600">{{ $errors->first() }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#c4e6f8] text-left text-gray-800">
                                <th class="px-3 py-3 font-semibold">Folio</th>
                                <th class="px-3 py-3 font-semibold">Fecha</th>
                                <th class="px-3 py-3 font-semibold">Solicitante</th>
                                <th class="px-3 py-3 font-semibold">Tipo</th>
                                <th class="px-3 py-3 font-semibold">Asunto</th>
                                <th class="px-3 py-3 font-semibold">Prioridad</th>
                                <th class="px-3 py-3 text-right font-semibold">Monto</th>
                                <th class="px-3 py-3 font-semibold">Estado</th>
                                <th class="px-3 py-3 text-center font-semibold">Opciones</th>
                            </tr>

                            {{-- Fila de filtros --}}
                            <tr class="border-b bg-[#c4e6f8]/50 align-top">
                                <th class="px-3 py-2">
                                    <input type="text" name="q" form="filtros" value="{{ $filtros['q'] ?? '' }}"
                                           placeholder="Folio o asunto" class="{{ $campo }}">
                                </th>
                                <th class="px-3 py-2">
                                    <div class="space-y-1">
                                        <input type="date" name="desde" form="filtros" value="{{ $filtros['desde'] ?? '' }}" class="{{ $campo }}" title="Desde">
                                        <input type="date" name="hasta" form="filtros" value="{{ $filtros['hasta'] ?? '' }}" class="{{ $campo }}" title="Hasta">
                                    </div>
                                </th>
                                <th class="px-3 py-2"></th>
                                <th class="px-3 py-2">
                                    <select name="tipo" form="filtros" class="{{ $campo }}">
                                        <option value="">Todos</option>
                                        @foreach ($tipos as $tipo)
                                            <option value="{{ $tipo->id }}" @selected(($filtros['tipo'] ?? null) == $tipo->id)>{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="px-3 py-2"></th>
                                <th class="px-3 py-2">
                                    <select name="prioridad" form="filtros" class="{{ $campo }}">
                                        <option value="">Todas</option>
                                        @foreach ($prioridades as $prioridad)
                                            <option value="{{ $prioridad->value }}" @selected(($filtros['prioridad'] ?? null) === $prioridad->value)>{{ $prioridad->etiqueta() }}</option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="px-3 py-2"></th>
                                <th class="px-3 py-2"></th>
                                <th class="px-3 py-2 text-center">
                                    <button type="submit" form="filtros"
                                            class="rounded bg-[#4f7ea6] px-3 py-1 text-xs font-semibold text-white hover:bg-[#3f6a8f]">
                                        Filtrar
                                    </button>
                                    <a href="{{ route('solicitudes.index', array_filter(['estado' => $estadoActual, 'por_resolver' => $porResolver ?: null])) }}"
                                       class="ml-1 text-xs text-[#3f6a8f] underline">Limpiar</a>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($solicitudes as $solicitud)
                                @php $urgente = $solicitud->esUrgente(); @endphp
                                <tr class="border-b hover:bg-sky-50 {{ $urgente ? 'bg-red-50' : 'odd:bg-gray-50' }}">
                                    <td class="whitespace-nowrap px-3 py-3 font-medium {{ $urgente ? 'border-l-4 border-red-600' : '' }}">
                                        <a href="{{ route('solicitudes.show', $solicitud) }}" class="text-[#3f6a8f] hover:underline">
                                            {{ $solicitud->folio }}
                                        </a>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3">{{ $solicitud->created_at->format('d-m-Y') }}</td>
                                    <td class="px-3 py-3">{{ $solicitud->solicitante->name }}</td>
                                    <td class="px-3 py-3">{{ $solicitud->tipo->nombre }}</td>
                                    <td class="max-w-xs truncate px-3 py-3" title="{{ $solicitud->titulo }}">{{ $solicitud->titulo }}</td>
                                    <td class="px-3 py-3"><x-badge-prioridad :prioridad="$solicitud->prioridad" /></td>
                                    <td class="whitespace-nowrap px-3 py-3 text-right">
                                        {{ $solicitud->monto !== null ? '$'.number_format($solicitud->monto, 2) : '—' }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <x-badge-estado :estado="$solicitud->estado" />
                                        @if ($solicitud->estado === \App\Enums\EstadoSolicitud::PendienteRevision)
                                            <p class="mt-1 text-xs text-gray-500">
                                                Nivel {{ $solicitud->nivel_actual }} de {{ $solicitud->niveles_requeridos }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-center">
                                        <a href="{{ route('solicitudes.show', $solicitud) }}"
                                           class="rounded bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700">Ver</a>
                                        @can('update', $solicitud)
                                            <a href="{{ route('solicitudes.edit', $solicitud) }}"
                                               class="rounded bg-cyan-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-cyan-700">Editar</a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-10 text-center text-gray-500">
                                        No se encontraron solicitudes con estos criterios.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $solicitudes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>