<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            (() => {
                const savedTheme = localStorage.getItem('mono-theme');
                const theme = savedTheme === 'light' ? 'theme-light' : 'theme-dark';
                document.documentElement.classList.add(theme);
            })();
        </script>

        <title>{{ config('app.name', 'MONO') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased min-h-screen anomaly-root mono-app-shell" data-page="guest">
        <div class="anomaly-scanlines pointer-events-none fixed inset-0 z-50 opacity-[0.04]"></div>
        <div id="mono-toast-stack" class="mono-toast-stack" aria-live="polite" aria-atomic="true"></div>
        <div class="absolute top-4 right-4 z-20">
            <button type="button" class="mono-theme-toggle" data-theme-toggle>Светлая тема</button>
        </div>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 px-4">
            <div class="mb-8">
                <a href="/" class="mono-brand-link text-xl font-medium tracking-[0.2em] uppercase">{{ config('app.name', 'MONO') }}</a>
            </div>

            <div class="w-full sm:max-w-md mono-surface px-6 py-8 backdrop-blur-sm">
                {{ $slot }}
            </div>
        </div>

        @include('layouts.lightbox')
    </body>
</html>
