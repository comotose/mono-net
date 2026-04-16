<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-white/50">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-white/70">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-white hover:text-white/80">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-white/90">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="bio" value="О себе" />
            <textarea id="bio" name="bio" rows="4" placeholder="Коротко о себе…" class="mono-textarea mt-1 block min-h-[6rem]">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="avatar" value="Аватар" />
            <input id="avatar" name="avatar" type="file" accept="image/*" class="mt-1 block w-full text-sm text-white/70 file:mr-4 file:py-2 file:px-4 file:border file:border-white/20 file:bg-black file:text-white file:text-xs" />
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            @if ($user->avatar)
                <p class="mt-2 text-xs text-white/40">Текущий аватар сохранён. Загрузите новый файл, чтобы заменить.</p>
            @endif
        </div>

        <div class="space-y-3 border border-white/10 p-4">
            <h3 class="text-sm text-white/90">Настройки уведомлений</h3>
            <p class="text-xs text-white/50">Выберите, какие события будут приходить в раздел «Уведомления».</p>

            <label class="flex items-center gap-3 text-sm text-white/80">
                <input type="checkbox" name="notify_on_message" value="1" {{ old('notify_on_message', $user->notify_on_message) ? 'checked' : '' }} class="border-white/30 bg-black text-white focus:ring-white/20" />
                Новые сообщения
            </label>
            <label class="flex items-center gap-3 text-sm text-white/80">
                <input type="checkbox" name="notify_on_follow" value="1" {{ old('notify_on_follow', $user->notify_on_follow) ? 'checked' : '' }} class="border-white/30 bg-black text-white focus:ring-white/20" />
                Подписки на аккаунт
            </label>
            <label class="flex items-center gap-3 text-sm text-white/80">
                <input type="checkbox" name="notify_on_like" value="1" {{ old('notify_on_like', $user->notify_on_like) ? 'checked' : '' }} class="border-white/30 bg-black text-white focus:ring-white/20" />
                Лайки публикаций
            </label>
            <label class="flex items-center gap-3 text-sm text-white/80">
                <input type="checkbox" name="notify_on_comment" value="1" {{ old('notify_on_comment', $user->notify_on_comment) ? 'checked' : '' }} class="border-white/30 bg-black text-white focus:ring-white/20" />
                Комментарии к публикациям
            </label>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-white/60"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
