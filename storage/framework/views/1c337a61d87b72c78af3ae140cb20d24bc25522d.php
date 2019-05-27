
<?php $__env->startSection('content'); ?>
    <div class="am-message result">
        <i class="am-icon result wait"></i>
        <div class="am-message-main"><?php echo e($message); ?></div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.antui', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>