@if (! $isSelf)
    @if ($isFollowing)
        <form action="{{ route('users.unfollow', $user) }}" method="post" data-async-follow-form data-user-id="{{ $user->id }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="mono-button-secondary mono-button-secondary--sm">
                <i class="bi bi-person-dash"></i>
                <span>Отписаться</span>
            </button>
        </form>
    @else
        <form action="{{ route('users.follow', $user) }}" method="post" data-async-follow-form data-user-id="{{ $user->id }}">
            @csrf
            <button type="submit" class="mono-button-primary mono-button-primary--sm">
                <i class="bi bi-person-plus"></i>
                <span>Подписаться</span>
            </button>
        </form>
    @endif
@endif
