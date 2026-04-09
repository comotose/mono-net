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

            const bubble = document.createElement('div');
            bubble.className = 'max-w-[85%] border border-white/15 px-3 py-2 text-sm bg-black';

            const body = document.createElement('p');
            body.className = 'whitespace-pre-wrap text-white/90';
            body.textContent = e.message.body;

            const time = document.createElement('p');
            time.className = 'text-[10px] text-white/30 mt-1';
            const d = new Date(e.message.created_at);
            time.textContent = d.toLocaleString('ru-RU');

            bubble.appendChild(body);
            bubble.appendChild(time);
            row.appendChild(bubble);
            list.appendChild(row);
            list.scrollTop = list.scrollHeight;
        });
    }
}
