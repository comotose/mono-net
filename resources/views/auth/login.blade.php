<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="mono-checkbox" name="remember">
                <span class="ms-2 text-sm text-[rgb(var(--mono-text-soft))]">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
            @if (Route::has('password.request'))
                <a class="mono-quiet-link underline text-sm" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                <i class="bi bi-box-arrow-in-right"></i>
                <span>{{ __('Log in') }}</span>
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="mt-6 border-t pt-5 mono-divider">
            <p class="text-sm mono-caption">Нет аккаунта?</p>
            <a href="{{ route('register') }}" class="mono-button-secondary mt-3 w-full">
                <i class="bi bi-person-plus"></i>
                <span>Зарегистрироваться</span>
            </a>
        </div>
    @endif
</x-guest-layout>
