<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'ChatApplication'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body>
    <?php if(session('success')): ?>
        <div class="toast toast-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="toast toast-error"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="toast toast-error"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
</body>
</html>
<?php /**PATH C:\Users\Teuku pon\ChatApplication\resources\views/layouts/app.blade.php ENDPATH**/ ?>