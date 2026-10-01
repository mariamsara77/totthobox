

<div class="animate-pulse">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < 6; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div
                class="rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden bg-zinc-400/10 flex flex-col">

                
                <div class="relative aspect-video bg-zinc-400/10">
                    
                    <div class="absolute top-2 right-2 h-7 w-28 rounded-full bg-zinc-300/80 dark:bg-zinc-700/80"></div>
                </div>

                
                <div class="p-4 flex flex-1 flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        
                        <div class="h-5 bg-zinc-400/10 rounded w-full"></div>
                        

                        
                        
                    </div>

                    
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-4">
                            <div class="h-5 w-16 rounded-full bg-zinc-400/10"></div>
                            <div class="h-3.5 w-20 rounded bg-zinc-400/10"></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="h-7 w-24 rounded-lg bg-zinc-400/10"></div>
                            <div class="h-7 w-7 rounded-lg bg-zinc-400/10"></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    
    <div class="py-12 flex justify-center">
        <div class="h-6 w-6 rounded-full bg-zinc-400/10"></div>
    </div>
</div>
<?php /**PATH /var/www/html/totthobox/resources/views/partials/news-skeleton.blade.php ENDPATH**/ ?>