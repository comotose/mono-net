<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('messages.index') }}" class="text-xs text-white/40 hover:text-white glitch-hover">← Назад</a>
            <img src="{{ $user->avatarUrl() }}" alt="" class="w-9 h-9 rounded-full border border-white/20" />
            <h1 class="font-medium text-lg text-white glitch-hover">{{ $user->name }}</h1>
        </div>
    </x-slot>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col min-h-[72vh]">
        <div id="messages-list" class="flex-1 space-y-3 mb-6 overflow-y-auto max-h-[50vh]">
            @foreach ($messages as $message)
                @php $mine = $message->sender_id === auth()->id(); @endphp
                <div class="message-row flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="chat-bubble {{ $message->isAudioAttachment() ? 'chat-bubble--voice' : 'max-w-[85%]' }} border border-white/15 px-3 py-2 text-sm {{ $mine ? 'bg-white/10' : 'bg-black' }}">
                        @if ($message->body)
                            <p class="whitespace-pre-wrap text-white/90">{{ $message->body }}</p>
                        @endif

                        @if ($message->attachment_path && $message->isImageAttachment())
                            <a href="{{ $message->attachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="block mt-2">
                                <img src="{{ $message->attachmentUrl() }}" alt="{{ $message->attachment_original_name }}" class="max-h-56 rounded border border-white/15 object-contain bg-black/40" />
                            </a>
                        @elseif ($message->attachment_path && $message->isAudioAttachment())
                            <div class="voice-message-card mt-2">
                                <div class="voice-message-label">
                                    <span class="voice-dot" aria-hidden="true"></span>
                                    Голосовое сообщение
                                </div>
                                <audio controls class="voice-audio-player">
                                    <source src="{{ $message->attachmentUrl() }}" type="{{ $message->attachment_mime }}">
                                </audio>
                            </div>
                        @elseif ($message->attachment_path)
                            <a href="{{ $message->attachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex mt-2 text-xs text-white/80 hover:text-white underline underline-offset-2">
                                📎 {{ $message->attachment_original_name ?: 'Скачать файл' }}
                            </a>
                        @endif
                        <p class="text-[10px] text-white/30 mt-1" data-chat-time="{{ $message->created_at->toIso8601String() }}">
                            {{ $message->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <form id="message-form" action="{{ route('messages.store', $user) }}" method="post" enctype="multipart/form-data" class="mt-auto space-y-2 border-t border-white/10 pt-4">
            @csrf
            <label for="body" class="sr-only">Сообщение</label>
            <textarea id="body" name="body" rows="3" placeholder="Введите сообщение…" class="mono-textarea min-h-[5.5rem]">{{ old('body') }}</textarea>

            <input id="attachment-input" name="attachment" type="file" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.7z,.csv,.json,.xml,.mp4,.mov,.webm,.mp3,.wav,.ogg" />
            <input id="voice-input" name="voice" type="file" class="hidden" accept="audio/*" />

            <div id="attachment-preview" class="hidden border border-white/10 p-2 text-xs text-white/70 space-y-2">
                <p id="attachment-name"></p>
                <img id="image-thumb" src="" alt="" class="hidden max-h-32 rounded border border-white/15 object-contain bg-black/40" />
                <button id="clear-attachment" type="button" class="text-white/50 hover:text-white">Очистить</button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button id="pick-attachment" type="button" class="glitch-hover inline-flex items-center px-3 py-2 border border-white/20 text-xs text-white/80 hover:text-white">
                    Файл/изображение
                </button>
                <button id="toggle-recording" type="button" class="glitch-hover inline-flex items-center px-3 py-2 border border-white/20 text-xs text-white/80 hover:text-white">
                    Голосовое
                </button>
                <span id="recording-status" class="text-xs text-white/50"></span>
            </div>

            @error('body')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
            @error('attachment')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
            @error('voice')
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
            window.chatPartnerId = "{{ $user->id }}";
            window.chatCurrentUserId = "{{ auth()->id() }}";
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
