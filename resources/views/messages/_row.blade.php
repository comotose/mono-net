@php
    $mine = $message->sender_id === auth()->id();
@endphp

<div id="message-{{ $message->id }}" class="message-row flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
    <div class="chat-bubble {{ $message->isAudioAttachment() ? 'chat-bubble--voice' : 'max-w-[85%]' }} mono-message-bubble {{ $mine ? 'mono-message-bubble--mine' : 'mono-message-bubble--other' }}">
        @if ($message->body)
            <p class="whitespace-pre-wrap mono-body-sm">{{ $message->body }}</p>
        @endif

        @if ($message->attachment_path && $message->isImageAttachment())
            <a href="{{ $message->attachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="block mt-2">
                <img src="{{ $message->attachmentUrl() }}" alt="{{ $message->attachment_original_name }}" class="max-h-56 rounded-xl border object-contain mono-image-frame" />
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
            <a href="{{ $message->attachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="mono-inline-link mt-2 inline-flex items-center gap-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4 3.75A1.75 1.75 0 015.75 2h5.19c.464 0 .909.184 1.237.513l2.81 2.81c.329.328.513.773.513 1.237v9.69A1.75 1.75 0 0113.75 18h-8A1.75 1.75 0 014 16.25v-12.5zm7 .81V7h2.44L11 4.56z" clip-rule="evenodd" />
                </svg>
                <span>{{ $message->attachment_original_name ?: 'Скачать файл' }}</span>
            </a>
        @endif

        <div class="mt-3 flex items-center justify-between gap-3">
            <p class="mono-caption" data-chat-time="{{ $message->created_at->toIso8601String() }}">
                {{ $message->created_at->format('d.m.Y H:i') }}
            </p>
            @include('reactions._picker', ['subject' => $message])
        </div>
    </div>
</div>
