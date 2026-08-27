<?php

use App\Models\Comment;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Flux\Flux;

new class extends Component {
    public Comment $comment;
    public bool $showReplyForm = false;
    public string $replyContent = '';
    public array $openMenus = [];

    protected int $maxDepth = 4;

    public function mount(Comment $comment): void
    {
        $this->comment = $comment;
    }

    public function canReply(): bool
    {
        return $this->comment->depth < $this->maxDepth;
    }

    public function ensureAuthenticated(string $message = 'লগইন করতে হবে।'): bool
    {
        if (auth()->check()) {
            return true;
        }

        Flux::modal('auth-modal')->show();
        Flux::toast(text: $message, variant: 'warning');

        return false;
    }

    public function toggleReplyForm(): void
    {
        $this->showReplyForm = !$this->showReplyForm;

        if (!$this->showReplyForm) {
            $this->replyContent = '';
            $this->resetValidation();
        }
    }

    public function submitReply(): void
    {
        if (!$this->ensureAuthenticated('উত্তর দেওয়ার জন্য লগইন করতে হবে।')) {
            return;
        }

        if (!$this->canReply()) {
            Flux::toast(text: 'আরও গভীরে উত্তর দেওয়া যাবে না।', variant: 'warning');
            return;
        }

        $this->validate([
            'replyContent' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        Comment::create([
            'content' => $this->replyContent,
            'user_id' => auth()->id(),
            'parent_id' => $this->comment->id,
            'commentable_id' => $this->comment->commentable_id,
            'commentable_type' => $this->comment->commentable_type,
            'depth' => $this->comment->depth + 1,
        ]);

        $this->showReplyForm = false;
        $this->replyContent = '';

        $this->dispatch('replyAdded', commentId: $this->comment->id);

        Flux::toast(text: 'উত্তর সফলভাবে যোগ করা হয়েছে!', variant: 'success');
    }

    public function deleteComment(): void
    {
        if (!$this->ensureAuthenticated('মন্তব্য মুছতে লগইন করতে হবে।')) {
            return;
        }

        $user = auth()->user();

        if ($this->comment->user_id !== $user->id && !($user->is_admin ?? false)) {
            Flux::toast(text: 'আপনি এই মন্তব্য মুছতে পারবেন না।', variant: 'danger');
            return;
        }

        $commentId = $this->comment->id;
        $this->comment->delete();

        $this->dispatch('commentDeleted', commentId: $commentId);

        Flux::toast(text: 'মন্তব্য সফলভাবে মুছে ফেলা হয়েছে!', variant: 'success');
    }

    public function react(string $type): void
    {
        if (!$this->ensureAuthenticated('রিয়্যাকশন করার জন্য লগইন করতে হবে।')) {
            return;
        }

        $this->comment->react($type);
        $this->comment->refresh();

        Flux::toast(text: 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }

    public function toggleMenu(string $key): void
    {
        $this->openMenus[$key] = !($this->openMenus[$key] ?? false);
    }

    #[On('replyAdded')]
    #[On('commentAdded')]
    #[On('commentDeleted')]
    public function refreshComment(): void
    {
        $this->comment->refresh();
        $this->comment->load(['user', 'replies.user']);
    }

    public function with(): array
    {
        $this->comment->loadMissing(['user', 'replies.user']);

        return [
            'comment' => $this->comment,
        ];
    }
}; ?>

<div class="comment-item relative" wire:key="comment-{{ $comment->id }}">
    <div class="flex items-start gap-4 sm:gap-4">

        {{-- Avatar --}}
        <div class="flex-shrink-0 mt-1">
            <flux:profile initials="{{ substr($comment->user->name ?? '??', 0, 2) }}"
                avatar="{{ $comment->user->avatar ?? null }}" :chevron="false" circle size="sm"
                class="w-10 h-10" />
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <flux:card class="!p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <flux:heading size="sm" class="font-semibold truncate">
                            {{ $comment->user->name ?? 'Unknown' }}
                        </flux:heading>
                        <flux:text size="xs" class="text-zinc-500 mt-0.5">
                            {{ $comment->created_at->diffForHumans() }}
                        </flux:text>
                    </div>

                    @auth
                        @if (auth()->id() === $comment->user_id || (auth()->user()->is_admin ?? false))
                            <div class="relative flex-shrink-0" wire:key="menu-{{ $comment->id }}">
                                <flux:button size="xs" variant="ghost" icon="ellipsis-vertical"
                                    wire:click="toggleMenu('menu-{{ $comment->id }}')" class="!p-1" />

                                @if ($openMenus['menu-' . $comment->id] ?? false)
                                    <div class="absolute right-0 top-8 z-40 min-w-[120px]">
                                        <flux:button wire:click="deleteComment" variant="danger" size="sm"
                                            class="w-full" icon="trash"
                                            wire:confirm="আপনি কি নিশ্চিত যে এই মন্তব্যটি মুছে ফেলতে চান?">
                                            মুছুন
                                        </flux:button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endauth
                </div>

                <div class="mt-2">
                    <flux:text class="break-words whitespace-pre-wrap">
                        {{ $comment->content }}
                    </flux:text>
                </div>
            </flux:card>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                @auth
                    @if ($this->canReply())
                        <flux:button variant="ghost" size="xs" icon="arrow-turn-up-left" wire:click="toggleReplyForm"
                            class="!px-2 !py-1">
                            উত্তর দিন
                        </flux:button>
                    @endif
                @endauth

                <flux:button variant="ghost" size="xs" wire:click="react('like')" class="!px-2 !py-1">
                    <div class="flex items-center gap-2 {{ $comment->hasReaction('like') ? 'text-blue-600' : '' }}">
                        <flux:icon name="thumb-up" class="w-4  h-4" />
                        <span class="text-xs font-medium">
                            {{ $comment->countReaction('like') }}
                        </span>
                    </div>
                </flux:button>

                <flux:button variant="ghost" size="xs" wire:click="react('dislike')" class="!px-2 !py-1">
                    <div class="flex items-center gap-2 {{ $comment->hasReaction('dislike') ? 'text-red-600' : '' }}">
                        <flux:icon name="thumb-down" class="w-4  h-4" />
                        <span class="text-xs font-medium">
                            {{ $comment->countReaction('dislike') }}
                        </span>
                    </div>
                </flux:button>
            </div>

            @if ($showReplyForm)
                <div class="mt-3 ml-0 sm:ml-2">
                    <flux:card class="!p-4">
                        <flux:field>
                            <flux:textarea wire:model="replyContent" resize="none" placeholder="আপনার উত্তর লিখুন..."
                                autofocus rows="auto" />

                            <div class="flex items-center justify-end gap-2 mt-3">
                                <flux:button size="sm" variant="ghost" class="!rounded-full"
                                    wire:click="toggleReplyForm">
                                    বাতিল
                                </flux:button>

                                <flux:button size="sm" variant="primary" color="black" class="!rounded-full"
                                    wire:click="submitReply">
                                    উত্তর দিন
                                </flux:button>
                            </div>
                        </flux:field>
                    </flux:card>
                </div>
            @endif

            @if ($comment->replies->isNotEmpty())
                <div class="mt-4 space-y-4 border-l border-zinc-400/25 pl-4 sm:pl-6">
                    @foreach ($comment->replies as $reply)
                        <livewire:website.comments.comment-item :comment="$reply" :key="'reply-' . $reply->id" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
