<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Nueva solicitud</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="GET" action="{{ route('solicitudes.create') }}" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">Tipo de solicitud</label>
                    <select name="tipo" onchange="this.form.submit()"
                            class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                        <option value="">-- Seleccione --</option>
                        @foreach ($tipos as $t)
                            <option value="{{ $t->id }}" @selected($tipo && $tipo->id === $t->id)>{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                </form>

                <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if ($tipo)
                        <input type="hidden" name="tipo_solicitud_id" value="{{ $tipo->id }}">
                    @else
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tipo de solicitud</label>
                            <select name="tipo_solicitud_id" required
                                    class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                                @foreach ($tipos as $t)
                                    <option value="{{ $t->id }}" @selected(old('tipo_solicitud_id') == $t->id)>{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tipo_solicitud_id')" class="mt-1" />
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Título</label>
                        <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="150"
                               class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                        <x-input-error :messages="$errors->get('titulo')" class="mt-1" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="descripcion" required maxlength="5000" rows="4"
                                  class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Prioridad</label>
                        <select name="prioridad" required
                                class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                            @foreach ($prioridades as $p)
                                <option value="{{ $p->value }}" @selected(old('prioridad') === $p->value)>{{ $p->etiqueta() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('prioridad')" class="mt-1" />
                    </div>

                    @if ($tipo && $tipo->requiere_monto)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Monto</label>
                            <input type="number" name="monto" value="{{ old('monto') }}" required step="0.01" min="0.01"
                                   class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                            <x-input-error :messages="$errors->get('monto')" class="mt-1" />
                        </div>
                    @endif

                    @if ($tipo && $tipo->esquema_campos)
                        <fieldset class="mb-4 rounded border border-gray-200 p-4">
                            <legend class="px-2 text-sm font-medium text-gray-700">Datos específicos</legend>
                            @foreach ($tipo->esquema_campos as $campo)
                                @php $nombre = $campo['nombre']; $old = old("datos.$nombre"); @endphp
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ $campo['etiqueta'] }} @if ($campo['requerido'] ?? false)*@endif
                                    </label>
                                    @switch($campo['tipo'])
                                        @case('textarea')
                                            <textarea name="datos[{{ $nombre }}]" rows="3" @if ($campo['requerido'] ?? false) required @endif
                                                      class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">{{ $old }}</textarea>
                                            @break
                                        @case('select')
                                            <select name="datos[{{ $nombre }}]" @if ($campo['requerido'] ?? false) required @endif
                                                    class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                                                @foreach ($campo['opciones'] ?? [] as $opcion)
                                                    <option value="{{ $opcion }}" @selected($old === $opcion)>{{ $opcion }}</option>
                                                @endforeach
                                            </select>
                                            @break
                                        @case('number')
                                            <input type="number" name="datos[{{ $nombre }}]" value="{{ $old }}" step="any" @if ($campo['requerido'] ?? false) required @endif
                                                   class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                                            @break
                                        @case('date')
                                            <input type="date" name="datos[{{ $nombre }}]" value="{{ $old }}" @if ($campo['requerido'] ?? false) required @endif
                                                   class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                                            @break
                                        @default
                                            <input type="text" name="datos[{{ $nombre }}]" value="{{ $old }}" @if ($campo['requerido'] ?? false) required @endif
                                                   class="mt-1 w-full rounded border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
                                    @endswitch
                                    <x-input-error :messages="$errors->get('datos.'.$nombre)" class="mt-1" />
                                </div>
                            @endforeach
                        </fieldset>
                    @endif

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Adjuntos (máx. 5, 5MB c/u)</label>
                        <input type="file" name="adjuntos[]" multiple
                               accept=".pdf,.png,.jpg,.jpeg,.docx,.xlsx"
                               class="mt-1 block w-full text-sm text-gray-700">
                        <x-input-error :messages="$errors->get('adjuntos')" class="mt-1" />
                        <x-input-error :messages="$errors->get('adjuntos.*')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('solicitudes.index') }}"
                           class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="rounded bg-[#4f7ea6] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3f6a8f]">
                            Guardar borrador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
