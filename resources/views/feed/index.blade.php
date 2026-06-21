<x-app-layout>
    <x-slot name="header">
        <h1 class="mono-page-title">Лента</h1>
    </x-slot>

    <div class="page-shell page-stack py-10">
        @if (session('status') === 'post-created')
            <p class="mono-alert">Публикация добавлена.</p>
        @endif
        @if (session('status') === 'post-deleted')
            <p class="mono-alert">Публикация удалена.</p>
        @endif

        <section class="mono-surface mono-surface--soft p-5 space-y-4" x-data="{ open: true }">
            <button type="button" @click="open = !open" class="mono-section-toggle">
                <span>Новая публикация</span>
                <svg class="h-4 w-4 mono-caption transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="open" x-transition class="space-y-4">
                <form id="post-create-form" action="{{ route('posts.store') }}" method="post" enctype="multipart/form-data" class="space-y-4" data-async-post-form>
                    @csrf
                    <div>
                        <label for="content" class="sr-only">Текст</label>
                        <textarea id="content" name="content" rows="4" required placeholder="Что у вас нового?" class="mono-textarea">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 mono-error-text">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="images" class="mono-field-label">Изображения (до 9)</label>
                        <input id="images" name="images[]" type="file" accept="image/*" multiple class="mono-file-input mt-1 block w-full" />
                        <p class="mt-2 mono-caption">Можно выбрать несколько файлов, из них будет собран коллаж.</p>
                        @error('images')
                            <p class="mt-1 mono-error-text">{{ $message }}</p>
                        @enderror
                        @error('images.*')
                            <p class="mt-1 mono-error-text">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="hidden mono-form-error" data-form-error></p>
                    <button type="submit" class="mono-button-primary">
                        <i class="bi bi-send"></i>
                        <span>Опубликовать</span>
                    </button>
                </form>
            </div>
        </section>

        <div id="posts-list" class="space-y-8">
            @forelse ($posts as $post)
                @include('posts._card', ['post' => $post])
            @empty
                <p class="mono-empty-state">Пока нет публикаций. Создайте первую.</p>
            @endforelse
        </div>

        <div class="mono-pagination-wrap pt-4">
            {{ $posts->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
