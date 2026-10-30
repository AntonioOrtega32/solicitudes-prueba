<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
     <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Solicitudes') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
          <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 py-8">
            <div class="mb-6 text-center">
                <a href="/">
                  <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#6593b9] text-white shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M9 12l2 2 4-4" />
                    </svg>
                </div>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Sistema de Solicitudes</h1>
                <p class="mt-1 text-sm text-gray-500">Gestión y aprobación de solicitudes internas</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        <script>
            document.addEventListener('submit', function (e) {
                e.target.querySelectorAll('button[type="submit"]').forEach(function (btn) { btn.disabled = true; });
            }, true);
        </script>
    </body>
</html>
