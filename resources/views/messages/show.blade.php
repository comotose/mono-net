<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('messages.index') }}" class="mono-quiet-link inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.22 3.22a.75.75 0 11-1.06 1.06l-4.5-4.5a.75.75 0 010-1.06l4.5-4.5a.75.75 0 011.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd" />
                </svg>
                <span>Назад</span>
            </a>
            <img src="{{ $user->avatarUrl() }}" alt="" class="w-9 h-9 rounded-full border mono-avatar-frame" />
            <h1 class="mono-page-title">{{ $user->name }}</h1>
            @include('users._role_badge', ['user' => $user])
        </div>
    </x-slot>

    <div class="page-shell chat-page-shell">
        <div id="messages-list" class="chat-messages-list">
            @foreach ($messages as $message)
                @include('messages._row', ['message' => $message])
            @endforeach
        </div>

        <form id="message-form" action="{{ route('messages.store', $user) }}" method="post" enctype="multipart/form-data" class="chat-composer" data-async-message-form>
            @csrf
            <label for="body" class="sr-only">Сообщение</label>
            <textarea id="body" name="body" rows="1" placeholder="Введите сообщение…" class="mono-textarea mono-chat-textarea" data-autogrow-textarea>{{ old('body') }}</textarea>

            <input id="attachment-input" name="attachment" type="file" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.7z,.csv,.json,.xml,.mp4,.mov,.webm,.mp3,.wav,.ogg" />
            <input id="voice-input" name="voice" type="file" class="hidden" accept="audio/*" />

            <div id="attachment-preview" class="hidden mono-surface mono-surface--soft p-3 text-xs space-y-2">
                <p id="attachment-name"></p>
                <img id="image-thumb" src="" alt="" class="hidden max-h-32 rounded-xl border object-contain mono-image-frame" />
                <button id="clear-attachment" type="button" class="mono-quiet-link">Очистить</button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button id="pick-attachment" type="button" class="mono-button-secondary mono-button-secondary--sm inline-flex gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M8.5 13.5a3 3 0 004.243 0l3.182-3.182a2.5 2.5 0 10-3.536-3.536L8.853 10.32a1.5 1.5 0 102.121 2.122l2.475-2.475a.75.75 0 111.06 1.06l-2.474 2.476a3 3 0 11-4.243-4.243l3.536-3.536a4 4 0 115.657 5.657l-3.182 3.182a4.5 4.5 0 01-6.364-6.364l4.243-4.242a.75.75 0 011.06 1.06L8.5 9.257a3 3 0 000 4.243z" />
                    </svg>
                    <span>Файл</span>
                </button>
                <button id="toggle-recording" type="button" class="mono-button-secondary mono-button-secondary--sm inline-flex gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 13.25A3.25 3.25 0 0013.25 10V6.75a3.25 3.25 0 10-6.5 0V10A3.25 3.25 0 0010 13.25z" />
                        <path d="M5.75 9.5a.75.75 0 011.5 0 2.75 2.75 0 005.5 0 .75.75 0 011.5 0 4.252 4.252 0 01-3.5 4.181V16h1.5a.75.75 0 010 1.5h-4.5a.75.75 0 010-1.5h1.5v-2.319A4.252 4.252 0 015.75 9.5z" />
                    </svg>
                    <span>Голосовое</span>
                </button>
                <span id="recording-status" class="mono-caption"></span>
            </div>

            @error('body')
                <p class="mono-error-text">{{ $message }}</p>
            @enderror
            @error('attachment')
                <p class="mono-error-text">{{ $message }}</p>
            @enderror
            @error('voice')
                <p class="mono-error-text">{{ $message }}</p>
            @enderror
            <p class="hidden mono-form-error" data-form-error></p>
            <button type="submit" class="mono-button-primary">
                <i class="bi bi-send"></i>
                <span>Отправить</span>
            </button>
        </form>
    </div>

    @push('scripts')
        @vite(['resources/js/echo-chat.js'])
        <script>
            window.chatPartnerId = "{{ $user->id }}";
            window.chatCurrentUserId = "{{ auth()->id() }}";
            window.messageReactionRouteTemplate = @json(route('messages.reactions.store', ['message' => '__MESSAGE__']));
            window.formatChatTimestamp = (value) => {
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }

                return new Intl.DateTimeFormat(navigator.language || 'ru-RU', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(date);
            };

            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('message-form');
                const bodyInput = document.getElementById('body');
                const messagesList = document.getElementById('messages-list');
                const attachmentInput = document.getElementById('attachment-input');
                const voiceInput = document.getElementById('voice-input');
                const pickAttachmentBtn = document.getElementById('pick-attachment');
                const toggleRecordingBtn = document.getElementById('toggle-recording');
                const recordingStatus = document.getElementById('recording-status');
                const previewWrap = document.getElementById('attachment-preview');
                const previewName = document.getElementById('attachment-name');
                const previewImage = document.getElementById('image-thumb');
                const clearBtn = document.getElementById('clear-attachment');

                if (!form || !bodyInput || !attachmentInput || !voiceInput) {
                    return;
                }

                document.querySelectorAll('[data-chat-time]').forEach((el) => {
                    const iso = el.getAttribute('data-chat-time');
                    if (!iso) {
                        return;
                    }
                    const formatted = window.formatChatTimestamp(iso);
                    if (formatted) {
                        el.textContent = formatted;
                    }
                });

                let recorder = null;
                let audioChunks = [];
                let isRecording = false;

                const syncComposerHeight = () => {
                    bodyInput.style.height = 'auto';
                    const maxHeight = Number.parseInt(getComputedStyle(bodyInput).getPropertyValue('--chat-textarea-max-height'), 10) || 180;
                    const nextHeight = Math.min(bodyInput.scrollHeight, maxHeight);
                    bodyInput.style.height = `${nextHeight}px`;
                    bodyInput.style.overflowY = bodyInput.scrollHeight > maxHeight ? 'auto' : 'hidden';
                };

                const scrollMessagesToBottom = () => {
                    if (messagesList) {
                        messagesList.scrollTop = messagesList.scrollHeight;
                    }
                };

                const syncVoiceInput = (file) => {
                    const voiceDt = new DataTransfer();
                    if (file) {
                        voiceDt.items.add(file);
                    }
                    voiceInput.files = voiceDt.files;
                };

                const clearPreview = () => {
                    attachmentInput.value = '';
                    syncVoiceInput(null);
                    previewName.textContent = '';
                    previewImage.src = '';
                    previewImage.classList.add('hidden');
                    previewWrap.classList.add('hidden');
                    syncComposerHeight();
                };

                window.resetChatComposer = () => {
                    clearPreview();
                    syncComposerHeight();
                };

                const showPreview = (file) => {
                    if (!file) {
                        clearPreview();
                        return;
                    }

                    previewName.textContent = file.name;
                    previewWrap.classList.remove('hidden');
                    previewImage.classList.add('hidden');
                    previewImage.src = '';

                    if (file.type.startsWith('image/')) {
                        const url = URL.createObjectURL(file);
                        previewImage.src = url;
                        previewImage.classList.remove('hidden');
                    } else if (file.type.startsWith('audio/')) {
                        previewName.textContent = `${file.name} (голосовое/аудио)`;
                    }
                };

                bodyInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        form.requestSubmit();
                    }
                });
                bodyInput.addEventListener('input', syncComposerHeight);
                syncComposerHeight();
                scrollMessagesToBottom();

                pickAttachmentBtn?.addEventListener('click', () => attachmentInput.click());

                attachmentInput.addEventListener('change', () => {
                    const file = attachmentInput.files?.[0] ?? null;
                    if (!file) {
                        return;
                    }
                    syncVoiceInput(null);
                    showPreview(file);
                });

                clearBtn?.addEventListener('click', clearPreview);

                toggleRecordingBtn?.addEventListener('click', async () => {
                    if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
                        recordingStatus.textContent = 'Запись не поддерживается браузером';
                        return;
                    }

                    if (isRecording && recorder) {
                        recorder.stop();
                        return;
                    }

                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        recorder = new MediaRecorder(stream);
                        audioChunks = [];

                        recorder.ondataavailable = (e) => {
                            if (e.data.size > 0) {
                                audioChunks.push(e.data);
                            }
                        };

                        recorder.onstop = () => {
                            const blob = new Blob(audioChunks, { type: recorder.mimeType || 'audio/webm' });
                            const ext = blob.type.includes('ogg') ? 'ogg' : 'webm';
                            const file = new File([blob], `voice-message.${ext}`, { type: blob.type });
                            attachmentInput.value = '';
                            syncVoiceInput(file);
                            showPreview(file);
                            recordingStatus.textContent = 'Голосовое готово к отправке';
                            toggleRecordingBtn.textContent = 'Голосовое';
                            isRecording = false;
                            stream.getTracks().forEach((track) => track.stop());
                        };

                        recorder.start();
                        isRecording = true;
                        recordingStatus.textContent = 'Идет запись... нажмите еще раз для остановки';
                        toggleRecordingBtn.textContent = 'Остановить запись';
                    } catch {
                        recordingStatus.textContent = 'Не удалось получить доступ к микрофону';
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
