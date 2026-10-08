<?php

use App\Models\ChatSession;
use App\Services\AiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Flux\Flux;

?>



<?php if (isset($component)) { $__componentOriginal42da61123f891e63201d7be28f403427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42da61123f891e63201d7be28f403427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo','data' => ['title' => 'Totthobox AI: আপনার বুদ্ধিমত্তাসম্পন্ন এআই অ্যাসিস্ট্যান্ট','description' => 'Totthobox AI-এর মাধ্যমে যেকোনো তথ্যের তাৎক্ষণিক সমাধান, ডেটা অ্যানালাইসিস এবং কাস্টমাইজড লার্নিং অভিজ্ঞতা উপভোগ করুন।','keywords' => 'টেলিগ্রাম এআই, Totthobox AI, কৃত্রিম বুদ্ধিমত্তা, এআই টিউটর, AI Agent Bangladesh, স্মার্ট অ্যাসিস্ট্যান্ট, বাংলা এআই']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Totthobox AI: আপনার বুদ্ধিমত্তাসম্পন্ন এআই অ্যাসিস্ট্যান্ট','description' => 'Totthobox AI-এর মাধ্যমে যেকোনো তথ্যের তাৎক্ষণিক সমাধান, ডেটা অ্যানালাইসিস এবং কাস্টমাইজড লার্নিং অভিজ্ঞতা উপভোগ করুন।','keywords' => 'টেলিগ্রাম এআই, Totthobox AI, কৃত্রিম বুদ্ধিমত্তা, এআই টিউটর, AI Agent Bangladesh, স্মার্ট অ্যাসিস্ট্যান্ট, বাংলা এআই']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $attributes = $__attributesOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__attributesOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $component = $__componentOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__componentOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('c403e573-a99b-47ae-8718-6a9309178957')): $__env->markAsRenderedOnce('c403e573-a99b-47ae-8718-6a9309178957'); ?>
    <?php $__env->startPush('styles'); ?>
        <style>
            @keyframes blink {

                0%,
                100% {
                    opacity: 1
                }

                50% {
                    opacity: 0
                }
            }

            .ai-cursor::after {
                content: '▋';
                display: inline-block;
                font-size: .75em;
                vertical-align: middle;
                margin-left: 2px;
                animation: blink .7s step-end infinite;
                color: currentColor;
                opacity: .7;
            }

            @keyframes aiBlockIn {
                from {
                    opacity: 0;
                    transform: translateY(4px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes aiPopIn {
                from {
                    opacity: 0;
                    transform: translateY(6px) scale(.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .ai-response-new>* {
                opacity: 0;
                animation: aiBlockIn .3s ease forwards;
            }

            .ai-response-new>*:nth-child(1) {
                animation-delay: 0ms;
            }

            .ai-response-new>*:nth-child(2) {
                animation-delay: 60ms;
            }

            .ai-response-new>*:nth-child(3) {
                animation-delay: 120ms;
            }

            .ai-response-new>*:nth-child(4) {
                animation-delay: 180ms;
            }

            .ai-response-new>*:nth-child(n+5) {
                animation-delay: 240ms;
            }

            .ai-msg-pop {
                animation: aiPopIn .25s cubic-bezier(.16, 1, .3, 1) both;
            }

            .ai-prose .code-block-wrapper {
                margin: .75rem 0;
                border-radius: .625rem;
                overflow: hidden;
                border: 1px solid rgba(0, 0, 0, .08);
                box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            }

            .dark .ai-prose .code-block-wrapper {
                border-color: rgba(255, 255, 255, .08);
            }

            .ai-prose .code-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .35rem .8rem;
                background: #1e2030;
                border-bottom: 1px solid rgba(255, 255, 255, .06);
                user-select: none;
            }

            .ai-prose .code-lang {
                font-size: .68rem;
                font-family: ui-monospace, monospace;
                text-transform: uppercase;
                letter-spacing: .06em;
                color: rgba(255, 255, 255, .4);
            }

            .ai-prose .code-copy-btn {
                display: flex;
                align-items: center;
                gap: .3rem;
                font-size: .7rem;
                color: rgba(255, 255, 255, .4);
                background: transparent;
                border: none;
                cursor: pointer;
                padding: .2rem .45rem;
                border-radius: .3rem;
                transition: color .15s, background .15s;
                line-height: 1;
            }

            .ai-prose .code-copy-btn:hover {
                color: rgba(255, 255, 255, .85);
                background: rgba(255, 255, 255, .08);
            }

            .ai-prose .code-copy-btn.copied {
                color: #4ade80;
            }

            .ai-prose pre {
                margin: 0 !important;
                border-radius: 0 !important;
            }

            .ai-prose pre code.hljs {
                border-radius: 0 !important;
                padding: .9rem 1.1rem !important;
                font-size: .79rem !important;
                line-height: 1.65 !important;
            }

            .ai-prose :not(pre)>code {
                background: rgba(0, 0, 0, .06);
                border: 1px solid rgba(0, 0, 0, .1);
                border-radius: .3rem;
                padding: .1em .38em;
                font-size: .84em;
                font-family: ui-monospace, 'Cascadia Code', monospace;
            }

            .dark .ai-prose :not(pre)>code {
                background: rgba(255, 255, 255, .08);
                border-color: rgba(255, 255, 255, .12);
            }

            .ai-prose a {
                color: var(--color-accent, #3b82f6);
                text-decoration: underline;
                text-decoration-color: color-mix(in srgb, var(--color-accent, #3b82f6) 35%, transparent);
                text-underline-offset: 2px;
                transition: text-decoration-color .15s;
                word-break: break-word;
            }

            .ai-prose a:hover {
                text-decoration-color: var(--color-accent, #3b82f6);
            }

            .ai-prose table {
                width: 100%;
                border-collapse: collapse;
                font-size: .8rem;
                margin: .75rem 0;
            }

            .ai-prose thead tr {
                background: rgba(0, 0, 0, .04);
                border-bottom: 2px solid rgba(0, 0, 0, .1);
            }

            .dark .ai-prose thead tr {
                background: rgba(255, 255, 255, .05);
                border-bottom-color: rgba(255, 255, 255, .1);
            }

            .ai-prose th,
            .ai-prose td {
                padding: .42rem .7rem;
                text-align: left;
                border-bottom: 1px solid rgba(0, 0, 0, .06);
            }

            .dark .ai-prose th,
            .dark .ai-prose td {
                border-bottom-color: rgba(255, 255, 255, .06);
            }

            .ai-prose tbody tr:last-child td {
                border-bottom: none;
            }

            .ai-prose img {
                border-radius: .625rem;
                max-width: 100%;
                height: auto;
                margin: .5rem 0;
                border: 1px solid rgba(0, 0, 0, .08);
            }

            .dark .ai-prose img {
                border-color: rgba(255, 255, 255, .08);
            }

            .ai-prose blockquote {
                border-left: 3px solid var(--color-accent, #3b82f6);
                padding-left: .75rem;
                margin-left: 0;
                font-style: normal;
                opacity: .85;
            }

            .ai-prose hr {
                border-color: rgba(0, 0, 0, .1);
                margin: 1rem 0;
            }

            .dark .ai-prose hr {
                border-color: rgba(255, 255, 255, .1);
            }

            .totthobox-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .totthobox-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(0, 0, 0, .12);
                border-radius: 999px;
            }

            .dark .totthobox-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, .12);
            }
        </style>
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('scripts'); ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css"
            media="(prefers-color-scheme: light)">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark-dimmed.min.css"
            media="(prefers-color-scheme: dark)">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

        <script>
            document.addEventListener('alpine:init', () => {

                // ── Root: drag & drop / paste-to-upload for the whole chat panel ──
                Alpine.data('totthoboxChatRoot', () => ({
                    isDragging: false,
                    showScrollBtn: false,

                    handleDrop(e) {
                        e.preventDefault();
                        this.isDragging = false;
                        const file = e.dataTransfer?.files?.[0];
                        if (file && file.type.startsWith('image/')) this.injectFile(file);
                    },

                    handlePaste(e) {
                        const items = e.clipboardData?.items;
                        if (!items) return;
                        for (const item of items) {
                            if (item.type.startsWith('image/')) {
                                e.preventDefault();
                                const file = item.getAsFile();
                                if (file) this.injectFile(file);
                                break;
                            }
                        }
                    },

                    injectFile(file) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        const input = this.$el.querySelector('input[type=file]');
                        if (input) {
                            input.files = dt.files;
                            input.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }
                    },
                }));

                // ── Each AI answer: reveals the (already server-rendered) HTML
                //    with a typewriter effect, then wires up copy buttons on code blocks.
                //    The element this binds to carries wire:ignore, so Livewire never
                //    rewrites its innerHTML again after mount — that's the actual fix
                //    for answers "disappearing" until reload. ──
                Alpine.data('totthoboxAiAnswer', (shouldType) => ({
                    typing: false,

                    init() {
                        if (!shouldType) {
                            this.postProcess();
                            return;
                        }
                        this.$nextTick(() => this.startTyping());
                    },

                    startTyping() {
                        this.typing = true;

                        const root = this.$el;
                        const textNodes = [];
                        const originals = [];

                        const walk = (node) => {
                            if (node.nodeType === 3) {
                                if (node.textContent.length > 0) {
                                    textNodes.push(node);
                                    originals.push(node.textContent);
                                    node.textContent = '';
                                }
                            } else {
                                node.childNodes.forEach(walk);
                            }
                        };
                        walk(root);

                        let nIdx = 0,
                            cIdx = 0;
                        const MS = 16;
                        const CHUNK = 2;
                        const FAST = 6;

                        const tick = () => {
                            if (nIdx >= textNodes.length) {
                                this.typing = false;
                                textNodes.forEach((n, i) => n.textContent = originals[i]);
                                this.postProcess();
                                return;
                            }

                            const node = textNodes[nIdx];
                            const full = originals[nIdx];
                            const inCode = node.parentElement?.closest('pre,code') !== null;
                            const chunk = inCode ? FAST : CHUNK;

                            cIdx = Math.min(cIdx + chunk, full.length);
                            node.textContent = full.slice(0, cIdx);
                            window.dispatchEvent(new CustomEvent('scroll-bottom'));

                            if (cIdx >= full.length) {
                                nIdx++;
                                cIdx = 0;
                            }
                            setTimeout(tick, MS);
                        };

                        setTimeout(tick, MS);
                    },

                    postProcess() {
                        if (typeof hljs !== 'undefined') {
                            this.$el.querySelectorAll('pre code:not([data-highlighted])').forEach(b => {
                                hljs.highlightElement(b);
                            });
                        }
                        this.wrapCodeBlocks();
                        window.dispatchEvent(new CustomEvent('scroll-bottom'));
                    },

                    wrapCodeBlocks() {
                        this.$el.querySelectorAll('pre:not(.code-wrapped)').forEach(pre => {
                            pre.classList.add('code-wrapped');
                            const code = pre.querySelector('code');
                            if (!code) return;

                            const langClass = [...code.classList].find(c => c.startsWith(
                                'language-'));
                            const lang = langClass ? langClass.replace('language-', '') : 'code';

                            const wrapper = document.createElement('div');
                            wrapper.className = 'code-block-wrapper not-prose';

                            const toolbar = document.createElement('div');
                            toolbar.className = 'code-toolbar';
                            toolbar.innerHTML =
                                `
                                                                                                                                                                                                                                                                                                                                                                                <span class="code-lang">${lang}</span>
                                                                                                                                                                                                                                                                                                                                                                                <button class="code-copy-btn" title="কোড কপি করুন">
                                                                                                                                                                                                                                                                                                                                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                                                                                                                                                                                                                                                                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/>
                                                                                                                                                                                                                                                                                                                                                                                    </svg>
                                                                                                                                                                                                                                                                                                                                                                                    <span class="copy-label">কপি</span>
                                                                                                                                                                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                                                                                                                                                            `;

                            const btn = toolbar.querySelector('.code-copy-btn');
                            const label = toolbar.querySelector('.copy-label');
                            btn.addEventListener('click', () => {
                                navigator.clipboard.writeText(code.innerText ?? code
                                    .textContent).then(() => {
                                    btn.classList.add('copied');
                                    label.textContent = 'কপি হয়েছে ✓';
                                    setTimeout(() => {
                                        btn.classList.remove('copied');
                                        label.textContent = 'কপি';
                                    }, 2000);
                                });
                            });

                            pre.parentNode.insertBefore(wrapper, pre);
                            wrapper.appendChild(toolbar);
                            wrapper.appendChild(pre);
                        });
                    },
                }));
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>

<div x-data="totthoboxChatRoot()" @dragover.window.prevent="isDragging = true" @dragleave.window="isDragging = false"
    @drop.window.prevent="handleDrop($event)" @paste.window="handlePaste($event)"
    class="max-w-2xl mx-auto lg:h-[91vh] h-[89vh] relative flex flex-col">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isGuest): ?>
        <div class="hidden lg:flex items-center justify-between h-8 mb-2 shrink-0 px-1">
            <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['class' => ' truncate flex items-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => ' truncate flex items-center']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => 'sparkles','class' => 'inline-block me-1.5 opacity-60 size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'sparkles','class' => 'inline-block me-1.5 opacity-60 size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $attributes = $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $component = $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
                তথ্যবক্স এআই
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
            <div class="flex items-center gap-4">
                <span class="text-[11px] text-zinc-400"><?php echo e($guestUsageRemaining); ?> বার বাকি</span>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'login','size' => 'xs','variant' => 'primary','wire:navigate' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'login','size' => 'xs','variant' => 'primary','wire:navigate' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    লগইন করুন
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
    <?php else: ?>
        <div class="hidden lg:flex items-center h-8 mb-2 shrink-0">
            <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['class' => ' truncate flex items-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => ' truncate flex items-center']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => 'chevron-right','class' => 'inline-block me-1.5 opacity-60 size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'chevron-right','class' => 'inline-block me-1.5 opacity-60 size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $attributes = $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $component = $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
                <?php echo e($session?->title ?? 'নতুন চ্যাট'); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-on:scroll-bottom.window="$nextTick(() => {
            $refs.chat.scrollTo({ top: $refs.chat.scrollHeight, behavior: 'smooth' });
            showScrollBtn = false;
        })"
        x-ref="chat"
        @scroll="showScrollBtn = ($refs.chat.scrollHeight - $refs.chat.scrollTop - $refs.chat.clientHeight) > 120"
        class="flex-1 overflow-y-auto min-h-0 space-y-1 scroll-smooth totthobox-scrollbar" id="chat-container">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($messages) === 0 && !$isTyping): ?>
            <div class="flex flex-col items-center justify-center h-full gap-4 opacity-50 select-none">
                <div class="p-3 rounded-2xl bg-accent/10">
                    <?php if (isset($component)) { $__componentOriginalcf196058b51a9cb5c102083fc6b9bc99 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf196058b51a9cb5c102083fc6b9bc99 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.sparkles','data' => ['class' => 'size-8 ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.sparkles'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-8 ']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf196058b51a9cb5c102083fc6b9bc99)): ?>
<?php $attributes = $__attributesOriginalcf196058b51a9cb5c102083fc6b9bc99; ?>
<?php unset($__attributesOriginalcf196058b51a9cb5c102083fc6b9bc99); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf196058b51a9cb5c102083fc6b9bc99)): ?>
<?php $component = $__componentOriginalcf196058b51a9cb5c102083fc6b9bc99; ?>
<?php unset($__componentOriginalcf196058b51a9cb5c102083fc6b9bc99); ?>
<?php endif; ?>
                </div>
                <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
আমি আপনাকে কিভাবে সাহায্য করতে পারি? <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
                <p class="text-xs text-zinc-400">ছবি paste করুন বা drag করে আনুন</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isGuest): ?>
                    <p class="text-xs text-zinc-400">লগইন ছাড়াই <?php echo e($guestUsageRemaining); ?> বার জিজ্ঞেস করা যাবে</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $isUser = $msg['role'] === 'user';
                $shouldType = !$isUser && (string) $msg['id'] === (string) $newMessageId;
            ?>

            <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'msg-'.e($msg['id']).''; ?>wire:key="msg-<?php echo e($msg['id']); ?>" x-data="{ editing: false, copied: false, collapsed: <?php echo e($isUser ? 'true' : 'false'); ?>, newContent: <?php echo \Illuminate\Support\Js::from($msg['content'])->toHtml() ?>, get isLong() { return this.newContent.length > 280 } }"
                class="flex <?php echo e($isUser ? 'justify-end' : 'justify-start'); ?> group ai-msg-pop">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isUser): ?>
                    <div class="flex items-start pt-3 pr-2 shrink-0">
                        <div class="p-1 rounded-lg bg-accent/10">
                            <?php if (isset($component)) { $__componentOriginalcde46ad147e4d63a74354a0e8c832877 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcde46ad147e4d63a74354a0e8c832877 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.brand','data' => ['class' => 'size-3.5 ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-3.5 ']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcde46ad147e4d63a74354a0e8c832877)): ?>
<?php $attributes = $__attributesOriginalcde46ad147e4d63a74354a0e8c832877; ?>
<?php unset($__attributesOriginalcde46ad147e4d63a74354a0e8c832877); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcde46ad147e4d63a74354a0e8c832877)): ?>
<?php $component = $__componentOriginalcde46ad147e4d63a74354a0e8c832877; ?>
<?php unset($__componentOriginalcde46ad147e4d63a74354a0e8c832877); ?>
<?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="max-w-[90%] md:max-w-[78%] lg:max-w-[70%] relative">

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isUser && !$isGuest): ?>
                        <button @click="editing = true"
                            class="absolute -left-8 top-3 p-1 text-zinc-400 
                                                                                                                                                                                                                                                                                                                                                                                   opacity-0 group-hover:opacity-100 transition-all rounded-md
                                                                                                                                                                                                                                                                                                                                                                                   hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            title="সম্পাদনা">
                            <?php if (isset($component)) { $__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.pencil-square','data' => ['variant' => 'micro']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.pencil-square'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6)): ?>
