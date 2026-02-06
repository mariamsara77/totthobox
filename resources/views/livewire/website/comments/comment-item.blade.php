<div class="comment-item" wire:key="comment-{{ $comment->id }}">
    <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
        <!-- Left border for nested comments -->
        @if ($comment->replies && $comment->replies->count() > 0)
            <div class="hidden sm:block absolute left-0 top-12 bottom-0 w-px bg-zinc-300/50 ml-6"></div>
        @endif

        <!-- Profile Avatar -->
        <div class="flex-shrink-0 mt-1">
            <flux:profile initials="{{ substr($comment->user->name, 0, 2) }}" avatar="{{ $comment->user->avatar }}"
                :chevron="false" circle size="sm" class="w-10 h-10" />
        </div>

        <!-- Comment Content Container -->
        <div class="flex-1 min-w-0 w-full">
            <flux:card>
                <!-- Comment Card -->

                <!-- Header with user info and actions -->
                <div class="">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <flux:heading size="md" class="font-semibold truncate">
                                {{ $comment->user->name }}
                            </flux:heading>
                        </div>

                        <!-- Actions Menu -->
                        @auth
                            @if (auth()->id() === $comment->user_id || (auth()->user()->is_admin ?? false))
                                <div class="relative flex-shrink-0" wire:key="comment-menu-{{ $comment->id }}">
                                    <flux:button size="xs" variant="ghost" icon="ellipsis-vertical"
                                        wire:click="toggleMenu('menu-{{ $comment->id }}')" class="!p-1" />

                                    @if ($openMenus['menu-' . $comment->id] ?? false)
                                        <div class="absolute right-6 top-0 z-40 min-w-[100px]">
                                            <flux:button wire:click="deleteComment" variant="danger" size="sm" class="w-full"
                                                icon="trash" wire:confirm="Are you sure you want to delete this comment?">
                                                Delete
                                            </flux:button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- Comment Body -->
                <div class="">
                    <flux:text class="break-words whitespace-pre-wrap overflow-hidden">
                        {{ $comment->content }}
                    </flux:text>
                </div>

            </flux:card>
            <!-- Footer Actions -->
            <div class="mt-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <!-- Left side: Reply button and reactions -->
                    <div class="flex items-center flex-wrap gap-2">
                        @auth
                            <flux:button variant="ghost" size="xs" icon="arrow-turn-up-left" wire:click="toggleReplyForm"
                                class="!px-2 !py-1 text-sm">
                                উত্তর দিন
                            </flux:button>
                        @endauth

                        <!-- Reactions Container -->
                        <div class="flex items-center gap-1">
                            <!-- Like Button -->
                            <flux:button variant="ghost" wire:click="react('like')" size="xs"
                                class="!px-2 !py-1 min-w-[60px]">
                                <div class="flex items-center gap-1.5"
                                    :class="{ 'text-blue-600': {{ $comment->hasReaction('like') ? 'true' : 'false' }} }"
                                    x-data="{ reacted: {{ $comment->hasReaction('like') ? 'true' : 'false' }} }"
                                    x-on:reaction-updated.window="reacted = event.detail.type == 'like' ? true : false">
                                    <flux:icon name="thumb-up" class="w-4 h-4" />
                                    <span class="text-xs font-medium">
                                        {{ $comment->countReaction('like') }}
                                    </span>
                                </div>
                            </flux:button>

                            <!-- Dislike Button -->
                            <flux:button variant="ghost" wire:click="react('dislike')" size="xs"
                                class="!px-2 !py-1 min-w-[60px]">
                                <div class="flex items-center gap-1.5"
                                    :class="{ 'text-red-600': {{ $comment->hasReaction('dislike') ? 'true' : 'false' }} }"
                                    x-data="{ reacted: {{ $comment->hasReaction('dislike') ? 'true' : 'false' }} }"
                                    x-on:reaction-updated.window="reacted = event.detail.type == 'dislike' ? true : false">
                                    <flux:icon name="thumb-down" class="w-4 h-4" />
                                    <span class="text-xs font-medium">
                                        {{ $comment->countReaction('dislike') }}
                                    </span>
                                </div>
                            </flux:button>
                        </div>
                        <flux:text size="xs" class="text-zinc-500 mt-0.5">
                            {{ $comment->created_at->diffForHumans() }}
                        </flux:text>
                    </div>
                </div>
            </div>


            <!-- Reply Form -->
            @if ($showReplyForm)
                <div class="mt-3 ml-0 sm:ml-4">
                    <flux:card>
                        <flux:field>
                            <flux:textarea wire:model="replyContent" resize="none" placeholder="Write your reply..."
                                autofocus rows="3" class="min-h-[80px] max-h-[200px]">
                            </flux:textarea>

                            <div class="flex items-center justify-end gap-2 mt-3">
                                <flux:button size="sm" variant="ghost" class="!rounded-full !px-4"
                                    wire:click="toggleReplyForm">
                                    বাতিল
                                </flux:button>

                                <flux:button size="sm" variant="primary" color="black" class="!rounded-full !px-4"
                                    wire:click="submitReply">
                                    উত্তর দিন
                                </flux:button>
                            </div>
                        </flux:field>
                    </flux:card>
                </div>
            @endif

            <!-- Replies List -->
            @if ($comment->replies && $comment->replies->count() > 0)
                <div class="mt-4 ml-0 sm:ml-8 space-y-3">
                    @foreach ($comment->replies as $reply)
                        <div class="relative">
                            @if (!$loop->last)
                                <div class="hidden sm:block absolute left-[-32px] top-0 bottom-0 w-px bg-zinc-300/50">
                                </div>
                            @endif
                            <livewire:website.comments.comment-item :key="'reply-' . $reply->id" :comment="$reply" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>


    <style>
        .comment-item {
            position: relative;
            overflow: visible;
        }

        /* Ensure proper text wrapping and prevent overflow */
        .flux-text {
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .comment-item {
                padding-left: 0.5rem;
            }

            .flux-heading {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .flux-button {
                font-size: 0.75rem;
            }
        }

        /* Prevent horizontal scrolling */
        .comment-item * {
            max-width: 100%;
        }
    </style>
</div>