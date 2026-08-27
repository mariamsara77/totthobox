<?php

use App\Models\Comment;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

new class extends Component {
    use WithPagination;

    public $model;
    public string $modelType;
    public int $modelId;
    public int $perPage = 10;

    public function mount($model): void
    {
        $this->model = $model;
        $this->modelType = get_class($model);
        $this->modelId = $model->id;
    }

    #[On('commentAdded')]
    #[On('commentDeleted')]
    #[On('replyAdded')]
    public function refreshComments(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $comments = Comment::query()
            ->where('commentable_type', $this->modelType)
            ->where('commentable_id', $this->modelId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate($this->perPage);

        return [
            'comments' => $comments,
        ];
    }
}; ?>

<div x-data="{ open: true }">
    <div class="mb-6">
        <flux:heading level="2" class="flex items-center font-semibold">
            <flux:icon icon="chat-bubble-left-right" class="h-5 w-5 mr-2 text-zinc-500" />
            কমেন্টস
            <flux:badge size="sm" color="blue" class="ml-2">
                {{ $comments->total() }}
            </flux:badge>
        </flux:heading>

        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
            আপনার মতামত শেয়ার করুন এবং আলোচনা শুরু করুন!
        </flux:text>
    </div>

    <div class="mb-8">
        <livewire:website.comments.comment-form :model="$model" />
    </div>

    <div class="w-full text-center mb-4">
        <flux:button @click="open = !open" variant="ghost" size="sm" icon:trailing="chevron-down">
            <span x-text="open ? 'কমেন্টস লুকান' : 'কমেন্টস পড়ুন'"></span>
        </flux:button>
    </div>

    <div x-show="open" x-collapse>
        @if ($comments->count() > 0)
            <div class="space-y-6">
                @foreach ($comments as $comment)
                    <div wire:key="comment-wrapper-{{ $comment->id }}">
                        <livewire:website.comments.comment-item :comment="$comment" :key="'comment-' . $comment->id" />
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $comments->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <flux:icon icon="chat-bubble-left-right" class="h-12 w-12 mx-auto mb-4 text-zinc-400" />
                <flux:heading size="lg">এখনও কোনো মন্তব্য নেই</flux:heading>
                <flux:text class="mt-2 text-zinc-500">
                    আপনার মতামত শেয়ার করা প্রথম ব্যক্তি হোন!
                </flux:text>
            </div>
        @endif
    </div>
</div>