<?php $attributes = $__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6; ?>
<?php unset($__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6)): ?>
<?php $component = $__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6; ?>
<?php unset($__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6); ?>
<?php endif; ?>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div
                        class="mb-3 <?php echo e($isUser ? 'bg-zinc-100 dark:bg-zinc-800 px-4 py-2.5 rounded-2xl rounded-tr-md shadow-sm' : 'py-1'); ?>">

                        
                        <div x-show="!editing">

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($msg['image_path'])): ?>
                                <div class="mb-2">
                                    <?php if (isset($component)) { $__componentOriginal3be3b786a3491a3a45d5180880ad0316 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3be3b786a3491a3a45d5180880ad0316 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::media','data' => ['media' => ''.e($msg['image_path']).'','alt' => 'uploaded']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::media'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['media' => ''.e($msg['image_path']).'','alt' => 'uploaded']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3be3b786a3491a3a45d5180880ad0316)): ?>
<?php $attributes = $__attributesOriginal3be3b786a3491a3a45d5180880ad0316; ?>
<?php unset($__attributesOriginal3be3b786a3491a3a45d5180880ad0316); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3be3b786a3491a3a45d5180880ad0316)): ?>
<?php $component = $__componentOriginal3be3b786a3491a3a45d5180880ad0316; ?>
<?php unset($__componentOriginal3be3b786a3491a3a45d5180880ad0316); ?>
<?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isUser): ?>
                                
                                <div class="relative">
                                    <div x-show="collapsed && isLong" class="flex items-center gap-2">
                                        <p class="truncate text-sm leading-relaxed max-w-[calc(100%-1.75rem)]">
                                            <?php echo e($msg['content']); ?>

                                        </p>
                                        <button @click="collapsed = false"
                                            class="shrink-0 p-0.5 rounded-full text-zinc-400
                                                                                                                                                                                                                                                                                                                                                                                                    dark:hover:text-zinc-300
                                                                                                                                                                                                                                                                                                                                                                                                   hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-all duration-200"
                                            title="বিস্তারিত দেখুন">
                                            <?php if (isset($component)) { $__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chevron-down','data' => ['variant' => 'micro','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-3.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0)): ?>
