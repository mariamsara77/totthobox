<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Lazy;

?>

<section>
    <div x-data="{
        open: <?php if ((object) ('isOpen') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('isOpen'->value()); ?>')<?php echo e('isOpen'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('isOpen'); ?>')<?php endif; ?>,
        query: <?php if ((object) ('query') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('query'->value()); ?>')<?php echo e('query'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('query'); ?>')<?php endif; ?>,
        highlightedIndex: <?php if ((object) ('highlightedIndex') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('highlightedIndex'->value()); ?>')<?php echo e('highlightedIndex'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('highlightedIndex'); ?>')<?php endif; ?>,
        showFilters: <?php if ((object) ('showFilters') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showFilters'->value()); ?>')<?php echo e('showFilters'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showFilters'); ?>')<?php endif; ?>,
        handleKeydown(event) {
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').decrementHighlight();
                this.scrollIntoView();
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').incrementHighlight();
                this.scrollIntoView();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').selectResult();
            } else if (event.key === 'Escape') {
                if (this.open) {
                    this.open = false;
                } else if (this.showFilters) {
                    this.showFilters = false;
                }
            } else if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
                event.preventDefault();
                this.open = true;
                $nextTick(() => {
                    const input = $el.querySelector('input');
                    if (input) input.focus();
                });
            } else if ((event.metaKey || event.ctrlKey) && event.shiftKey && event.key === 'f') {
                event.preventDefault();
                this.showFilters = !this.showFilters;
            }
        },
        scrollIntoView() {
            const el = this.$refs.resultsContainer?.querySelector(`[data-index='${this.highlightedIndex}']`);
            if (el) {
                el.scrollIntoView({ block: 'nearest' });
            }
        }
    }" @keydown.window="handleKeydown" @click.outside="if (open && !showFilters) open = false"
        class="relative max-w-2xl mx-auto z-50">
        <!-- Search Input -->
        <div class="relative">


            <!-- Search Input -->
            <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['type' => 'text','kbd' => '⌘K','icon' => 'magnifying-glass','placeholder' => 'Search...','wire:model.live.debounce.300ms' => 'query','clearable' => true,'autofocus' => true,'class' => 'backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','kbd' => '⌘K','icon' => 'magnifying-glass','placeholder' => 'Search...','wire:model.live.debounce.300ms' => 'query','clearable' => true,'autofocus' => true,'class' => 'backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-lg']); ?>
                 <?php $__env->slot('iconTrailing', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['size' => 'sm','@click' => 'showFilters = !showFilters','variant' => 'subtle','class' => '-mr-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','@click' => 'showFilters = !showFilters','variant' => 'subtle','class' => '-mr-1']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
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
                 <?php $__env->endSlot(); ?>
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


            <!-- Filters Panel -->
            <div x-show="showFilters" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="absolute top-full mt-2 w-full backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-4 z-50">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by content type</h3>
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['@click' => 'showFilters = false','class' => 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'showFilters = false','class' => 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
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

                <div class="flex flex-wrap gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_unique(array_column($this->getSearchableModels(), 'label')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button wire:click="toggleFilter('<?php echo e($filter); ?>')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center"
                            :class="<?php echo e(in_array($filter, $activeFilters)
                                ? "'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-700'"
                                : "'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600 border border-transparent'"); ?>">
                            <?php echo e($filter); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($filter, $activeFilters)): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($activeFilters) > 0): ?>
                        <button wire:click="clearFilters"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600 transition-colors">
                            Clear all
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-700">
                    <div class="flex items-center space-x-2">
                        <button wire:click="setSearchMode('standard')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex-1 text-center"
                            :class="<?php echo e($searchMode === 'standard'
                                ? "'bg-primary-600'"
                                : "'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600'"); ?>">
                            Standard Search
                        </button>
                        <button wire:click="setSearchMode('advanced')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex-1 text-center"
                            :class="<?php echo e($searchMode === 'advanced'
                                ? "'bg-primary-600'"
                                : "'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600'"); ?>">
                            Advanced Search
                        </button>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($searchMode === 'advanced'): ?>
                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            <p>Use operators like <kbd
                                    class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">"quotes"</kbd> for exact
                                matches, <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">OR</kbd> for
                                alternatives, and <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">-</kbd>
                                to exclude terms.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Results Dropdown -->
        <div x-show="open && query.length > 0" x-transition.opacity.duration.200ms
            class="absolute z-40 mt-2 w-full backdrop-blur-xl bg-zinc-100/50 dark:bg-zinc-800/50 rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 overflow-hidden"
            id="search-results" role="listbox" x-ref="dropdown">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
                <!-- Error State -->
                <div class="p-4 text-center text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="mt-2 text-sm"><?php echo e($error); ?></p>
                </div>
            <?php elseif(empty($flattenedResults) && $query): ?>
                <!-- No Results / Suggestions -->
                <div class="p-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try different search terms or check your
                        filters</p>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($recentSearches) > 0): ?>
                        <div class="mt-6 text-left">
                            <h4
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                Recent searches</h4>
                            <div class="space-y-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentSearches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button wire:click="useRecentSearch('<?php echo e($recent['query']); ?>')"
                                        class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors flex items-center justify-between group">
                                        <span
                                            class="text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400"><?php echo e($recent['query']); ?></span>
                                        <span
                                            class="text-xs text-gray-400"><?php echo e(Carbon::parse($recent['timestamp'])->diffForHumans()); ?></span>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-zinc-700">
                        <button wire:click="$toggle('showSearchTips')"
                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center justify-center mx-auto">
                            Search tips
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSearchTips): ?>
                            <div class="mt-3 text-xs text-left text-gray-500 dark:text-gray-400 space-y-2">
                                <p>• Try different keywords or more general terms</p>
                                <p>• Check your spelling</p>
                                <p>• Use filters to narrow down results</p>
                                <p>• Use quotes for exact phrase matches: <code>"exact phrase"</code></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Results Container -->
                <div class="backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50" x-ref="resultsContainer">
                    <!-- Results Summary -->
                    <div class=" px-4 py-2.5 border-b border-gray-100 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Found <?php echo e(count($flattenedResults)); ?> result types with
                                <?php echo e(array_sum(array_map(fn($group) => count($group['items']), $flattenedResults))); ?>

                                items
                            </p>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($activeFilters) > 0): ?>
                                <button wire:click="clearFilters"
                                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                    Clear <?php echo e(count($activeFilters)); ?> filter(s)
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="max-h-[50vh] overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $flattenedResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupIndex => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <div
                                    class="top-[46px] px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center border-b border-gray-100 dark:border-zinc-700">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($group['icon']):
                                        case ('user'): ?>
                                            <?php if (isset($component)) { $__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.user','data' => ['class' => 'h-4 w-4 mr-2 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 mr-2 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff)): ?>
