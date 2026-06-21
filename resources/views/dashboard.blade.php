<x-app-layout>
    <x-slot name="header">
        <h2 class="mono-page-title">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="page-shell py-12">
        <div class="mono-surface p-6">
            <div class="mono-body">
                {{ __("You're logged in!") }}
            </div>
        </div>
    </div>
</x-app-layout>
