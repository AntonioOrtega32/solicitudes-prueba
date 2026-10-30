<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="mb-6 text-xl font-semibold text-gray-800">Iniciar sesión</h2>

    <form method="POST" action="{{ route('login') }}"
          x-data="{ correo: @js(old('email', '')), clave: '', enviando: false }"
          @submit="enviando = true">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
            <input id="email" name="email" type="email" x-model="correo" required autofocus autocomplete="username"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
            <input id="password" name="password" type="password" x-model="clave" required autocomplete="current-password"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6593b9] focus:ring-[#6593b9]">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="mt-4 inline-flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-gray-300 text-[#4f7ea6] shadow-sm focus:ring-[#6593b9]">
            <span class="ms-2 text-sm text-gray-600">Mantener sesión iniciada</span>
        </label>

        <button type="submit" :disabled="enviando"
                class="mt-6 w-full rounded-md bg-[#4f7ea6] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#3f6a8f] focus:outline-none focus:ring-2 focus:ring-[#6593b9] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
            <span x-show="! enviando">Entrar</span>
            <span x-show="enviando" x-cloak>Ingresando…</span>
        </button>

        @if (app()->environment('local'))
            <div class="mt-6 border-t pt-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Usuarios de demostración
                </p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ([
                        ['Carla', 'Colaboradora', 'colaborador1@example.com'],
                        ['Mario', 'Colaborador', 'colaborador2@example.com'],
                        ['Ana', 'Aprobadora', 'aprobador1@example.com'],
                        ['Luis', 'Aprobador', 'aprobador2@example.com'],
                        ['Admin', 'Administrador', 'admin@example.com'],
                    ] as [$nombre, $rol, $correo])
                        <button type="button" @click="correo = '{{ $correo }}'; clave = 'password'"
                                class="rounded border border-gray-200 px-3 py-2 text-left text-xs hover:bg-gray-50">
                            <span class="block font-semibold text-gray-800">{{ $nombre }}</span>
                            <span class="text-gray-500">{{ $rol }}</span>
                        </button>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-400">Esto solo es visible en entorno local. Rellena el formulario y luego pulsa Entrar.</p>
            </div>
        @endif
    </form>
</x-guest-layout>