{{-- resources/views/components/global-lightbox.blade.php --}}
<div x-data="{ 
        open: false, 
        currentIndex: 0,
        items: [], 
        loading: true,
        scale: 1,
        touchStartX: 0,
        
        // Panning/Dragging Logic
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
                this.scale = 1;
            }
        },

        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.scale = 1;
            }
        },

        // Touch Swipe Logic
        handleTouchStart(e) {
            this.touchStartX = e.changedTouches[0].screenX;
        },

        handleTouchEnd(e) {
            if (this.scale > 1) return; // জুম থাকলে সওয়াইপ বন্ধ
            let touchEndX = e.changedTouches[0].screenX;
            if (this.touchStartX - touchEndX > 50) this.next(); 
            if (touchEndX - this.touchStartX > 50) this.prev(); 
        },

        // Drag to Pan Logic
        startDragging(e) {
            if (this.scale <= 1) return;
            this.isDragging = true;
            this.startX = e.pageX - this.$refs.stage.offsetLeft;
            this.startY = e.pageY - this.$refs.stage.offsetTop;
            this.scrollLeft = this.$refs.stage.scrollLeft;
            this.scrollTop = this.$refs.stage.scrollTop;
        },

        stopDragging() {
            this.isDragging = false;
        },

        onDragging(e) {
            if (!this.isDragging) return;
            e.preventDefault();
            const x = e.pageX - this.$refs.stage.offsetLeft;
            const y = e.pageY - this.$refs.stage.offsetTop;
            const walkX = (x - this.startX) * 1.5; // স্ক্রলিং স্পিড
            const walkY = (y - this.startY) * 1.5;
            this.$refs.stage.scrollLeft = this.scrollLeft - walkX;
            this.$refs.stage.scrollTop = this.scrollTop - walkY;
        }
    }"
    @open-lightbox.window="items = $event.detail.items; currentIndex = $event.detail.index || 0; open = true; document.body.style.overflow = 'hidden';"
    @keydown.escape.window="close()" @keydown.left.window="prev()" @keydown.right.window="next()"
    x-init="$watch('currentIndex', () => { loading = true; scale = 1; })" x-cloak>

    <template x-teleport="body">
        <div x-show="open" class="fixed inset-0 z-[9999] flex flex-col bg-zinc-950/98 backdrop-blur-3xl"
            x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">

            {{-- Top Bar --}}
            <div
                class="absolute top-0 inset-x-0 p-4 md:p-6 flex justify-between items-center z-[110] bg-gradient-to-b from-black/60 to-transparent">
                <div
                    class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-white text-[10px] font-mono tracking-tighter ">
                    <span x-text="(currentIndex + 1)"></span> / <span x-text="items.length"></span>
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    <template x-if="items.length > 0 && items[currentIndex] && items[currentIndex]['type'] === 'image'">
                        <div class="hidden sm:flex items-center bg-white/10 rounded-full p-1 backdrop-blur-md">
                            <flux:button @click="scale = Math.max(1, scale - 0.5)" variant="ghost" size="xs"
                                icon="minus" class="!rounded-full" />
                            <div class="w-10 text-center text-[10px] font-mono text-white/80"
                                x-text="Math.round(scale * 100) + '%'"></div>
                            <flux:button @click="scale = Math.min(4, scale + 0.5)" variant="ghost" size="xs" icon="plus"
                                class="!rounded-full" />
                        </div>
                    </template>

                    <a :href="items.length > 0 ? items[currentIndex]['url'] : '#'" download>
                        <flux:button variant="filled" size="sm" icon="arrow-down-tray" class="!rounded-full">
                            <span class="hidden md:flex">Download</span>
                        </flux:button>
                    </a>

                    <flux:button @click="close()" variant="ghost" size="sm" icon="x-mark" class="!rounded-full" />
                </div>
            </div>

            {{-- Main Stage (Scrollable & Draggable Area) --}}
            <div x-ref="stage"
                class="flex-1 overflow-auto no-scrollbar flex items-center justify-center p-4 selection:bg-none"
                :class="scale > 1 ? 'cursor-grab active:cursor-grabbing' : 'touch-pan-y'"
                @mousedown="startDragging($event)" @mousemove="onDragging($event)" @mouseup="stopDragging()"
                @mouseleave="stopDragging()" @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)"
                @click.self="close()">

                {{-- Desktop Nav Buttons (Scale ১ এর বেশি হলে হাইড হবে) --}}
                <div x-show="scale === 1"
                    class="hidden md:flex absolute inset-x-8 justify-between z-50 pointer-events-none">
                    <flux:button x-show="currentIndex > 0" @click="prev()" icon="chevron-left" variant="ghost"
                        class="pointer-events-auto !size-14 !rounded-full bg-white/10 backdrop-blur-md border-white/10 text-white" />
                    <div class="w-1"></div>
                    <flux:button x-show="items.length > 0 && currentIndex < items.length - 1" @click="next()"
                        icon="chevron-right" variant="ghost"
                        class="pointer-events-auto !size-14 !rounded-full bg-white/10 backdrop-blur-md border-white/10 text-white" />
                </div>

                {{-- Content Container --}}
                <div class="relative transition-all duration-300 ease-out origin-center shrink-0"
                    :style="'transform: scale(' + scale + ')'" @dblclick="scale === 1 ? scale = 2 : scale = 1">

                    <div x-show="loading" class="absolute inset-0 flex items-center justify-center z-40">
                        <flux:icon.loading class="size-10 text-white/20" />
                    </div>

                    <template x-if="items.length > 0 && items[currentIndex] && items[currentIndex]['type'] === 'image'">
                        <img :src="items[currentIndex]['url']" @load="loading = false"
                            class="max-w-[95vw] max-h-[85vh] object-contain rounded-md shadow-2xl transition-all duration-500 pointer-events-none select-none"
                            :class="loading ? 'opacity-0' : 'opacity-100'">
                    </template>

                    <template x-if="items.length > 0 && items[currentIndex] && items[currentIndex]['type'] === 'video'">
                        <div class="w-full max-w-5xl px-4">
                            <video x-ref="videoPlayer" :src="items[currentIndex]['url']" controls autoplay
                                class="w-full h-auto max-h-[75vh] rounded-xl shadow-2xl bg-black"
                                @loadeddata="loading = false">
                            </video>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Thumbnails --}}
            <div x-show="items.length > 1 && scale === 1"
                class="p-6 hidden md:flex md:p-8 flex justify-center items-center gap-4 bg-gradient-to-t from-black/60 to-transparent">
                <div class="flex gap-2 overflow-x-auto no-scrollbar p-2">
                    <template x-for="(item, idx) in items" :key="idx">
                        <button @click="currentIndex = idx"
                            class="relative shrink-0 w-12 h-12 md:w-16 md:h-16 rounded-xl overflow-hidden transition-all duration-300 border-2 shadow-2xl"
                            :class="currentIndex === idx ? 'border-white scale-110 shadow-white/20' : 'border-transparent opacity-30 hover:opacity-100'">
                            <img :src="item.thumb" class="w-full h-full object-cover pointer-events-none">
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>