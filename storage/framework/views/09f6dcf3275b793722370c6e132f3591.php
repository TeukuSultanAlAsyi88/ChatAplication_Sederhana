<?php $__env->startSection('title', 'Masuk - ChatApplication'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand auth-brand">
            <div class="logo">CA</div>
            <div>
                <h2>ChatApplication</h2>
                <p>Chat aja yaa</p>
            </div>
        </div>

        <h1>Masuk</h1>
        <p>Login pakai email atau nomor HP.</p>

        <form method="POST" action="<?php echo e(route('login.store')); ?>">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label>Email atau Nomor HP</label>
                <input
                    class="form-control"
                    type="text"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
                    required
                    autocomplete="username"
                >

                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small class="text-danger"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    class="form-control"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >

                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small class="text-danger"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button class="btn w-full" type="submit">Masuk</button>
        </form>

        <p class="auth-footer">
            Belum punya akun?
            <a href="<?php echo e(route('register')); ?>">Daftar</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Teuku pon\ChatApplication\resources\views/auth/login.blade.php ENDPATH**/ ?>