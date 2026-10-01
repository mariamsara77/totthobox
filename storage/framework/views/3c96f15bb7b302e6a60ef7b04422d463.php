<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'placeholder' => 'Select date...',
    'withToday' => false,
    'selectableHeader' => true,
    'size' => 'md',
    'variant' => 'default',
    'label' => null,
    'clearable' => false,
    'mode' => 'single', // 'single' অথবা 'range'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'placeholder' => 'Select date...',
    'withToday' => false,
    'selectableHeader' => true,
    'size' => 'md',
    'variant' => 'default',
    'label' => null,
    'clearable' => false,
    'mode' => 'single', // 'single' অথবা 'range'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{
    open: false,
    view: 'calendar',
    mode: '<?php echo e($mode); ?>',
    value: <?php if ((object) ($attributes->wire('model')) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')->value()); ?>')<?php echo e($attributes->wire('model')->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')); ?>')<?php endif; ?>,
    rangeStart: null,
    rangeEnd: null,
    viewYear: new Date().getFullYear(),
    viewMonth: new Date().getMonth(),
    days: [],
    months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    popoverStyle: {},

    init() {
        this.parseValue();
        this.generateCalendar();
        this.$watch('value', () => this.parseValue());

        this.$watch('open', (value) => {
            if (value) {
                this.$nextTick(() => this.updatePopoverPosition());
                window.addEventListener('scroll', this._scrollHandler, true);
                window.addEventListener('resize', this._resizeHandler);
            } else {
                window.removeEventListener('scroll', this._scrollHandler, true);
                window.removeEventListener('resize', this._resizeHandler);
            }
        });

        this._scrollHandler = () => {
            if (this.open) this.updatePopoverPosition();
        };
        this._resizeHandler = () => {
            if (this.open) this.updatePopoverPosition();
        };
    },

    updatePopoverPosition() {
        const trigger = this.$refs.trigger;
        if (!trigger) return;

        const rect = trigger.getBoundingClientRect();
        const gap = 8;
        const popoverWidth = Math.max(rect.width, 280); // minimum width

        // Viewport-এর বাইরে চলে যাওয়া ঠেকানোর জন্য simple check
        let top = rect.bottom + gap;
        let left = rect.left;

        // যদি নিচে জায়গা না থাকে তাহলে উপরে দেখাবে
        const estimatedHeight = 320;
        if (top + estimatedHeight > window.innerHeight && rect.top > estimatedHeight) {
            top = rect.top - estimatedHeight - gap;
        }

        // বামে/ডানে overflow হলে adjust
        if (left + popoverWidth > window.innerWidth) {
            left = window.innerWidth - popoverWidth - 10;
        }
        if (left < 10) left = 10;

        this.popoverStyle = {
            position: 'fixed',
            top: `${top}px`,
            left: `${left}px`,
            width: `${popoverWidth}px`,
            zIndex: 9999
        };
    },

    parseValue() {
        if (!this.value) {
            this.rangeStart = null;
            this.rangeEnd = null;
            return;
        }
        if (this.mode === 'range') {
            const parts = this.value.split('/');
            this.rangeStart = parts[0] || null;
            this.rangeEnd = parts[1] || null;
            if (this.rangeStart) {
                let d = new Date(this.rangeStart + 'T00:00:00');
                if (!isNaN(d)) {
                    this.viewYear = d.getFullYear();
                    this.viewMonth = d.getMonth();
                }
            }
        } else {
            let d = new Date(this.value + 'T00:00:00');
            if (!isNaN(d)) {
                this.viewYear = d.getFullYear();
                this.viewMonth = d.getMonth();
            }
        }
    },

    generateCalendar() {
        const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay();
        const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
        this.days = Array(firstDay).fill(null).concat(
            Array.from({ length: daysInMonth }, (_, i) => i + 1)
        );
    },

    selectDate(day) {
        if (!day) return;
        const m = String(this.viewMonth + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        const dateStr = `${this.viewYear}-${m}-${d}`;

        if (this.mode === 'single') {
            this.value = dateStr;
            this.open = false;
        } else {
            if (!this.rangeStart || (this.rangeStart && this.rangeEnd)) {
                this.rangeStart = dateStr;
                this.rangeEnd = null;
                this.value = dateStr;
            } else {
                if (new Date(dateStr + 'T00:00:00') < new Date(this.rangeStart + 'T00:00:00')) {
                    this.rangeStart = dateStr;
                    this.value = dateStr;
                } else {
                    this.rangeEnd = dateStr;
                    this.value = `${this.rangeStart}/${this.rangeEnd}`;
                    this.open = false;
                }
            }
        }
    },

    selectMonth(index) {
        this.viewMonth = index;
        this.generateCalendar();
        this.view = 'calendar';
    },

    selectYear(year) {
        this.viewYear = year;
        this.generateCalendar();
        this.view = 'calendar';
    },

    clearDate() {
        this.value = '';
        this.rangeStart = null;
        this.rangeEnd = null;
        this.open = false;
    },

    prevAction() {
        if (this.view === 'calendar') {
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear--;
            } else {
                this.viewMonth--;
            }
        } else if (this.view === 'year') {
            this.viewYear -= 12;
        } else if (this.view === 'month') {
            this.viewYear--;
        }
        this.generateCalendar();
    },

    nextAction() {
        if (this.view === 'calendar') {
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear++;
            } else {
                this.viewMonth++;
            }
        } else if (this.view === 'year') {
            this.viewYear += 12;
        } else if (this.view === 'month') {
            this.viewYear++;
        }
        this.generateCalendar();
    },

    isSelected(day) {
        if (!day) return false;
        const m = String(this.viewMonth + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        const dateStr = `${this.viewYear}-${m}-${d}`;

        if (this.mode === 'single') {
            return this.value === dateStr;
        }
        return this.rangeStart === dateStr || this.rangeEnd === dateStr;
    },

    isInRange(day) {
        if (!day || this.mode !== 'range' || !this.rangeStart || !this.rangeEnd) return false;
        const m = String(this.viewMonth + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        const dateStr = `${this.viewYear}-${m}-${d}`;

        const current = new Date(dateStr + 'T00:00:00');
        const start = new Date(this.rangeStart + 'T00:00:00');
        const end = new Date(this.rangeEnd + 'T00:00:00');

        return current > start && current < end;
    },

    isToday(day) {
        if (!day) return false;
        const now = new Date();
        return now.getFullYear() === this.viewYear && now.getMonth() === this.viewMonth && now.getDate() === day;
    },

    goToday() {
        const now = new Date();
        this.viewYear = now.getFullYear();
        this.viewMonth = now.getMonth();
        this.generateCalendar();
        this.selectDate(now.getDate());
    }
}" class="relative w-full">

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <?php if (isset($component)) { $__componentOriginal8a84eac5abb8af1e2274971f8640b38f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a84eac5abb8af1e2274971f8640b38f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::label','data' => ['class' => 'mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($label); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a84eac5abb8af1e2274971f8640b38f)): ?>
<?php $attributes = $__attributesOriginal8a84eac5abb8af1e2274971f8640b38f; ?>
<?php unset($__attributesOriginal8a84eac5abb8af1e2274971f8640b38f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a84eac5abb8af1e2274971f8640b38f)): ?>
<?php $component = $__componentOriginal8a84eac5abb8af1e2274971f8640b38f; ?>
<?php unset($__componentOriginal8a84eac5abb8af1e2274971f8640b38f); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="relative items-center w-full" x-ref="trigger">
        <div class="w-full" @click="open = !open">
            <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['readonly' => true,'xBind:value' => 'value','placeholder' => ''.e($placeholder).'','icon' => 'calendar','class' => 'w-full cursor-pointer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['readonly' => true,'x-bind:value' => 'value','placeholder' => ''.e($placeholder).'','icon' => 'calendar','class' => 'w-full cursor-pointer']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
        </div>

        <div class="absolute right-2 top-1/2 -translate-y-1/2">
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['xShow' => ''.e($clearable ? 'true' : 'false').' && value','xCloak' => true,'@click.stop' => 'clearDate()','size' => 'sm','variant' => 'ghost','icon' => 'x-mark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => ''.e($clearable ? 'true' : 'false').' && value','x-cloak' => true,'@click.stop' => 'clearDate()','size' => 'sm','variant' => 'ghost','icon' => 'x-mark']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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

    
    <template x-teleport="body">
        <div x-show="open" x-cloak x-transition @click.outside="open = false" x-bind:style="popoverStyle"
            class="p-2 bg-zinc-100 dark:bg-zinc-700 rounded-xl shadow-xl border border-zinc-400/25">

            
            <div class="flex items-center justify-between mb-3">
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','size' => 'sm','square' => true,'@click' => 'prevAction()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','square' => true,'@click' => 'prevAction()']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal93e8a1cf63877447e3f60f50005ff258 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93e8a1cf63877447e3f60f50005ff258 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chevron-left','data' => ['class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chevron-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal93e8a1cf63877447e3f60f50005ff258)): ?>
<?php $attributes = $__attributesOriginal93e8a1cf63877447e3f60f50005ff258; ?>
<?php unset($__attributesOriginal93e8a1cf63877447e3f60f50005ff258); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal93e8a1cf63877447e3f60f50005ff258)): ?>
<?php $component = $__componentOriginal93e8a1cf63877447e3f60f50005ff258; ?>
<?php unset($__componentOriginal93e8a1cf63877447e3f60f50005ff258); ?>
<?php endif; ?>
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

                <div class="flex items-center gap-41">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectableHeader): ?>
                        <button type="button" @click="view = (view === 'month') ? 'calendar' : 'month'"
                            class="px-2 py-1 text-sm font-semibold rounded-md hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors"
                            x-text="months[viewMonth]"></button>
                        <button type="button" @click="view = (view === 'year') ? 'calendar' : 'year'"
                            class="px-2 py-1 text-sm font-semibold rounded-md hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors"
                            x-text="viewYear"></button>
                    <?php else: ?>
                        <span class="text-sm font-semibold px-2" x-text="months[viewMonth] + ' ' + viewYear"></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','size' => 'sm','square' => true,'@click' => 'nextAction()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','square' => true,'@click' => 'nextAction()']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if (isset($component)) { $__componentOriginal31cb76c8d087d4f00797aeea7232b4c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chevron-right','data' => ['class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3)): ?>
<?php $attributes = $__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3; ?>
<?php unset($__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31cb76c8d087d4f00797aeea7232b4c3)): ?>
<?php $component = $__componentOriginal31cb76c8d087d4f00797aeea7232b4c3; ?>
<?php unset($__componentOriginal31cb76c8d087d4f00797aeea7232b4c3); ?>
<?php endif; ?>
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

            
            <div x-show="view === 'calendar'">
                <div class="grid grid-cols-7 mb-2">
                    <template x-for="day in ['Su','Mo','Tu','We','Th','Fr','Sa']">
                        <div class="text-center py-1 text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
                            x-text="day"></div>
                    </template>
                </div>

                <div class="grid grid-cols-7 gap-4y-1">
                    <template x-for="(day, index) in days" :key="index">
                        <div class="aspect-square flex items-center justify-center relative">
                            <button x-show="day !== null" type="button" @click="selectDate(day)"
                                :class="{
                                    'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold rounded-lg': isSelected(
                                        day),
                                    'bg-zinc-200 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100': isInRange(day),
                                    'text-zinc-900 dark:text-zinc-100 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-lg':
                                        !isSelected(day) && !isInRange(day),
                                    'ring-1 ring-zinc-400 dark:ring-zinc-500': isToday(day) && !isSelected(day)
                                }"
                                class="w-full h-full flex items-center justify-center text-sm transition-all"
                                x-text="day">
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            
            <div x-show="view === 'month'" class="grid grid-cols-3 gap-4">
                <template x-for="(m, i) in months">
                    <button type="button" @click="selectMonth(i)"
                        :class="viewMonth === i ?
                            'bg-zinc-950 text-white dark:bg-zinc-100 dark:text-zinc-900' :
                            'hover:bg-zinc-200 dark:hover:bg-zinc-600'"
                        class="py-2 text-sm font-medium rounded-lg transition-colors" x-text="m.substring(0, 3)">
                    </button>
                </template>
            </div>

            
            <div x-show="view === 'year'" class="h-48 overflow-y-auto pr-1 custom-scrollbar">
                <div class="grid grid-cols-3 gap-4">
                    <template x-for="y in Array.from({ length: 101 }, (_, i) => new Date().getFullYear() - 80 + i)">
                        <button type="button" @click="selectYear(y)"
                            :class="viewYear === y ?
                                'bg-zinc-950 text-white dark:bg-zinc-100 dark:text-zinc-900' :
                                'hover:bg-zinc-200 dark:hover:bg-zinc-600'"
                            class="py-1.5 text-sm font-medium rounded-lg transition-colors" x-text="y">
                        </button>
                    </template>
                </div>
            </div>

            
            <div class="mt-4 pt-2 border-t border-zinc-400/25 flex gap-4 justify-between items-center">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($withToday): ?>
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'filled','size' => 'sm','@click' => 'goToday()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'filled','size' => 'sm','@click' => 'goToday()']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Today
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
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['size' => 'sm','variant' => 'ghost','xShow' => 'value','@click' => 'clearDate()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','variant' => 'ghost','x-show' => 'value','@click' => 'clearDate()']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Remove Date
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
    </template>
</div>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/date-picker.blade.php ENDPATH**/ ?>