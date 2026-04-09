<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
            <meta name="user-id" content="{{ auth()->id() }}">
        @endauth

        <title>{{ $title ?? config('app.name', 'MONO') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts')
    </head>
    <body class="font-mono antialiased bg-black text-white min-h-screen anomaly-root">
        <div class="anomaly-scanlines pointer-events-none fixed inset-0 z-50 opacity-[0.025]"></div>
        <div class="anomaly-noise pointer-events-none fixed inset-0 z-40 mix-blend-overlay opacity-[0.04]"></div>

        @include('layouts.navigation')

        @if (isset($header))
            <header class="border-b border-white/10">
                <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="relative z-10">
            {{ $slot }}
        </main>
    </body>
</html>
