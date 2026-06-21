<x-app-layout>
    <x-slot name="header">
        <h1 class="mono-page-title">Поиск друзей</h1>
    </x-slot>

    <div class="page-shell page-stack py-10">
        <form method="get" action="{{ route('search.friends') }}" class="space-y-2">
            <label for="q" class="mono-field-label block">Имя, email или о себе</label>
            <input
                type="search"
                name="q"
                id="q"
                value="{{ $q }}"
                autocomplete="off"
                placeholder="Введите запрос и нажмите Enter…"
                class="mono-search-input"
            />
            <p class="mono-caption">Не менее 2 символов. Отправка по клавише Enter.</p>
        </form>

        @if (mb_strlen($q) > 0 && mb_strlen($q) < 2)
            <p class="mono-empty-state">Введите не менее двух символов для поиска.</p>
        @endif

        @if (mb_strlen($q) >= 2)
            @if ($users->isEmpty())
                <p class="mono-empty-state">Никого не найдено. Попробуйте другой запрос.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($users as $user)
                        <li class="mono-surface mono-surface--soft px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 anomaly-tilt">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $user->avatarUrl() }}" alt="" class="w-11 h-11 rounded-full border object-cover shrink-0 mono-avatar-frame" width="44" height="44" />
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('profile.show', $user) }}" class="mono-strong-link text-sm block truncate">{{ $user->name }}</a>
                                        @include('users._role_badge', ['user' => $user])
                                    </div>
                                    <p class="mono-caption truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('messages.show', $user) }}" class="mono-button-secondary mono-button-secondary--sm">Написать</a>
                                <div data-follow-button-target="{{ $user->id }}">
                                    @include('users._follow_button', ['user' => $user, 'isFollowing' => $followingIds->contains($user->id), 'isSelf' => false])
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>
</x-app-layout>
