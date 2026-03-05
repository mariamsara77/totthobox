<div x-data="{ 
        open: false, 
        currentIndex: 0,
        items: [], 
        loading: true,
        scale: 1,
        touchStartX: 0,
        dist: 0, 
        initialScale: 1,
        
        isDragging: false,
        startX: 0, startY: 0,
        scrollLeft: 0, scrollTop: 0,

        close() {
            this.open = false;
            this.scale = 1;
            if(this.$refs.videoPlayer) this.$refs.videoPlayer.pause();
            document.body.style.overflow = 'auto';
        },

        next() {
            if (this.currentIndex < this.items.length - 1) {
                this.currentIndex++;
            }
        },

        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },

        handleTouchMove(e) {
            if (e.touches.length === 2) {
                let d = Math.hypot(e.touches[0].pageX - e.touches[1].pageX, e.touches[0].pageY - e.touches[1].pageY);
                if (!this.dist) { this.dist = d; this.initialScale = this.scale; }
                else { this.scale = Math.min(Math.max(1, this.initialScale * (d / this.dist)), 4); }
            }
        }
    }"
    @open-lightbox.window="items = $event.detail.items; currentIndex = $event.detail.index || 0; open = true; document.body.style.overflow = 'hidden';"
    @keydown.escape.window="close()" @keydown.left.window="prev()" @keydown.right.window="next()"
    x-init="$watch('currentIndex', () => { loading = true; scale = 1; })" x-cloak>

    <template x-teleport="body">
        <div x-show="open" class="fixed inset-0 z-[9999] flex flex-col bg-black/95 backdrop-blur-xl"
            x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">

            <div class="absolute top-0 inset-x-0 p-4 flex justify-between items-center z-[110]">
                <div class="text-white/70 text-sm font-medium bg-black/20 px-3 py-1 rounded-full">
                    <span x-text="currentIndex + 1"></span> / <span x-text="items.length"></span>
                </div>

                <div class="flex items-center gap-2">
                    <flux:button @click="close()" variant="ghost" icon="x-mark"
                        class="!text-white hover:!bg-white/10 !rounded-full" />
                </div>
            </div>

            <div x-ref="stage" class="flex-1 flex items-center justify-center overflow-auto no-scrollbar relative"
                :class="scale > 1 ? 'cursor-grab active:cursor-grabbing' : ''"
                @touchstart="touchStartX = $event.changedTouches[0].screenX" @touchmove="handleTouchMove($event)"
                @touchend="
                    let touchEndX = $event.changedTouches[0].screenX;
                    if (scale === 1) {
                        if (touchStartX - touchEndX > 60) next();
                        if (touchEndX - touchStartX > 60) prev();
                    }
                    dist = 0;
                "
                @mousedown="if(scale > 1) { isDragging = true; startX = $event.pageX - $refs.stage.offsetLeft; scrollLeft = $refs.stage.scrollLeft; }"
                @mousemove="if(isDragging) { $refs.stage.scrollLeft = scrollLeft - ($event.pageX - $refs.stage.offsetLeft - startX); }"
                @mouseup="isDragging = false" @click.self="close()">

                <div class="relative transition-transform duration-200 ease-out"
                    :style="'transform: scale(' + scale + ')'" @dblclick="scale = scale === 1 ? 2 : 1">

                    <template x-if="items[currentIndex]?.type === 'image'">
                        <img :src="items[currentIndex].url" @load="loading = false"
                            class="max-w-[100vw] max-h-[90vh] md:max-w-[90vw] object-contain select-none shadow-2xl">
                    </template>

                    <template x-if="items[currentIndex]?.type === 'video'">
                        <video controls autoplay :src="items[currentIndex].url"
                            class="max-w-[100vw] max-h-[80vh] bg-black"></video>
                    </template>
                </div>
            </div>

            <div x-show="items.length > 1 && scale === 1"
                class="hidden md:flex p-6 justify-center gap-2 overflow-x-auto">
                <template x-for="(item, idx) in items" :key="idx">
                    <button @click="currentIndex = idx"
                        :class="currentIndex === idx ? 'ring-2 ring-white scale-110' : 'opacity-50'"
                        class="w-16 h-16 rounded-lg overflow-hidden transition-all">
                        <img :src="item.thumb" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>
    </template>
</div>