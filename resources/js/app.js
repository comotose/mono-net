import './bootstrap';

import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Alpine = Alpine;
window.Pusher = Pusher;

const echoKey = import.meta.env.VITE_PUSHER_APP_KEY;
const echoCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1';
const echoScheme = import.meta.env.VITE_PUSHER_SCHEME ?? 'https';
const echoHost = import.meta.env.VITE_PUSHER_HOST;
const parsedPort = Number.parseInt(import.meta.env.VITE_PUSHER_PORT ?? '', 10);
const echoPort = Number.isNaN(parsedPort) ? (echoScheme === 'https' ? 443 : 80) : parsedPort;

if (echoKey && !window.Echo) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: echoKey,
        cluster: echoCluster,
        wsHost: echoHost || `ws-${echoCluster}.pusher.com`,
        wsPort: echoPort,
        wssPort: echoPort,
        forceTLS: echoScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

Alpine.start();

const REACTION_OPTIONS = [
    ['heart', '❤️', 'Нравится'],
    ['fire', '🔥', 'Сильно'],
    ['clap', '👏', 'Поддерживаю'],
    ['eyes', '👀', 'Смотрю'],
];

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function parseHtml(html) {
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstElementChild;
}

function firstError(data) {
    if (!data || typeof data !== 'object') {
        return 'Не удалось выполнить действие.';
    }

    if (typeof data.message === 'string' && data.message.length > 0) {
        return data.message;
    }

    const errors = data.errors ?? null;
    if (!errors || typeof errors !== 'object') {
        return 'Не удалось выполнить действие.';
    }

    const entry = Object.values(errors).find((value) => Array.isArray(value) && value.length > 0);
    return Array.isArray(entry) ? entry[0] : 'Не удалось выполнить действие.';
}

async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers ?? {}),
        },
        ...options,
    });

    const contentType = response.headers.get('content-type') ?? '';
    const payload = contentType.includes('application/json') ? await response.json() : null;

    if (!response.ok) {
        const error = new Error(firstError(payload));
        error.status = response.status;
        error.payload = payload;
        throw error;
    }

    return payload;
}

function setFormError(form, message = '') {
    const node = form.querySelector('[data-form-error]');
    if (!node) {
        return;
    }

    node.textContent = message;
    node.classList.toggle('hidden', message.length === 0);
}

function setFormBusy(form, busy) {
    form.classList.toggle('is-pending', busy);
    form.setAttribute('aria-busy', busy ? 'true' : 'false');

    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((control) => {
        if (busy) {
            control.dataset.wasDisabled = control.disabled ? '1' : '0';
            control.disabled = true;
            return;
        }

        control.disabled = control.dataset.wasDisabled === '1';
        delete control.dataset.wasDisabled;
    });
}

function markInserted(node) {
    if (!node) {
        return;
    }

    node.classList.add('mono-enter-highlight');
    window.setTimeout(() => node.classList.remove('mono-enter-highlight'), 900);
}

function removeWithMotion(node) {
    if (!node) {
        return;
    }

    node.classList.add('mono-removing');
    window.setTimeout(() => node.remove(), 260);
}

function pulseNode(node) {
    if (!node) {
        return;
    }

    node.classList.remove('mono-pulse-once');
    void node.offsetWidth;
    node.classList.add('mono-pulse-once');
}

function clearEmptyState(container) {
    const empty = container?.querySelector('.mono-empty-state');
    if (empty) {
        empty.remove();
    }
}

function themeLabel(theme) {
    return theme === 'light' ? 'Тёмная тема' : 'Светлая тема';
}

function applyTheme(theme) {
    window.clearTimeout(window.monoThemeChangeTimeout);
    document.documentElement.classList.add('mono-theme-changing');
    document.documentElement.classList.remove('theme-dark', 'theme-light');
    document.documentElement.classList.add(theme === 'light' ? 'theme-light' : 'theme-dark');
    localStorage.setItem('mono-theme', theme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', themeLabel(theme));
        button.setAttribute('title', themeLabel(theme));

        const label = button.querySelector('[data-theme-label]');
        if (label) {
            label.textContent = themeLabel(theme);
            return;
        }
    });

    window.monoThemeChangeTimeout = window.setTimeout(() => {
        document.documentElement.classList.remove('mono-theme-changing');
    }, 340);
}

