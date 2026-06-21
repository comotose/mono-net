<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="mono-page-title">Сообщения</h1>

            <div class="mono-segmented-control" role="group" aria-label="Фильтр сообщений">
                <a href="{{ route('messages.index') }}" class="mono-segmented-control__item {{ $onlyUnread ? '' : 'is-active' }}">
                    Все
                </a>
                <a href="{{ route('messages.index', ['unread' => 1]) }}" class="mono-segmented-control__item {{ $onlyUnread ? 'is-active' : '' }}">
                    Непрочитанные
                    @if ($unreadDialogsCount > 0)
                        <span class="mono-counter-pill">{{ $unreadDialogsCount > 99 ? '99+' : $unreadDialogsCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </x-slot>

    <div class="page-shell py-10">
        @if ($partners->isEmpty())
            <p class="mono-empty-state">
                {{ $onlyUnread ? 'Непрочитанных диалогов нет.' : 'Нет диалогов. Откройте профиль пользователя и нажмите «Написать сообщение».' }}
            </p>
        @else
            <ul class="mono-list-shell divide-y">
                @foreach ($partners as $partner)
                    <li>
                        <a href="{{ route('messages.show', $partner) }}" class="mono-list-link flex items-center gap-3 px-4 py-3">
                            <img src="{{ $partner->avatarUrl() }}" alt="" class="h-11 w-11 rounded-full border object-cover mono-avatar-frame" />
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="mono-body-sm font-medium">{{ $partner->name }}</span>
                                    @include('users._role_badge', ['user' => $partner])
                                </span>
                            </span>
                            @if ($partner->unread_messages_count > 0)
                                <span class="mono-counter-pill">
                                    {{ $partner->unread_messages_count > 99 ? '99+' : $partner->unread_messages_count }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
