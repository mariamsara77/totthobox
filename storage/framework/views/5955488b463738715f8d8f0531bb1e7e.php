<figure class="not-prose attachment attachment--file attachment--<?php echo e($remoteFile->extension()); ?>">
    <figcaption class="attachment__caption">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remoteFile->caption): ?>
            <?php echo e($remoteFile->caption); ?>

        <?php else: ?>
            <span class="attachment__name"><?php echo e($remoteFile->filename); ?></span>
            <span class="attachment__size"><?php echo e($remoteFile->filesizeForHumans()); ?></span>
            <span class="attachment__download"><a class="!underline" href="<?php echo e($remoteFile->url); ?>"><?php echo e(__('Download')); ?></a></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </figcaption>
</figure>
<?php /**PATH /var/www/html/totthobox/vendor/tonysm/rich-text-laravel/resources/views/attachables/_remote_file.blade.php ENDPATH**/ ?>