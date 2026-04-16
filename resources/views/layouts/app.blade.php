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

        @php
            $statusMessages = [
                'message-sent' => 'Сообщение отправлено.',
                'followed' => 'Подписка оформлена.',
                'unfollowed' => 'Подписка отменена.',
                'comment-added' => 'Комментарий добавлен.',
                'comment-deleted' => 'Комментарий удален.',
            ];
            $statusText = session('status') ? ($statusMessages[session('status')] ?? null) : null;
        @endphp
        @if ($statusText)
            <div class="w-full px-4 sm:px-6 lg:px-8 pt-4 relative z-20">
                <p class="text-sm text-white/70 border border-white/15 bg-black/70 px-3 py-2">{{ $statusText }}</p>
            </div>
        @endif

        @if (isset($header))
            <header class="border-b border-white/10">
                <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="relative z-10">
            {{ $slot }}
        </main>
    </body>
</html>
