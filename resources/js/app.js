import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function initLikes() {
    document.querySelectorAll('.like-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.getAttribute('data-url');
            if (!url) {
                return;
            }
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    return;
                }
                const data = await res.json();
                const countEl = btn.querySelector('.like-count');
                const iconEl = btn.querySelector('.like-icon');
                if (countEl) {
                    countEl.textContent = data.count;
                }
                if (iconEl) {
                    iconEl.textContent = data.liked ? '♥' : '♡';
                }
                btn.setAttribute('data-liked', data.liked ? '1' : '0');
            } catch {
                /* ignore */
            }
        });
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
    const unreadUrl = document.querySelector('meta[name="notifications-unread-url"]')?.getAttribute('content');

    if (!userId || !unreadUrl || !('Notification' in window)) {
        return;
    }

    const askedKey = `mono_push_permission_asked_${userId}`;
    const canUseAudioContext = typeof window.AudioContext !== 'undefined' || typeof window.webkitAudioContext !== 'undefined';
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    const audioContext = canUseAudioContext ? new AudioCtx() : null;
    let soundUnlocked = false;

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
        if (!audioContext || !soundUnlocked) {
            return;
        }
        try {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            oscillator.type = 'triangle';
            oscillator.frequency.setValueAtTime(880, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.001, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.06, audioContext.currentTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.22);
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.24);
        } catch {
            /* ignore */
        }
    };

    const requestPermission = () => {
        if (Notification.permission !== 'default') {
            return;
        }
        localStorage.setItem(askedKey, '1');
        Notification.requestPermission().catch(() => {
            /* ignore */
        });
    };

    if (!localStorage.getItem(askedKey)) {
        const accepted = window.confirm('Разрешить браузерные уведомления от MONO для новых сообщений и активности?');
        if (accepted) {
            requestPermission();
        } else {
            localStorage.setItem(askedKey, '1');
        }
    } else if (Notification.permission === 'default') {
        requestPermission();
    }

    if (Notification.permission !== 'granted') {
        return;
    }

    const shownStorageKey = `mono_shown_notification_ids_${userId}`;
    const shownRaw = JSON.parse(localStorage.getItem(shownStorageKey) ?? '[]');
    const shown = new Set(Array.isArray(shownRaw) ? shownRaw : []);
    const sessionSeen = new Set();

    const persistShown = () => {
        const ids = Array.from(shown).slice(-200);
        localStorage.setItem(shownStorageKey, JSON.stringify(ids));
    };

    const poll = async () => {
        try {
            const res = await fetch(unreadUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            if (!res.ok) {
                return;
            }
            const data = await res.json();
            const notifications = Array.isArray(data.notifications) ? data.notifications : [];

            notifications
                .slice()
                .reverse()
                .forEach((item) => {
                    if (!item?.id || shown.has(item.id) || sessionSeen.has(item.id)) {
                        return;
                    }

                    const browserNotification = new Notification(item.title ?? 'Уведомление', {
                        body: item.text ?? '',
                        tag: `mono-${item.id}`,
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
                    sessionSeen.add(item.id);
                    playNotificationSound();
                });

            persistShown();
        } catch {
            /* ignore */
        }
    };

    poll();
    window.setInterval(poll, 15000);
}

document.addEventListener('DOMContentLoaded', () => {
    initLikes();
    initAnomalyFlicker();
    initBrowserNotifications();
});
