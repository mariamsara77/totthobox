<?php

use Livewire\Volt\Component;

?>

<div class="max-w-7xl mx-auto" x-data="modernDrawingApp()" x-init="init()">


    <!-- Mobile-First Header -->
    <header class="">
        <div class="">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold ">Writing Practice</h1>
                    <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Advanced drawing & writing tool</p>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center space-x-2">
                    <?php if (isset($component)) { $__componentOriginal1db8c57e729d67f7d4103875cf3230cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.trigger','data' => ['name' => 'help']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'help']); ?>
                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','class' => '!text-gray-600','size' => 'sm','icon' => 'question-mark-circle','title' => 'Help']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => '!text-gray-600','size' => 'sm','icon' => 'question-mark-circle','title' => 'Help']); ?>
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
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $attributes = $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $component = $__componentOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','size' => 'sm','@click' => 'toggleFullscreen()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','@click' => 'toggleFullscreen()']); ?>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" />
                        </svg>
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
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        <div class="">
            <!-- Canvas Container -->
            <div id="myCanvas" class="relative bg-white border rounded-2xl sm:rounded-3xl overflow-hidden">

                <!-- Canvas -->
                <canvas id="drawing-canvas" x-ref="canvas"
                    class="w-full h-[80vh] sm:h-[80vh] lg:h-[85vh] bg-white cursor-crosshair touch-none"
                    @mousedown="startDrawing($event)" @mousemove="draw($event)" @mouseup="stopDrawing()"
                    @mouseleave="stopDrawing()" @touchstart.prevent="startDrawing($event)"
                    @touchmove.prevent="draw($event)" @touchend.prevent="stopDrawing()">

                </canvas>

                <!-- Guide Text Overlay -->
                <div x-show="guideText" x-text="guideText"
                    class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-10 text-gray-400 z-10 transition-opacity duration-500"
                    :style="`font-size: ${Math.min(window.innerWidth * 0.4, 300)}px; font-family: 'Noto Serif Bengali', serif;`">
                </div>

                <!-- Floating Toolbar - Mobile Optimized -->
                <div class="absolute bottom-2 sm:bottom-2 left-1/2 transform -translate-x-1/2 z-20">
                    <div class="bg-zinc-200 dark:bg-zinc-700 backdrop-blur-xl rounded-2xl sm:rounded-xl px-2 py-1">
                        <div class="flex items-center justify-center space-x-1 sm:space-x-2">
                            <!-- Tools -->
                            <div class="flex items-center space-x-1">
                                <template x-for="tool in tools">
                                    <button @click="setActiveTool(tool.id)" :class="{
                                            'floating-btn-active p-1 rounded-lg': activeTool === tool.id,
                                            'floating-btn text-zinc-400': activeTool !== tool.id
                                        }" :title="tool.name" class="">

                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 " fill="currentColor" viewBox="0 0 24 24">
                                            <path x-show="tool.id === 'pen'"
                                                d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001" />
                                            <path x-show="tool.id === 'eraser'"
                                                d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828zm.66 11.34L3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293z" />
                                        </svg>
                                    </button>
                                </template>
                            </div>

                            <div class="w-px h-8 bg-gray-200/60"></div>

                            <!-- History Actions -->
                            <div class="flex items-center space-x-1">
                                <button @click="undo()" :disabled="historyIndex <= 0"
                                    class="floating-btn disabled:opacity-40 disabled:cursor-not-allowed" title="Undo">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z" />
                                    </svg>
                                </button>
                                <button @click="redo()" :disabled="historyIndex >= drawingHistory.length - 1"
                                    class="floating-btn disabled:opacity-40 disabled:cursor-not-allowed" title="Redo">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="w-px h-8 bg-gray-200/60"></div>

                            <!-- Main Actions -->
                            <div class="flex items-center space-x-1">

                                <?php if (isset($component)) { $__componentOriginal1db8c57e729d67f7d4103875cf3230cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.trigger','data' => ['name' => 'canvas-save-confirm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'canvas-save-confirm']); ?>
                                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','class' => '!text-green-600','size' => 'sm','icon' => 'arrow-down-tray','title' => 'Save']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => '!text-green-600','size' => 'sm','icon' => 'arrow-down-tray','title' => 'Save']); ?>
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
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $attributes = $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $component = $__componentOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>

                                <?php if (isset($component)) { $__componentOriginal1db8c57e729d67f7d4103875cf3230cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.trigger','data' => ['name' => 'canvas-clear-confirm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'canvas-clear-confirm']); ?>
                                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','class' => '!text-red-500','size' => 'sm','icon' => 'trash','title' => 'Clear']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => '!text-red-500','size' => 'sm','icon' => 'trash','title' => 'Clear']); ?>
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
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $attributes = $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $component = $__componentOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>

                                <?php if (isset($component)) { $__componentOriginal1db8c57e729d67f7d4103875cf3230cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.trigger','data' => ['name' => 'settings-writing-pad']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'settings-writing-pad']); ?>
                                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','class' => '!text-purple-500','size' => 'sm','icon' => 'cog-6-tooth','title' => 'Settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => '!text-purple-500','size' => 'sm','icon' => 'cog-6-tooth','title' => 'Settings']); ?>
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
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $attributes = $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $component = $__componentOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php if (isset($component)) { $__componentOriginal8cc9d3143946b992b324617832699c5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cc9d3143946b992b324617832699c5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.index','data' => ['name' => 'settings-writing-pad','variant' => 'flyout','class' => 'md:w-96']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'settings-writing-pad','variant' => 'flyout','class' => 'md:w-96']); ?>

        <div class="">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg sm:text-xl font-bold">Settings</h3>
            </div>

            <div class="space-y-6">
                <!-- Color Picker -->
                <div>
                    <label class="block text-sm font-semibold mb-3">Pen Color</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" x-model="currentColor"
                            class="w-12 h-12 rounded-xl cursor-pointer border-2 border-gray-200 shadow-sm">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="color in colorPresets">
                                <button @click="currentColor = color"
                                    class="w-8 h-8 rounded-lg border-2 shadow-sm transition-all hover:scale-110"
                                    :style="`background-color: ${color}`" :class="{
                                        'border-blue-500 scale-110': currentColor ===
                                            color,
                                        'border-gray-200': currentColor !== color
                                    }">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Brush Size -->
                <div>
                    <label class="block text-sm font-semibold mb-3">
                        Brush Size: <span x-text="currentSize" class="text-blue-600"></span>px
                    </label>
                    <input type="range" x-model="currentSize" min="1" max="30" class="modern-slider w-full">
                    <div class="flex justify-between text-xs mt-2">
                        <span>Thin</span>
                        <span>Medium</span>
                        <span>Thick</span>
                    </div>
                </div>

                <!-- Opacity -->
                <div>
                    <label class="block text-sm font-semibold  mb-3">
                        Opacity: <span x-text="Math.round(opacity * 100)" class="text-blue-600"></span>%
                    </label>
                    <input type="range" x-model="opacity" min="0.1" max="1" step="0.1" class="modern-slider w-full">
                </div>

                <!-- Character Selection -->
                <div>
                    <label class="block text-sm font-semibold  mb-3">Practice Characters</label>
                    <div class="space-y-2">
                        <template x-for="(category, index) in characterCategories">
                            <div class="rounded-xl overflow-hidden">
                                <button @click="toggleCategory(index)"
                                    class="w-full flex justify-between items-center p-4 bg-gray-50/25 hover:bg-gray-100/25 transition-colors">
                                    <span x-text="category.name" class="font-medium "></span>
                                    <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                                        :class="{ 'rotate-180': category.open }" fill="currentColor"
                                        viewBox="0 0 24 24">
                                        <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z" />
                                    </svg>
                                </button>
                                <div x-show="category.open" x-collapse class="p-4 grid grid-cols-6 gap-2">
                                    <template x-for="char in category.characters">
                                        <button @click="setGuideText(char)"
                                            class="h-10 flex items-center justify-center bg-gray-50/25 rounded-lg hover:bg-blue-50/25 transition-colors  font-medium"
                                            x-text="char">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Paper Style -->
                <div>
                    <label class="block text-sm font-semibold  mb-3">Paper Style</label>
                    <div class="grid grid-cols-3 gap-3">
                        <template x-for="paper in paperStyles">
                            <button @click="setPaperStyle(paper.id)" :class="{
                                    'ring-2 ring-blue-500 border-blue-300': currentPaperStyle === paper.id,
                                    'border-gray-200 hover:border-gray-300': currentPaperStyle !== paper.id
                                }" class="p-3 rounded-xl border-2 transition-all hover:shadow-md">
                                <div class="h-12 rounded-lg mb-2" :class="paper.class"></div>
                                <span class="text-xs font-medium " x-text="paper.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Advanced Options -->
                <div>
                    <label class="block text-sm font-semibold  mb-3">Advanced Options</label>
                    <div class="space-y-3">
                        <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['xModel' => 'pressureSensitivity','label' => 'Pressure sensitivity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'pressureSensitivity','label' => 'Pressure sensitivity']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $attributes = $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $component = $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['xModel' => 'smoothing','label' => 'Line smoothing']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'smoothing','label' => 'Line smoothing']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $attributes = $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $component = $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['xModel' => 'guideLines','label' => 'Show guide lines']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'guideLines','label' => 'Show guide lines']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $attributes = $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $component = $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $attributes = $__attributesOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__attributesOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $component = $__componentOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__componentOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal8cc9d3143946b992b324617832699c5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cc9d3143946b992b324617832699c5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.index','data' => ['name' => 'canvas-clear-confirm','class' => 'min-w-[22rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'canvas-clear-confirm','class' => 'min-w-[22rem]']); ?>

        <div class="space-y-6">
            <div>
                <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>Delete project? <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal0638ebfbd490c7a414275d493e14cb4e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0638ebfbd490c7a414275d493e14cb4e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::text','data' => ['class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-2']); ?>
                    <p>You're about to delete this project.</p>
                    <p>This action cannot be reversed.</p>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0638ebfbd490c7a414275d493e14cb4e)): ?>
