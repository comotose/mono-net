<nav x-data="{ open: false }" class="mono-nav-shell">
    @php
        $navUser = Auth::user();
        $unreadNotificationsCount = $navUser->unreadNotifications()->count();
    @endphp
    <div class="page-shell">
        <div class="py-3 space-y-3">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('feed.index') }}" class="glitch-hover text-sm font-medium tracking-tight shrink-0 mono-brand-link">
                    {{ config('app.name', 'MONO') }}
                </a>

                <div class="hidden md:flex items-center justify-end gap-3 flex-wrap">
                    <button type="button" class="mono-theme-toggle" data-theme-toggle>
                        <i class="bi bi-circle-half"></i>
                        <span data-theme-label>Светлая тема</span>
                    </button>
                    <!-- <button type="button" class="mono-theme-toggle" data-enable-browser-notifications>
                        <i class="bi bi-bell"></i>
                        <span>Push</span>
                    </button> -->
                    <a href="{{ route('profile.show', Auth::user()) }}" class="mono-profile-link max-w-[12rem] truncate">
                        <i class="bi bi-person-circle"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    @include('users._role_badge', ['user' => $navUser])
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="mono-quiet-link">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Выйти</span>
                        </button>
                    </form>
                </div>

                <div class="flex items-center md:hidden">
                    <button type="button" @click="open = ! open" class="mono-icon-toggle" aria-label="Меню">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="hidden md:flex md:flex-col lg:flex-row lg:items-center gap-3 lg:gap-4">
                <form method="get" action="{{ route('search.friends') }}" class="flex-1 min-w-0 lg:max-w-xl" title="Поиск: Enter">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск людей, почты и описаний" autocomplete="off" class="mono-search-input text-sm py-2.5" />
                </form>

                <div class="flex flex-wrap items-center gap-4 xl:gap-5 shrink-0">
                    <a href="{{ route('feed.index') }}" class="mono-nav-link {{ request()->routeIs('feed.*') ? 'is-active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span>Лента</span>
                    </a>
                    <a href="{{ route('messages.index') }}" class="mono-nav-link {{ request()->routeIs('messages.*') ? 'is-active' : '' }}">
                        <i class="bi bi-chat-dots"></i>
                        <span>Сообщения</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="mono-nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}">
                        <i class="bi bi-bell"></i>
                        <span>Уведомления</span>
                        <span data-notification-counter-slot data-counter-class="mono-counter-pill ml-1">
                            @if ($unreadNotificationsCount > 0)
                                <span class="mono-counter-pill ml-1" data-notification-counter data-count="{{ $unreadNotificationsCount }}">
                                    {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                                </span>
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="mono-nav-link {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}">
                        <i class="bi bi-sliders"></i>
                        <span>Настройки</span>
                    </a>
                </div>
            </div>

            <form method="get" action="{{ route('search.friends') }}" class="md:hidden w-full" title="Поиск: Enter">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск людей, почты и описаний" autocomplete="off" class="mono-search-input text-sm py-2.5" />
            </form>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden mono-mobile-nav">
        <div class="px-4 pt-2 pb-4 space-y-2">
            <button type="button" class="mono-theme-toggle mono-theme-toggle--mobile" data-theme-toggle>
                <i class="bi bi-circle-half"></i>
                <span data-theme-label>Светлая тема</span>
            </button>
            <button type="button" class="mono-theme-toggle mono-theme-toggle--mobile" data-enable-browser-notifications>
                <i class="bi bi-bell"></i>
                <span>Включить push</span>
            </button>
            <a href="{{ route('feed.index') }}" class="mono-mobile-link"><i class="bi bi-grid-1x2"></i><span>Лента</span></a>
            <a href="{{ route('messages.index') }}" class="mono-mobile-link"><i class="bi bi-chat-dots"></i><span>Сообщения</span></a>
            <a href="{{ route('notifications.index') }}" class="mono-mobile-link">
                <i class="bi bi-bell"></i>
                <span>Уведомления</span>
                <span data-notification-counter-slot data-counter-class="mono-counter-pill ml-2">
                    @if ($unreadNotificationsCount > 0)
                        <span class="mono-counter-pill ml-2" data-notification-counter data-count="{{ $unreadNotificationsCount }}">
                            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </span>
            </a>
            <a href="{{ route('profile.edit') }}" class="mono-mobile-link"><i class="bi bi-sliders"></i><span>Настройки</span></a>
            <a href="{{ route('profile.show', Auth::user()) }}" class="mono-mobile-link"><i class="bi bi-person-circle"></i><span>Профиль</span></a>
            @if ($navUser->isAdmin())
                <p class="mono-caption">{{ $navUser->roleLabel() }}</p>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mono-quiet-link"><i class="bi bi-box-arrow-right"></i><span>Выйти</span></button>
            </form>
        </div>
    </div>
</nav>
