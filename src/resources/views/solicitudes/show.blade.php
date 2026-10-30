<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-gray-800">{{ $solicitud->folio }}</h2>
            @can('update', $solicitud)
                <a href="{{ route('solicitudes.edit', $solicitud) }}"
                   class="rounded bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">
                    Editar
                </a>
            @endcan
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded bg-green-50 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
        </div>
    @endif

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            {{-- Detalle --}}
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Estado</p>
                        <p class="mt-1"><x-badge-estado :estado="$solicitud->estado" /></p>
                        @if ($solicitud->estado === \App\Enums\EstadoSolicitud::PendienteRevision)
                            <p class="mt-1 text-xs text-gray-500">Nivel {{ $solicitud->nivel_actual }} de {{ $solicitud->niveles_requeridos }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Tipo</p>
                        <p class="mt-1 text-sm text-gray-800">{{ $solicitud->tipo->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Solicitante</p>
                        <p class="mt-1 text-sm text-gray-800">{{ $solicitud->solicitante->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Prioridad</p>
                        <p class="mt-1"><x-badge-prioridad :prioridad="$solicitud->prioridad" /></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Monto</p>
                        <p class="mt-1 text-sm text-gray-800">{{ $solicitud->monto !== null ? '$'.number_format($solicitud->monto, 2) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Fechas</p>
                        <p class="mt-1 text-sm text-gray-800">
                            Creada: {{ $solicitud->created_at->format('d-m-Y H:i') }}
                            @if ($solicitud->enviada_en) · Enviada: {{ $solicitud->enviada_en->format('d-m-Y H:i') }} @endif
                            @if ($solicitud->resuelta_en) · Resuelta: {{ $solicitud->resuelta_en->format('d-m-Y H:i') }} @endif
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase text-gray-500">Título</p>
                    <p class="mt-1 text-sm text-gray-800">{{ $solicitud->titulo }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase text-gray-500">Descripción</p>
                    <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $solicitud->descripcion }}</p>
                </div>

                @if ($solicitud->datos)
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">Datos específicos</p>
                        <dl class="mt-1 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($solicitud->datos as $key => $value)
                                <div>
                                    <dt class="text-xs text-gray-500">{{ $key }}</dt>
                                    <dd class="text-sm text-gray-800">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>

            {{-- Adjuntos --}}
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">Adjuntos</h3>
                @forelse ($solicitud->adjuntos as $adjunto)
                    <div class="flex items-center justify-between border-b py-2 last:border-0">
                        <div>
                            <p class="text-sm text-gray-800">{{ $adjunto->nombre_original }}</p>
                            <p class="text-xs text-gray-500">{{ $adjunto->mime }} · {{ number_format($adjunto->tamano / 1024, 1) }} KB</p>
                        </div>
                        <a href="{{ route('adjuntos.descargar', $adjunto) }}"
                           class="rounded bg-[#4f7ea6] px-3 py-1 text-xs font-semibold text-white hover:bg-[#3f6a8f]">
                            Descargar
                        </a>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Sin adjuntos.</p>
                @endforelse
            </div>

            {{-- Enviar a aprobación --}}
            @if ($puedeEnviar)
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Enviar a aprobación</h3>
                    <form method="POST" action="{{ route('solicitudes.enviar', $solicitud) }}">
                        @csrf
                        @for ($i = 1; $i <= $nivelesRequeridos; $i++)
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700">Aprobador nivel {{ $i }}</label>
                                <select name="aprobadores[{{ $i }}]" required
                                        class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($aprobadoresDisponibles as $aprobador)
                                        <option value="{{ $aprobador->id }}">{{ $aprobador->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endfor
                        <button type="submit"
                                class="rounded bg-[#4f7ea6] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3f6a8f]">
                            Enviar
                        </button>
                    </form>
                </div>
            @endif

            {{-- Resolver (aprobar/rechazar) --}}
            @can('resolver', $solicitud)
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Resolver</h3>
                    <form method="POST" action="{{ route('solicitudes.aprobar', $solicitud) }}" class="mb-3">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700">Comentario (opcional)</label>
                        <textarea name="comentario" rows="2" maxlength="2000"
                                  class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]"></textarea>
                        <button type="submit"
                                class="mt-2 rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Aprobar
                        </button>
                    </form>
                    <form method="POST" action="{{ route('solicitudes.rechazar', $solicitud) }}">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700">Motivo de rechazo (obligatorio)</label>
                        <textarea name="comentario" rows="2" maxlength="2000" required
                                  class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]"></textarea>
                        <button type="submit"
                                class="mt-2 rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            Rechazar
                        </button>
                    </form>
                </div>
            @endcan

            {{-- Aprobaciones --}}
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">Aprobaciones</h3>
                @forelse ($solicitud->aprobaciones as $aprobacion)
                    <div class="flex items-center justify-between border-b py-2 last:border-0">
                        <div>
                            <p class="text-sm text-gray-800">
                                Nivel {{ $aprobacion->nivel }} · Ciclo {{ $aprobacion->ciclo }} · {{ $aprobacion->aprobador->name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $aprobacion->estado->value }} @if ($aprobacion->decidida_en)· {{ $aprobacion->decidida_en->format('d-m-Y H:i') }}@endif
                            </p>
                            @if ($aprobacion->comentario)
                                <p class="mt-1 text-xs text-gray-600">{{ $aprobacion->comentario }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Sin aprobaciones registradas.</p>
                @endforelse
            </div>

            {{-- Historial --}}
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">Historial</h3>
                @forelse ($solicitud->historial as $item)
                    <div class="border-b py-2 last:border-0">
                        <p class="text-sm text-gray-800">
                            <span class="font-medium">{{ $item->usuario->name }}</span>
                            — {{ ucfirst($item->accion->value) }}
                            <span class="text-xs text-gray-500">({{ $item->created_at->format('d-m-Y H:i') }})</span>
                        </p>
                        @if ($item->comentario)
                            <p class="mt-1 text-xs text-gray-600">{{ $item->comentario }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Sin historial.</p>
                @endforelse
            </div>

            {{-- Comentar --}}
            @can('comentar', $solicitud)
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Comentario</h3>
                    <form method="POST" action="{{ route('solicitudes.comentarios.store', $solicitud) }}">
                        @csrf
                        <textarea name="comentario" rows="2" maxlength="2000" required
                                  class="w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]"></textarea>
                        <button type="submit"
                                class="mt-2 rounded bg-[#4f7ea6] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3f6a8f]">
                            Agregar comentario
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
