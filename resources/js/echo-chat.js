const meta = document.querySelector('meta[name="user-id"]');
const uid = meta ? meta.getAttribute('content') : null;
const partnerId = typeof window.chatPartnerId !== 'undefined' ? window.chatPartnerId : null;

if (window.Echo && uid) {
    const buildReactionPicker = (messageId) => {
        const template = typeof window.messageReactionRouteTemplate === 'string'
            ? window.messageReactionRouteTemplate
            : '';
        const picker = document.createElement('div');
        picker.className = 'reaction-picker';
        picker.dataset.reactionPicker = '';
        picker.dataset.reactionUrl = template.replace('__MESSAGE__', messageId);

        [
            ['heart', '❤️', 'Нравится'],
            ['fire', '🔥', 'Сильно'],
            ['clap', '👏', 'Поддерживаю'],
            ['eyes', '👀', 'Смотрю'],
        ].forEach(([kind, symbol, label]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'reaction-chip';
            button.dataset.reactionKind = kind;
            button.setAttribute('aria-pressed', 'false');
            button.title = label;
            button.innerHTML = `<span class="reaction-symbol">${symbol}</span><span class="reaction-count"></span>`;
            picker.appendChild(button);
        });

        return picker;
    };

    const renderMessageBubble = (message) => {
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble mono-message-bubble mono-message-bubble--other max-w-[85%]';

            if (message.body) {
                const body = document.createElement('p');
                body.className = 'whitespace-pre-wrap mono-body-sm';
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
                img.className = 'max-h-56 rounded-xl border object-contain mono-image-frame';
                link.appendChild(img);
                bubble.appendChild(link);
            } else if (url && mime.startsWith('audio/')) {
                bubble.classList.remove('max-w-[85%]');
                bubble.classList.add('chat-bubble--voice');

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
            link.className = 'mono-inline-link inline-flex items-center gap-2 mt-2';
            link.innerHTML = '<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 3.75A1.75 1.75 0 015.75 2h5.19c.464 0 .909.184 1.237.513l2.81 2.81c.329.328.513.773.513 1.237v9.69A1.75 1.75 0 0113.75 18h-8A1.75 1.75 0 014 16.25v-12.5zm7 .81V7h2.44L11 4.56z" clip-rule="evenodd" /></svg>';

            const nameSpan = document.createElement('span');
            nameSpan.textContent = name;
            link.appendChild(nameSpan);
            bubble.appendChild(link);
        }

        const footer = document.createElement('div');
        footer.className = 'mt-3 flex items-center justify-between gap-3';

        const time = document.createElement('p');
        time.className = 'mono-caption';
        const formatter = typeof window.formatChatTimestamp === 'function'
            ? window.formatChatTimestamp
            : (value) => {
                  const d = new Date(value);
                  return Number.isNaN(d.getTime()) ? '' : d.toLocaleString(navigator.language || 'ru-RU');
              };
        time.textContent = formatter(message.created_at);
        footer.appendChild(time);

        if (message.id) {
            footer.appendChild(buildReactionPicker(message.id));
        }

        bubble.appendChild(footer);

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
        row.id = `message-${e.message.id}`;
        row.dataset.messageId = e.message.id;
        const bubble = renderMessageBubble(e.message);
        row.appendChild(bubble);
        list.appendChild(row);
        list.scrollTop = list.scrollHeight;
    });
}
