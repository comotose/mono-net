@if (auth()->user()->isAdmin())
    <form action="{{ route('users.role.update', $user) }}" method="post" class="flex items-center gap-2" data-async-role-form data-user-id="{{ $user->id }}">
        @csrf
        @method('PATCH')
        <label for="role-{{ $user->id }}" class="sr-only">Роль</label>
        <select id="role-{{ $user->id }}" name="role" class="mono-select mono-select--sm">
            @foreach (\App\Models\User::availableRoles() as $value => $label)
                <option value="{{ $value }}" @selected($user->normalizedRole() === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="mono-button-secondary mono-button-secondary--sm">
            <i class="bi bi-shield-check"></i>
            <span>Сменить роль</span>
        </button>
    </form>
@endif
