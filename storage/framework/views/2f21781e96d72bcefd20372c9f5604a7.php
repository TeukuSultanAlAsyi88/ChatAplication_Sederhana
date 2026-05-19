<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Chat dengan <?php echo e($chatUser->name); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body data-authenticated="1">

<div class="main-layout">

    <aside class="main-sidebar">
        <div class="profile-box">
            <div class="avatar"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></div>
            <div>
                <h3><?php echo e(auth()->user()->name); ?></h3>
                <small class="online">Online</small>
            </div>
        </div>

        <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button class="logout-btn">Logout</button>
        </form>

        <div class="section-title">Chat Private</div>

        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('chat.with', $user->id)); ?>" class="chat-list-item <?php echo e($chatUser->id == $user->id ? 'active' : ''); ?>">
                <div class="avatar"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                <div>
                    <strong><?php echo e($user->name); ?></strong>
                    <small
                        data-user-status="<?php echo e($user->id); ?>"
                        class="<?php echo e($user->is_online ? 'online' : 'offline'); ?>"
                    >
                        <?php echo e($user->is_online ? 'Online' : 'Offline'); ?>

                    </small>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="section-title">Grup Chat</div>

        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('groups.show', $group->id)); ?>" class="chat-list-item">
                <div class="avatar group-avatar">G</div>
                <div>
                    <strong><?php echo e($group->name); ?></strong>
                    <small><?php echo e($group->members->count()); ?> anggota</small>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </aside>

    <main class="main-chat">
        <div class="chat-header-clean">
            <div class="avatar"><?php echo e(strtoupper(substr($chatUser->name, 0, 1))); ?></div>
            <div>
                <h2><?php echo e($chatUser->name); ?></h2>
                <small
                    data-user-status="<?php echo e($chatUser->id); ?>"
                    class="<?php echo e($chatUser->is_online ? 'online' : 'offline'); ?>"
                >
                    <?php echo e($chatUser->is_online ? 'Online' : 'Offline'); ?>

                </small>
            </div>
        </div>

        <div class="clean-messages" id="privateMessages">
            <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="clean-message <?php echo e($message->user_id == auth()->id() ? 'me' : 'other'); ?>">
                    <p><?php echo e($message->message); ?></p>
                    <small><?php echo e($message->created_at->format('H:i')); ?></small>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <form
            id="privateChatForm"
            class="clean-chat-form"
            action="<?php echo e(route('messages.store', $room->id)); ?>"
            method="POST"
        >
            <?php echo csrf_field(); ?>
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
    const roomId = <?php echo e($room->id); ?>;
    const currentUserId = <?php echo e(auth()->id()); ?>;

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
</html><?php /**PATH C:\Users\Teuku pon\ChatApplication\resources\views/chat/room.blade.php ENDPATH**/ ?>