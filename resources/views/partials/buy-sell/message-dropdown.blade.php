@if ($activePostId === $post->id && $selectedPost)
    <div class="fixed inset-0 z-40" wire:click="showContact({{ $post->id }})">
    </div>
    <div
        class="absolute bottom-10 bg-zinc-100 dark:bg-zinc-800 border border-zinc-400/25 rounded-xl p-3 w-64 space-y-2 shadow-lg z-50">
        <flux:button size="sm"
            wire:click="sendQuickMessage('want_buy', {{ $selectedPost->id }}, {{ $selectedPost->user->id }})">
            আমি আপনার পণ্যটি কিনতে আগ্রহী
        </flux:button>

        <flux:button size="sm"
            wire:click="sendQuickMessage('price', {{ $selectedPost->id }}, {{ $selectedPost->user->id }})">
            পণ্যটির দাম কত জানাবেন?
        </flux:button>

        <flux:button size="sm"
            wire:click="sendQuickMessage('contact', {{ $selectedPost->id }}, {{ $selectedPost->user->id }})">
            পণ্যটি নিয়ে কথা বলতে চাই
        </flux:button>

        <flux:button size="sm"
            wire:click="sendQuickMessage('availability', {{ $selectedPost->id }}, {{ $selectedPost->user->id }})">
            পণ্যটি এখনো অ্যাভেইলেবল আছে?
        </flux:button>

        <flux:button size="sm"
            wire:click="sendQuickMessage('meetup', {{ $selectedPost->id }}, {{ $selectedPost->user->id }})">
            পণ্যটি দেখতে কোথায় আসব?
        </flux:button>
    </div>
@endif
