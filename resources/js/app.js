import './bootstrap';

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function timeNow() {
    return new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

window.chatRealtime = {
    escapeHtml,
    timeNow,
};

function setUserStatus(userId, isOnline) {
    document.querySelectorAll(`[data-user-status="${userId}"]`).forEach((element) => {
        element.textContent = isOnline ? 'Online' : 'Offline';
        element.classList.toggle('online', isOnline);
        element.classList.toggle('offline', !isOnline);
    });
}

function sendPresenceOnline() {
    if (!document.body.dataset.authenticated) return;

    window.axios.post('/presence/online').catch(() => {});
}

function sendPresenceOffline() {
    if (!document.body.dataset.authenticated) return;

    const token = document.head.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const body = new URLSearchParams({ _token: token });

    if (navigator.sendBeacon) {
        navigator.sendBeacon('/presence/offline', body);
        return;
    }

    fetch('/presence/offline', {
        method: 'POST',
        body,
        keepalive: true,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
            'X-CSRF-TOKEN': token,
        },
    }).catch(() => {});
}

window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.reply-action').forEach((button) => {
        button.addEventListener('click', () => {
            const replyId = button.dataset.replyId;
            const replyText = button.dataset.replyText || 'Pesan dipilih';
            const input = document.getElementById('reply_to_id');
            const body = document.getElementById('message_body');
            if (input) input.value = replyId;
            if (body) {
                body.placeholder = `Membalas: ${replyText}`;
                body.focus();
            }
        });
    });

    if (document.body.dataset.authenticated && window.Echo) {
        sendPresenceOnline();

        window.Echo.channel('users.status')
            .listen('.user.status.changed', (e) => {
                if (!e.user) return;
                setUserStatus(e.user.id, Boolean(e.user.is_online));
            });

        setInterval(sendPresenceOnline, 30000);
        window.addEventListener('beforeunload', sendPresenceOffline);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                sendPresenceOnline();
            }
        });
    }
});
