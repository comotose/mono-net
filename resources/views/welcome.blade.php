<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MONO') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-mono antialiased bg-black text-white min-h-screen flex flex-col items-center justify-center px-4 anomaly-root">
    <div class="anomaly-scanlines pointer-events-none fixed inset-0 z-50 opacity-[0.025]"></div>
    <div class="max-w-md text-center space-y-8 relative z-10">
        <h1 class="text-3xl sm:text-4xl font-medium tracking-[0.35em] uppercase glitch-hover glitch-text">{{ config('app.name', 'MONO') }}</h1>
        <p class="text-sm text-white/50 leading-relaxed">
            Локанично. Просто. Удобно
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @auth
                <a href="{{ route('feed.index') }}" class="glitch-hover px-6 py-3 border border-white text-sm uppercase tracking-widest hover:bg-white hover:text-black transition-colors duration-300">Лента</a>
            @else
                <a href="{{ route('login') }}" class="glitch-hover px-6 py-3 bg-white text-black text-sm uppercase tracking-widest hover:bg-white/90 transition-colors duration-300">Войти</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="glitch-hover px-6 py-3 border border-white/40 text-sm uppercase tracking-widest text-white/80 hover:border-white hover:text-white transition-colors duration-300">Регистрация</a>
                @endif
            @endauth
        </div>
    </div>
</body>
</html>