<?php $attributes = $__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0; ?>
<?php unset($__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0)): ?>
<?php $component = $__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0; ?>
<?php unset($__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0); ?>
<?php endif; ?>
                                        </button>
                                    </div>
                                    <div x-show="!collapsed || !isLong"
                                        class="prose prose-sm dark:prose-invert max-w-none">
                                        <div x-show="isLong" class="flex justify-end mb-2">
                                            <button @click="collapsed = true"
                                                class="p-0.5 rounded-full text-zinc-400
                                                                                                                                                                                                                                                                                                                                                                                                        dark:hover:text-zinc-300
                                                                                                                                                                                                                                                                                                                                                                                                       hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-all duration-200"
                                                title="সংক্ষিপ্ত করুন">
                                                <?php if (isset($component)) { $__componentOriginal6b14ccea37ceba802c7692663ec127c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b14ccea37ceba802c7692663ec127c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chevron-up','data' => ['variant' => 'micro','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-3.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b14ccea37ceba802c7692663ec127c4)): ?>
<?php $attributes = $__attributesOriginal6b14ccea37ceba802c7692663ec127c4; ?>
<?php unset($__attributesOriginal6b14ccea37ceba802c7692663ec127c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b14ccea37ceba802c7692663ec127c4)): ?>
<?php $component = $__componentOriginal6b14ccea37ceba802c7692663ec127c4; ?>
<?php unset($__componentOriginal6b14ccea37ceba802c7692663ec127c4); ?>
<?php endif; ?>
                                            </button>
                                        </div>
                                        <?php echo str($msg['content'])->markdown(); ?>

                                    </div>
                                </div>
                            <?php else: ?>
                                
                                <div wire:ignore x-data="totthoboxAiAnswer(<?php echo \Illuminate\Support\Js::from($shouldType)->toHtml() ?>)"
                                    :class="typing ? 'ai-prose prose prose-sm dark:prose-invert max-w-none ai-cursor' :
                                        'ai-prose prose prose-sm dark:prose-invert max-w-none'">
                                    <?php echo str($msg['content'])->markdown(); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isUser): ?>
                                <div
                                    class="flex items-center gap-1 pt-2 mt-1
                                                                                                                                                                                                                                                                                                                                                                                            border-t border-zinc-100 dark:border-zinc-700/50
                                                                                                                                                                                                                                                                                                                                                                                            opacity-0 group-hover:opacity-100 ">
                                    <button
                                        @click="navigator.clipboard.writeText(<?php echo \Illuminate\Support\Js::from($msg['content'])->toHtml() ?>); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="flex items-center gap-2 px-2 py-1 text-xs text-zinc-400
                                                                                                                                                                                                                                                                                                                                                                                               hover:text-zinc-700 dark:hover:text-zinc-200
                                                                                                                                                                                                                                                                                                                                                                                               hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-lg transition-all">
                                        <template x-if="!copied">
                                            <?php if (isset($component)) { $__componentOriginald3a04877b88f14dba17702f724393f4a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3a04877b88f14dba17702f724393f4a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.clipboard','data' => ['variant' => 'micro','class' => 'size-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.clipboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3a04877b88f14dba17702f724393f4a)): ?>
