<x-app-layout>
    <x-slot name="header">
        <h1 class="font-medium text-lg text-white tracking-tight glitch-hover inline-block">Поиск друзей</h1>
    </x-slot>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <form method="get" action="{{ route('search.friends') }}" class="space-y-2">
            <label for="q" class="block text-xs uppercase tracking-widest text-white/40">Имя, email или о себе</label>
            <input
                type="search"
                name="q"
                id="q"
                value="{{ $q }}"
                autocomplete="off"
                placeholder="Введите запрос и нажмите Enter…"
                class="mono-search-input"
            />
            <p class="text-[11px] text-white/30">Не менее 2 символов. Отправка по клавише Enter.</p>
        </form>

        @if (mb_strlen($q) > 0 && mb_strlen($q) < 2)
            <p class="text-sm text-white/45">Введите не менее двух символов для поиска.</p>
        @endif

        @if (mb_strlen($q) >= 2)
            @if ($users->isEmpty())
                <p class="text-sm text-white/45">Никого не найдено. Попробуйте другой запрос.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($users as $user)
                        <li class="border border-white/10 bg-black/30 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 anomaly-tilt rounded-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $user->avatarUrl() }}" alt="" class="w-11 h-11 rounded-full border border-white/15 object-cover shrink-0" width="44" height="44" />
                                <div class="min-w-0">
                                    <a href="{{ route('profile.show', $user) }}" class="glitch-hover text-sm font-medium text-white block truncate">{{ $user->name }}</a>
                                    <p class="text-xs text-white/35 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('messages.show', $user) }}" class="glitch-hover text-xs text-white/55 hover:text-white border border-white/15 px-3 py-1.5 rounded-sm">Написать</a>
                                @if ($followingIds->contains($user->id))
                                    <form action="{{ route('users.unfollow', $user) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="glitch-hover text-xs uppercase tracking-wider text-white/70 border border-white/25 px-3 py-1.5 rounded-sm hover:bg-white/5">Отписаться</button>
                                    </form>
                                @else
                                    <form action="{{ route('users.follow', $user) }}" method="post">
                                        @csrf
                                        <button type="submit" class="glitch-hover text-xs uppercase tracking-wider text-black bg-white/90 px-3 py-1.5 rounded-sm hover:bg-white">Подписаться</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>
</x-app-layout>
