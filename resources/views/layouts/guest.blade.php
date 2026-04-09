<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MONO') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-mono antialiased bg-black text-white min-h-screen anomaly-root" data-page="guest">
        <div class="anomaly-scanlines pointer-events-none fixed inset-0 z-50 opacity-[0.04]"></div>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 px-4">
            <div class="mb-8">
                <a href="/" class="glitch-hover text-xl font-medium tracking-[0.2em] uppercase">{{ config('app.name', 'MONO') }}</a>
            </div>

            <div class="w-full sm:max-w-md border border-white/15 bg-black/80 px-6 py-8 backdrop-blur-sm">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
