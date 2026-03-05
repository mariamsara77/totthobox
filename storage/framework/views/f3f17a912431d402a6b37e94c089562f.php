<div x-data="{ 
        open: false, 
        currentIndex: 0,
        items: [], 
        loading: true,
        scale: 1,
        
        // Advanced Panning & Zoom State
        translateX: 0,
        translateY: 0,
        lastX: 0,
        lastY: 0,
        isDragging: false,
        touchStartX: 0,
        touchStartY: 0,
        lastDist: 0,

        close() {
            this.open = false;
            this.resetZoom();
            if(this.$refs.videoPlayer) this.$refs.videoPlayer.pause();
            document.body.style.overflow = 'auto';
        },

        resetZoom() {
            this.scale = 1;
            this.translateX = 0;
            this.translateY = 0;
        },

        next() {
            if (this.currentIndex < this.items.length - 1) {
                this.currentIndex++;
                this.resetZoom();
            }
        },

        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.resetZoom();
            }
        },

        // Unified Drag/Pan Logic (Mouse & Touch)
        startDragging(e) {
            if (this.scale <= 1) return;
            this.isDragging = true;
            const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            
            this.lastX = clientX - this.translateX;
            this.lastY = clientY - this.translateY;
        },

        onDragging(e) {
            if (!this.isDragging || this.scale <= 1) return;
            const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            
            this.translateX = clientX - this.lastX;
            this.translateY = clientY - this.lastY;
        },

        // Pinch to Zoom for Mobile
        handlePinch(e) {
            if (e.touches.length === 2) {
                let dist = Math.hypot(
                    e.touches[0].pageX - e.touches[1].pageX,
                    e.touches[0].pageY - e.touches[1].pageY
                );
                if (this.lastDist > 0) {
                    let diff = dist - this.lastDist;
                    let newScale = this.scale + (diff * 0.015);
                    this.scale = Math.min(Math.max(1, newScale), 5);
                }
                this.lastDist = dist;
            }
        }
    }"
    @open-lightbox.window="items = $event.detail.items; currentIndex = $event.detail.index || 0; open = true; document.body.style.overflow = 'hidden';"
    @keydown.escape.window="close()" @keydown.left.window="prev()" @keydown.right.window="next()"
    x-init="$watch('currentIndex', () => { loading = true; })" x-cloak>

    <template x-teleport="body">
        <div x-show="open" class="fixed inset-0 z-[9999] flex flex-col bg-zinc-950/98 backdrop-blur-3xl select-none"
            x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">

            
            <div
                class="absolute top-0 inset-x-0 p-4 md:p-6 flex justify-between items-center z-[120] bg-gradient-to-b from-black/70 to-transparent">
                <div
                    class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-white text-[10px] font-mono tracking-tighter">
                    <span x-text="(currentIndex + 1)"></span> / <span x-text="items.length"></span>
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    
                    <template x-if="items[currentIndex]?.type === 'image'">
                        <div
                            class="hidden sm:flex items-center bg-white/10 rounded-full p-1 backdrop-blur-md border border-white/10">
                            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['@click' => 'resetZoom()','variant' => 'ghost','size' => 'xs','icon' => 'arrow-path','class' => '!rounded-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'resetZoom()','variant' => 'ghost','size' => 'xs','icon' => 'arrow-path','class' => '!rounded-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                            <div class="w-12 text-center text-[10px] font-mono text-white/80"
                                x-text="Math.round(scale * 100) + '%'"></div>
                            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['@click' => 'scale = Math.min(5, scale + 0.5)','variant' => 'ghost','size' => 'xs','icon' => 'plus','class' => '!rounded-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'scale = Math.min(5, scale + 0.5)','variant' => 'ghost','size' => 'xs','icon' => 'plus','class' => '!rounded-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                        </div>
                    </template>

                    
                    <a :href="items[currentIndex]?.url" download class="contents">
                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'filled','size' => 'sm','icon' => 'arrow-down-tray','class' => '!rounded-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'filled','size' => 'sm','icon' => 'arrow-down-tray','class' => '!rounded-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                    </a>

                    
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['@click' => 'close()','variant' => 'ghost','size' => 'sm','icon' => 'x-mark','class' => '!rounded-full !text-white hover:!bg-white/20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'close()','variant' => 'ghost','size' => 'sm','icon' => 'x-mark','class' => '!rounded-full !text-white hover:!bg-white/20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                </div>
            </div>

            
            <div x-ref="stage" class="flex-1 relative flex items-center justify-center overflow-hidden touch-none"
                @mousedown="startDragging($event)" @mousemove="onDragging($event)" @mouseup="isDragging = false"
                @mouseleave="isDragging = false" @touchstart="
                    touchStartX = $event.changedTouches[0].screenX; 
                    touchStartY = $event.changedTouches[0].screenY;
                    startDragging($event);
                " @touchmove="handlePinch($event); onDragging($event);" @touchend="
                    isDragging = false; 
                    lastDist = 0;
                    if (scale === 1) {
                        let touchEndX = $event.changedTouches[0].screenX;
                        let touchEndY = $event.changedTouches[0].screenY;
                        if (touchStartX - touchEndX > 60) next(); 
                        if (touchEndX - touchStartX > 60) prev(); 
                        if (touchEndY - touchStartY > 120) close(); // Swipe down to close
                    }
                " @click.self="close()">

                
                <div x-show="scale === 1"
                    class="hidden md:flex absolute inset-x-8 justify-between z-50 pointer-events-none">
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['xShow' => 'currentIndex > 0','@click' => 'prev()','icon' => 'chevron-left','variant' => 'filled','class' => 'pointer-events-auto rounded-full!']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'currentIndex > 0','@click' => 'prev()','icon' => 'chevron-left','variant' => 'filled','class' => 'pointer-events-auto rounded-full!']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                    <div class="w-1"></div>
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['xShow' => 'currentIndex < items.length - 1','@click' => 'next()','icon' => 'chevron-right','variant' => 'filled','class' => 'pointer-events-auto rounded-full!']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'currentIndex < items.length - 1','@click' => 'next()','icon' => 'chevron-right','variant' => 'filled','class' => 'pointer-events-auto rounded-full!']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                </div>

                
                <div class="relative flex items-center justify-center shrink-0 transition-transform duration-75 ease-out"
                    :style="`transform: translate(${translateX}px, ${translateY}px) scale(${scale})`"
                    @dblclick="scale === 1 ? (scale = 2.5) : resetZoom()">

                    <div x-show="loading" class="absolute inset-0 flex items-center justify-center z-40">
                        <?php if (isset($component)) { $__componentOriginalb06f0c5905a9427a630c5e299af7ce46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb06f0c5905a9427a630c5e299af7ce46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.loading','data' => ['class' => 'size-10 text-white/20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.loading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-10 text-white/20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb06f0c5905a9427a630c5e299af7ce46)): ?>
