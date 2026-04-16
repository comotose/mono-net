<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="font-medium text-lg text-white tracking-tight glitch-hover inline-block">Уведомления</h1>
            @if (auth()->user()->unreadNotifications()->count() > 0)
                <form method="post" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-white/60 hover:text-white border border-white/20 px-3 py-1">
                        Прочитать все
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-10">
        @if (session('status') === 'notifications-read')
            <p class="text-sm text-white/60 border border-white/10 px-3 py-2 mb-6">Все уведомления помечены прочитанными.</p>
        @endif

        @if ($notifications->isEmpty())
            <p class="text-sm text-white/50">Пока нет уведомлений.</p>
        @else
            <ul class="divide-y divide-white/10 border border-white/10">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = $notification->read_at === null;
                        $data = $notification->data;
                    @endphp
                    <li class="px-4 py-3 {{ $isUnread ? 'bg-white/[0.03]' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="text-sm text-white">{{ $data['title'] ?? 'Уведомление' }}</p>
                                <p class="text-xs text-white/60">{{ $data['text'] ?? '' }}</p>
                                <p class="text-[11px] text-white/35">
                                    {{ $notification->created_at->translatedFormat('d.m.Y H:i') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if (!empty($data['url']))
                                    <a href="{{ $data['url'] }}" class="text-xs text-white/70 hover:text-white">Открыть</a>
                                @endif
                                @if ($isUnread)
                                    <form method="post" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-white/50 hover:text-white">
                                            Прочитано
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="text-white/40 text-xs pt-4">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
