<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat dengan {{ $chatUser->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-authenticated="1">

<div class="main-layout">

    <aside class="main-sidebar">
        <div class="profile-box">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <h3>{{ auth()->user()->name }}</h3>
                <small class="online">Online</small>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>

        <div class="section-title">Chat Private</div>

        @foreach($users as $user)
            <a href="{{ route('chat.with', $user->id) }}" class="chat-list-item {{ $chatUser->id == $user->id ? 'active' : '' }}">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div>
                    <strong>{{ $user->name }}</strong>
                    <small
                        data-user-status="{{ $user->id }}"
                        class="{{ $user->is_online ? 'online' : 'offline' }}"
                    >
                        {{ $user->is_online ? 'Online' : 'Offline' }}
                    </small>
                </div>
            </a>
        @endforeach

        <div class="section-title">Grup Chat</div>

        @foreach($groups as $group)
            <a href="{{ route('groups.show', $group->id) }}" class="chat-list-item">
                <div class="avatar group-avatar">G</div>
                <div>
                    <strong>{{ $group->name }}</strong>
                    <small>{{ $group->members->count() }} anggota</small>
                </div>
            </a>
        @endforeach
    </aside>

    <main class="main-chat">
        <div class="chat-header-clean">
            <div class="avatar">{{ strtoupper(substr($chatUser->name, 0, 1)) }}</div>
            <div>
                <h2>{{ $chatUser->name }}</h2>
                <small
                    data-user-status="{{ $chatUser->id }}"
                    class="{{ $chatUser->is_online ? 'online' : 'offline' }}"
                >
                    {{ $chatUser->is_online ? 'Online' : 'Offline' }}
                </small>
            </div>
        </div>

        <div class="clean-messages" id="privateMessages">
            @foreach($messages as $message)
                <div class="clean-message {{ $message->user_id == auth()->id() ? 'me' : 'other' }}">
                    <p>{{ $message->message }}</p>
                    <small>{{ $message->created_at->format('H:i') }}</small>
                </div>
            @endforeach
        </div>

        <form
            id="privateChatForm"
            class="clean-chat-form"
            action="{{ route('messages.store', $room->id) }}"
            method="POST"
        >
            @csrf
            <input
                id="privateMessageInput"
                type="text"
                name="message"
                placeholder="Tulis pesan..."
                required
                autocomplete="off"
            >
            <button type="submit">Kirim</button>
        </form>
    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roomId = {{ $room->id }};
    const currentUserId = {{ auth()->id() }};

    const privateMessages = document.getElementById('privateMessages');
    const privateChatForm = document.getElementById('privateChatForm');
    const privateMessageInput = document.getElementById('privateMessageInput');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function timeNow() {
        const date = new Date();
        return date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function scrollToBottom() {
        privateMessages.scrollTop = privateMessages.scrollHeight;
    }

    function appendPrivateMessage(message, type) {
        const div = document.createElement('div');
        div.classList.add('clean-message', type);

        div.innerHTML = `
            <p>${escapeHtml(message.message)}</p>
            <small>${timeNow()}</small>
        `;

        privateMessages.appendChild(div);
        scrollToBottom();
    }

    scrollToBottom();

    privateChatForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const text = privateMessageInput.value.trim();

        if (!text) {
            return;
        }

        privateMessageInput.value = '';

        try {
            const response = await fetch(privateChatForm.action, {
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
                throw new Error('Gagal mengirim pesan');
            }

            appendPrivateMessage(data.message, 'me');
        } catch (error) {
            privateMessageInput.value = text;
            alert('Pesan gagal dikirim. Coba lagi.');
        }
    });

    if (window.Echo) {
        window.Echo.channel(`chat.${roomId}`)
            .listen('.message.sent', function (e) {
                if (e.message.user_id == currentUserId) {
                    return;
                }

                appendPrivateMessage(e.message, 'other');
            });
    }
});
</script>

</body>
</html>