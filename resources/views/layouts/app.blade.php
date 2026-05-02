<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-[linear-gradient(180deg,_#f8fafc_0%,_#eef2f7_100%)] text-slate-900">
            <div
                x-cloak
                x-show="sidebarOpen"
                class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            <div class="relative min-h-screen lg:flex">
                <div
                    class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transition-transform duration-300 lg:static lg:z-auto lg:translate-x-0"
                    :class="{ 'translate-x-0': sidebarOpen }"
                >
                    <div class="h-full">
                        @include('layouts.sidebar')
                    </div>
                </div>

                <div class="flex min-w-0 flex-1 flex-col">
                    @include('layouts.topbar', ['header' => $header ?? null])

                    <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                        <div class="mx-auto max-w-7xl space-y-6">
                            <x-flash-message />
                            {{ $slot }}
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