<?php $attributes = $__attributesOriginald3a04877b88f14dba17702f724393f4a; ?>
<?php unset($__attributesOriginald3a04877b88f14dba17702f724393f4a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3a04877b88f14dba17702f724393f4a)): ?>
<?php $component = $__componentOriginald3a04877b88f14dba17702f724393f4a; ?>
<?php unset($__componentOriginald3a04877b88f14dba17702f724393f4a); ?>
<?php endif; ?>
                                        </template>
                                        <template x-if="copied">
                                            <?php if (isset($component)) { $__componentOriginal9c2dfd6cb98f4df18e26d1694500af11 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.check','data' => ['variant' => 'micro','class' => 'size-3 text-green-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-3 text-green-500']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11)): ?>
<?php $attributes = $__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11; ?>
<?php unset($__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c2dfd6cb98f4df18e26d1694500af11)): ?>
<?php $component = $__componentOriginal9c2dfd6cb98f4df18e26d1694500af11; ?>
<?php unset($__componentOriginal9c2dfd6cb98f4df18e26d1694500af11); ?>
<?php endif; ?>
                                        </template>
                                        <span x-text="copied ? 'কপি হয়েছে' : 'কপি'"></span>
                                    </button>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loop->last): ?>
                                        <button wire:click="regenerateLast" wire:loading.attr="disabled"
                                            wire:target="regenerateLast,ask"
                                            class="flex items-center gap-2 px-2 py-1 text-xs text-zinc-400
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           hover:text-zinc-700 dark:hover:text-zinc-200
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-lg transition-all
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           disabled:opacity-50 disabled:cursor-not-allowed">
                                            <?php if (isset($component)) { $__componentOriginal18ce857dfc449fdd246010f7208cb6d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18ce857dfc449fdd246010f7208cb6d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.arrow-path','data' => ['variant' => 'micro','class' => 'size-3','wire:loading.class' => 'animate-spin','wire:target' => 'regenerateLast']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-3','wire:loading.class' => 'animate-spin','wire:target' => 'regenerateLast']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18ce857dfc449fdd246010f7208cb6d5)): ?>