<?php $attributes = $__attributesOriginalb06f0c5905a9427a630c5e299af7ce46; ?>
<?php unset($__attributesOriginalb06f0c5905a9427a630c5e299af7ce46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb06f0c5905a9427a630c5e299af7ce46)): ?>
<?php $component = $__componentOriginalb06f0c5905a9427a630c5e299af7ce46; ?>
<?php unset($__componentOriginalb06f0c5905a9427a630c5e299af7ce46); ?>
<?php endif; ?>
                    </div>

                    <template x-if="items[currentIndex]?.type === 'image'">
                        <img :src="items[currentIndex].url" @load="loading = false"
                            class="max-w-[95vw] max-h-[55vh] md:max-w-[90vw] object-contain rounded-sm shadow-2xl pointer-events-none select-none"
                            :class="loading ? 'opacity-0' : 'opacity-100 transition-opacity duration-300'">
                    </template>

                    <template x-if="items[currentIndex]?.type === 'video'">
                        <div class="w-screen max-w-4xl px-4 pointer-events-auto" @touchstart.stop @mousedown.stop>
                            <video x-ref="videoPlayer" :src="items[currentIndex].url" controls autoplay
                                class="w-full h-auto max-h-[75vh] rounded-xl shadow-2xl bg-black"
                                @loadeddata="loading = false">
                            </video>
                        </div>
                    </template>
                </div>
            </div>

            
            
        </div>
    </template>
</div><?php /**PATH /var/www/html/totthobox/resources/views/components/global-lightbox.blade.php ENDPATH**/ ?>