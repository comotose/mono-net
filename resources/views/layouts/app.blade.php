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
        @auth
            <meta name="user-id" content="{{ auth()->id() }}">
            <meta name="notifications-unread-url" content="{{ route('notifications.unread') }}">
        @endauth

        <title>{{ $title ?? config('app.name', 'MONO') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts')
    </head>
    <body class="antialiased min-h-screen anomaly-root mono-app-shell">
        <div class="anomaly-scanlines pointer-events-none fixed inset-0 z-50 opacity-[0.025]"></div>
        <div class="anomaly-noise pointer-events-none fixed inset-0 z-40 mix-blend-overlay opacity-[0.04]"></div>

        @include('layouts.navigation')

        <div id="mono-toast-stack" class="mono-toast-stack" aria-live="polite" aria-atomic="true"></div>

        @php
            $statusMessages = [
                'message-sent' => 'Сообщение отправлено.',
                'followed' => 'Подписка оформлена.',
                'unfollowed' => 'Подписка отменена.',
                'comment-added' => 'Комментарий добавлен.',
                'comment-deleted' => 'Комментарий удален.',
                'role-updated' => 'Роль обновлена.',
            ];
            $statusText = session('status') ? ($statusMessages[session('status')] ?? null) : null;
        @endphp
        @if ($statusText)
            <div class="page-shell pt-4 relative z-20">
                <p class="mono-alert">{{ $statusText }}</p>
            </div>
        @endif

        @if (isset($header))
            <header class="mono-header-bar">
                <div class="page-shell py-6">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="relative z-10">
            {{ $slot }}
        </main>

        @include('layouts.lightbox')
    </body>
</html>
