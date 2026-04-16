<nav x-data="{ open: false }" class="border-b border-white/10 bg-black">
    @php
        $navUser = Auth::user();
        $unreadNotificationsCount = $navUser->unreadNotifications()->count();
        $roleLabel = match ($navUser->role) {
            'admin' => 'Администратор',
            'moderator' => 'Модератор',
            default => null,
        };
    @endphp
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 py-3 sm:py-0 sm:h-14 sm:flex-row sm:justify-between sm:items-center">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-6 sm:gap-8 flex-1 min-w-0">
                    <a href="{{ route('feed.index') }}" class="glitch-hover text-sm font-medium tracking-tight text-white shrink-0">
                        {{ config('app.name', 'MONO') }}
                    </a>
                    <form method="get" action="{{ route('search.friends') }}" class="hidden md:flex flex-1 max-w-[min(280px,100%)]" title="Поиск: Enter">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск… " autocomplete="off" class="mono-search-input text-xs py-1.5" />
                    </form>
                    <div class="hidden sm:flex gap-5 items-center shrink-0">
                        <a href="{{ route('feed.index') }}" class="glitch-hover text-xs {{ request()->routeIs('feed.*') ? 'text-white underline underline-offset-4' : 'text-white/60 hover:text-white' }}">
                            Лента
                        </a>
                        <a href="{{ route('messages.index') }}" class="glitch-hover text-xs {{ request()->routeIs('messages.*') ? 'text-white underline underline-offset-4' : 'text-white/60 hover:text-white' }}">
                            Сообщения
                        </a>
                        <a href="{{ route('notifications.index') }}" class="glitch-hover text-xs {{ request()->routeIs('notifications.*') ? 'text-white underline underline-offset-4' : 'text-white/60 hover:text-white' }}">
                            Уведомления
                            @if ($unreadNotificationsCount > 0)
                                <span class="ml-1 inline-flex min-w-[1.1rem] justify-center px-1 rounded-full border border-white/30 text-[10px] leading-4">
                                    {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('profile.edit') }}" class="glitch-hover text-xs {{ request()->routeIs('profile.edit') ? 'text-white underline underline-offset-4' : 'text-white/60 hover:text-white' }}">
                            Настройки
                        </a>
                    </div>
                </div>

                <div class="hidden sm:flex sm:items-center gap-4 shrink-0">
                    <a href="{{ route('profile.show', Auth::user()) }}" class="glitch-hover text-xs text-white/80 hover:text-white max-w-[10rem] truncate">
                        {{ Auth::user()->name }}
                    </a>
                    @if ($roleLabel)
                        <span class="text-[10px] border border-white/20 px-2 py-0.5 text-white/60">{{ $roleLabel }}</span>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="glitch-hover text-xs text-white/50 hover:text-white">
                            Выйти
                        </button>
                    </form>
                </div>

                <div class="flex items-center sm:hidden">
                    <button type="button" @click="open = ! open" class="text-white/70 p-2 rounded-md transition-colors duration-300 hover:bg-white/5" aria-label="Меню">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form method="get" action="{{ route('search.friends') }}" class="sm:hidden w-full" title="Поиск: Enter">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск друзей — Enter" autocomplete="off" class="mono-search-input text-xs py-2" />
            </form>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-white/10">
        <div class="px-4 pt-2 pb-4 space-y-2">
            <a href="{{ route('feed.index') }}" class="block text-sm text-white/80">Лента</a>
            <a href="{{ route('messages.index') }}" class="block text-sm text-white/80">Сообщения</a>
            <a href="{{ route('notifications.index') }}" class="block text-sm text-white/80">
                Уведомления
                @if ($unreadNotificationsCount > 0)
                    ({{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }})
                @endif
            </a>
            <a href="{{ route('profile.edit') }}" class="block text-sm text-white/80">Настройки</a>
            <a href="{{ route('profile.show', Auth::user()) }}" class="block text-sm text-white/80">Профиль</a>
            @if ($roleLabel)
                <p class="text-xs text-white/40">{{ $roleLabel }}</p>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-white/50">Выйти</button>
            </form>
        </div>
    </div>
</nav>