<?php $attributes = $__attributesOriginal18ce857dfc449fdd246010f7208cb6d5; ?>
<?php unset($__attributesOriginal18ce857dfc449fdd246010f7208cb6d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18ce857dfc449fdd246010f7208cb6d5)): ?>
<?php $component = $__componentOriginal18ce857dfc449fdd246010f7208cb6d5; ?>
<?php unset($__componentOriginal18ce857dfc449fdd246010f7208cb6d5); ?>
<?php endif; ?>
                                            পুনরায় তৈরি
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isGuest): ?>
                            <div x-show="editing" x-cloak class="flex flex-col gap-2 min-w-[200px]">
                                <?php if (isset($component)) { $__componentOriginal0ee30026125d1a66523211147b00e4dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ee30026125d1a66523211147b00e4dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::textarea','data' => ['xModel' => 'newContent','rows' => 'auto','resize' => 'none','class' => 'text-sm','@keydown.escape' => 'editing = false','@keydown.ctrl.enter.prevent' => '$wire.editAndRegenerate('.e($msg['id']).', newContent); editing = false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'newContent','rows' => 'auto','resize' => 'none','class' => 'text-sm','@keydown.escape' => 'editing = false','@keydown.ctrl.enter.prevent' => '$wire.editAndRegenerate('.e($msg['id']).', newContent); editing = false']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $attributes = $__attributesOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__attributesOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $component = $__componentOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__componentOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
                                <div class="flex justify-between items-center">
                                    <p class="text-xs text-zinc-400">Ctrl+Enter এ পাঠান</p>
                                    <div class="flex gap-4">
                                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['size' => 'xs','variant' => 'subtle','@click' => 'editing = false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xs','variant' => 'subtle','@click' => 'editing = false']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
