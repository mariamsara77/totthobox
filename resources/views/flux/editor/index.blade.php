@props([
    'label' => null,
    'description' => null,
    'disabled' => false,
    'placeholder' => null,
])

@php
    $wireModel = $attributes->wire('model');
    $fieldName = $wireModel->value();
@endphp

<div {{ $attributes->only('class') }}>

    @if ($label)
        <flux:label class="mb-2">{{ $label }}</flux:label>
    @endif

    @if ($description)
        <flux:description class="mb-3">{{ $description }}</flux:description>
    @endif

    {{-- Editor wrapper: wire:ignore keeps Livewire from touching the DOM inside --}}
    <div wire:ignore>
        <div x-data="quillEditor({
            modelValue: @entangle($wireModel),
            disabled: {{ $disabled ? 'true' : 'false' }},
            placeholder: {{ $placeholder ? json_encode($placeholder) : 'null' }}
        })" x-init="init"
            x-on:editor-clear.window="if ($event.detail?.field === '{{ $fieldName }}') clearContent()"
            class="relative">
            <flux:card class="!p-0 overflow-hidden">
                {{-- Toolbar --}}
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

                    <flux:separator vertical class="mx-1.5 self-stretch" />

                    <span class="ql-formats !mr-1">
                        <button class="ql-bold" title="Bold (Ctrl+B)"></button>
                        <button class="ql-italic" title="Italic (Ctrl+I)"></button>
                        <button class="ql-underline" title="Underline (Ctrl+U)"></button>
                        <button class="ql-strike" title="Strikethrough"></button>
                    </span>

                    <flux:separator vertical class="mx-1.5 self-stretch" />

                    <span class="ql-formats !mr-1">
                        <button class="ql-list" value="ordered" title="Numbered list"></button>
                        <button class="ql-list" value="bullet" title="Bullet list"></button>
                    </span>

                    <flux:separator vertical class="mx-1.5 self-stretch" />

                    <span class="ql-formats !mr-1">
                        <button class="ql-blockquote" title="Blockquote"></button>
                        <button class="ql-code-block" title="Code block"></button>
                    </span>

                    <flux:separator vertical class="mx-1.5 self-stretch" />

                    <span class="ql-formats !mr-0">
                        <button class="ql-link" title="Insert link"></button>
                        <button class="ql-clean" title="Remove formatting"></button>
                    </span>

                    <div class="flex-1"></div>

                    <flux:text size="xs" class="pr-1 tabular-nums select-none text-zinc-400 dark:text-zinc-500">
                        <span x-text="charCount"></span>
                    </flux:text>
                </div>

                {{-- Quill content area --}}
                <div x-ref="quillEditor" class="max-h-[60vh] overflow-y-auto text-sm leading-relaxed">
                </div>
            </flux:card>

            @if ($disabled)
                <div class="absolute inset-0 rounded-lg bg-white/60 dark:bg-zinc-900/60 cursor-not-allowed z-10"></div>
            @endif
        </div>
    </div>

    @if ($fieldName)
        <flux:error :name="$fieldName" />
    @endif
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
