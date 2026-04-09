<x-app-layout>
    <x-slot name="header">
        <h1 class="font-medium text-lg text-white tracking-tight glitch-hover inline-block">Сообщения</h1>
    </x-slot>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-10">
        @if ($partners->isEmpty())
            <p class="text-sm text-white/50">Нет диалогов. Откройте профиль пользователя и нажмите «Написать сообщение».</p>
        @else
            <ul class="divide-y divide-white/10 border border-white/10">
                @foreach ($partners as $partner)
                    <li>
                        <a href="{{ route('messages.show', $partner) }}" class="glitch-hover flex items-center gap-3 px-4 py-3 hover:bg-white/5">
                            <img src="{{ $partner->avatarUrl() }}" alt="" class="w-10 h-10 rounded-full border border-white/20 object-cover" />
                            <span class="text-sm text-white">{{ $partner->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