বাতিল
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['size' => 'xs','variant' => 'primary','@click' => '$wire.editAndRegenerate('.e($msg['id']).', newContent); editing = false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xs','variant' => 'primary','@click' => '$wire.editAndRegenerate('.e($msg['id']).', newContent); editing = false']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                            আপডেট ও পাঠান
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
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

        
        <div wire:loading wire:target="ask,regenerateLast,editAndRegenerate" class="flex justify-start py-2 ps-2">
            <div
                class="flex items-center gap-4 px-4 py-3 bg-white dark:bg-zinc-800
                        border border-zinc-100 dark:border-zinc-700/50 rounded-2xl shadow-sm">
                <div class="relative flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-accent/20 animate-ping"></div>
                    <div class="relative bg-accent/10 p-1.5 rounded-lg animate-bounce">
                        <?php if (isset($component)) { $__componentOriginalcde46ad147e4d63a74354a0e8c832877 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcde46ad147e4d63a74354a0e8c832877 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.brand','data' => ['class' => 'size-5 ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5 ']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcde46ad147e4d63a74354a0e8c832877)): ?>
<?php $attributes = $__attributesOriginalcde46ad147e4d63a74354a0e8c832877; ?>
<?php unset($__attributesOriginalcde46ad147e4d63a74354a0e8c832877); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcde46ad147e4d63a74354a0e8c832877)): ?>
<?php $component = $__componentOriginalcde46ad147e4d63a74354a0e8c832877; ?>
<?php unset($__componentOriginalcde46ad147e4d63a74354a0e8c832877); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">তথ্যবক্স এআই ভাবছে...</span>
                    <div class="flex gap-1 mt-0.5">
                        <span class="w-1 h-1 bg-accent/40 rounded-full animate-pulse"></span>
                        <span class="w-1 h-1 bg-accent/40 rounded-full animate-pulse [animation-delay:200ms]"></span>
                        <span class="w-1 h-1 bg-accent/40 rounded-full animate-pulse [animation-delay:400ms]"></span>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasError): ?>
            <div class="flex justify-start ps-1 pb-2">
                <div
                    class="flex items-center gap-2 text-sm text-red-500 dark:text-red-400
                                                                                                                                                                                                    bg-red-50 dark:bg-red-950/30 px-3 py-2 rounded-xl">
                    <?php if (isset($component)) { $__componentOriginal7f0e8d69add49581695c1337b3f85fff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7f0e8d69add49581695c1337b3f85fff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.exclamation-triangle','data' => ['variant' => 'micro','class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.exclamation-triangle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'shrink-0']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7f0e8d69add49581695c1337b3f85fff)): ?>
