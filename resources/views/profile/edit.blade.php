<x-app-layout>
    <x-slot name="header">
        <h2 class="mono-page-title">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="page-shell py-10">
        <div class="w-full space-y-8">
            <div class="mono-surface p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="mono-surface p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="mono-surface p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
