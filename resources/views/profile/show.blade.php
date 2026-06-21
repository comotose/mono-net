<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" alt="" class="w-16 h-16 rounded-full border object-cover mono-avatar-frame" width="64" height="64" />
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="mono-page-title">{{ $user->name }}</h1>
                        @include('users._role_badge', ['user' => $user])
                    </div>
                    <p class="mono-caption mt-1">
                        <span data-followers-count="{{ $user->id }}">{{ $followersCount }}</span> подписчиков ·
                        <span data-following-count="{{ $user->id }}">{{ $followingCount }}</span> подписок
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div data-follow-button-target="{{ $user->id }}">
                    @include('users._follow_button', ['user' => $user, 'isFollowing' => $isFollowing, 'isSelf' => $isSelf])
                </div>
                <div data-role-manager-target="{{ $user->id }}">
                    @include('users._role_manager', ['user' => $user])
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-shell page-stack py-10">
        @php
            $interestTags = is_array($user->interest_tags) ? array_filter($user->interest_tags) : [];
        @endphp

        @if ($user->bio)
            <p class="mono-surface mono-body-sm whitespace-pre-wrap p-4">{{ $user->bio }}</p>
        @endif

        @if (! empty($interestTags))
            <section class="flex flex-wrap gap-2" aria-label="Интересы">
                @foreach ($interestTags as $tag)
                    <span class="mono-tag">{{ $tag }}</span>
                @endforeach
            </section>
        @elseif ($isSelf)
            <a href="{{ route('profile.edit') }}" class="mono-empty-state mono-quiet-link">
                Добавьте интересы в настройках профиля.
            </a>
        @endif

        @unless ($isSelf)
            <div class="flex gap-4 text-xs">
                <a href="{{ route('messages.show', $user) }}" class="mono-button-secondary mono-button-secondary--sm">
                    <i class="bi bi-chat-dots"></i>
                    <span>Написать</span>
                </a>
            </div>
        @endunless

        @if (session('status') === 'followed')
            <p class="mono-alert">Вы подписались.</p>
        @endif
        @if (session('status') === 'unfollowed')
            <p class="mono-alert">Подписка отменена.</p>
        @endif

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="mono-surface p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="mono-section-title">Подписчики</h2>
                    <span class="mono-counter-pill">{{ $followersCount }}</span>
                </div>

                @if ($followers->isEmpty())
                    <p class="mono-caption">Пока нет подписчиков.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($followers as $follower)
                            <li>
                                <a href="{{ route('profile.show', $follower) }}" class="mono-list-link flex items-center gap-3 rounded-xl px-2 py-2">
                                    <img src="{{ $follower->avatarUrl() }}" alt="" class="h-9 w-9 rounded-full border object-cover mono-avatar-frame" />
                                    <span class="min-w-0 flex-1 truncate mono-body-sm">{{ $follower->name }}</span>
                                    @include('users._role_badge', ['user' => $follower])
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mono-surface p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="mono-section-title">Подписки</h2>
                    <span class="mono-counter-pill">{{ $followingCount }}</span>
                </div>

                @if ($following->isEmpty())
                    <p class="mono-caption">Пока нет подписок.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($following as $followedUser)
                            <li>
                                <a href="{{ route('profile.show', $followedUser) }}" class="mono-list-link flex items-center gap-3 rounded-xl px-2 py-2">
                                    <img src="{{ $followedUser->avatarUrl() }}" alt="" class="h-9 w-9 rounded-full border object-cover mono-avatar-frame" />
                                    <span class="min-w-0 flex-1 truncate mono-body-sm">{{ $followedUser->name }}</span>
                                    @include('users._role_badge', ['user' => $followedUser])
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <div class="space-y-8">
            @forelse ($posts as $post)
                @include('posts._card', ['post' => $post])
            @empty
                <p class="mono-empty-state">Нет публикаций.</p>
            @endforelse
        </div>

        <div class="mono-pagination-wrap pt-4">
            {{ $posts->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
