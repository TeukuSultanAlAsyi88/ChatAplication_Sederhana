@extends('layouts.app')

@section('title', $group->name . ' - ChatApplication')

@section('content')
<div class="main-layout">

    <aside class="main-sidebar">
        <h2>{{ $group->name }}</h2>
        <small>{{ $members->count() }} Anggota</small>

        <div class="section-title">Anggota Grup</div>

        @foreach($members as $member)
            <div class="chat-list-item">
                <div class="avatar">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                <div>
                    <strong>{{ $member->name }}</strong>
                    <small
                        data-user-status="{{ $member->id }}"
                        class="{{ $member->is_online ? 'online' : 'offline' }}"
                    >
                        {{ $member->is_online ? 'Online' : 'Offline' }}
                    </small>
                </div>
            </div>
        @endforeach
    </aside>

    <main class="main-chat">
        <div class="chat-header-clean">
            <div class="avatar group-avatar">G</div>
            <div>
                <h2>{{ $group->name }}</h2>
                <small>{{ $members->count() }} anggota</small>
            </div>
        </div>

        <div class="clean-messages" id="groupMessages">
            @foreach($messages as $message)
                <div class="clean-message {{ $message->user_id == auth()->id() ? 'me' : 'other' }}">
                    <strong>{{ $message->user->name }}</strong>
                    <p>{{ $message->message }}</p>
                    <small>{{ $message->created_at->format('H:i') }}</small>
                </div>
            @endforeach
        </div>

        <form
            id="groupChatForm"
            class="clean-chat-form"
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
    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const groupId = {{ $group->id }};
    const currentUserId = {{ auth()->id() }};

    const groupMessages = document.getElementById('groupMessages');
    const groupChatForm = document.getElementById('groupChatForm');
    const groupMessageInput = document.getElementById('groupMessageInput');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function timeNow() {
        return new Date().toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function scrollToBottom() {
        groupMessages.scrollTop = groupMessages.scrollHeight;
    }

    function appendGroupMessage(message, type) {
        const div = document.createElement('div');
        div.classList.add('clean-message', type);

        div.innerHTML = `
            <strong>${escapeHtml(message.user.name)}</strong>
            <p>${escapeHtml(message.message)}</p>
            <small>${timeNow()}</small>
        `;

        groupMessages.appendChild(div);
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
                throw new Error('Gagal mengirim pesan grup');
            }

            appendGroupMessage(data.message, 'me');
        } catch (error) {
            groupMessageInput.value = text;
            alert('Pesan grup gagal dikirim.');
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
@endsection