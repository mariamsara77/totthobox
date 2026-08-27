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

new class extends Component {
    use WithFileUploads;

    public ?ChatSession $session = null;
    public array $messages = [];

    #[Validate('required_without:uploadedImage|string|min:0|max:8000')]
    public string $question = '';

    public bool $isTyping = false;
    public bool $sending = false; // guards against double-submit
    public bool $hasError = false;
    public string $errorText = '';

    #[Validate('nullable|image|mimes:jpg,jpeg,png,gif,webp|max:8192')]
    public $uploadedImage = null;
    public ?string $imagePreviewUrl = null;

    public int $guestUsageRemaining = 20;
    public bool $isGuest = false;

    public string|int|null $newMessageId = null;

    public function mount(?string $uuid = null): void
    {
        $this->isGuest = !auth()->check();

        // SEO-র জন্য গেস্ট বা বটের ক্ষেত্রে জোর করে ব্রাউজার ইউআরএল চেঞ্জ করা বন্ধ করলাম
        if ($this->isGuest) {
            $this->syncGuestUsage();
            $this->messages = [];
            return;
        }

        // যদি ইউজার লগইন করা থাকে কিন্তু কোনো UUID না থাকে (যেমন: সরাসরি /ai/chat এ এসেছে)
        if (!$uuid) {
            $this->messages = [];
            return;
        }

        $session = ChatSession::withTrashed()
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if ($session && $session->trashed()) {
            $this->redirect(route('ai.chat.show'), navigate: true);
            return;
        }

        $this->session = $session;
        $this->loadMessages();
    }

    public function loadMessages(): void
    {
        if (!$this->session?->exists) {
            $this->messages = [];
            return;
        }

        $this->messages = $this->session->messages()->orderBy('created_at')->get()->toArray();
    }

    #[Computed]
    public function inputDisabled(): bool
    {
        return $this->isTyping || $this->sending || ($this->isGuest && $this->guestUsageRemaining <= 0);
    }

    public function ask(AiService $ai): void
    {
        // Double-submit / spam-click guard
        if ($this->sending) {
            return;
        }

        $this->hasError = false;
        $this->errorText = '';
        $this->newMessageId = null;

        $content = trim($this->question);

        if ($content === '' && !$this->uploadedImage) {
            return;
        }

        $this->validate();

        if ($this->isGuest && $this->guestUsageRemaining <= 0) {
            $this->hasError = true;
            $this->errorText = 'আপনার বিনামূল্যে সীমা শেষ। আরও ব্যবহারের জন্য লগইন করুন।';
            return;
        }

        $this->sending = true;

        $imageBase64 = null;
        $imageMime = null;
        $imageStoredPath = null;

        if ($this->uploadedImage) {
            $imageMime = $this->uploadedImage->getMimeType();
            $imageBase64 = base64_encode(file_get_contents($this->uploadedImage->getRealPath()));
            $imageStoredPath = "data:{$imageMime};base64,{$imageBase64}";
        }

        try {
            if ($this->isGuest) {
                $this->handleGuestAsk($ai, $content, $imageBase64, $imageMime);
            } else {
                $this->handleAuthAsk($ai, $content, $imageBase64, $imageMime, $imageStoredPath);
            }
        } finally {
            $this->sending = false;
        }
    }

    private function handleGuestAsk(AiService $ai, string $content, ?string $imageBase64, ?string $imageMime): void
    {
        $usage = $ai->getGuestUsage(request()->ip());

        if ($usage['remaining'] <= 0 && $usage['used'] >= 20) {
            $this->hasError = true;
            $this->errorText = 'আপনার বিনামূল্যে সীমা শেষ। আরও ব্যবহারের জন্য লগইন করুন।';
            return;
        }

        $this->messages[] = [
            'id' => 'guest_' . uniqid(),
            'role' => 'user',
            'content' => $content ?: '🖼️ ছবি পাঠানো হয়েছে',
            'image_path' => null,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->resetInput();
        $this->isTyping = true;
        $this->dispatch('scroll-bottom');

        try {
            $context = collect(array_slice($this->messages, -11, -1))
                ->map(fn($m) => ['role' => $m['role'], 'content' => $m['content']])
                ->values()
                ->toArray();

            $response = $ai->askAi($content, $context, $imageBase64, $imageMime, request()->ip());

            $newId = 'guest_' . uniqid();

            $this->messages[] = [
                'id' => $newId,
                'role' => 'model',
                'content' => $response,
                'image_path' => null,
                'created_at' => now()->toDateTimeString(),
            ];

            $this->newMessageId = $newId;
        } catch (\Throwable $e) {
            Log::error('Guest AI error', ['message' => $e->getMessage()]);
            $this->hasError = true;
            $this->errorText = 'উত্তর পেতে সমস্যা হচ্ছে — আবার চেষ্টা করুন।';
        } finally {
            $this->syncGuestUsage();
            $this->isTyping = false;
            $this->dispatch('scroll-bottom');
        }
    }

    private function handleAuthAsk(AiService $ai, string $content, ?string $imageBase64, ?string $imageMime, ?string $imageStoredPath): void
    {
        $isNewSession = !$this->session || !$this->session->exists;

        if ($isNewSession) {
            $this->session = ChatSession::create([
                'uuid' => request()->route('uuid') ?? (string) Str::uuid(),
                'user_id' => auth()->id(),
                'title' => Str::limit($content ?: 'ছবি সহ কথোপকথন', 50),
            ]);

            $newUrl = route('ai.chat.show', $this->session->uuid);
            $this->js("window.history.replaceState({}, '', '{$newUrl}')");
            $this->dispatch('session-created', uuid: $this->session->uuid)->to('ai.sidebar-history');
        }

        // Inside handleAuthAsk, after validation / before creating message
        $imageStoredPath = null;

        if ($this->uploadedImage) {
            $path = $this->uploadedImage->store('chat-images/' . auth()->id(), 'public');
            $imageStoredPath = $path; // relative path, e.g. chat-images/5/xyz.jpg
            // Keep base64 only for the current AI request
            $imageMime = $this->uploadedImage->getMimeType();
            $imageBase64 = base64_encode(file_get_contents($this->uploadedImage->getRealPath()));
        }

        // Then when creating the user message:
        $this->session->messages()->create([
            'role' => 'user',
            'content' => $content ?: '🖼️ ছবি পাঠানো হয়েছে',
            'image_path' => $imageStoredPath, // now a proper path
        ]);

        // $this->session->messages()->create([
        //     'role' => 'user',
        //     'content' => $content ?: '🖼️ ছবি পাঠানো হয়েছে',
        //     'image_path' => $imageStoredPath,
        // ]);

        $this->resetInput();
        $this->isTyping = true;
        $this->loadMessages();
        $this->dispatch('scroll-bottom');

        try {
            $context = collect(array_slice($this->messages, -12))
                ->map(fn($m) => ['role' => $m['role'], 'content' => $m['content']])
                ->values()
                ->toArray();

            $response = $ai->askAi($content, $context, $imageBase64, $imageMime);

            $aiMessage = $this->session->messages()->create([
                'role' => 'model',
                'content' => $response,
            ]);

            $this->newMessageId = $aiMessage->id;
        } catch (\Throwable $e) {
            Log::error('Auth AI error', [
                'message' => $e->getMessage(),
                'session' => $this->session?->uuid,
            ]);
            $this->hasError = true;
            $this->errorText = 'উত্তর পেতে সমস্যা হচ্ছে — আবার চেষ্টা করুন।';
        } finally {
            $this->isTyping = false;
            $this->loadMessages();
            $this->dispatch('scroll-bottom');
        }
    }

    public function editAndRegenerate(int $messageId, string $newContent): void
    {
        if ($this->isGuest || $this->sending) {
            return;
        }

        $newContent = trim($newContent);
        if ($newContent === '') {
            return;
        }

        $message = $this->session->messages()->findOrFail($messageId);
        $message->update(['content' => $newContent]);

        $this->session->messages()->where('created_at', '>', $message->created_at)->delete();

        $this->question = $newContent;
        $this->ask(app(AiService::class));
    }

    public function regenerateLast(): void
    {
        if ($this->sending) {
            return;
        }

        if ($this->isGuest) {
            $lastUser = collect($this->messages)->filter(fn($m) => $m['role'] === 'user')->last();
            if (!$lastUser) {
                return;
            }

            $lastIndex = array_key_last($this->messages);
            if ($lastIndex !== null && $this->messages[$lastIndex]['role'] !== 'user') {
                array_splice($this->messages, $lastIndex, 1);
            }

            $this->question = $lastUser['content'];
            $this->ask(app(AiService::class));
            return;
        }

        $lastUserMessage = $this->session->messages()->where('role', 'user')->latest()->first();
        if ($lastUserMessage) {
            $this->editAndRegenerate($lastUserMessage->id, $lastUserMessage->content);
        }
    }

    public function updatedUploadedImage(): void
    {
        $this->validateOnly('uploadedImage');
        if ($this->uploadedImage) {
            $this->imagePreviewUrl = $this->uploadedImage->temporaryUrl();
        }
    }

    public function removeImage(): void
    {
        $this->uploadedImage = null;
        $this->imagePreviewUrl = null;
    }

    public function syncGuestUsage(): void
    {
        $stats = app(AiService::class)->getGuestUsage(request()->ip());
        $this->guestUsageRemaining = $stats['remaining'];
    }

    private function resetInput(): void
    {
        $this->question = '';
        $this->uploadedImage = null;
        $this->imagePreviewUrl = null;
    }

    public function login()
    {
        Flux::modal('auth-modal')->show();
    }
};
?>

{{--
╔══════════════════════════════════════════════════════════════════════╗
║ HEAD ASSETS — put once in your main layout

<head> (not per component) ║
    ╚══════════════════════════════════════════════════════════════════════╝

    <link id="hljs-light" rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css"
        media="(prefers-color-scheme: light)">
    <link id="hljs-dark" rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark-dimmed.min.css"
        media="(prefers-color-scheme: dark)">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    WHY THE BUG HAPPENED (fixed below):
    The old markup rendered an *empty* <div x-data="..."></div> from Blade and let
    Alpine fill it with innerHTML on the client. Livewire never saw that content,
    so the next time it re-rendered the component (next question, regenerate,
    even just an unrelated wire update) morphdom "corrected" that div back to
    the empty version straight from the server — wiping the answer until you
    reloaded the page. Fixed by rendering the markdown server-side (so it's
    always the real DOM content) and putting wire:ignore on that node so
    Livewire never touches it again after first paint. Alpine only reveals it
    with a typewriter animation on top — it never "owns" the content anymore.
    --}}

<x-seo title="Totthobox AI: আপনার বুদ্ধিমত্তাসম্পন্ন এআই অ্যাসিস্ট্যান্ট"
    description="Totthobox AI-এর মাধ্যমে যেকোনো তথ্যের তাৎক্ষণিক সমাধান, ডেটা অ্যানালাইসিস এবং কাস্টমাইজড লার্নিং অভিজ্ঞতা উপভোগ করুন।"
    keywords="টেলিগ্রাম এআই, Totthobox AI, কৃত্রিম বুদ্ধিমত্তা, এআই টিউটর, AI Agent Bangladesh, স্মার্ট অ্যাসিস্ট্যান্ট, বাংলা এআই" />

@once
    @push('styles')
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
    @endpush

    @push('scripts')
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
    @endpush
@endonce

<div x-data="totthoboxChatRoot()" @dragover.window.prevent="isDragging = true" @dragleave.window="isDragging = false"
    @drop.window.prevent="handleDrop($event)" @paste.window="handlePaste($event)"
    class="max-w-2xl mx-auto lg:h-[91vh] h-[89vh] relative flex flex-col">

    {{-- Header --}}
    @if ($isGuest)
        <div class="hidden lg:flex items-center justify-between h-8 mb-2 shrink-0 px-1">
            <flux:heading class=" truncate flex items-center">
                <flux:icon icon="sparkles" class="inline-block me-1.5 opacity-60 size-4" />
                তথ্যবক্স এআই
            </flux:heading>
            <div class="flex items-center gap-4">
                <span class="text-[11px] text-zinc-400">{{ $guestUsageRemaining }} বার বাকি</span>
                <flux:button wire:click="login" size="xs" variant="primary" wire:navigate>
                    লগইন করুন
                </flux:button>
            </div>
        </div>
    @else
        <div class="hidden lg:flex items-center h-8 mb-2 shrink-0">
            <flux:heading class=" truncate flex items-center">
                <flux:icon icon="chevron-right" class="inline-block me-1.5 opacity-60 size-4" />
                {{ $session?->title ?? 'নতুন চ্যাট' }}
            </flux:heading>
        </div>
    @endif

    {{-- ── Message list ── --}}
    <div x-on:scroll-bottom.window="$nextTick(() => {
            $refs.chat.scrollTo({ top: $refs.chat.scrollHeight, behavior: 'smooth' });
            showScrollBtn = false;
        })"
        x-ref="chat"
        @scroll="showScrollBtn = ($refs.chat.scrollHeight - $refs.chat.scrollTop - $refs.chat.clientHeight) > 120"
        class="flex-1 overflow-y-auto min-h-0 space-y-1 scroll-smooth totthobox-scrollbar" id="chat-container">
        {{-- Empty state --}}
        @if (count($messages) === 0 && !$isTyping)
            <div class="flex flex-col items-center justify-center h-full gap-4 opacity-50 select-none">
                <div class="p-3 rounded-2xl bg-accent/10">
                    <flux:icon.sparkles class="size-8 " />
                </div>
                <flux:heading size="sm">আমি আপনাকে কিভাবে সাহায্য করতে পারি?</flux:heading>
                <p class="text-xs text-zinc-400">ছবি paste করুন বা drag করে আনুন</p>
                @if ($isGuest)
                    <p class="text-xs text-zinc-400">লগইন ছাড়াই {{ $guestUsageRemaining }} বার জিজ্ঞেস করা যাবে</p>
                @endif
            </div>
        @endif

        {{-- Messages --}}
        @foreach ($messages as $msg)
            @php
                $isUser = $msg['role'] === 'user';
                $shouldType = !$isUser && (string) $msg['id'] === (string) $newMessageId;
            @endphp

            <div wire:key="msg-{{ $msg['id'] }}" x-data="{ editing: false, copied: false, collapsed: {{ $isUser ? 'true' : 'false' }}, newContent: @js($msg['content']), get isLong() { return this.newContent.length > 280 } }"
                class="flex {{ $isUser ? 'justify-end' : 'justify-start' }} group ai-msg-pop">
                {{-- AI avatar --}}
                @if (!$isUser)
                    <div class="flex items-start pt-3 pr-2 shrink-0">
                        <div class="p-1 rounded-lg bg-accent/10">
                            <flux:icon.brand class="size-3.5 " />
                        </div>
                    </div>
                @endif

                <div class="max-w-[90%] md:max-w-[78%] lg:max-w-[70%] relative">

                    {{-- Edit button --}}
                    @if ($isUser && !$isGuest)
                        <button @click="editing = true"
                            class="absolute -left-8 top-3 p-1 text-zinc-400 
                                                                                                                                                                                                                                                                                                                                                                                   opacity-0 group-hover:opacity-100 transition-all rounded-md
                                                                                                                                                                                                                                                                                                                                                                                   hover:bg-zinc-100 dark:hover:bg-zinc-700"
                            title="সম্পাদনা">
                            <flux:icon.pencil-square variant="micro" />
                        </button>
                    @endif

                    <div
                        class="mb-3 {{ $isUser ? 'bg-zinc-100 dark:bg-zinc-800 px-4 py-2.5 rounded-2xl rounded-tr-md shadow-sm' : 'py-1' }}">

                        {{-- ── View mode ── --}}
                        <div x-show="!editing">

                            {{-- Attached image --}}
                            @if (!empty($msg['image_path']))
                                <div class="mb-2">
                                    <flux:media media="{{ $msg['image_path'] }}" alt="uploaded" />
                                </div>
                            @endif

                            @if ($isUser)
                                {{-- ── User message (collapsible) ── --}}
                                <div class="relative">
                                    <div x-show="collapsed && isLong" class="flex items-center gap-2">
                                        <p class="truncate text-sm leading-relaxed max-w-[calc(100%-1.75rem)]">
                                            {{ $msg['content'] }}
                                        </p>
                                        <button @click="collapsed = false"
                                            class="shrink-0 p-0.5 rounded-full text-zinc-400
                                                                                                                                                                                                                                                                                                                                                                                                    dark:hover:text-zinc-300
                                                                                                                                                                                                                                                                                                                                                                                                   hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-all duration-200"
                                            title="বিস্তারিত দেখুন">
                                            <flux:icon.chevron-down variant="micro" class="size-3.5" />
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
                                                <flux:icon.chevron-up variant="micro" class="size-3.5" />
                                            </button>
                                        </div>
                                        {!! str($msg['content'])->markdown() !!}
                                    </div>
                                </div>
                            @else
                                {{-- ══════════════════════════════════════════════════════
                                    AI RESPONSE
                                    — content is rendered server-side (real DOM, real
                                    Livewire-tracked HTML), and carries wire:ignore so
                                    Livewire never resets it on a later re-render.
                                    — Alpine only adds a typewriter reveal on top for the
                                    message that was *just* generated (shouldType).
                                    ══════════════════════════════════════════════════════ --}}
                                <div wire:ignore x-data="totthoboxAiAnswer(@js($shouldType))"
                                    :class="typing ? 'ai-prose prose prose-sm dark:prose-invert max-w-none ai-cursor' :
                                        'ai-prose prose prose-sm dark:prose-invert max-w-none'">
                                    {!! str($msg['content'])->markdown() !!}
                                </div>
                            @endif

                            {{-- AI message actions --}}
                            @if (!$isUser)
                                <div
                                    class="flex items-center gap-1 pt-2 mt-1
                                                                                                                                                                                                                                                                                                                                                                                            border-t border-zinc-100 dark:border-zinc-700/50
                                                                                                                                                                                                                                                                                                                                                                                            opacity-0 group-hover:opacity-100 ">
                                    <button
                                        @click="navigator.clipboard.writeText(@js($msg['content'])); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="flex items-center gap-2 px-2 py-1 text-xs text-zinc-400
                                                                                                                                                                                                                                                                                                                                                                                               hover:text-zinc-700 dark:hover:text-zinc-200
                                                                                                                                                                                                                                                                                                                                                                                               hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-lg transition-all">
                                        <template x-if="!copied">
                                            <flux:icon.clipboard variant="micro" class="size-3" />
                                        </template>
                                        <template x-if="copied">
                                            <flux:icon.check variant="micro" class="size-3 text-green-500" />
                                        </template>
                                        <span x-text="copied ? 'কপি হয়েছে' : 'কপি'"></span>
                                    </button>

                                    @if ($loop->last)
                                        <button wire:click="regenerateLast" wire:loading.attr="disabled"
                                            wire:target="regenerateLast,ask"
                                            class="flex items-center gap-2 px-2 py-1 text-xs text-zinc-400
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           hover:text-zinc-700 dark:hover:text-zinc-200
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-lg transition-all
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           disabled:opacity-50 disabled:cursor-not-allowed">
                                            <flux:icon.arrow-path variant="micro" class="size-3"
                                                wire:loading.class="animate-spin" wire:target="regenerateLast" />
                                            পুনরায় তৈরি
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- ── Edit mode ── --}}
                        @if (!$isGuest)
                            <div x-show="editing" x-cloak class="flex flex-col gap-2 min-w-[200px]">
                                <flux:textarea x-model="newContent" rows="auto" resize="none" class="text-sm"
                                    @keydown.escape="editing = false"
                                    @keydown.ctrl.enter.prevent="$wire.editAndRegenerate({{ $msg['id'] }}, newContent); editing = false" />
                                <div class="flex justify-between items-center">
                                    <p class="text-xs text-zinc-400">Ctrl+Enter এ পাঠান</p>
                                    <div class="flex gap-4">
                                        <flux:button size="xs" variant="subtle" @click="editing = false">বাতিল
                                        </flux:button>
                                        <flux:button size="xs" variant="primary"
                                            @click="$wire.editAndRegenerate({{ $msg['id'] }}, newContent); editing = false">
                                            আপডেট ও পাঠান
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Loading indicator --}}
        <div wire:loading wire:target="ask,regenerateLast,editAndRegenerate" class="flex justify-start py-2 ps-2">
            <div
                class="flex items-center gap-4 px-4 py-3 bg-white dark:bg-zinc-800
                        border border-zinc-100 dark:border-zinc-700/50 rounded-2xl shadow-sm">
                <div class="relative flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-accent/20 animate-ping"></div>
                    <div class="relative bg-accent/10 p-1.5 rounded-lg animate-bounce">
                        <flux:icon.brand class="size-5 " />
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

        {{-- Error banner --}}
        @if ($hasError)
            <div class="flex justify-start ps-1 pb-2">
                <div
                    class="flex items-center gap-2 text-sm text-red-500 dark:text-red-400
                                                                                                                                                                                                    bg-red-50 dark:bg-red-950/30 px-3 py-2 rounded-xl">
                    <flux:icon.exclamation-triangle variant="micro" class="shrink-0" />
                    <span>{{ $errorText }}</span>
                    <button wire:click="regenerateLast"
                        class="underline underline-offset-2 ml-1 font-medium hover:text-red-600 transition-colors">
                        আবার চেষ্টা
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Scroll to bottom button --}}
    <div x-show="showScrollBtn" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" class="absolute bottom-30 left-1/2 -translate-x-1/2 z-30">
        <flux:button icon="arrow-down"
            @click="$refs.chat.scrollTo({ top: $refs.chat.scrollHeight, behavior: 'smooth' }); showScrollBtn = false"
            variant="subtle" size="xs" class="shadow-lg" />
    </div>

    {{-- ── Input area ── --}}
    <div class="shrink-0 pt-2">

        {{-- Guest usage progress --}}
        @if ($isGuest)
            <div class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="flex justify-between items-center mb-2 text-xs font-medium text-zinc-500">
                    <span>ফ্রি লিমিট বাকি</span>
                    <span>{{ $guestUsageRemaining }} / 20</span>
                </div>
                <div class="w-full bg-zinc-200 dark:bg-zinc-700 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-accent h-full transition-all duration-200"
                        style="width: {{ ($guestUsageRemaining / 20) * 100 }}%"></div>
                </div>
                @if ($guestUsageRemaining <= 0)
                    <flux:subheading class="mt-2 text-red-500 text-xs">
                        আপনার লিমিট শেষ। আরও ব্যবহারের জন্য
                        <flux:link as="button" wire:click="login" navigate>লগইন করুন</flux:link>।
                    </flux:subheading>
                @endif
            </div>
        @endif

        {{-- Guest hard limit warning --}}
        @if ($isGuest && $guestUsageRemaining <= 0)
            <div
                class="mb-2 p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 rounded-xl text-center">
                <p class="text-xs text-red-600 dark:text-red-400 font-medium">
                    আপনার বিনামূল্যে ২০টি প্রশ্নের সীমা শেষ হয়েছে।
                </p>
                <flux:button wire:click="login" size="xs" variant="primary" wire:navigate class="mt-2">
                    লগইন করে আনলিমিটেড ব্যবহার করুন
                </flux:button>
            </div>
        @endif

        {{-- Input box --}}
        <div class="relative overflow-hidden rounded-3xl  shadow-md bg-zinc-50 dark:bg-zinc-700 transition-all duration-200"
            :class="isDragging ? 'ring-2 ring-accent/50 bg-accent/5' : ''">

            {{-- Image preview --}}
            @if ($imagePreviewUrl)
                <div class="px-4 pt-3 pb-1" wire:key="image-preview">
                    <div class="relative inline-block group/img">
                        <img src="{{ $imagePreviewUrl }}"
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
                            <flux:icon.x-mark variant="micro" class="size-3" />
                        </button>
                    </div>
                </div>
            @endif

            <flux:textarea wire:model="question" x-ref="textarea"
                @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); if (!$wire.inputDisabled) $wire.ask(); }"
                @input="
                    const ta = $el.querySelector('textarea') ?? $el;
                    ta.style.height = 'auto';
                    ta.style.height = Math.min(ta.scrollHeight, 208) + 'px';
                "
                x-init="$watch('$wire.question', v => {
                    if (!v) {
                        const ta = $el.querySelector('textarea') ?? $el;
                        ta.style.height = 'auto';
                    }
                });" placeholder="একটি বার্তা টাইপ করুন... (Shift+Enter এ নতুন লাইন)" autofocus
                rows="1" resize="none" :disabled="$this->inputDisabled"
                class="! bg-transparent! shadow-none! ring-0! focus:ring-0! ! px-4 py-3 text-sm disabled:opacity-60 disabled:cursor-not-allowed overflow-hidden transition-none!" />
            {{-- Toolbar --}}
            <div class="flex justify-between items-center px-2 pb-2">
                <div class="flex items-center gap-1">
                    <label
                        class="cursor-pointer flex items-center gap-2 text-zinc-400
                                   dark:hover:text-zinc-300 transition-colors
                                  px-2 py-1.5 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-600
                                  text-xs font-medium"
                        title="ছবি যুক্ত করুন (অথবা paste করুন)">
                        <flux:icon.photo variant="micro" class="size-4" />
                        <span class="hidden sm:inline">ছবি</span>
                        <input type="file" wire:model="uploadedImage"
                            accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" />
                    </label>

                    @if (strlen($question) > 3000)
                        <span
                            class="text-[11px] px-2 py-0.5 rounded-lg
                                                                                                                                                                                                    {{ strlen($question) > 7000 ? 'text-red-500 bg-red-50 dark:bg-red-950/30' : 'text-amber-500 bg-amber-50 dark:bg-amber-950/30' }}">
                            {{ number_format(strlen($question)) }} / 8,000
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-[11px] text-zinc-300 dark:text-zinc-500 hidden sm:block select-none">
                        Enter পাঠাবে
                    </span>
                    <flux:button wire:click="ask" wire:loading.attr="disabled" wire:target="ask"
                        wire:loading.class="opacity-60 cursor-not-allowed" icon="paper-airplane" size="sm"
                        :variant="($question || $uploadedImage) ? 'primary' : 'subtle'" :disabled="$this->inputDisabled"
                        class="rounded-xl transition-all duration-200" />
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-1.5 flex items-center justify-center gap-4">
            <span class="text-center text-xs text-zinc-400 dark:text-zinc-500 select-none">
                তথ্যবক্স এআই ভুল করতে পারে — গুরুত্বপূর্ণ তথ্য যাচাই করুন
            </span>
            @if ($isGuest)
                <span class="text-xs text-zinc-400">·</span>
                <flux:button wire:click="login" size="xs" variant="subtle">
                    লগইন করুন
                </flux:button>
            @endif
        </div>
    </div>
</div>
