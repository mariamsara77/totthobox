<figure class="not-prose attachment attachment--preview attachment--<?php echo e($remoteImage->extension()); ?>" <?php if($gallery ?? false): ?> data-trix-attributes='{"presentation":"gallery"}' <?php endif; ?>>
    <img src="<?php echo e($remoteImage->url); ?>" width="<?php echo e($remoteImage->width); ?>" height="<?php echo e($remoteImage->height); ?>" />
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remoteImage->caption): ?>
    <figcaption class="attachment__caption">
        <?php echo e($remoteImage->caption); ?>

    </figcaption>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</figure>
<?php /**PATH /var/www/html/totthobox/vendor/tonysm/rich-text-laravel/resources/views/attachables/_remote_image.blade.php ENDPATH**/ ?>