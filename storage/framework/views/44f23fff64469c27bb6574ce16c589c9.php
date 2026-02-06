<?php
    $classes = Flux::classes()->add('p-3 rounded-xl')->add('bg-zinc-400/10 dark:bgzinc-400/5');
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-card>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/card/index.blade.php ENDPATH**/ ?>