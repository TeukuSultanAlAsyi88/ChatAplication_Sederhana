<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($group->name); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')([
        'resources/css/app.css',
        'resources/js/app.js'
    ]); ?>
</head>
<body data-authenticated="1">

<div class="group-page">

    <div class="group-sidebar">

        <div class="group-title">
            <?php echo e($group->name); ?>

        </div>

        <div class="group-member-count">
            <?php echo e($group->members->count()); ?> Anggota
        </div>

        <div class="member-list">
            <h3>Anggota Grup</h3>

            <?php $__currentLoopData = $group->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="member-item">
                    <strong><?php echo e($member->name); ?></strong>
                    <br>
                    <small
                        data-user-status="<?php echo e($member->id); ?>"
                        class="<?php echo e($member->is_online ? 'online' : 'offline'); ?>"
                    >
                        <?php echo e($member->is_online ? 'Online' : 'Offline'); ?>

                    </small>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>

    <div class="group-chat-area">

        <div class="group-chat-header">
            <h2><?php echo e($group->name); ?></h2>
        </div>

        <div class="group-messages" id="groupMessages">
            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="group-message <?php echo e($message->user_id == auth()->id() ? 'me' : 'other'); ?>">
                    <strong><?php echo e($message->user->name); ?></strong>
                    <p><?php echo e($message->message); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p id="emptyMessage">Belum ada pesan di grup ini.</p>
            <?php endif; ?>
        </div>

        <form
            id="groupChatForm"
            class="group-chat-form"
            action="<?php echo e(route('groups.send', $group->id)); ?>"
            method="POST"
        >
            <?php echo csrf_field(); ?>

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
    const groupId = <?php echo e($group->id); ?>;
    const currentUserId = <?php echo e(auth()->id()); ?>;
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
</html><?php /**PATH C:\Users\Teuku pon\ChatApplication\resources\views/groups/chat.blade.php ENDPATH**/ ?>