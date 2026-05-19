<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Chat Application</title>
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

        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('chat.with', $user->id)); ?>" class="chat-list-item">
                <div class="avatar"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                <div>
                    <strong><?php echo e($user->name); ?></strong>
                    <small data-user-status="<?php echo e($user->id); ?>" class="<?php echo e($user->is_online ? 'online' : 'offline'); ?>"><?php echo e($user->is_online ? 'Online' : 'Offline'); ?></small>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="empty-text">Belum ada user lain.</p>
        <?php endif; ?>

        <div class="section-title">Grup Chat</div>

        <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('groups.show', $group->id)); ?>" class="chat-list-item">
                <div class="avatar group-avatar">G</div>
                <div>
                    <strong><?php echo e($group->name); ?></strong>
                    <small><?php echo e($group->members->count()); ?> anggota</small>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="empty-text">Belum ada grup.</p>
        <?php endif; ?>
    </aside>

    <main class="main-chat">
        <div class="chat-placeholder">
            <h2>Pilih Chat</h2>
            <p>Pilih user atau grup untuk mulai chat.</p>
        </div>
    </main>

</div>

</body>
</html><?php /**PATH C:\Users\Teuku pon\ChatApplication\resources\views/chat/index.blade.php ENDPATH**/ ?>