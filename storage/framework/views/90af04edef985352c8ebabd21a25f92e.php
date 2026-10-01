<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => null,
    'description' => null,
    'disabled' => false,
    'placeholder' => null,
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
    'label' => null,
    'description' => null,
    'disabled' => false,
    'placeholder' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $wireModel = $attributes->wire('model');
    $fieldName = $wireModel->value();
?>

<div <?php echo e($attributes->only('class')); ?>>

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
<?php $component->withAttributes(['class' => 'mb-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($description); ?> <?php echo $__env->renderComponent(); ?>
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

    
    <div wire:ignore>
        <div x-data="quillEditor({
            modelValue: <?php if ((object) ($wireModel) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($wireModel->value()); ?>')<?php echo e($wireModel->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($wireModel); ?>')<?php endif; ?>,
            disabled: <?php echo e($disabled ? 'true' : 'false'); ?>,
            placeholder: <?php echo e($placeholder ? json_encode($placeholder) : 'null'); ?>

        })" x-init="init"
            x-on:editor-clear.window="if ($event.detail?.field === '<?php echo e($fieldName); ?>') clearContent()"
            class="relative">
            <?php if (isset($component)) { $__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::card.index','data' => ['class' => '!p-0 overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0 overflow-hidden']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                
                <div x-ref="toolbar"
                    class="flex items-center flex-wrap gap-0.5 px-2 py-1.5 border-b border-zinc-400/25 bg-zinc-50 dark:bg-zinc-800/60">
                    <span class="ql-formats !mr-1">
                        <select class="ql-header" title="Text style">
                            <option value="1">Heading 1</option>
                            <option value="2">Heading 2</option>
                            <option value="3">Heading 3</option>
                            <option selected>Paragraph</option>
                        </select>
                    </span>

                    <?php if (isset($component)) { $__componentOriginalc481942d30cc0ab06077963cf20a45e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc481942d30cc0ab06077963cf20a45e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::separator','data' => ['vertical' => true,'class' => 'mx-1.5 self-stretch']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['vertical' => true,'class' => 'mx-1.5 self-stretch']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $attributes = $__attributesOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $component = $__componentOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__componentOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>

                    <span class="ql-formats !mr-1">
                        <button class="ql-bold" title="Bold (Ctrl+B)"></button>
                        <button class="ql-italic" title="Italic (Ctrl+I)"></button>
                        <button class="ql-underline" title="Underline (Ctrl+U)"></button>
                        <button class="ql-strike" title="Strikethrough"></button>
                    </span>

                    <?php if (isset($component)) { $__componentOriginalc481942d30cc0ab06077963cf20a45e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc481942d30cc0ab06077963cf20a45e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::separator','data' => ['vertical' => true,'class' => 'mx-1.5 self-stretch']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['vertical' => true,'class' => 'mx-1.5 self-stretch']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $attributes = $__attributesOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $component = $__componentOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__componentOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>

                    <span class="ql-formats !mr-1">
                        <button class="ql-list" value="ordered" title="Numbered list"></button>
                        <button class="ql-list" value="bullet" title="Bullet list"></button>
                    </span>

                    <?php if (isset($component)) { $__componentOriginalc481942d30cc0ab06077963cf20a45e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc481942d30cc0ab06077963cf20a45e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::separator','data' => ['vertical' => true,'class' => 'mx-1.5 self-stretch']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['vertical' => true,'class' => 'mx-1.5 self-stretch']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $attributes = $__attributesOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $component = $__componentOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__componentOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>

                    <span class="ql-formats !mr-1">
                        <button class="ql-blockquote" title="Blockquote"></button>
                        <button class="ql-code-block" title="Code block"></button>
                    </span>

                    <?php if (isset($component)) { $__componentOriginalc481942d30cc0ab06077963cf20a45e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc481942d30cc0ab06077963cf20a45e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::separator','data' => ['vertical' => true,'class' => 'mx-1.5 self-stretch']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['vertical' => true,'class' => 'mx-1.5 self-stretch']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $attributes = $__attributesOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__attributesOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc481942d30cc0ab06077963cf20a45e8)): ?>
<?php $component = $__componentOriginalc481942d30cc0ab06077963cf20a45e8; ?>
<?php unset($__componentOriginalc481942d30cc0ab06077963cf20a45e8); ?>
<?php endif; ?>

                    <span class="ql-formats !mr-0">
                        <button class="ql-link" title="Insert link"></button>
                        <button class="ql-clean" title="Remove formatting"></button>
                    </span>

                    <div class="flex-1"></div>

                    <?php if (isset($component)) { $__componentOriginal0638ebfbd490c7a414275d493e14cb4e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0638ebfbd490c7a414275d493e14cb4e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::text','data' => ['size' => 'xs','class' => 'pr-1 tabular-nums select-none text-zinc-400 dark:text-zinc-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xs','class' => 'pr-1 tabular-nums select-none text-zinc-400 dark:text-zinc-500']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <span x-text="charCount"></span>
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

                
                <div x-ref="quillEditor" class="max-h-[60vh] overflow-y-auto text-sm leading-relaxed">
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec)): ?>
<?php $attributes = $__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec; ?>
<?php unset($__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec)): ?>
<?php $component = $__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec; ?>
<?php unset($__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($disabled): ?>
                <div class="absolute inset-0 rounded-lg bg-white/60 dark:bg-zinc-900/60 cursor-not-allowed z-10"></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fieldName): ?>
        <?php if (isset($component)) { $__componentOriginal5730b1630871592dc0d77210545c88c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5730b1630871592dc0d77210545c88c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::error','data' => ['name' => $fieldName]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldName)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<script>
    /**
     * Alpine component factory for the Quill rich-text editor.
     * Lazy-loads Quill via window.initQuill() (defined in quill-editor.js).
     */
    function quillEditor({
        modelValue,
        disabled = false,
        placeholder = null
    }) {
        return {
            content: modelValue,
            charCount: 0,
            _quill: null,
            _debounce: null,

            async init() {
                const Quill = await window.initQuill();
                if (!Quill) {
                    console.error('[flux:editor] Quill failed to load.');
                    return;
                }

                const quill = new Quill(this.$refs.quillEditor, {
                    theme: 'snow',
                    bounds: this.$refs.quillEditor,
                    placeholder: placeholder ?? '',
                    readOnly: disabled,
                    modules: {
                        toolbar: this.$refs.toolbar,
                    },
                });

                this._quill = quill;

                // Seed initial content
                if (this.content) {
                    quill.root.innerHTML = this.content;
                    this._updateCharCount();
                }

                // DOM → Livewire (debounced 300 ms)
                quill.on('text-change', () => {
                    clearTimeout(this._debounce);
                    this._debounce = setTimeout(() => {
                        const html = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root
                            .innerHTML;
                        if (this.content !== html) {
                            this.content = html;
                        }
                        this._updateCharCount();
                    }, 300);
                });

                // Livewire → DOM (only when value differs to avoid cursor jump)
                this.$watch('content', value => {
                    if (value !== quill.root.innerHTML) {
                        quill.root.innerHTML = value || '';
                        this._updateCharCount();
                    }
                });
            },

            clearContent() {
                if (this._quill) {
                    this._quill.setContents([]);
                    this.content = '';
                    this.charCount = 0;
                }
            },

            _updateCharCount() {
                this.charCount = this._quill ?
                    this._quill.getText().replace(/\n$/, '').length :
                    0;
            },
        };
    }
</script>

<style>
    /* Quill Snow resets — strip the library's own chrome */
    .ql-toolbar.ql-snow,
    .ql-container.ql-snow {
        border: none !important;
        font-family: inherit !important;
    }

    /* Toolbar icon colours */
    .ql-snow .ql-stroke {
        stroke: #71717a !important;
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

    /* Active / hover icon state */
    .ql-snow.ql-toolbar button:hover .ql-stroke,
    .ql-snow.ql-toolbar button.ql-active .ql-stroke {
        stroke: #18181b !important;
    }

    .dark .ql-snow.ql-toolbar button:hover .ql-stroke,
    .dark .ql-snow.ql-toolbar button.ql-active .ql-stroke {
        stroke: #f4f4f5 !important;
    }

    .ql-snow.ql-toolbar button:hover .ql-fill,
    .ql-snow.ql-toolbar button.ql-active .ql-fill {
        fill: #18181b !important;
    }

    .dark .ql-snow.ql-toolbar button:hover .ql-fill,
    .dark .ql-snow.ql-toolbar button.ql-active .ql-fill {
        fill: #f4f4f5 !important;
    }

    /* Toolbar button pill */
    .ql-snow.ql-toolbar button {
        border-radius: 0.375rem !important;
        padding: 3px 5px !important;
        transition: background-color 0.1s ease !important;
    }

    .ql-snow.ql-toolbar button:hover {
        background-color: rgb(244 244 245 / 1) !important;
    }

    .dark .ql-snow.ql-toolbar button:hover {
        background-color: rgb(63 63 70 / 0.5) !important;
    }

    .ql-snow.ql-toolbar button.ql-active {
        background-color: rgb(228 228 231 / 1) !important;
    }

    .dark .ql-snow.ql-toolbar button.ql-active {
        background-color: rgb(63 63 70 / 0.8) !important;
    }

    /* Heading select */
    .ql-snow .ql-picker.ql-header {
        width: 7.5rem !important;
    }

    .ql-snow .ql-picker-label,
    .ql-snow .ql-picker-options {
        background: white !important;
        border-color: #e4e4e7 !important;
        border-radius: 0.375rem !important;
        color: #3f3f46 !important;
        font-size: 0.8125rem !important;
    }

    .dark .ql-snow .ql-picker-label,
    .dark .ql-snow .ql-picker-options {
        background: #27272a !important;
        border-color: #3f3f46 !important;
        color: #d4d4d8 !important;
    }

    .ql-snow .ql-picker-label .ql-stroke {
        stroke: #71717a !important;
    }

    .dark .ql-snow .ql-picker-label .ql-stroke {
        stroke: #a1a1aa !important;
    }

    .ql-snow .ql-picker-item:hover {
        color: #18181b !important;
        background: #f4f4f5 !important;
    }

    .dark .ql-snow .ql-picker-item:hover {
        color: #f4f4f5 !important;
        background: #3f3f46 !important;
    }

    /* Editor content area */
    .ql-editor {
        padding: 0.75rem 0.875rem !important;
        font-size: 0.875rem !important;
        line-height: 1.6 !important;
        color: inherit !important;
        caret-color: #18181b;
    }

    .dark .ql-editor {
        caret-color: #f4f4f5;
    }

    /* Placeholder */
    .ql-editor.ql-blank::before {
        color: #a1a1aa !important;
        font-style: normal !important;
        left: 0.875rem !important;
        right: 0.875rem !important;
    }

    .dark .ql-editor.ql-blank::before {
        color: #71717a !important;
    }

    /* Spacing — key fix for extra space */
    .ql-editor p {
        margin: 0 !important;
        padding: 0 !important;
    }

    .ql-editor h1,
    .ql-editor h2,
    .ql-editor h3,
    .ql-editor blockquote,
    .ql-editor pre.ql-syntax,
    .ql-editor ol,
    .ql-editor ul {
        margin-top: 0.75rem !important;
        margin-bottom: 0.75rem !important;
    }

    .ql-editor>*:first-child {
        margin-top: 0 !important;
    }

    .ql-editor>*:last-child {
        margin-bottom: 0 !important;
    }

    /* Headings */
    .ql-editor h1 {
        font-size: 1.375rem !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        letter-spacing: -0.01em;
    }

    .ql-editor h2 {
        font-size: 1.125rem !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
    }

    .ql-editor h3 {
        font-size: 1rem !important;
        font-weight: 600 !important;
        line-height: 1.4 !important;
    }

    /* Blockquote */
    .ql-editor blockquote {
        border-left: 3px solid #d4d4d8 !important;
        padding-left: 0.875rem !important;
        color: #71717a !important;
        font-style: italic;
    }

    .dark .ql-editor blockquote {
        border-left-color: #52525b !important;
        color: #a1a1aa !important;
    }

    /* Code */
    .ql-editor code,
    .ql-editor pre.ql-syntax {
        background: #f4f4f5 !important;
        border-radius: 0.375rem !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace !important;
        font-size: 0.8125rem !important;
    }

    .dark .ql-editor code,
    .dark .ql-editor pre.ql-syntax {
        background: #3f3f46 !important;
        color: #d4d4d8 !important;
    }

    .ql-editor pre.ql-syntax {
        padding: 0.625rem 0.875rem !important;
        overflow-x: auto !important;
    }

    /* Lists */
    .ql-editor ol,
    .ql-editor ul {
        padding-left: 1.5rem !important;
    }

    .ql-editor li {
        margin: 0.15rem 0 !important;
        padding-left: 0.25rem !important;
    }

    .ql-editor ol {
        counter-reset: list-0;
        list-style: none !important;
    }

    .ql-editor ol li[data-list="ordered"] {
        counter-increment: list-0;
        position: relative;
    }

    .ql-editor ol li[data-list="ordered"]::before {
        content: counter(list-0) ".";
        position: absolute;
        left: -1.5rem;
        width: 1.25rem;
        text-align: right;
        color: #71717a;
        font-variant-numeric: tabular-nums;
    }

    .ql-editor ul li[data-list="bullet"] {
        list-style-type: disc !important;
    }

    .ql-editor ul li[data-list="bullet"]::before {
        content: none !important;
    }

    /* Links */
    .ql-editor a {
        color: #2563eb !important;
        text-decoration: underline !important;
        text-underline-offset: 2px;
    }

    .dark .ql-editor a {
        color: #60a5fa !important;
    }

    /* Floating link tooltip */
    .ql-snow .ql-tooltip {
        left: 12px !important;
        top: 10px !important;
        z-index: 50;
        border-radius: 0.5rem !important;
        border: 1px solid #e4e4e7 !important;
        background: #ffffff !important;
        box-shadow: 0 4px 16px rgb(0 0 0 / 0.08) !important;
        color: #3f3f46 !important;
        font-size: 0.8125rem !important;
        padding: 6px 10px !important;
    }

    .dark .ql-snow .ql-tooltip {
        border-color: #3f3f46 !important;
        background: #18181b !important;
        color: #d4d4d8 !important;
        box-shadow: 0 4px 16px rgb(0 0 0 / 0.4) !important;
    }

    .ql-snow .ql-tooltip input[type="text"] {
        border: 1px solid #d4d4d8 !important;
        border-radius: 0.375rem !important;
        padding: 2px 8px !important;
        font-size: 0.8125rem !important;
        outline: none !important;
        background: #f4f4f5 !important;
        color: #18181b !important;
    }

    .dark .ql-snow .ql-tooltip input[type="text"] {
        background: #27272a !important;
        border-color: #52525b !important;
        color: #f4f4f5 !important;
    }

    .ql-snow .ql-tooltip input[type="text"]:focus {
        border-color: #a1a1aa !important;
        box-shadow: 0 0 0 2px rgb(161 161 170 / 0.15) !important;
    }

    .ql-snow .ql-tooltip a.ql-action::after {
        content: 'Apply' !important;
        color: #2563eb !important;
        font-weight: 600 !important;
    }

    .dark .ql-snow .ql-tooltip a.ql-action::after {
        color: #60a5fa !important;
    }

    .ql-snow .ql-tooltip a.ql-remove::after {
        content: 'Remove' !important;
        color: #ef4444 !important;
    }

    /* Thin scrollbar */
    .ql-editor::-webkit-scrollbar {
        width: 4px;
    }

    .ql-editor::-webkit-scrollbar-track {
        background: transparent;
    }

    .ql-editor::-webkit-scrollbar-thumb {
        background: #d4d4d8;
        border-radius: 9999px;
    }

    .dark .ql-editor::-webkit-scrollbar-thumb {
        background: #52525b;
    }
</style>
<?php /**PATH /var/www/html/totthobox/resources/views/flux/editor/index.blade.php ENDPATH**/ ?>