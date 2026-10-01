<div class="space-y-6 animate-pulse">
    <div class="text-center space-y-2">
        <div class="h-8 bg-zinc-400/10 rounded-lg w-4/4 mx-auto"></div>
        <div class="h-4 bg-zinc-400/10 rounded w-1/2 mx-auto"></div>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-sm">
        <div class="flex flex-wrap gap-4">
            <div class="h-10 bg-zinc-400/10 rounded-lg flex-1 min-w-[180px]"></div>
            <div class="h-10 bg-zinc-400/10 rounded-lg flex-1 min-w-[140px]"></div>
            <div class="h-10 bg-zinc-400/10 rounded-lg flex-1 min-w-[140px]"></div>
            <div class="h-10 bg-zinc-400/10 rounded-lg flex-1 min-w-[140px]"></div>
        </div>
    </div>

    <div class="h-4 bg-zinc-400/10 rounded w-46"></div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < 5; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-400/10 p-4 space-y-4">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-full bg-zinc-400/10 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-5 bg-zinc-400/10 rounded w-2/3"></div>
                    <div class="h-3.5 bg-zinc-400/10 rounded w-1/2"></div>
                </div>
            </div>
            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/50 p-3 space-y-2">
                <div class="h-4 bg-zinc-400/10 rounded w-40"></div>
                <div class="h-4 bg-zinc-400/10 rounded w-42"></div>
                <div class="h-4 bg-zinc-400/10 rounded w-full"></div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-400/25">
                <div class="h-8 w-24 rounded-lg bg-zinc-400/10"></div>
                <div class="h-8 w-28 rounded-lg bg-zinc-400/10"></div>
                <div class="h-8 w-16 rounded-lg bg-zinc-400/10"></div>
            </div>
        </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>
<?php /**PATH /var/www/html/totthobox/resources/views/partials/contact-skeleton.blade.php ENDPATH**/ ?>