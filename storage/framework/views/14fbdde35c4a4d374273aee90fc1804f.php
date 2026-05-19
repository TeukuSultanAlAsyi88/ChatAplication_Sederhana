<?php $__env->startSection('title','Tambah Anggota Grup - ChatApplication'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-wrap">
    <div class="form-card">
        <h1>Tambah Anggota</h1>
        <form method="POST" action="<?php echo e(route('groups.storeMember', $group)); ?>">
            <?php echo csrf_field(); ?>
            <label>Nomor HP Anggota</label>
            <input name="phone" required>
            <div class="form-actions">
                <a class="btn" href="<?php echo e(route('groups.show', $group)); ?>">Batal</a>
                <button class="btn primary" type="submit">Tambah</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Teuku pon\ChatApplication\resources\views/groups/add-member.blade.php ENDPATH**/ ?>