<?php $attributes = $__attributesOriginal7f0e8d69add49581695c1337b3f85fff; ?>
<?php unset($__attributesOriginal7f0e8d69add49581695c1337b3f85fff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7f0e8d69add49581695c1337b3f85fff)): ?>
<?php $component = $__componentOriginal7f0e8d69add49581695c1337b3f85fff; ?>
<?php unset($__componentOriginal7f0e8d69add49581695c1337b3f85fff); ?>
<?php endif; ?>
                    <span><?php echo e($errorText); ?></span>
                    <button wire:click="regenerateLast"
                        class="underline underline-offset-2 ml-1 font-medium hover:text-red-600 transition-colors">
                        আবার চেষ্টা
                    </button>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="showScrollBtn" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" class="absolute bottom-30 left-1/2 -translate-x-1/2 z-30">
        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['icon' => 'arrow-down','@click' => '$refs.chat.scrollTo({ top: $refs.chat.scrollHeight, behavior: \'smooth\' }); showScrollBtn = false','variant' => 'subtle','size' => 'xs','class' => 'shadow-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'arrow-down','@click' => '$refs.chat.scrollTo({ top: $refs.chat.scrollHeight, behavior: \'smooth\' }); showScrollBtn = false','variant' => 'subtle','size' => 'xs','class' => 'shadow-lg']); ?>
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

    
    <div class="shrink-0 pt-2">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isGuest): ?>
            <div class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="flex justify-between items-center mb-2 text-xs font-medium text-zinc-500">
                    <span>ফ্রি লিমিট বাকি</span>
                    <span><?php echo e($guestUsageRemaining); ?> / 20</span>
                </div>
                <div class="w-full bg-zinc-200 dark:bg-zinc-700 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-accent h-full transition-all duration-200"
                        style="width: <?php echo e(($guestUsageRemaining / 20) * 100); ?>%"></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($guestUsageRemaining <= 0): ?>
                    <?php if (isset($component)) { $__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::subheading','data' => ['class' => 'mt-2 text-red-500 text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-2 text-red-500 text-xs']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        আপনার লিমিট শেষ। আরও ব্যবহারের জন্য
                        <?php if (isset($component)) { $__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::link','data' => ['as' => 'button','wire:click' => 'login','navigate' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','wire:click' => 'login','navigate' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
লগইন করুন <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477)): ?>
<?php $attributes = $__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477; ?>
<?php unset($__attributesOriginal54ddb5b70b37b1e1cf0f2f95e4c53477); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477)): ?>
<?php $component = $__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477; ?>
<?php unset($__componentOriginal54ddb5b70b37b1e1cf0f2f95e4c53477); ?>
<?php endif; ?>।
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97)): ?>
<?php $attributes = $__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97; ?>
<?php unset($__attributesOriginal43e8c568bbb8b06b9124aad3ccf4ec97); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97)): ?>
<?php $component = $__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97; ?>
<?php unset($__componentOriginal43e8c568bbb8b06b9124aad3ccf4ec97); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isGuest && $guestUsageRemaining <= 0): ?>
            <div
                class="mb-2 p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 rounded-xl text-center">
                <p class="text-xs text-red-600 dark:text-red-400 font-medium">
                    আপনার বিনামূল্যে ২০টি প্রশ্নের সীমা শেষ হয়েছে।
                </p>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'login','size' => 'xs','variant' => 'primary','wire:navigate' => true,'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'login','size' => 'xs','variant' => 'primary','wire:navigate' => true,'class' => 'mt-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    লগইন করে আনলিমিটেড ব্যবহার করুন
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="relative overflow-hidden rounded-3xl  shadow-md bg-zinc-50 dark:bg-zinc-700 transition-all duration-200"
            :class="isDragging ? 'ring-2 ring-accent/50 bg-accent/5' : ''">

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($imagePreviewUrl): ?>
                <div class="px-4 pt-3 pb-1" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'image-preview'; ?>wire:key="image-preview">
                    <div class="relative inline-block group/img">
                        <img src="<?php echo e($imagePreviewUrl); ?>"
                            class="h-20 w-auto rounded-xl object-cover border border-zinc-200 dark:border-zinc-600 shadow-sm"
                            alt="preview" />
                        <div
                            class="absolute bottom-1 left-1 bg-zinc-400/10 text-white text-xs px-1.5 py-0.5 rounded-md backdrop-blur">
                            ছবি নির্বাচিত
                        </div>
                        <button wire:click="removeImage"
                            class="absolute -top-2 -right-2 bg-zinc-800 text-white rounded-full p-0.5
                                                                                                                                                                                                           hover:bg-red-500 transition-colors shadow-sm
                                                                                                                                                                                                           opacity-0 group-hover/img:opacity-100">
                            <?php if (isset($component)) { $__componentOriginal155e76c41fe51242bc25d269fabf82f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal155e76c41fe51242bc25d269fabf82f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.x-mark','data' => ['variant' => 'micro','class' => 'size-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal155e76c41fe51242bc25d269fabf82f5)): ?>
