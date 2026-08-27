<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $filter = 'all';
    public string $variant = 'sidebar';
    public int $perPage = 10;

    #[Computed]
    public function notifications()
    {
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        $query = $this->filter === 'unread' ? $user->unreadNotifications() : $user->notifications();

        $fetched = $query->latest()->limit($this->perPage)->get();

        $senderIds = $fetched->pluck('data.sender_id')->filter()->unique();
        $senders = \App\Models\User::whereIn('id', $senderIds)
            ->with(['media'])
            ->get()
            ->keyBy('id');

        return $fetched->map(function ($n) use ($senders) {
            $sender = $senders->get($n->data['sender_id'] ?? null);
            return (object) [
                'id' => $n->id,
                'sender_name' => $sender?->name ?? 'System',
                'sender_avatar' => $sender?->getAvatarUrlAttribute(),
                'is_online' => $sender ? $sender->isOnline() : false,
                'display_title' => $n->data['title'] ?? 'Update',
                'message' => str($n->data['message'] ?? '')->limit(70),
                'action_url' => $n->data['action_url'] ?? '#',
                'time' => $n->created_at->diffForHumans(short: true),
                'is_unread' => $n->unread(),
            ];
        });
    }

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    #[Computed]
    public function totalCount(): int
    {
        return auth()->user()?->notifications()->count() ?? 0;
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
    }
    public function updatedFilter(): void
    {
        $this->perPage = 10;
    }

    public function markAsRead(string $id, string $url): mixed
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification && $notification->unread()) {
            $notification->markAsRead();
        }
        return $url !== '#' && !empty($url) ? redirect($url) : null;
    }

    public function markAllAsRead(): void
    {
        auth()
            ->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);
    }

    public function clearAll(): void
    {
        auth()->user()->notifications()->delete();
    }

    #[Computed]
    public function hasMore(): bool
    {
        $total = $this->filter === 'unread' ? $this->unreadCount : $this->totalCount;
        return $this->perPage < $total;
    }
}; ?>

<section>
    @if ($variant === 'header')
        <flux:modal.trigger name="notifications_modal_{{ $variant }}">
            <flux:button class="relative" tooltip="Notifications" variant="subtle">
                <flux:icon name="bell" variant="solid" class="size-5" />

                @if ($this->unreadCount > 0)
                    <span
                        class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white ring-2 ring-white dark:ring-zinc-900">
                        {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                    </span>
                @endif
            </flux:button>
        </flux:modal.trigger>
    @else
        <flux:modal.trigger name="notifications_modal_{{ $variant }}">
            <flux:sidebar.item icon="bell" class="cursor-pointer relative"
                :badge="$this->unreadCount > 0 ? ($this->unreadCount > 99 ? '99+' : $this->unreadCount) : null"
                badge:color="red" badge:variant="solid">
                Notifications
            </flux:sidebar.item>
        </flux:modal.trigger>
    @endif

    <flux:modal name="notifications_modal_{{ $variant }}" flyout class="!p-0 w-full max-w-[400px]">
        {{-- Header --}}
        <div class="p-5 border-b border-zinc-400/25">
            <div class="flex items-center gap-4 mb-3">
                <div class="p-2 dark:bg-white/25 text-black dark:text-white bg-zinc-400/25 rounded-xl">
                    <flux:icon.bell-alert class="size-6" variant="solid" />
                </div>
                <div>
                    <flux:heading size="lg">Notifications</flux:heading>
                    <div class="flex items-center justify-between gap-4">
                        <flux:subheading size="sm">
                            {{ $this->unreadCount > 0 ? "You have $this->unreadCount unread notifications" : "You're all caught up!" }}
                        </flux:subheading>
                        @if ($this->unreadCount > 0)
                            <flux:button wire:click="markAllAsRead" variant="subtle" size="xs" icon="check-circle"
                                tooltip="Mark all read" />
                        @endif
                    </div>
                </div>
            </div>

            <flux:radio.group wire:model.live="filter" variant="segmented" size="sm" class="flex-1">
                <flux:radio value="all" label="All ({{ $this->totalCount }})" />
                <flux:radio value="unread" label="Unread ({{ $this->unreadCount }})" />
            </flux:radio.group>
        </div>

        {{-- List Section --}}
        <div class="overflow-y-auto max-h-[60vh] divide-y divide-zinc-100 dark:divide-zinc-800 scrollbar-thin">
            @forelse ($this->notifications as $notification)
                <div wire:key="notif-{{ $notification->id }}"
                    wire:click="markAsRead('{{ $notification->id }}', '{{ $notification->action_url }}')"
                    class="group relative p-4 flex gap-4 transition-colors cursor-pointer dark:hover:bg-zinc-400/10 {{ $notification->is_unread ? 'bg-indigo-50/30 dark:bg-indigo-500/5' : '' }}">
                    <div class="relative flex-shrink-0">
                        <flux:avatar src="{{ $notification->sender_avatar }}" size="sm"
                            class="rounded-lg shadow-sm" name="{{ $notification->sender_name }}"
                            :badge="$notification->is_online" badge:color="green" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <p class="text-sm text-zinc-600 dark:text-zinc-300 leading-snug">
                                <span
                                    class="font-bold text-zinc-900 dark:text-white">{{ $notification->sender_name }}</span>
                                {{ $notification->display_title }}
                            </p>
                            <span class="text-xs font-medium text-zinc-400 whitespace-nowrap pt-0.5">
                                {{ $notification->time }}
                            </span>
                        </div>

                        @if ($notification->message)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-2 leading-relaxed">
                                {{ $notification->message }}
                            </p>
                        @endif
                    </div>

                    @if ($notification->is_unread)
                        <div class="flex items-center">
                            <div class="size-2 rounded-full bg-indigo-600 animate-pulse"></div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 px-10 text-center">
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-full mb-4">
                        <flux:icon.bell class="size-10 text-zinc-300" />
                    </div>
                    <flux:heading>All clear!</flux:heading>
                    <flux:subheading>No notifications found in this category.</flux:subheading>
                </div>
            @endforelse

            @if ($this->hasMore)
                <div x-intersect="$wire.loadMore()" class="flex justify-center p-4">
                    <flux:icon.loading />
                </div>
            @endif
        </div>

        {{-- Footer --}}
        @if ($this->totalCount > 0)
            <div class="p-2 border-t border-zinc-400/25">
                <flux:button wire:click="clearAll" wire:confirm="Are you sure you want to delete all notifications?"
                    variant="subtle" size="sm" icon="trash"
                    class="w-full !text-red-500 hover:!bg-red-50 dark:hover:!bg-red-500/10">
                    Clear All Notifications
                </flux:button>
            </div>
        @endif
    </flux:modal>
</section>