<?php $attributes = $__attributesOriginal0638ebfbd490c7a414275d493e14cb4e; ?>
<?php unset($__attributesOriginal0638ebfbd490c7a414275d493e14cb4e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0638ebfbd490c7a414275d493e14cb4e)): ?>
<?php $component = $__componentOriginal0638ebfbd490c7a414275d493e14cb4e; ?>
<?php unset($__componentOriginal0638ebfbd490c7a414275d493e14cb4e); ?>
<?php endif; ?>
            </div>
            <div class="flex gap-2 justify-end">
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','size' => 'sm','xOn:click' => '$flux.modal(\'canvas-clear-confirm\').close()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','x-on:click' => '$flux.modal(\'canvas-clear-confirm\').close()']); ?>
                    Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'submit','size' => 'sm','@click' => 'clearCanvas()','variant' => 'danger','xOn:click' => '$flux.modal(\'canvas-clear-confirm\').close()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm','@click' => 'clearCanvas()','variant' => 'danger','x-on:click' => '$flux.modal(\'canvas-clear-confirm\').close()']); ?>Clear <?php echo $__env->renderComponent(); ?>
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
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $attributes = $__attributesOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__attributesOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $component = $__componentOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__componentOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal8cc9d3143946b992b324617832699c5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cc9d3143946b992b324617832699c5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.index','data' => ['name' => 'canvas-save-confirm','class' => 'min-w-[22rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'canvas-save-confirm','class' => 'min-w-[22rem]']); ?>

        <div class="">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg sm:text-xl font-bold">Save Options</h3>
            </div>

            <div class="space-y-6">
                <!-- File Name -->
                <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['type' => 'text','xModel' => 'fileName','label' => 'File Name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','x-model' => 'fileName','label' => 'File Name']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>

                <!-- Format -->
                <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['xModel' => 'saveFormat','label' => 'Format']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'saveFormat','label' => 'Format']); ?>
                    <option value="png">PNG (High Quality)</option>
                    <option value="jpeg">JPEG (Smaller Size)</option>
                    <option value="webp">WebP (Modern Format)</option>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>

                <!-- PNG Options -->
                <div x-show="saveFormat === 'png'">
                    <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['xModel' => 'transparentBackground','label' => 'Transparent background']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'transparentBackground','label' => 'Transparent background']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $attributes = $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $component = $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-3 flex gap-2 justify-end">
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['size' => 'sm','xOn:click' => '$flux.modal(\'canvas-save-confirm\').close()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','x-on:click' => '$flux.modal(\'canvas-save-confirm\').close()']); ?>
                Cancel
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
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'primary','size' => 'sm','color' => 'blue','@click' => 'saveWithOptions()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','size' => 'sm','color' => 'blue','@click' => 'saveWithOptions()']); ?>
                Save Drawing
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

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $attributes = $__attributesOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__attributesOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $component = $__componentOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__componentOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal8cc9d3143946b992b324617832699c5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cc9d3143946b992b324617832699c5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.index','data' => ['name' => 'help','class' => 'min-w-[22rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'help','class' => 'min-w-[22rem]']); ?>
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <h3 class="text-lg font-semibold ">Help & Shortcuts</h3>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto space-y-8 px-1">

                <!-- Keyboard Shortcuts -->
                <div>
                    <h4 class="text-sm font-medium  mb-3">Keyboard Shortcuts</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-400/10">
                            <span class="">Undo</span>
                            <kbd class="kbd">Ctrl+Z</kbd>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-400/10">
                            <span class="">Redo</span>
                            <kbd class="kbd">Ctrl+Y</kbd>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-400/10">
                            <span class="">Save</span>
                            <kbd class="kbd">Ctrl+S</kbd>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-400/10">
                            <span class="">Clear</span>
                            <kbd class="kbd">Ctrl+Del</kbd>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-400/10">
                            <span class="">Toggle Tool</span>
                            <kbd class="kbd">E</kbd>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-400/10">
                            <span class="">Settings</span>
                            <kbd class="kbd">S</kbd>
                        </div>
                    </div>
                </div>

                <!-- Tips & Features -->
                <div>
                    <h4 class="text-sm font-medium  mb-3">Tips & Features</h4>
                    <div class="space-y-2">
                        <div class="flex items-start space-x-3 p-3 rounded-lg bg-blue-200/10">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <span class="text-sm ">Select practice characters from settings to use as writing
                                guides</span>
                        </div>

                        <div class="flex items-start space-x-3 p-3 rounded-lg bg-green-200/10">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            <span class="text-sm ">Choose different paper styles for various writing experiences</span>
                        </div>

                        <div class="flex items-start space-x-3 p-3 rounded-lg bg-purple-200/10">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                            <span class="text-sm ">Adjust opacity for tracing practice over guide characters</span>
                        </div>

                        <div class="flex items-start space-x-3 p-3 rounded-lg bg-orange-200/10">
                            <div class="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                            <span class="text-sm ">Enable pressure sensitivity when using a stylus for natural
                                writing</span>
                        </div>

                        <div class="flex items-start space-x-3 p-3 rounded-lg bg-indigo-200/10">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mt-2"></div>
                            <span class="text-sm ">Use guide lines to help maintain proper character alignment</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end border-t border-gray-200 mt-6 pt-4">
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['xOn:click' => '$flux.modal(\'help\').close()','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click' => '$flux.modal(\'help\').close()','size' => 'sm']); ?>

                    Got it!
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

        <!-- Small helper for keyboard style -->
        <style>
            .kbd {
                @apply px-2 py-1 text-xs font-semibold bg-gray-200 border border-gray-300 rounded;
            }
        </style>


     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $attributes = $__attributesOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__attributesOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $component = $__componentOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__componentOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>


    <!-- Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Floating Button Styles */
        .floating-btn {
            @apply w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-xl bg-white/90 backdrop-blur-sm border border-gray-200/50 shadow-lg hover:shadow-xl hover: transition-all duration-200 hover:scale-105;
        }

        .floating-btn-active {
            @apply w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-xl bg-blue-500 border border-blue-600 shadow-lg text-white transition-all duration-200 scale-105;
        }

        .floating-btn-ghost {
            @apply inline-flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors duration-200;
        }

        /* Modern Slider */
        .modern-slider {
            @apply h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer;
        }

        .modern-slider::-webkit-slider-thumb {
            @apply appearance-none w-6 h-6 bg-blue-500 rounded-full cursor-pointer shadow-lg hover:shadow-xl transition-shadow;
        }

        .modern-slider::-moz-range-thumb {
            @apply w-6 h-6 bg-blue-500 rounded-full cursor-pointer border-none shadow-lg;
        }

        /* Paper Patterns */
        .bg-lined-paper {
            background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 1px, transparent 1px);
            background-size: 100% 20px;
        }

        .bg-grid-paper {
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.3) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .bg-graph-paper {
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 1px, transparent 1px),
                linear-gradient(to right, rgba(0, 0, 0, 0.3) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 1px, transparent 1px);
            background-size: 20px 20px, 20px 20px, 100px 100px, 100px 100px;
        }

        /* Touch Improvements */
        @media (hover: none) and (pointer: coarse) {
            .floating-btn:hover {
                @apply scale-100;
            }

            .floating-btn:active {
                @apply scale-95;
            }
        }

        /* Focus States */
        .floating-btn:focus-visible {
            @apply outline-none ring-2 ring-blue-500 ring-offset-2;
        }
    </style>

    <!-- JavaScript -->
    <script>
        function modernDrawingApp() {
            return {
                // Canvas & Drawing State
                canvas: null,
                ctx: null,
                isDrawing: false,
                lastX: 0,
                lastY: 0,

                // Tool Settings
                currentColor: '#000000',
                currentSize: 5,
                opacity: 1,
                activeTool: 'pen',

                // UI State
                showToast: false,
                toastMessage: '',
                toastIcon: 'success',
                guideText: '',
                currentPaperStyle: 'blank',
                fileName: `writing-practice-${new Date().toISOString().slice(0, 10)}`,
                saveFormat: 'png',
                transparentBackground: false,

                // Advanced Settings
                pressureSensitivity: false,
                smoothing: true,
                guideLines: true,

                // History
                drawingHistory: [],
                historyIndex: -1,

                // Presets
                colorPresets: ['#000000', '#dc2626', '#2563eb', '#16a34a', '#ea580c', '#9333ea', '#64748b', '#ffffff'],

                // Tools Configuration
                tools: [{
                    id: 'pen',
                    name: 'Pen'
                }, {
                    id: 'eraser',
                    name: 'Eraser'
                }],

                // Paper Styles
                paperStyles: [{
                    id: 'blank',
                    name: 'Blank',
                    class: 'bg-white'
                }, {
                    id: 'lined',
                    name: 'Lined',
                    class: 'bg-white bg-lined-paper'
                }, {
                    id: 'grid',
                    name: 'Grid',
                    class: 'bg-white bg-grid-paper'
                }, {
                    id: 'graph',
                    name: 'Graph',
                    class: 'bg-white bg-graph-paper'
                }, {
                    id: 'yellow',
                    name: 'Yellow',
                    class: 'bg-yellow-50'
                }, {
                    id: 'parchment',
                    name: 'Parchment',
                    class: 'bg-amber-50'
                }],

                // Character Categories
                characterCategories: [{
                    name: 'English Uppercase',
                    open: false,
                    characters: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')
                }, {
                    name: 'English Lowercase',
                    open: false,
                    characters: 'abcdefghijklmnopqrstuvwxyz'.split('')
                }, {
                    name: 'Numbers',
                    open: false,
                    characters: '0123456789'.split('')
                }, {
                    name: 'Bangla Vowels (স্বরবর্ণ)',
                    open: false,
                    characters: 'অআইঈউঊঋএঐওঔ'.split('')
                }, {
                    name: 'Bangla Consonants (ব্যঞ্জনবর্ণ)',
                    open: false,
                    characters: 'কখগঘঙচছজঝঞটঠডঢণতথদধনপফবভমযরলশষসহড়ঢ়য়'.split('')
                }, {
                    name: 'Bangla Numbers',
                    open: false,
                    characters: '০১২৩৪৫৬৭৮৯'.split('')
                }],

                // Initialize
                init() {
                    this.$nextTick(() => {
                        this.setupCanvas();
                        this.setupEventListeners();
                        this.clearCanvas();
                    });
                },

                // Canvas Setup
                setupCanvas() {
                    this.canvas = this.$refs.canvas;
                    this.ctx = this.canvas.getContext('2d');
                    this.resizeCanvas();
                },

                setupEventListeners() {
                    window.addEventListener('resize', () => this.resizeCanvas());
                    document.addEventListener('keydown', (e) => this.handleKeydown(e));

                    // Prevent context menu on canvas
                    this.canvas.addEventListener('contextmenu', e => e.preventDefault());
                },

                resizeCanvas() {
                    const container = this.canvas.parentElement;
                    const dpr = window.devicePixelRatio || 1;

                    // Store current canvas content
                    const imageData = this.canvas.toDataURL();

                    this.canvas.width = container.clientWidth * dpr;
                    this.canvas.height = container.clientHeight * dpr;

                    this.ctx.scale(dpr, dpr);
                    this.canvas.style.width = `${container.clientWidth}px`;
                    this.canvas.style.height = `${container.clientHeight}px`;

                    // Restore canvas content
                    if (imageData && imageData !== 'data:,') {
                        const img = new Image();
                        img.onload = () => {
                            this.drawPaperBackground();
                            this.ctx.drawImage(img, 0, 0);
                        };
                        img.src = imageData;
                    } else {
                        this.drawPaperBackground();
                    }
                },

                // Drawing Functions
                startDrawing(e) {
                    this.isDrawing = true;
                    const pos = this.getPosition(e);
                    this.lastX = pos.x;
                    this.lastY = pos.y;

                    this.ctx.beginPath();
                    this.ctx.moveTo(this.lastX, this.lastY);
                    this.setDrawingStyle();
                },

                draw(e) {
                    if (!this.isDrawing) return;

                    const pos = this.getPosition(e);

                    if (this.smoothing) {
                        this.drawSmoothLine(this.lastX, this.lastY, pos.x, pos.y);
                    } else {
                        this.ctx.lineTo(pos.x, pos.y);
                        this.ctx.stroke();
                    }

                    this.lastX = pos.x;
                    this.lastY = pos.y;
                },

                stopDrawing() {
                    if (this.isDrawing) {
                        this.isDrawing = false;
                        this.ctx.globalCompositeOperation = 'source-over';
                        this.saveState();
                    }
                },

                drawSmoothLine(x1, y1, x2, y2) {
                    const cp1x = x1 + (x2 - x1) / 3;
                    const cp1y = y1 + (y2 - y1) / 3;
                    const cp2x = x1 + 2 * (x2 - x1) / 3;
                    const cp2y = y1 + 2 * (y2 - y1) / 3;

                    this.ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, x2, y2);
                    this.ctx.stroke();
                },

                setDrawingStyle() {
                    if (this.activeTool === 'eraser') {
                        this.ctx.globalCompositeOperation = 'destination-out';
                        this.ctx.strokeStyle = 'rgba(255,255,255,1)';
                    } else {
                        this.ctx.globalCompositeOperation = 'source-over';
                        this.ctx.strokeStyle = this.hexToRgba(this.currentColor, this.opacity);
                    }

                    this.ctx.lineWidth = this.currentSize;
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';
                },

                // Utility Functions
                getPosition(e) {
                    const rect = this.canvas.getBoundingClientRect();

                    if (e.type.includes('touch')) {
                        const touch = e.touches[0] || e.changedTouches[0];
                        return {
                            x: (touch.clientX - rect.left) * (this.canvas.width / rect.width) / (window.devicePixelRatio ||
                                1),
                            y: (touch.clientY - rect.top) * (this.canvas.height / rect.height) / (window.devicePixelRatio ||
                                1)
                        };
                    }

                    return {
                        x: (e.clientX - rect.left) * (this.canvas.width / rect.width) / (window.devicePixelRatio || 1),
                        y: (e.clientY - rect.top) * (this.canvas.height / rect.height) / (window.devicePixelRatio || 1)
                    };
                },

                hexToRgba(hex, alpha) {
                    const r = parseInt(hex.slice(1, 3), 16);
                    const g = parseInt(hex.slice(3, 5), 16);
                    const b = parseInt(hex.slice(5, 7), 16);
                    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                },

                // Tool Functions
                setActiveTool(toolId) {
                    this.activeTool = toolId;
                    // this.showToast(`Switched to ${toolId}`, 'info');
                },

                // Character Functions
                toggleCategory(index) {
                    this.characterCategories[index].open = !this.characterCategories[index].open;
                },

                setGuideText(char) {
                    this.guideText = char;
                    // this.showToast(`Guide character: ${char}`, 'info');
                },

                // Paper Functions
                setPaperStyle(styleId) {
                    this.currentPaperStyle = styleId;
                    this.clearCanvas();
                    // this.showToast(`Paper style: ${styleId}`, 'info');
                },

                drawPaperBackground() {
                    const width = this.canvas.width / (window.devicePixelRatio || 1);
                    const height = this.canvas.height / (window.devicePixelRatio || 1);

                    // Base background
                    this.ctx.fillStyle = this.getPaperColor();
                    this.ctx.fillRect(0, 0, width, height);

                    if (!this.guideLines) return;

                    // Draw patterns based on paper style
                    switch (this.currentPaperStyle) {
                        case 'lined':
                            this.drawLinedPattern(width, height);
                            break;
                        case 'grid':
                            this.drawGridPattern(width, height);
                            break;
                        case 'graph':
                            this.drawGraphPattern(width, height);
                            break;
                    }
                },

                getPaperColor() {
                    const colors = {
                        'blank': '#ffffff',
                        'lined': '#ffffff',
                        'grid': '#ffffff',
                        'graph': '#ffffff',
                        'yellow': '#fefce8',
                        'parchment': '#fffbeb'
                    };
                    return colors[this.currentPaperStyle] || '#ffffff';
                },

                drawLinedPattern(width, height) {
                    const lineSpacing = 30;
                    this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)';
                    this.ctx.lineWidth = 1;

                    for (let y = lineSpacing; y < height; y += lineSpacing) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }
                },

                drawGridPattern(width, height) {
                    const gridSize = 25;
                    this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)';
                    this.ctx.lineWidth = 1;

                    // Vertical lines
                    for (let x = gridSize; x < width; x += gridSize) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(x, 0);
                        this.ctx.lineTo(x, height);
                        this.ctx.stroke();
                    }

                    // Horizontal lines
                    for (let y = gridSize; y < height; y += gridSize) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }
                },

                drawGraphPattern(width, height) {
                    const subGrid = 25;
                    const mainGrid = 125;

                    // Sub grid
                    this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.05)';
                    this.ctx.lineWidth = 1;

                    for (let x = subGrid; x < width; x += subGrid) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(x, 0);
                        this.ctx.lineTo(x, height);
                        this.ctx.stroke();
                    }

                    for (let y = subGrid; y < height; y += subGrid) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }

                    // Main grid
                    this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.2)';
                    this.ctx.lineWidth = 1.5;

                    for (let x = mainGrid; x < width; x += mainGrid) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(x, 0);
                        this.ctx.lineTo(x, height);
                        this.ctx.stroke();
                    }

                    for (let y = mainGrid; y < height; y += mainGrid) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(0, y);
                        this.ctx.lineTo(width, y);
                        this.ctx.stroke();
                    }
                },

                // History Functions
                saveState() {
                    if (this.historyIndex < this.drawingHistory.length - 1) {
                        this.drawingHistory = this.drawingHistory.slice(0, this.historyIndex + 1);
                    }

                    this.drawingHistory.push(this.canvas.toDataURL());
                    this.historyIndex = this.drawingHistory.length - 1;

                    // Limit history
                    if (this.drawingHistory.length > 50) {
                        this.drawingHistory.shift();
                        this.historyIndex--;
                    }
                },

                undo() {
                    if (this.historyIndex > 0) {
                        this.historyIndex--;
                        this.restoreCanvas();
                        // this.showToast('Undone', 'info');
                    }
                },

                redo() {
                    if (this.historyIndex < this.drawingHistory.length - 1) {
                        this.historyIndex++;
                        this.restoreCanvas();
                        // this.showToast('Redone', 'info');
                    }
                },

                restoreCanvas() {
                    if (this.drawingHistory.length === 0) return;

                    const img = new Image();
                    img.onload = () => {
                        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                        this.drawPaperBackground();
                        this.ctx.drawImage(img, 0, 0);
                    };
                    img.src = this.drawingHistory[this.historyIndex];
                },

                // Canvas Actions
                clearCanvas() {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.drawPaperBackground();
                    this.saveState();
                    // this.showToast('Canvas cleared', 'success');
                },

                // Save Functions
                saveWithOptions() {
                    let mimeType, quality;

                    switch (this.saveFormat) {
                        case 'jpeg':
                            mimeType = 'image/jpeg';
                            quality = 0.9;
                            break;
                        case 'webp':
                            mimeType = 'image/webp';
                            quality = 0.9;
                            break;
                        default:
                            mimeType = 'image/png';
                            quality = 1;
                    }

                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = this.canvas.width;
                    tempCanvas.height = this.canvas.height;
                    const tempCtx = tempCanvas.getContext('2d');

                    if (this.transparentBackground && this.saveFormat === 'png') {
                        tempCtx.clearRect(0, 0, tempCanvas.width, tempCanvas.height);
                    } else {
                        tempCtx.fillStyle = '#ffffff';
                        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                    }

                    tempCtx.drawImage(this.canvas, 0, 0);

                    const dataURL = tempCanvas.toDataURL(mimeType, quality);
                    const link = document.createElement('a');
                    link.download = `${this.fileName}.${this.saveFormat}`;
                    link.href = dataURL;
                    link.click();

                    // this.showToast('Drawing saved successfully!', 'success');
                },

                // Utility Functions
                toggleFullscreen() {
                    const canvas = document.getElementById('myCanvas'); // replace with your canvas ID

                    if (!document.fullscreenElement) {
                        if (canvas.requestFullscreen) {
                            canvas.requestFullscreen().catch(err => {
                                // this.showToast('Fullscreen not supported', 'warning');
                            });
                        } else {
                            // this.showToast('Fullscreen API not available', 'warning');
                        }
                    } else {
                        document.exitFullscreen();
                    }
                },



                handleKeydown(e) {
                    // Prevent default for our shortcuts
                    const shortcuts = ['z', 'y', 's', 'e', 'Delete'];
                    if ((e.ctrlKey || e.metaKey) && shortcuts.includes(e.key)) {
                        e.preventDefault();
                    }

                    // Undo
                    if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                        this.undo();
                    }

                    // Redo
                    if (((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'Z') ||
                        ((e.ctrlKey || e.metaKey) && e.key === 'y')) {
                        this.redo();
                    }

                    // Save
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        this.$wire.set('showSaveOptions', true);
                    }

                    // Clear
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Delete') {
                        this.$wire.set('showClearConfirm', true);
                    }

                    // Toggle tool
                    if (e.key.toLowerCase() === 'e') {
                        e.preventDefault();
                        const currentIndex = this.tools.findIndex(tool => tool.id === this.activeTool);
                        const nextIndex = (currentIndex + 1) % this.tools.length;
                        this.setActiveTool(this.tools[nextIndex].id);
                    }

                    // Settings
                    if (e.key.toLowerCase() === 's' && !e.ctrlKey && !e.metaKey) {
                        e.preventDefault();
                        this.$wire.set('showSettings', true);
                    }
                },

                // Toast System
                showToast(message, icon = 'info') {
                    this.toastMessage = message;
                    this.toastIcon = icon;
                    // this.showToast = true;

                    setTimeout(() => {
                        // this.showToast = false;
                    }, 3000);
                }
            };
        }
    </script>
</div><?php /**PATH /var/www/html/totthobox/resources/views/livewire/website/education/child/practice.blade.php ENDPATH**/ ?>