<?php $attributes = $__attributesOriginal155e76c41fe51242bc25d269fabf82f5; ?>
<?php unset($__attributesOriginal155e76c41fe51242bc25d269fabf82f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal155e76c41fe51242bc25d269fabf82f5)): ?>
<?php $component = $__componentOriginal155e76c41fe51242bc25d269fabf82f5; ?>
<?php unset($__componentOriginal155e76c41fe51242bc25d269fabf82f5); ?>
<?php endif; ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal0ee30026125d1a66523211147b00e4dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ee30026125d1a66523211147b00e4dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::textarea','data' => ['wire:model' => 'question','xRef' => 'textarea','@keydown.enter' => 'if (!$event.shiftKey) { $event.preventDefault(); if (!$wire.inputDisabled) $wire.ask(); }','@input' => '
                    const ta = $el.querySelector(\'textarea\') ?? $el;
                    ta.style.height = \'auto\';
                    ta.style.height = Math.min(ta.scrollHeight, 208) + \'px\';
                ','xInit' => '$watch(\'$wire.question\', v => {
                    if (!v) {
                        const ta = $el.querySelector(\'textarea\') ?? $el;
                        ta.style.height = \'auto\';
                    }
                });','placeholder' => 'একটি বার্তা টাইপ করুন... (Shift+Enter এ নতুন লাইন)','autofocus' => true,'rows' => '1','resize' => 'none','disabled' => $this->inputDisabled,'class' => '! bg-transparent! shadow-none! ring-0! focus:ring-0! ! px-4 py-3 text-sm disabled:opacity-60 disabled:cursor-not-allowed overflow-hidden transition-none!']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'question','x-ref' => 'textarea','@keydown.enter' => 'if (!$event.shiftKey) { $event.preventDefault(); if (!$wire.inputDisabled) $wire.ask(); }','@input' => '
                    const ta = $el.querySelector(\'textarea\') ?? $el;
                    ta.style.height = \'auto\';
                    ta.style.height = Math.min(ta.scrollHeight, 208) + \'px\';
                ','x-init' => '$watch(\'$wire.question\', v => {
                    if (!v) {
                        const ta = $el.querySelector(\'textarea\') ?? $el;
                        ta.style.height = \'auto\';
                    }
                });','placeholder' => 'একটি বার্তা টাইপ করুন... (Shift+Enter এ নতুন লাইন)','autofocus' => true,'rows' => '1','resize' => 'none','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->inputDisabled),'class' => '! bg-transparent! shadow-none! ring-0! focus:ring-0! ! px-4 py-3 text-sm disabled:opacity-60 disabled:cursor-not-allowed overflow-hidden transition-none!']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $attributes = $__attributesOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__attributesOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $component = $__componentOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__componentOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
            
            <div class="flex justify-between items-center px-2 pb-2">
                <div class="flex items-center gap-1">
                    <label
                        class="cursor-pointer flex items-center gap-2 text-zinc-400
                                   dark:hover:text-zinc-300 transition-colors
                                  px-2 py-1.5 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-600
                                  text-xs font-medium"
                        title="ছবি যুক্ত করুন (অথবা paste করুন)">
                        <?php if (isset($component)) { $__componentOriginal2d7605e1adbee8a1737ebec29a91da61 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d7605e1adbee8a1737ebec29a91da61 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.photo','data' => ['variant' => 'micro','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.photo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d7605e1adbee8a1737ebec29a91da61)): ?>
<?php $attributes = $__attributesOriginal2d7605e1adbee8a1737ebec29a91da61; ?>
<?php unset($__attributesOriginal2d7605e1adbee8a1737ebec29a91da61); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d7605e1adbee8a1737ebec29a91da61)): ?>
<?php $component = $__componentOriginal2d7605e1adbee8a1737ebec29a91da61; ?>
<?php unset($__componentOriginal2d7605e1adbee8a1737ebec29a91da61); ?>
<?php endif; ?>
                        <span class="hidden sm:inline">ছবি</span>
                        <input type="file" wire:model="uploadedImage"
                            accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" />
                    </label>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strlen($question) > 3000): ?>
                        <span
                            class="text-[11px] px-2 py-0.5 rounded-lg
                                                                                                                                                                                                    <?php echo e(strlen($question) > 7000 ? 'text-red-500 bg-red-50 dark:bg-red-950/30' : 'text-amber-500 bg-amber-50 dark:bg-amber-950/30'); ?>">
                            <?php echo e(number_format(strlen($question))); ?> / 8,000
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-[11px] text-zinc-300 dark:text-zinc-500 hidden sm:block select-none">
                        Enter পাঠাবে
                    </span>
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'ask','wire:loading.attr' => 'disabled','wire:target' => 'ask','wire:loading.class' => 'opacity-60 cursor-not-allowed','icon' => 'paper-airplane','size' => 'sm','variant' => ($question || $uploadedImage) ? 'primary' : 'subtle','disabled' => $this->inputDisabled,'class' => 'rounded-xl transition-all duration-200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'ask','wire:loading.attr' => 'disabled','wire:target' => 'ask','wire:loading.class' => 'opacity-60 cursor-not-allowed','icon' => 'paper-airplane','size' => 'sm','variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($question || $uploadedImage) ? 'primary' : 'subtle'),'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->inputDisabled),'class' => 'rounded-xl transition-all duration-200']); ?>
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
        </div>

        
        <div class="mt-1.5 flex items-center justify-center gap-4">
            <span class="text-center text-xs text-zinc-400 dark:text-zinc-500 select-none">
                তথ্যবক্স এআই ভুল করতে পারে — গুরুত্বপূর্ণ তথ্য যাচাই করুন
            </span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isGuest): ?>
                <span class="text-xs text-zinc-400">·</span>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'login','size' => 'xs','variant' => 'subtle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'login','size' => 'xs','variant' => 'subtle']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    লগইন করুন
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
        </div>
    </div>
</div><?php /**PATH /var/www/html/totthobox/resources/views/livewire/ai/tutor.blade.php ENDPATH**/ ?>