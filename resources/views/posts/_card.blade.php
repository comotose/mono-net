@php
    $countComments = function ($comments) use (&$countComments): int {
        return $comments->sum(fn ($comment) => 1 + $countComments($comment->replies));
    };
    $commentsCount = $countComments($post->comments);
    $imageUrls = $post->imageUrls();
    $imageCount = count($imageUrls);
@endphp

<article id="post-{{ $post->id }}" class="mono-surface p-5 space-y-5 post-card anomaly-tilt" data-post-id="{{ $post->id }}">
    <header class="flex items-start gap-3">
        <a href="{{ route('profile.show', $post->user) }}" class="shrink-0">
            <img src="{{ $post->user->avatarUrl() }}" alt="" class="w-10 h-10 rounded-full border object-cover mono-avatar-frame" width="40" height="40" />
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('profile.show', $post->user) }}" class="mono-strong-link mono-post-author">{{ $post->user->name }}</a>
                @include('users._role_badge', ['user' => $post->user])
            </div>
            <p class="mono-caption">{{ $post->created_at->translatedFormat('d.m.Y H:i') }}</p>
        </div>
        @if ($post->user_id === auth()->id() || auth()->user()->hasRole('admin', 'moderator'))
            <form action="{{ route('posts.destroy', $post) }}" method="post" data-async-post-delete-form>
                @csrf
                @method('DELETE')
                <button type="submit" class="mono-quiet-link inline-flex items-center gap-2" data-confirm="Удалить публикацию?">
                    <i class="bi bi-trash3"></i>
                    <span>Удалить</span>
                </button>
            </form>
        @endif
    </header>

    <div class="mono-body whitespace-pre-wrap">{{ $post->content }}</div>

    @if ($imageCount)
        @if ($imageCount === 1)
            <button
                type="button"
                class="mono-image-frame mono-post-image-trigger overflow-hidden rounded-3xl"
                data-lightbox-trigger
                data-lightbox-gallery='@json($imageUrls)'
                data-lightbox-index="0"
                data-lightbox-caption="{{ $post->user->name }}"
                aria-label="Открыть фото"
            >
                <img src="{{ $imageUrls[0] }}" alt="" class="mono-post-image w-full max-h-96 object-contain" loading="lazy" />
            </button>
        @else
            @php
                $galleryClass = $imageCount === 2
                    ? 'mono-post-gallery--2'
                    : ($imageCount === 3 ? 'mono-post-gallery--3' : 'mono-post-gallery--many');
            @endphp
            <div class="mono-post-gallery {{ $galleryClass }}">
                @foreach ($imageUrls as $index => $imageUrl)
                    <button
                        type="button"
                        class="mono-image-frame mono-post-image-trigger mono-post-gallery__item {{ $imageCount === 3 && $index === 0 ? 'mono-post-gallery__item--featured' : '' }} overflow-hidden rounded-3xl"
                        data-lightbox-trigger
                        data-lightbox-gallery='@json($imageUrls)'
                        data-lightbox-index="{{ $index }}"
                        data-lightbox-caption="{{ $post->user->name }}"
                        aria-label="Открыть фото"
                    >
                        <img src="{{ $imageUrl }}" alt="" class="mono-post-image w-full h-full object-cover" loading="lazy" />
                    </button>
                @endforeach
            </div>
        @endif
    @endif

    <div class="border-t pt-4 mono-divider">
        @include('reactions._picker', ['subject' => $post])
    </div>

    <details class="mono-comments-details" data-post-comments-details="{{ $post->id }}">
        <summary class="mono-comments-summary">
            <i class="bi bi-chat-right-text"></i>
            <span>Комментарии</span>
            <span class="mono-counter-pill" data-comments-count="{{ $post->id }}">{{ $commentsCount }}</span>
        </summary>

        <section class="mono-comments-panel">
            <ul class="space-y-3" data-comments-list="{{ $post->id }}">
                @foreach ($post->comments as $comment)
                    @include('posts._comment', ['comment' => $comment, 'post' => $post, 'depth' => 0])
                @endforeach
            </ul>

            <form action="{{ route('posts.comments.store', $post) }}" method="post" class="flex flex-col gap-2" data-async-comment-form data-post-id="{{ $post->id }}">
                @csrf
                <label for="comment-{{ $post->id }}" class="sr-only">Комментарий</label>
                <textarea id="comment-{{ $post->id }}" name="body" rows="2" required placeholder="Написать комментарий…" class="mono-textarea mono-textarea--compact"></textarea>
                @error('body')
                    <p class="mono-error-text">{{ $message }}</p>
                @enderror
                <p class="hidden mono-form-error" data-form-error></p>
                <button type="submit" class="self-start mono-button-secondary mono-button-secondary--sm">
                    <i class="bi bi-chat-right-dots"></i>
                    <span>Отправить</span>
                </button>
            </form>
        </section>
    </details>
</article>
