<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label' => null, 'description' => null]));

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

foreach (array_filter((['label' => null, 'description' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->only('class')); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <?php if (isset($component)) { $__componentOriginal8a84eac5abb8af1e2274971f8640b38f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a84eac5abb8af1e2274971f8640b38f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::label','data' => ['class' => 'mb-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-1']); ?><?php echo e($label); ?> <?php echo $__env->renderComponent(); ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
        <?php if (isset($component)) { $__componentOriginalf323826200b199a8f33f16501b918a9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf323826200b199a8f33f16501b918a9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::description','data' => ['class' => 'mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-3']); ?><?php echo e($description); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $attributes = $__attributesOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__attributesOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $component = $__componentOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__componentOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div x-data="{
        content: <?php if ((object) ($attributes->wire('model')) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')->value()); ?>')<?php echo e($attributes->wire('model')->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')); ?>')<?php endif; ?>,
        async initEditor() {
            // ১. Quill লাইব্রেরিটি লোড হওয়ার জন্য অপেক্ষা করবে
            const Quill = await window.initQuill();

            // ২. এডিটর সেটআপ (আপনার আগের লজিক অনুযায়ী)
            const quill = new Quill($refs.quillEditor, {
                theme: 'snow',
                modules: {
                    toolbar: $refs.toolbar
                }
            });

            if (this.content) {
                quill.root.innerHTML = this.content;
            }

            quill.on('text-change', () => {
                this.content = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            });

            this.$watch('content', value => {
                if (value !== quill.root.innerHTML) {
                    quill.root.innerHTML = value || '';
                }
            });
        }
    }" x-init="initEditor" class="relative group">
        <div
            class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-700/50 shadow-sm transition-all">

            
            <div x-ref="toolbar"
                class="flex items-center flex-wrap gap-1 px-2 py-1.5 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50">

                
                <span class="ql-formats !mr-2">
                    <select class="ql-header !w-24 !bg-transparent">
                        <option value="1">Heading 1</option>
                        <option value="2">Heading 2</option>
                        <option selected>Normal</option>
                    </select>
                </span>

                <div class="h-4 w-[1px] bg-zinc-300 dark:bg-zinc-600 mx-1"></div>

                
                <span class="ql-formats !mr-1">
                    <button class="ql-bold" title="Bold"></button>
                    <button class="ql-italic" title="Italic"></button>
                    <button class="ql-underline" title="Underline"></button>
                    
                </span>

                <div class="h-4 w-[1px] bg-zinc-300 dark:bg-zinc-600 mx-1"></div>

                
                <span class="ql-formats !mr-1">
                    <button class="ql-list" value="ordered" title="Numbered List"></button>
                    <button class="ql-list" value="bullet" title="Bullet List"></button>
                </span>

                <div class="h-4 w-[1px] bg-zinc-300 dark:bg-zinc-600 mx-1"></div>

                
                <span class="ql-formats !mr-0">
                    <button class="ql-link" title="Insert Link"></button>
                    <button class="ql-clean" title="Remove Formatting"></button>
                </span>
            </div>

            
            <div x-ref="quillEditor" class="min-h-[150px] leading-relaxed text-black dark:text-white">
            </div>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal5730b1630871592dc0d77210545c88c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5730b1630871592dc0d77210545c88c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::error','data' => ['name' => $attributes->wire('model')->value()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->wire('model')->value())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5730b1630871592dc0d77210545c88c1)): ?>
<?php $attributes = $__attributesOriginal5730b1630871592dc0d77210545c88c1; ?>
<?php unset($__attributesOriginal5730b1630871592dc0d77210545c88c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5730b1630871592dc0d77210545c88c1)): ?>
<?php $component = $__componentOriginal5730b1630871592dc0d77210545c88c1; ?>
<?php unset($__componentOriginal5730b1630871592dc0d77210545c88c1); ?>
<?php endif; ?>
</div>

<style>
    /* Quill Snow Theme Overrides */
    .ql-toolbar.ql-snow,
    .ql-container.ql-snow {
        border: none !important;
    }

    /* Toolbar Icons & Dropdown Style */
    .ql-snow .ql-stroke {
        stroke: #71717a !important;
        stroke-width: 2px;
    }

    .ql-snow .ql-fill {
        fill: #71717a !important;
    }

    .dark .ql-snow .ql-stroke {
        stroke: #a1a1aa !important;
    }

    .dark .ql-snow .ql-fill {
        fill: #a1a1aa !important;
    }

    /* Active Tool State */
    .ql-snow.ql-toolbar button:hover .ql-stroke,
    .ql-snow.ql-toolbar button.ql-active .ql-stroke {
        stroke: #3b82f6 !important;
    }

    .ql-snow.ql-toolbar button:hover .ql-fill,
    .ql-snow.ql-toolbar button.ql-active .ql-fill {
        fill: #3b82f6 !important;
    }

    /* Floating Link Bar Fix & Customization */
    .ql-snow .ql-tooltip {
        @apply !rounded-lg !border-zinc-200 dark: !border-zinc-700 !bg-white dark: !bg-zinc-800 !shadow-xl !text-zinc-800 dark: !text-zinc-200;
        left: 20px !important;
        top: 10px !important;
        z-index: 50;
    }

    .ql-snow .ql-tooltip input[type=text] {
        @apply !rounded-md !border-zinc-200 dark: !border-zinc-600 dark: !bg-zinc-900 !px-2 !py-1;
    }

    .ql-snow .ql-tooltip a.ql-action::after {
        content: 'Apply';
        @apply !text-blue-600;
        font-weight: bold;
    }

    .ql-snow .ql-tooltip a.ql-remove::after {
        content: 'Remove';
        @apply !text-red-500;
    }

    /* Fix for Ordered List Numbering */
    .ql-editor ol {
        padding-left: 0 !important;
    }

    .ql-editor ol li {
        padding-left: 1.5rem !important;
    }

    /* Buttons Styling */
    .ql-formats button {
        @apply rounded-md transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700/50 !important;
    }



    /* ১. শুধুমাত্র Ordered List (Numbered) ফিক্স */
    .ql-editor ol li[data-list="ordered"] {
        counter-increment: list-0;
        padding-left: 1.5rem !important;
        position: relative;
        list-style-type: none !important;
        /* ডিফল্ট ০ বা ডট সরানোর জন্য */
    }

    .ql-editor ol li[data-list="ordered"]::before {
        content: counter(list-0) ". ";
        position: absolute;
        left: 0;
        @apply text-zinc-500 font-medium;
    }

    /* ২. বুলেট লিস্টের জন্য ডিফল্ট স্টাইল (নম্বর আসা বন্ধ করবে) */
    .ql-editor ul li[data-list="bullet"] {
        list-style-type: disc !important;
        /* ডট দেখাবে */
        padding-left: 0.5rem !important;
    }

    .ql-editor ul li[data-list="bullet"]::before {
        content: "" !important;
        /* নম্বর বা অন্য কিছু আসা ব্লক করবে */
    }

    /* কাউন্টার রিসেট */
    .ql-editor ol {
        counter-reset: list-0;
    }
</style><?php /**PATH /var/www/html/totthobox/resources/views/flux/editor.blade.php ENDPATH**/ ?>