function initLightbox() {
    const root = document.querySelector('[data-lightbox-root]');
    if (!root) {
        return;
    }

    const image = root.querySelector('[data-lightbox-image]');
    const caption = root.querySelector('[data-lightbox-caption]');
    const closers = root.querySelectorAll('[data-lightbox-close]');
    const prevButton = root.querySelector('[data-lightbox-prev]');
    const nextButton = root.querySelector('[data-lightbox-next]');
    const counter = root.querySelector('[data-lightbox-counter]');
    const openClass = 'is-open';
    let currentGallery = [];
    let currentIndex = 0;
    let currentCaption = '';

    const render = () => {
        if (!image || currentGallery.length === 0) {
            return;
        }

        const safeIndex = ((currentIndex % currentGallery.length) + currentGallery.length) % currentGallery.length;
        currentIndex = safeIndex;
        image.src = currentGallery[safeIndex];
        image.alt = currentCaption;

        if (caption) {
            caption.textContent = currentCaption;
            caption.classList.toggle('hidden', !currentCaption);
        }

        if (counter) {
            const multiple = currentGallery.length > 1;
            counter.textContent = multiple ? `${safeIndex + 1} / ${currentGallery.length}` : '';
            counter.classList.toggle('hidden', !multiple);
        }

        if (prevButton) {
            prevButton.classList.toggle('hidden', currentGallery.length <= 1);
        }

        if (nextButton) {
            nextButton.classList.toggle('hidden', currentGallery.length <= 1);
        }
    };

    const setGallery = (gallery, index, captionText) => {
        currentGallery = Array.isArray(gallery) ? gallery.filter(Boolean) : [];
        currentIndex = Number.isInteger(index) ? index : 0;
        currentCaption = captionText ?? '';
        render();
    };

    const close = () => {
        root.classList.add('hidden');
        root.classList.remove(openClass);
        root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        currentGallery = [];
        currentIndex = 0;
        currentCaption = '';
        if (image) {
            image.removeAttribute('src');
            image.removeAttribute('alt');
        }
        if (caption) {
            caption.textContent = '';
            caption.classList.add('hidden');
        }
        if (counter) {
            counter.textContent = '';
            counter.classList.add('hidden');
        }
        prevButton?.classList.add('hidden');
        nextButton?.classList.add('hidden');
    };

    const open = (trigger) => {
        const triggerImage = trigger.querySelector('img');
        const galleryRaw = trigger.dataset.lightboxGallery;
        const fallbackSrc = trigger.dataset.lightboxSrc || triggerImage?.currentSrc || triggerImage?.src;
        if (!image || !fallbackSrc) {
            return;
        }

        let gallery = [fallbackSrc];
        if (galleryRaw) {
            try {
                const parsed = JSON.parse(galleryRaw);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    gallery = parsed;
                }
            } catch {
                gallery = [fallbackSrc];
            }
        }

        const captionText = trigger.dataset.lightboxCaption || triggerImage?.alt || '';
        const index = Number.parseInt(trigger.dataset.lightboxIndex ?? '0', 10);
        root.classList.remove('hidden');
        root.classList.add(openClass);
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        setGallery(gallery, Number.isNaN(index) ? 0 : index, captionText);
    };

    const move = (delta) => {
        if (currentGallery.length <= 1) {
            return;
        }

        currentIndex += delta;
        render();
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-lightbox-trigger]');
        if (trigger) {
            event.preventDefault();
            open(trigger);
            return;
        }

        if (!root.classList.contains('hidden') && event.target.closest('[data-lightbox-prev]')) {
            event.preventDefault();
            move(-1);
            return;
        }

        if (!root.classList.contains('hidden') && event.target.closest('[data-lightbox-next]')) {
            event.preventDefault();
            move(1);
            return;
        }

        if (!root.classList.contains('hidden') && event.target.closest('[data-lightbox-close]')) {
            event.preventDefault();
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (root.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            close();
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            move(-1);
            return;
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            move(1);
        }
    });

    closers.forEach((node) => {
        node.addEventListener('click', (event) => {
            event.preventDefault();
            close();
        });
    });
}

function initTheme() {
    const savedTheme = localStorage.getItem('mono-theme') === 'light' ? 'light' : 'dark';
    applyTheme(savedTheme);

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-theme-toggle]');
        if (!toggle) {
            return;
        }

        event.preventDefault();
        const nextTheme = document.documentElement.classList.contains('theme-light') ? 'dark' : 'light';
        applyTheme(nextTheme);
    });
}

