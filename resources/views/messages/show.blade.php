<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('messages.index') }}" class="text-xs text-white/40 hover:text-white glitch-hover">← Назад</a>
            <img src="{{ $user->avatarUrl() }}" alt="" class="w-9 h-9 rounded-full border border-white/20" />
            <h1 class="font-medium text-lg text-white glitch-hover">{{ $user->name }}</h1>
        </div>
    </x-slot>

    <div class="max-w-xl mx-auto px-4 py-8 flex flex-col min-h-[60vh]">
        <div id="messages-list" class="flex-1 space-y-3 mb-6 overflow-y-auto max-h-[50vh]">
            @foreach ($messages as $message)
                @php $mine = $message->sender_id === auth()->id(); @endphp
                <div class="message-row flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] border border-white/15 px-3 py-2 text-sm {{ $mine ? 'bg-white/10' : 'bg-black' }}">
                        <p class="whitespace-pre-wrap text-white/90">{{ $message->body }}</p>
                        <p class="text-[10px] text-white/30 mt-1">{{ $message->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @if (session('status') === 'message-sent')
            <p class="text-xs text-white/40 mb-2">Сообщение отправлено.</p>
        @endif

        <form action="{{ route('messages.store', $user) }}" method="post" class="mt-auto space-y-2 border-t border-white/10 pt-4">
            @csrf
            <label for="body" class="sr-only">Сообщение</label>
            <textarea id="body" name="body" rows="3" required placeholder="Введите сообщение…" class="mono-textarea min-h-[5.5rem]">{{ old('body') }}</textarea>
            @error('body')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
            <button type="submit" class="glitch-hover inline-flex items-center px-4 py-2 bg-white text-black text-xs font-semibold uppercase tracking-widest border border-white hover:bg-white/90">
                Отправить
            </button>
        </form>
    </div>

    @push('scripts')
        @vite(['resources/js/echo-chat.js'])
        <script>
            window.chatPartnerId = {{ $user->id }};
        </script>
    @endpush
</x-app-layout>
