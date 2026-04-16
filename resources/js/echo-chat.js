import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const key = import.meta.env.VITE_PUSHER_APP_KEY;

if (key) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    });

    const meta = document.querySelector('meta[name="user-id"]');
    const uid = meta ? meta.getAttribute('content') : null;
    const partnerId = typeof window.chatPartnerId !== 'undefined' ? window.chatPartnerId : null;

    if (window.Echo && uid) {
        const renderMessageBubble = (message) => {
            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble max-w-[85%] border border-white/15 px-3 py-2 text-sm bg-black';

            if (message.body) {
                const body = document.createElement('p');
                body.className = 'whitespace-pre-wrap text-white/90';
                body.textContent = message.body;
                bubble.appendChild(body);
            }

            const attachment = message.attachment ?? null;
            const mime = attachment?.mime ?? '';
            const url = attachment?.url ?? '';
            const name = attachment?.name ?? 'Скачать файл';

            if (url && mime.startsWith('image/')) {
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'block mt-2';

                const img = document.createElement('img');
                img.src = url;
                img.alt = name;
                img.className = 'max-h-56 rounded border border-white/15 object-contain bg-black/40';
                link.appendChild(img);
                bubble.appendChild(link);
            } else if (url && mime.startsWith('audio/')) {
                const voiceWrap = document.createElement('div');
                voiceWrap.className = 'voice-message-card mt-2';

                const voiceLabel = document.createElement('div');
                voiceLabel.className = 'voice-message-label';
                voiceLabel.innerHTML = '<span class="voice-dot" aria-hidden="true"></span>Голосовое сообщение';

                const audio = document.createElement('audio');
                audio.className = 'voice-audio-player';
                audio.controls = true;

                const source = document.createElement('source');
                source.src = url;
                source.type = mime;
                audio.appendChild(source);
                voiceWrap.appendChild(voiceLabel);
                voiceWrap.appendChild(audio);
                bubble.appendChild(voiceWrap);
            } else if (url) {
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'inline-flex mt-2 text-xs text-white/80 hover:text-white underline underline-offset-2';
                link.textContent = `📎 ${name}`;
                bubble.appendChild(link);
            }

            const time = document.createElement('p');
            time.className = 'text-[10px] text-white/30 mt-1';
            const formatter = typeof window.formatChatTimestamp === 'function'
                ? window.formatChatTimestamp
                : (value) => {
                      const d = new Date(value);
                      return Number.isNaN(d.getTime()) ? '' : d.toLocaleString(navigator.language || 'ru-RU');
                  };
            time.textContent = formatter(message.created_at);
            bubble.appendChild(time);

            return bubble;
        };

        window.Echo.private(`messages.${uid}`).listen('.message.sent', (e) => {
            if (!e.message || partnerId === null) {
                return;
            }
            if (String(e.message.sender.id) !== String(partnerId)) {
                return;
            }

            const list = document.getElementById('messages-list');
            if (!list) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'message-row flex justify-start';
            const bubble = renderMessageBubble(e.message);
            row.appendChild(bubble);
            list.appendChild(row);
            list.scrollTop = list.scrollHeight;
        });
    }
}
