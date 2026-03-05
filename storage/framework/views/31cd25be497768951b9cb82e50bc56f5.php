<div class="trix-content">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim($trixContent = $content->renderWithAttachments())): ?>
    <?php echo $trixContent; ?>

<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/totthobox/vendor/tonysm/rich-text-laravel/resources/views/content.blade.php ENDPATH**/ ?>