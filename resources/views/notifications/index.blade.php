<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="mono-page-title">Уведомления</h1>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="mono-button-secondary mono-button-secondary--sm" data-enable-browser-notifications>
                    <i class="bi bi-bell"></i>
                    <span>Push</span>
                </button>
                @if (auth()->user()->unreadNotifications()->count() > 0)
                    <form method="post" action="{{ route('notifications.read-all') }}" data-async-read-all-form>
                        @csrf
                        <button type="submit" class="mono-button-secondary mono-button-secondary--sm">
                            <i class="bi bi-check2-all"></i>
                            <span>Прочитать все</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="page-shell py-10">
        @if (session('status') === 'notifications-read')
            <p class="mono-alert mb-6">Все уведомления помечены прочитанными.</p>
        @endif

        @if ($notifications->isEmpty())
            <p class="mono-empty-state">Пока нет уведомлений.</p>
        @else
            <ul class="mono-list-shell divide-y" id="notifications-list">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = $notification->read_at === null;
                        $data = $notification->data;
                    @endphp
                    <li id="notification-{{ $notification->id }}" class="px-4 py-3 {{ $isUnread ? 'mono-notification--unread' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="mono-body-sm">{{ $data['title'] ?? 'Уведомление' }}</p>
                                <p class="mono-caption">{{ $data['text'] ?? '' }}</p>
                                <p class="mono-caption">
                                    {{ $notification->created_at->translatedFormat('d.m.Y H:i') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
                                @if (!empty($data['url']))
                                    <a href="{{ $data['url'] }}" class="mono-quiet-link inline-flex items-center gap-2"><i class="bi bi-box-arrow-up-right"></i><span>Открыть</span></a>
                                @endif
                                @if ($isUnread)
                                    <form method="post" action="{{ route('notifications.read', $notification->id) }}" data-async-notification-read-form>
                                        @csrf
                                        <button type="submit" class="mono-quiet-link inline-flex items-center gap-2">
                                            <i class="bi bi-check2"></i>
                                            <span>Прочитано</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mono-pagination-wrap pt-4">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
