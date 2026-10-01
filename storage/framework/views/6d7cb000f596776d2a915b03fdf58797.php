<news:news>
    <news:publication>
        <news:name><?php echo e($news->name); ?></news:name>
        <news:language><?php echo e($news->language); ?></news:language>
    </news:publication>
    <news:title><?php echo e($news->title); ?></news:title>
    <news:publication_date><?php echo e($news->publicationDate->toW3cString()); ?></news:publication_date>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $news->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <news:<?php echo e($tag); ?>><?php echo e($value); ?></news:<?php echo e($tag); ?>>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</news:news><?php /**PATH /var/www/html/totthobox/vendor/spatie/laravel-sitemap/resources/views/news.blade.php ENDPATH**/ ?>