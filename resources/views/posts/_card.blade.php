@php
    $liked = (bool) ($post->liked ?? false);
@endphp
<article class="border border-white/15 p-4 space-y-4 post-card anomaly-tilt" data-post-id="{{ $post->id }}">
    <header class="flex items-start gap-3">
        <a href="{{ route('profile.show', $post->user) }}" class="shrink-0">
            <img src="{{ $post->user->avatarUrl() }}" alt="" class="w-10 h-10 rounded-full border border-white/20 object-cover" width="40" height="40" />
        </a>
        <div class="min-w-0 flex-1">
            <a href="{{ route('profile.show', $post->user) }}" class="glitch-hover text-sm font-medium text-white">{{ $post->user->name }}</a>
            <p class="text-xs text-white/35">{{ $post->created_at->translatedFormat('d.m.Y H:i') }}</p>
        </div>
        @if ($post->user_id === auth()->id())
            <form action="{{ route('posts.destroy', $post) }}" method="post" onsubmit="return confirm('Удалить публикацию?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-white/40 hover:text-red-400 glitch-hover">Удалить</button>
            </form>
        @endif
    </header>

    <div class="text-sm text-white/90 whitespace-pre-wrap leading-relaxed">{{ $post->content }}</div>

    @if ($post->image)
        <div class="border border-white/10 overflow-hidden">
            <img src="{{ $post->imageUrl() }}" alt="" class="w-full max-h-96 object-contain bg-black" loading="lazy" />
        </div>
    @endif

    <div class="flex items-center gap-6 pt-2 border-t border-white/10">
        <button
            type="button"
            class="like-btn glitch-hover flex items-center gap-2 text-xs text-white/70 hover:text-white"
            data-liked="{{ $liked ? '1' : '0' }}"
            data-url="{{ route('posts.like', $post) }}"
        >
            <span class="like-icon">{{ $liked ? '♥' : '♡' }}</span>
            <span class="like-label">Нравится</span>
            <span class="like-count tabular-nums">{{ $post->likes_count }}</span>
        </button>
    </div>

    <section class="space-y-3">
        <h3 class="text-xs uppercase tracking-widest text-white/40">Комментарии</h3>
        <ul class="space-y-3">
            @foreach ($post->comments as $comment)
                <li class="text-sm border-l border-white/10 pl-3">
                    <div class="flex justify-between gap-2">
                        <span class="text-white/80"><span class="text-white font-medium">{{ $comment->user->name }}</span> — {{ $comment->body }}</span>
                        @if ($comment->user_id === auth()->id() || $post->user_id === auth()->id())
                            <form action="{{ route('comments.destroy', $comment) }}" method="post" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-white/30 hover:text-white/60">×</button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <form action="{{ route('posts.comments.store', $post) }}" method="post" class="flex flex-col gap-2">
            @csrf
            <label for="comment-{{ $post->id }}" class="sr-only">Комментарий</label>
            <textarea id="comment-{{ $post->id }}" name="body" rows="2" required placeholder="Написать комментарий…" class="mono-textarea mono-textarea--compact"></textarea>
            @error('body')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
            <button type="submit" class="self-start glitch-hover text-xs uppercase tracking-widest text-white/70 hover:text-white border border-white/20 px-3 py-1">
                Отправить комментарий
            </button>
        </form>
    </section>
</article>
