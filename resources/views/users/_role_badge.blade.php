@if (auth()->user()?->isAdmin())
    <span class="mono-role-badge" data-role-label-user="{{ $user->id }}">{{ $user->roleLabel() }}</span>
@endif