<?php $attributes = $__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff; ?>
<?php unset($__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff)): ?>
<?php $component = $__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff; ?>
<?php unset($__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff); ?>
<?php endif; ?>
                                        <?php break; ?>

                                        <?php case ('globe'): ?>
                                            <?php if (isset($component)) { $__componentOriginale02ab0f625e6b2501fa40e35388d0046 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale02ab0f625e6b2501fa40e35388d0046 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.globe-alt','data' => ['class' => 'h-4 w-4 mr-2 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.globe-alt'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 mr-2 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale02ab0f625e6b2501fa40e35388d0046)): ?>
<?php $attributes = $__attributesOriginale02ab0f625e6b2501fa40e35388d0046; ?>
<?php unset($__attributesOriginale02ab0f625e6b2501fa40e35388d0046); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale02ab0f625e6b2501fa40e35388d0046)): ?>
<?php $component = $__componentOriginale02ab0f625e6b2501fa40e35388d0046; ?>
<?php unset($__componentOriginale02ab0f625e6b2501fa40e35388d0046); ?>
<?php endif; ?>
                                        <?php break; ?>

                                        <?php case ('document-text'): ?>
                                           <?php if (isset($component)) { $__componentOriginal74697c151ccb8418c53b50a995b31225 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal74697c151ccb8418c53b50a995b31225 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.document-text','data' => ['class' => 'h-4 w-4 mr-2 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.document-text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 mr-2 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal74697c151ccb8418c53b50a995b31225)): ?>
<?php $attributes = $__attributesOriginal74697c151ccb8418c53b50a995b31225; ?>
<?php unset($__attributesOriginal74697c151ccb8418c53b50a995b31225); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal74697c151ccb8418c53b50a995b31225)): ?>
<?php $component = $__componentOriginal74697c151ccb8418c53b50a995b31225; ?>
<?php unset($__componentOriginal74697c151ccb8418c53b50a995b31225); ?>
<?php endif; ?>
                                        <?php break; ?>

                                        <?php default: ?>
                                            <?php if (isset($component)) { $__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.user','data' => ['class' => 'h-4 w-4 mr-2 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 mr-2 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff)): ?>
