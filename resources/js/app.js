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

document.addEventListener('DOMContentLoaded', () => {
    initLikes();
    initAnomalyFlicker();
});
