@php
    $usuario = Auth::user();

    $enlaces = [[
        'texto' => match ($usuario->rol) {
            \App\Enums\Rol::Colaborador => 'Mis solicitudes',
            \App\Enums\Rol::Aprobador => 'Asignadas a mí',
            \App\Enums\Rol::Administrador => 'Todas las solicitudes',
        },
        'url' => route('solicitudes.index'),
        'activo' => request()->routeIs('solicitudes.index') && ! request()->boolean('por_resolver'),
    ]];

    if ($usuario->can('create', \App\Models\Solicitud::class)) {
        $enlaces[] = ['texto' => 'Nueva solicitud', 'url' => route('solicitudes.create'), 'activo' => request()->routeIs('solicitudes.create')];
    }

    if ($usuario->esAprobador()) {
        $enlaces[] = ['texto' => 'Por resolver', 'url' => route('solicitudes.index', ['por_resolver' => 1]), 'activo' => request()->boolean('por_resolver')];
    }
@endphp

<nav x-data="{ open: false }" class="bg-[#6593b9] text-white shadow">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('solicitudes.index') }}" class="text-lg font-bold tracking-tight">Solicitudes</a>

                <div class="hidden gap-1 sm:flex">
                    @foreach ($enlaces as $enlace)
                        <a href="{{ $enlace['url'] }}"
                           class="rounded px-3 py-2 text-sm font-medium {{ $enlace['activo'] ? 'bg-[#3f6a8f]' : 'hover:bg-[#4f7ea6]' }}">
                            {{ $enlace['texto'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Usuario (escritorio) --}}
            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded px-3 py-2 text-sm font-medium hover:bg-[#4f7ea6] focus:outline-none">
                            <span>{{ $usuario->name }}</span>
                            <span class="rounded bg-white/20 px-2 py-0.5 text-xs">{{ $usuario->rol->etiqueta() }}</span>
                            <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburguesa (móvil) --}}
            <button @click="open = ! open" class="rounded p-2 hover:bg-[#4f7ea6] focus:outline-none sm:hidden">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Menú móvil --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-white/20 sm:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($enlaces as $enlace)
                <a href="{{ $enlace['url'] }}"
                   class="block rounded px-3 py-2 text-sm font-medium {{ $enlace['activo'] ? 'bg-[#3f6a8f]' : 'hover:bg-[#4f7ea6]' }}">
                    {{ $enlace['texto'] }}
                </a>
            @endforeach
        </div>

        <div class="border-t border-white/20 px-4 py-3">
            <p class="text-sm font-medium">{{ $usuario->name }} · {{ $usuario->rol->etiqueta() }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="text-sm underline">Cerrar sesión</button>
            </form>
        </div>
    </div>
</nav>