<?php $attributes = $__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff; ?>
<?php unset($__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff)): ?>
<?php $component = $__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff; ?>
<?php unset($__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff); ?>
<?php endif; ?>
                                    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span><?php echo e($group['type']); ?></span>
                                    <span class="ml-2 text-xs text-gray-400  rounded-full px-2 py-0.5">
                                        <?php echo e(count($group['items'])); ?>

                                    </span>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group['view_all_route']): ?>
                                        <a href="<?php echo e($group['view_all_route']); ?>"
                                            class="ml-auto text-xs text-primary-600 dark:text-primary-400 hover:underline flex items-center"
                                            wire:navigate.hover>
                                            View all
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-0.5"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemIndex => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $absoluteIndex = $loop->parent->index * count($group['items']) + $loop->index;
                                    ?>
                                    <a href="<?php echo e($item['route']); ?>" wire:navigate
                                        class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors border-b border-gray-100 dark:border-zinc-700 last:border-b-0
                              <?php echo e($highlightedIndex === $absoluteIndex ? 'bg-primary-50 dark:bg-primary-900/20' : ''); ?>"
                                        data-index="<?php echo e($absoluteIndex); ?>" role="option"
                                        :aria-selected="<?php echo e($highlightedIndex === $absoluteIndex ? 'true' : 'false'); ?>">
                                        <div class="flex items-start gap-3">
                                            <!-- Icon/Avatar -->
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center mt-0.5">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($item['raw']['avatar'])): ?>
                                                    <img src="<?php echo e(Storage::url($item['raw']['avatar'])); ?>"
                                                        class="h-full w-full rounded-lg object-cover" alt="">
                                                <?php elseif(isset($item['raw']['photo'])): ?>
                                                    <img src="<?php echo e(Storage::url($item['raw']['photo'])); ?>"
                                                        class="h-full w-full rounded-lg object-cover" alt="">
                                                <?php elseif(isset($item['raw']['image'])): ?>
                                                    <img src="<?php echo e(Storage::url($item['raw']['image'])); ?>"
                                                        class="h-full w-full rounded-lg object-cover" alt="">
                                                <?php else: ?>
                                                    <span
                                                        class="text-gray-500 dark:text-gray-400 font-medium uppercase text-sm h-full w-full flex items-center justify-center bg-gray-300 dark:bg-zinc-600 rounded-lg">

                                                        <?php echo e(Str::substr($item['display'], 0, 1)); ?>

                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>

                                            <!-- Main Content -->
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        <?php echo $item['highlighted_display']; ?>

                                                    </p>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($item['badge'])): ?>
                                                        <span
                                                            class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($item['badge']['class']); ?>">
                                                            <?php echo e($item['badge']['value']); ?>

                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>

                                                <!-- Metadata -->
                                                <div class="mt-1.5 space-y-1">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['metadata']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div
                                                            class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                                            <span
                                                                class="font-medium capitalize"><?php echo e(Str::headline($field)); ?>:</span>
                                                            <?php echo $value; ?>

                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Quick Action -->
                                            <div
                                                class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- Search Tips Footer -->
                    <div class="border-t border-gray-100 dark:border-zinc-700 p-3 z-10">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center">
                                    <kbd
                                        class="px-1.5 py-0.5 rounded border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mr-1">↑↓</kbd>
                                    Navigate
                                </span>
                                <span class="flex items-center">
                                    <kbd
                                        class="px-1.5 py-0.5 rounded border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mr-1">↵</kbd>
                                    Select
                                </span>
                                <span class="flex items-center">
                                    <kbd
                                        class="px-1.5 py-0.5 rounded border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mr-1">Esc</kbd>
                                    Close
                                </span>
                            </div>

                            <button wire:click="$toggle('showSearchTips')"
                                class="text-primary-600 dark:text-primary-400 hover:underline">
                                Search help
                            </button>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSearchTips): ?>
                            <div
                                class="mt-2 pt-2 border-t border-gray-200 dark:border-zinc-700 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                <p>• Use <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">Ctrl+F</kbd> /
                                    <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">Cmd+F</kbd> to open
                                    filters
                                </p>
                                <p>• Try different keywords or check your spelling</p>
                                <p>• Use quotes for exact matches: <code>"exact phrase"</code></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section><?php /**PATH /var/www/html/totthobox/resources/views/livewire/global/search.blade.php ENDPATH**/ ?>