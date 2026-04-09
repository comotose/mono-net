<x-app-layout>
    <x-slot name="header">
        <h1 class="font-medium text-lg text-white tracking-tight glitch-hover inline-block">Лента</h1>
    </x-slot>

    <div class="max-w-xl mx-auto px-4 py-10 space-y-10">
        @if (session('status') === 'post-created')
            <p class="text-sm text-white/60 border border-white/10 px-3 py-2">Публикация добавлена.</p>
        @endif
        @if (session('status') === 'post-deleted')
            <p class="text-sm text-white/60 border border-white/10 px-3 py-2">Публикация удалена.</p>
        @endif

        <section class="border border-white/15 p-4 space-y-4" x-data="{ open: true }">
            <button type="button" @click="open = !open" class="glitch-hover text-left w-full text-sm text-white/80 flex justify-between items-center">
                <span>Новая публикация</span>
                <span class="text-white/40" x-text="open ? '−' : '+'"></span>
            </button>
            <div x-show="open" x-transition class="space-y-4">
                <form action="{{ route('posts.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="content" class="sr-only">Текст</label>
                        <textarea id="content" name="content" rows="4" required placeholder="Что у вас нового?" class="mono-textarea">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="image" class="text-xs text-white/50">Изображение (необязательно)</label>
                        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-white/70 file:mr-4 file:py-1 file:px-3 file:border file:border-white/20 file:bg-black file:text-white file:text-xs" />
                        @error('image')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="glitch-hover inline-flex items-center px-4 py-2 bg-white text-black text-xs font-semibold uppercase tracking-widest border border-white hover:bg-white/90">
                        Опубликовать
                    </button>
                </form>
            </div>
        </section>

        <div class="space-y-8">
            @forelse ($posts as $post)
                @include('posts._card', ['post' => $post])
            @empty
                <p class="text-white/40 text-sm">Пока нет публикаций. Создайте первую.</p>
            @endforelse
        </div>

        <div class="text-white/40 text-xs pt-4">
            {{ $posts->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
