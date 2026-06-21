<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (() => {
            const savedTheme = localStorage.getItem('mono-theme');
            const theme = savedTheme === 'light' ? 'theme-light' : 'theme-dark';
            document.documentElement.classList.add(theme);
        })();
    </script>
    <title>{{ config('app.name', 'MONO') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center px-4 anomaly-root mono-app-shell">
    <div class="anomaly-scanlines pointer-events-none fixed inset-0 z-50 opacity-[0.025]"></div>
    <div class="absolute top-4 right-4 z-20">
        <button type="button" class="mono-theme-toggle" data-theme-toggle>Светлая тема</button>
    </div>
    <div class="max-w-xl text-center space-y-8 relative z-10 mono-surface p-8 sm:p-10">
        <h1 class="text-3xl sm:text-4xl font-medium tracking-[0.35em] uppercase glitch-hover glitch-text">{{ config('app.name', 'MONO') }}</h1>
        <p class="mono-body-sm leading-relaxed">
            Локанично. Просто. Удобно
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @auth
                <a href="{{ route('feed.index') }}" class="mono-button-secondary">Лента</a>
            @else
                <a href="{{ route('login') }}" class="mono-button-primary">Войти</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="mono-button-secondary">Регистрация</a>
                @endif
            @endauth
        </div>
    </div>
</body>
</html>
