<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $group->name }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>
<body data-authenticated="1">

<div class="group-page">

    <div class="group-sidebar">

        <div class="group-title">
            {{ $group->name }}
        </div>

        <div class="group-member-count">
            {{ $group->members->count() }} Anggota
        </div>

        <div class="member-list">
            <h3>Anggota Grup</h3>

            @foreach($group->members as $member)
                <div class="member-item">
                    <strong>{{ $member->name }}</strong>
                    <br>
                    <small
                        data-user-status="{{ $member->id }}"
                        class="{{ $member->is_online ? 'online' : 'offline' }}"
                    >
                        {{ $member->is_online ? 'Online' : 'Offline' }}
                    </small>
                </div>
            @endforeach
        </div>

    </div>

    <div class="group-chat-area">

        <div class="group-chat-header">
            <h2>{{ $group->name }}</h2>
        </div>

        <div class="group-messages" id="groupMessages">
            @forelse($messages as $message)
                <div class="group-message {{ $message->user_id == auth()->id() ? 'me' : 'other' }}">
                    <strong>{{ $message->user->name }}</strong>
                    <p>{{ $message->message }}</p>
                </div>
            @empty
                <p id="emptyMessage">Belum ada pesan di grup ini.</p>
            @endforelse
        </div>

        <form
            id="groupChatForm"
            class="group-chat-form"
            action="{{ route('groups.send', $group->id) }}"
            method="POST"
        >
            @csrf

            <input
                id="groupMessageInput"
                type="text"
                name="message"
                placeholder="Ketik pesan grup..."
                required
                autocomplete="off"
            >

            <button type="submit">Kirim</button>
        </form>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const groupId = {{ $group->id }};
    const currentUserId = {{ auth()->id() }};
    const messagesBox = document.getElementById('groupMessages');
    const groupChatForm = document.getElementById('groupChatForm');
    const groupMessageInput = document.getElementById('groupMessageInput');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function scrollToBottom() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function appendGroupMessage(message, type = 'other') {
        const emptyMessage = document.getElementById('emptyMessage');

        if (emptyMessage) {
            emptyMessage.remove();
        }

        const div = document.createElement('div');
        div.classList.add('group-message', type);

        div.innerHTML = `
            <strong>${escapeHtml(message.user?.name ?? 'User')}</strong>
            <p>${escapeHtml(message.message)}</p>
        `;

        messagesBox.appendChild(div);
        scrollToBottom();
    }

    scrollToBottom();

    groupChatForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const text = groupMessageInput.value.trim();

        if (!text) {
            return;
        }

        groupMessageInput.value = '';

        try {
            const response = await fetch(groupChatForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                body: JSON.stringify({
                    message: text
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error('Pesan grup gagal dikirim');
            }

            appendGroupMessage(data.message, 'me');
        } catch (error) {
            groupMessageInput.value = text;
            alert('Pesan grup gagal dikirim. Coba lagi.');
        }
    });

    if (window.Echo) {
        window.Echo.channel(`group.${groupId}`)
            .listen('.group.message.sent', function (e) {
                if (e.message.user_id == currentUserId) {
                    return;
                }

                appendGroupMessage(e.message, 'other');
            });
    }
});
</script>

</body>
</html>