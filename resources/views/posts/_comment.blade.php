@php
    $depth = $depth ?? 0;
    $visualDepth = min($depth, 2);
    $isDeepThread = $depth >= 3;
    $repliesCount = $comment->replies->count();
    $canDelete = $comment->user_id === auth()->id()
        || $post->user_id === auth()->id()
        || auth()->user()->hasRole('admin', 'moderator');
@endphp

<li
    id="comment-{{ $comment->id }}"
    class="mono-comment-item {{ $isDeepThread ? 'mono-comment-item--deep' : '' }}"
    data-comment-id="{{ $comment->id }}"
    data-comment-depth="{{ $depth }}"
    style="--comment-depth: {{ $visualDepth }};"
>
    <article class="mono-comment-card">
        @if ($isDeepThread && $comment->parent)
            <p class="mono-thread-context">
                Ответ для {{ $comment->parent->user->name }}
            </p>
        @endif

        <header class="flex items-start gap-3">
            <a href="{{ route('profile.show', $comment->user) }}" class="shrink-0">
                <img src="{{ $comment->user->avatarUrl() }}" alt="" class="h-9 w-9 rounded-full border object-cover mono-avatar-frame" />
            </a>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('profile.show', $comment->user) }}" class="mono-strong-link mono-comment-author">
                        {{ $comment->user->name }}
                    </a>
                    @include('users._role_badge', ['user' => $comment->user])
                </div>
                <p class="mono-caption">{{ $comment->created_at->translatedFormat('d.m.Y H:i') }}</p>
            </div>

            @if ($canDelete)
                <form action="{{ route('comments.destroy', $comment) }}" method="post" class="shrink-0" data-async-comment-delete-form data-post-id="{{ $post->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="mono-icon-button" aria-label="Удалить комментарий">
                        <i class="bi bi-trash3"></i>
                    </button>
                </form>
            @endif
        </header>

        <p class="mono-comment-body">{{ $comment->body }}</p>

        <details class="mono-reply-details">
            <summary class="mono-reply-summary">
                <i class="bi bi-reply"></i>
                <span>Ответить</span>
            </summary>

            <form action="{{ route('posts.comments.store', $post) }}" method="post" class="mt-3 flex flex-col gap-2" data-async-comment-form data-post-id="{{ $post->id }}">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}" />
                <label for="reply-{{ $comment->id }}" class="sr-only">Ответ</label>
                <textarea id="reply-{{ $comment->id }}" name="body" rows="2" required placeholder="Ответить {{ $comment->user->name }}…" class="mono-textarea mono-textarea--compact"></textarea>
                <p class="hidden mono-form-error" data-form-error></p>
                <button type="submit" class="self-start mono-button-secondary mono-button-secondary--sm">
                    <i class="bi bi-send"></i>
                    <span>Отправить</span>
                </button>
            </form>
        </details>
    </article>

    <details class="mono-thread-details {{ $repliesCount === 0 ? 'hidden' : '' }}" data-comment-thread="{{ $comment->id }}">
        <summary class="mono-thread-summary">
            <i class="bi bi-diagram-3"></i>
            <span>Ответы</span>
            <span class="mono-counter-pill" data-replies-count="{{ $comment->id }}">{{ $repliesCount }}</span>
        </summary>

        <ul class="mono-replies-list {{ $comment->replies->isEmpty() ? 'is-empty' : '' }}" data-comment-replies="{{ $comment->id }}">
            @foreach ($comment->replies as $reply)
                @include('posts._comment', ['comment' => $reply, 'post' => $post, 'depth' => $depth + 1])
            @endforeach
        </ul>
    </details>
</li>
