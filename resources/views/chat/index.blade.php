<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat Application</title>
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

        @forelse($users as $user)
            <a href="{{ route('chat.with', $user->id) }}" class="chat-list-item">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div>
                    <strong>{{ $user->name }}</strong>
                    <small data-user-status="{{ $user->id }}" class="{{ $user->is_online ? 'online' : 'offline' }}">{{ $user->is_online ? 'Online' : 'Offline' }}</small>
                </div>
            </a>
        @empty
            <p class="empty-text">Belum ada user lain.</p>
        @endforelse

        <div class="section-title">Grup Chat</div>

        @forelse($groups as $group)
            <a href="{{ route('groups.show', $group->id) }}" class="chat-list-item">
                <div class="avatar group-avatar">G</div>
                <div>
                    <strong>{{ $group->name }}</strong>
                    <small>{{ $group->members->count() }} anggota</small>
                </div>
            </a>
        @empty
            <p class="empty-text">Belum ada grup.</p>
        @endforelse
    </aside>

    <main class="main-chat">
        <div class="chat-placeholder">
            <h2>Pilih Chat</h2>
            <p>Pilih user atau grup untuk mulai chat.</p>
        </div>
    </main>

</div>

</body>
</html>