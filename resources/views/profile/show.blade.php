<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" alt="" class="w-16 h-16 rounded-full border border-white/20 object-cover" width="64" height="64" />
                <div>
                    <h1 class="font-medium text-lg text-white glitch-hover inline-block">{{ $user->name }}</h1>
                    <p class="text-xs text-white/40 mt-1">{{ $followersCount }} подписчиков · {{ $followingCount }} подписок</p>
                </div>
            </div>
            @if (! $isSelf)
                <div>
                    @if ($isFollowing)
                        <form action="{{ route('users.unfollow', $user) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="glitch-hover px-4 py-2 border border-white/30 text-xs uppercase tracking-widest text-white/80 hover:bg-white/10">Отписаться</button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $user) }}" method="post">
                            @csrf
                            <button type="submit" class="glitch-hover px-4 py-2 bg-white text-black text-xs uppercase tracking-widest hover:bg-white/90">Подписаться</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        @if ($user->bio)
            <p class="text-sm text-white/70 whitespace-pre-wrap border border-white/10 p-4">{{ $user->bio }}</p>
        @endif

        <div class="flex gap-4 text-xs">
            <a href="{{ route('messages.show', $user) }}" class="glitch-hover text-white/60 hover:text-white">Написать сообщение</a>
        </div>

        @if (session('status') === 'followed')
            <p class="text-sm text-white/50">Вы подписались.</p>
        @endif
        @if (session('status') === 'unfollowed')
            <p class="text-sm text-white/50">Подписка отменена.</p>
        @endif

        <div class="space-y-8">
            @forelse ($posts as $post)
                @include('posts._card', ['post' => $post])
            @empty
                <p class="text-white/40 text-sm">Нет публикаций.</p>
            @endforelse
        </div>

        <div class="text-white/40 text-xs pt-4">
            {{ $posts->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
