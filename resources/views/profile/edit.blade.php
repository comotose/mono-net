<x-app-layout>
    <x-slot name="header">
        <h2 class="font-medium text-lg text-white tracking-tight glitch-hover inline-block">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full space-y-8">
            <div class="p-6 sm:p-8 border border-white/10 bg-black/40">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 border border-white/10 bg-black/40">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 border border-white/10 bg-black/40">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