function initInteractionEffects() {
    const interactiveSelector = [
        '.mono-button-primary',
        '.mono-button-secondary',
        '.mono-button-danger',
        '.mono-theme-toggle',
        '.mono-icon-button',
        '.mono-icon-toggle',
        '.reaction-chip',
        '.mono-comments-summary',
        '.mono-thread-summary',
        '.mono-reply-summary',
        '.mono-segmented-control__item',
    ].join(',');

    document.addEventListener('pointerdown', (event) => {
        const target = event.target.closest(interactiveSelector);
        if (!target) {
            return;
        }

        const rect = target.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.className = 'mono-ripple';
        ripple.style.setProperty('--ripple-x', `${event.clientX - rect.left}px`);
        ripple.style.setProperty('--ripple-y', `${event.clientY - rect.top}px`);

        target.querySelectorAll('.mono-ripple').forEach((node) => node.remove());
        target.appendChild(ripple);
        ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
    });
}

function createReactionPicker(url) {
    const picker = document.createElement('div');
    picker.className = 'reaction-picker';
    picker.dataset.reactionPicker = '';
    picker.dataset.reactionUrl = url;

    REACTION_OPTIONS.forEach(([kind, symbol, label]) => {
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
}

function ensureToastStack() {
    return document.getElementById('mono-toast-stack');
}

function showToast({ title, text = '', variant = 'default', iconClass = 'bi bi-info-circle', imageUrl = '', duration = 5000, actionText = '', onAction = null, eyebrow = '' }) {
    const stack = ensureToastStack();
    if (!stack) {
        return;
    }

    const toast = document.createElement('div');
    toast.className = `mono-toast mono-toast--${variant}`;
    toast.setAttribute('role', variant === 'error' ? 'alert' : 'status');
    if (imageUrl) {
        toast.classList.add('mono-toast--has-avatar');
    }
    if (duration > 0) {
        toast.classList.add('mono-toast--timed');
        toast.style.setProperty('--mono-toast-duration', `${duration}ms`);
    }

    const body = document.createElement('div');
    body.className = 'mono-toast__body';

    const iconWrap = document.createElement('span');
    iconWrap.className = imageUrl ? 'mono-toast__icon mono-toast__icon--avatar' : 'mono-toast__icon';
    if (imageUrl) {
        const image = document.createElement('img');
        image.src = imageUrl;
        image.alt = '';
        image.className = 'mono-toast__avatar';
        image.width = 52;
        image.height = 52;
        iconWrap.appendChild(image);
    } else {
        const icon = document.createElement('i');
        icon.className = iconClass;
        iconWrap.appendChild(icon);
    }

    const content = document.createElement('div');
    content.className = 'mono-toast__content';

    const meta = document.createElement('div');
    meta.className = 'mono-toast__meta';

    if (eyebrow) {
        const eyebrowNode = document.createElement('span');
        eyebrowNode.className = 'mono-toast__eyebrow';
        eyebrowNode.textContent = eyebrow;
        meta.appendChild(eyebrowNode);
    }

    content.appendChild(meta);

    const header = document.createElement('div');
    header.className = 'mono-toast__header';

    const titleNode = document.createElement('p');
    titleNode.className = 'mono-toast__title';
    titleNode.textContent = title;
    header.appendChild(titleNode);
    content.appendChild(header);

    if (text) {
        const textNode = document.createElement('p');
        textNode.className = 'mono-toast__text';
        textNode.textContent = text;
        content.appendChild(textNode);
    }

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'mono-toast__close';
    closeButton.dataset.toastClose = '';
    closeButton.setAttribute('aria-label', 'Закрыть');
    closeButton.innerHTML = '<i class="bi bi-x-lg"></i>';

    meta.appendChild(closeButton);

    if (actionText) {
        const footer = document.createElement('div');
        footer.className = 'mono-toast__footer';

        const actionButton = document.createElement('button');
        actionButton.type = 'button';
        actionButton.className = 'mono-button-secondary mono-button-secondary--sm mono-toast__action';
        actionButton.dataset.toastAction = '';
        actionButton.innerHTML = '<i class="bi bi-box-arrow-up-right"></i>';

        const actionLabel = document.createElement('span');
        actionLabel.textContent = actionText;
        actionButton.appendChild(actionLabel);

        footer.appendChild(actionButton);
        content.appendChild(footer);
    }

    body.appendChild(iconWrap);
    body.appendChild(content);
    toast.appendChild(body);

    let isRemoving = false;
    const removeToast = () => {
        if (isRemoving) {
            return;
        }

        isRemoving = true;
        toast.classList.add('is-leaving');
        window.setTimeout(() => toast.remove(), 220);
    };

    toast.querySelector('[data-toast-close]')?.addEventListener('click', removeToast);
    toast.querySelector('[data-toast-action]')?.addEventListener('click', async () => {
        if (typeof onAction === 'function') {
            await onAction();
        }
        removeToast();
    });

    stack.prepend(toast);

    if (duration > 0) {
        window.setTimeout(removeToast, duration);
    }
}

function toastMetaForNotificationKind(kind) {
    switch (kind) {
        case 'message':
            return { iconClass: 'bi bi-chat-dots', eyebrow: 'Сообщение' };
        case 'comment':
            return { iconClass: 'bi bi-chat-right-text', eyebrow: 'Комментарий' };
        case 'follow':
            return { iconClass: 'bi bi-person-plus', eyebrow: 'Подписка' };
        case 'reaction':
            return { iconClass: 'bi bi-heart', eyebrow: 'Реакция' };
        default:
            return { iconClass: 'bi bi-bell', eyebrow: 'Уведомление' };
    }
}

function initAsyncUi() {
    document.addEventListener('click', async (event) => {
        const reactionButton = event.target.closest('[data-reaction-kind]');
        if (!reactionButton) {
            return;
        }

        const picker = reactionButton.closest('[data-reaction-picker]');
        const url = picker?.getAttribute('data-reaction-url');
        const kind = reactionButton.getAttribute('data-reaction-kind');

        if (!picker || !url || !kind) {
            return;
        }

        event.preventDefault();

        try {
            const payload = new FormData();
            payload.set('kind', kind);
            const data = await requestJson(url, { method: 'POST', body: payload });
            const nextPicker = parseHtml(data.html ?? '');
            if (nextPicker) {
                picker.replaceWith(nextPicker);
            }
        } catch {
            /* ignore */
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.matches('[data-async-post-form]')) {
            event.preventDefault();
            setFormError(form);
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });
                const list = document.getElementById('posts-list');
                const node = parseHtml(data.html ?? '');
                if (list && node) {
                    clearEmptyState(list);
                    list.prepend(node);
                    markInserted(node);
                }
                form.reset();
                showToast({ title: 'Публикация создана', text: 'Запись добавлена в ленту.', iconClass: 'bi bi-send-check', variant: 'success' });
            } catch (error) {
                setFormError(form, error.message);
                showToast({ title: 'Не удалось создать публикацию', text: error.message, iconClass: 'bi bi-x-circle', variant: 'error' });
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-post-delete-form]')) {
            event.preventDefault();
            const confirmationText = form.querySelector('[data-confirm]')?.getAttribute('data-confirm') ?? 'Удалить?';
            if (!window.confirm(confirmationText)) {
                return;
            }
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });
                removeWithMotion(document.getElementById(`post-${data.id}`));
                showToast({ title: 'Публикация удалена', iconClass: 'bi bi-trash3', variant: 'success' });
            } catch {
                /* ignore */
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-comment-form]')) {
            event.preventDefault();
            setFormError(form);
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });
                const list = data.parent_id
                    ? document.querySelector(`[data-comment-replies="${data.parent_id}"]`)
                    : document.querySelector(`[data-comments-list="${data.post_id}"]`);
                const node = parseHtml(data.html ?? '');
                if (list && node) {
                    list.appendChild(node);
                    list.classList.remove('is-empty');
                    markInserted(node);
                }

                const postDetails = document.querySelector(`[data-post-comments-details="${data.post_id}"]`);
                if (postDetails) {
                    postDetails.open = true;
                }

                const commentsCount = document.querySelector(`[data-comments-count="${data.post_id}"]`);
                if (commentsCount) {
                    commentsCount.textContent = String((Number.parseInt(commentsCount.textContent || '0', 10) || 0) + 1);
                    pulseNode(commentsCount);
                }

                if (data.parent_id) {
                    const threadDetails = document.querySelector(`[data-comment-thread="${data.parent_id}"]`);
                    if (threadDetails) {
                        threadDetails.classList.remove('hidden');
                        threadDetails.open = true;
                    }

                    const repliesCount = document.querySelector(`[data-replies-count="${data.parent_id}"]`);
                    if (repliesCount) {
                        repliesCount.textContent = String((Number.parseInt(repliesCount.textContent || '0', 10) || 0) + 1);
                        pulseNode(repliesCount);
                    }
                }

                form.reset();
                form.closest('.mono-reply-details')?.removeAttribute('open');
                showToast({ title: 'Комментарий отправлен', iconClass: 'bi bi-chat-right-dots', variant: 'success' });
            } catch (error) {
                setFormError(form, error.message);
                showToast({ title: 'Не удалось отправить комментарий', text: error.message, iconClass: 'bi bi-x-circle', variant: 'error' });
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-comment-delete-form]')) {
            event.preventDefault();
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });
                removeWithMotion(document.getElementById(`comment-${data.id}`));
                showToast({ title: 'Комментарий удалён', iconClass: 'bi bi-trash3', variant: 'success' });
            } catch {
                /* ignore */
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-follow-form]')) {
            event.preventDefault();
            const userId = form.getAttribute('data-user-id');
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });

                document.querySelectorAll(`[data-follow-button-target="${userId}"]`).forEach((node) => {
                    node.innerHTML = data.button_html ?? '';
                });

                document.querySelectorAll(`[data-followers-count="${userId}"]`).forEach((node) => {
                    node.textContent = data.followers_count;
                    pulseNode(node);
                });

                document.querySelectorAll(`[data-following-count="${userId}"]`).forEach((node) => {
                    node.textContent = data.following_count;
                    pulseNode(node);
                });
                showToast({ title: data.is_following ? 'Подписка оформлена' : 'Подписка отменена', iconClass: data.is_following ? 'bi bi-person-check' : 'bi bi-person-dash', variant: 'success' });
            } catch {
                /* ignore */
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-role-form]')) {
            event.preventDefault();
            const userId = form.getAttribute('data-user-id');
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });

                document.querySelectorAll(`[data-role-label-user="${userId}"]`).forEach((node) => {
                    node.outerHTML = data.badge_html ?? node.outerHTML;
                });

                document.querySelectorAll(`[data-role-manager-target="${userId}"]`).forEach((node) => {
                    node.innerHTML = data.manager_html ?? '';
                });
                showToast({ title: 'Роль обновлена', text: data.role_label ?? '', iconClass: 'bi bi-shield-check', variant: 'success' });
            } catch {
                /* ignore */
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-notification-read-form]')) {
            event.preventDefault();
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });
                const item = document.getElementById(`notification-${data.id}`);
                item?.classList.remove('mono-notification--unread');
                removeWithMotion(form);
                window.dispatchEvent(new CustomEvent('mono:notifications:decrement'));
                showToast({ title: 'Уведомление отмечено', iconClass: 'bi bi-check2', variant: 'success' });
            } catch {
                /* ignore */
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-read-all-form]')) {
            event.preventDefault();
            setFormBusy(form, true);

            try {
                await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });

                document.querySelectorAll('.mono-notification--unread').forEach((item) => {
                    item.classList.remove('mono-notification--unread');
                });

                document.querySelectorAll('[data-async-notification-read-form]').forEach((item) => {
                    removeWithMotion(item);
                });

                removeWithMotion(form);
                window.dispatchEvent(new CustomEvent('mono:notifications:clear'));
                showToast({ title: 'Все уведомления прочитаны', iconClass: 'bi bi-check2-all', variant: 'success' });
            } catch {
                /* ignore */
            } finally {
                setFormBusy(form, false);
            }

            return;
        }

        if (form.matches('[data-async-message-form]')) {
            event.preventDefault();
            setFormError(form);
            setFormBusy(form, true);

            try {
                const data = await requestJson(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                });
                const list = document.getElementById('messages-list');
                const node = parseHtml(data.html ?? '');
                if (list && node) {
                    list.appendChild(node);
                    markInserted(node);
                    list.scrollTop = list.scrollHeight;
                }
                form.reset();
                window.resetChatComposer?.();
                showToast({ title: 'Сообщение отправлено', iconClass: 'bi bi-send-check', variant: 'success', duration: 2500 });
            } catch (error) {
                setFormError(form, error.message);
                showToast({ title: 'Не удалось отправить сообщение', text: error.message, iconClass: 'bi bi-x-circle', variant: 'error' });
            } finally {
                setFormBusy(form, false);
            }
        }
    });
}

