<div class="" x-data="{ open: false }">
    <!-- Header -->
    <div class="">
        <flux:heading level="2" class="flex items-center font-semibold mb-1">
            <flux:icon icon="chat-bubble-left-right" class="h-5 w-5 mr-2 text-gray-600 dark:text-gray-400" />
            কমেন্টস
            <flux:badge size="sm" color="blue" class="ml-2">
                {{ $comments->total() }}
            </flux:badge>
        </flux:heading>
        <flux:text>
            আপনার মতামত শেয়ার করুন এবং আলোচনা শুরু করুন!
        </flux:text>
    </div>

    <!-- Comment Form Component -->
    <div class="my-8">
        @livewire('website.comments.comment-form', ['model' => $model])
    </div>

    <div class="w-full text-center my-4">
        <flux:button @click="open = !open" variant="ghost" size="sm" icon:trailing="chevron-down">
            কমেন্টস পড়ুন
        </flux:button>
    </div>


    <div x-show="open">
        <!-- Comments List -->
        @if ($comments->count() > 0)
            <div class="space-y-6">
                @foreach ($comments as $comment)
                    <div wire:key="comment-wrapper-{{ $comment->id }}">
                        @livewire('website.comments.comment-item', ['comment' => $comment], key('comment-' . $comment->id))
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div>
                {{ $comments->links() }}
            </div>
        @else
            <div class="text-center py-12 rounded-lg">
                <flux:icon icon="chat-bubble-left-right" class="h-12 w-12 mx-auto mb-4 text-gray-400 dark:text-gray-500" />
                <flux:heading>
                    এখনও কোনো মন্তব্য নেই
                </flux:heading>
                <flux:text>
                    আপনার মতামত শেয়ার করা প্রথম ব্যক্তি হোন! উপরে মন্তব্য যোগ করে আলোচনা শুরু করুন।
                </flux:text>
            </div>
        @endif
    </div>
</div>