/** Очень лёгкая «аномалия» яркости — редко и с плавным переходом */
function initAnomalyFlicker() {
    const root = document.querySelector('.anomaly-root');
    if (!root) {
        return;
    }
    root.style.transition = 'opacity 1.1s ease-in-out';

    const tick = () => {
        if (Math.random() > 0.9985) {
            root.style.opacity = '0.988';
            window.setTimeout(() => {
                root.style.opacity = '1';
            }, 450);
        }
    };
    window.setInterval(tick, 380);
}

function initBrowserNotifications() {
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

    if (!userId) {
        return;
    }

    const supportsBrowserNotifications = 'Notification' in window;
    const canUseAudioContext = typeof window.AudioContext !== 'undefined' || typeof window.webkitAudioContext !== 'undefined';
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    const audioContext = canUseAudioContext ? new AudioCtx() : null;
    let soundUnlocked = false;
    let permissionToastShown = false;

    const unlockSound = () => {
        if (!audioContext || soundUnlocked) {
            return;
        }
        audioContext.resume().then(() => {
            soundUnlocked = true;
        }).catch(() => {
            /* ignore */
        });
    };

    ['click', 'keydown', 'touchstart'].forEach((eventName) => {
        window.addEventListener(eventName, unlockSound, { once: true, passive: true });
    });

    const playNotificationSound = () => {
        try {
            if (!audioContext || !soundUnlocked) {
                if (typeof navigator.vibrate === 'function') {
                    navigator.vibrate(120);
                }
                return;
            }

            const now = audioContext.currentTime;
            const notes = [
                { frequency: 1396.91, start: 0, duration: 0.075, gain: 0.022 },
                { frequency: 1760, start: 0.085, duration: 0.14, gain: 0.032 },
            ];

            notes.forEach(({ frequency, start, duration, gain }) => {
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                const toneStart = now + start;
                const tonePeak = toneStart + Math.min(duration * 0.35, 0.028);
                const toneEnd = toneStart + duration;

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(frequency, toneStart);
                oscillator.frequency.exponentialRampToValueAtTime(frequency * 0.985, toneEnd);

                gainNode.gain.setValueAtTime(0.0001, toneStart);
                gainNode.gain.exponentialRampToValueAtTime(gain, tonePeak);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, toneEnd);

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.start(toneStart);
                oscillator.stop(toneEnd + 0.02);
            });
        } catch {
            /* ignore */
        }
    };

    const requestPermission = async () => {
        if (!supportsBrowserNotifications) {
            showToast({
                title: 'Браузер не поддерживает push',
                text: 'Системные уведомления недоступны в этом браузере.',
                iconClass: 'bi bi-bell-slash',
                variant: 'error',
            });
            return 'denied';
        }

        unlockSound();

        try {
            return await Notification.requestPermission();
        } catch {
            return 'denied';
        }
    };

    const showPermissionToast = () => {
        if (!supportsBrowserNotifications || Notification.permission !== 'default' || permissionToastShown) {
            return;
        }

        permissionToastShown = true;
        showToast({
            title: 'Включить push-уведомления?',
            text: 'Тогда браузер будет показывать новые сообщения, реакции и активность сразу.',
            iconClass: 'bi bi-bell',
            duration: 0,
            actionText: 'Включить',
            onAction: async () => {
                const permission = await requestPermission();

                if (permission === 'granted') {
                    showToast({
                        title: 'Push включены',
                        text: 'Теперь уведомления будут приходить прямо в браузер.',
                        iconClass: 'bi bi-bell-fill',
                        variant: 'success',
                    });
                } else {
                    showToast({
                        title: 'Push не включены',
                        text: 'Если браузер уже запретил уведомления, разрешите их в настройках сайта.',
                        iconClass: 'bi bi-bell-slash',
                        variant: 'error',
                        duration: 7000,
                    });
                }
            },
        });
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-enable-browser-notifications]');
        if (!button) {
            return;
        }

        event.preventDefault();
        const permission = await requestPermission();

        if (permission === 'granted') {
            showToast({
                title: 'Push включены',
                text: 'Системные уведомления активированы.',
                iconClass: 'bi bi-bell-fill',
                variant: 'success',
            });
        } else {
            showToast({
                title: 'Нужен доступ к уведомлениям',
                text: 'Проверьте разрешения сайта в браузере.',
                iconClass: 'bi bi-bell-slash',
                variant: 'error',
                duration: 7000,
            });
        }
    });

    showPermissionToast();

    const shownStorageKey = `mono_shown_notification_ids_${userId}`;
    const shownRaw = JSON.parse(localStorage.getItem(shownStorageKey) ?? '[]');
    const shown = new Set(Array.isArray(shownRaw) ? shownRaw : []);

    const persistShown = () => {
        const ids = Array.from(shown).slice(-200);
        localStorage.setItem(shownStorageKey, JSON.stringify(ids));
    };

    const updateNotificationCounter = (nextCount) => {
        document.querySelectorAll('[data-notification-counter]').forEach((node) => {
            const count = Math.max(0, nextCount);
            if (count === 0) {
                node.remove();
                return;
            }

            node.textContent = count > 99 ? '99+' : String(count);
        });
    };

    const currentCount = () => {
        const first = document.querySelector('[data-notification-counter]');
        if (!first) {
            return 0;
        }

        const raw = first.getAttribute('data-count') ?? first.textContent ?? '0';
        const normalized = Number.parseInt(raw, 10);
        return Number.isNaN(normalized) ? 0 : normalized;
    };

    const ensureCounter = () => {
        document.querySelectorAll('[data-notification-counter-slot]').forEach((slot) => {
            if (!slot.querySelector('[data-notification-counter]')) {
                const span = document.createElement('span');
                span.className = slot.getAttribute('data-counter-class') ?? 'mono-counter-pill ml-1';
                span.dataset.notificationCounter = '';
                span.dataset.count = '0';
                slot.appendChild(span);
            }
        });
    };

    const incrementCounter = () => {
        ensureCounter();
        const next = currentCount() + 1;
        document.querySelectorAll('[data-notification-counter]').forEach((node) => {
            node.setAttribute('data-count', String(next));
        });
        updateNotificationCounter(next);
    };

    const showBrowserNotification = (item) => {
        if (!supportsBrowserNotifications || Notification.permission !== 'granted') {
            return;
        }

        if (!item?.id || shown.has(item.id)) {
            return;
        }

        const browserNotification = new Notification(item.title ?? 'Уведомление', {
            body: item.text ?? '',
            tag: `mono-${item.id}`,
            icon: item.icon ?? undefined,
            badge: item.icon ?? undefined,
            silent: false,
            renotify: true,
        });

        browserNotification.onclick = () => {
            if (item.url) {
                window.location.href = item.url;
            } else {
                window.focus();
            }
        };

        shown.add(item.id);
        persistShown();
    };

    window.addEventListener('mono:notifications:clear', () => {
        updateNotificationCounter(0);
    });

    window.addEventListener('mono:notifications:decrement', () => {
        const next = Math.max(0, currentCount() - 1);
        document.querySelectorAll('[data-notification-counter]').forEach((node) => {
            node.setAttribute('data-count', String(next));
        });
        updateNotificationCounter(next);
    });

    if (!window.Echo) {
        showToast({
            title: 'Realtime не подключён',
            text: 'Проверьте PUSHER-настройки, если push и toast не приходят в реальном времени.',
            iconClass: 'bi bi-wifi-off',
            variant: 'error',
            duration: 7000,
        });
        return;
    }

    const connection = window.Echo.connector?.pusher?.connection;
    if (connection) {
        connection.bind('error', () => {
            showToast({
                title: 'Ошибка realtime-подключения',
                text: 'Сокет не подключился. Проверьте PUSHER_HOST, PUSHER_PORT и BROADCAST_DRIVER.',
                iconClass: 'bi bi-wifi-off',
                variant: 'error',
                duration: 7000,
            });
        });
    }

    window.Echo.private(`App.Models.User.${userId}`).notification((item) => {
        const toastMeta = toastMetaForNotificationKind(item.kind);
        incrementCounter();
        playNotificationSound();
        showToast({
            title: item.title ?? 'Уведомление',
            text: item.text ?? '',
            iconClass: toastMeta.iconClass,
            imageUrl: item.icon ?? '',
            variant: item.kind ?? 'default',
            duration: 6500,
            eyebrow: toastMeta.eyebrow,
            actionText: item.url ? 'Открыть' : '',
            onAction: item.url ? async () => {
                window.location.href = item.url;
            } : null,
        });
        showBrowserNotification(item);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initLightbox();
    initInteractionEffects();
    initAsyncUi();
    initAnomalyFlicker();
    initBrowserNotifications();
});
