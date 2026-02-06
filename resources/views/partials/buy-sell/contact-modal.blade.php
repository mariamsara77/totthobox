<!-- Contact Modal -->
<flux:modal name="contactModal" wire:model="showContactModal" class="w-full max-w-lg" close-button>
    @if ($selectedPost)
        <div class="">
            <div class="text-center mb-5">
                <h3 class="text-xl font-semibold">
                    পণ্যের বিস্তারিত তথ্য
                </h3>
            </div>

            @if ($selectedPost->images && $selectedPost->images->count() > 0)
                <div data-viewer-gallery="post" class="flex overflow-x-auto gap-2">
                    @foreach ($selectedPost->images as $image)
                        <img src="{{ asset($image->path) }}"
                            class="w-full h-full object-cover viewer-image transition-all duration-500 ease-in-out cursor-pointer"
                            loading="lazy" alt="Post Image">
                    @endforeach
                </div>
            @endif

            <!-- Description -->
            <div class="mb-4 p-4 rounded-lg border border-zinc-400/25">
                <h4 class="font-medium text-gray-900 dark:text-white mb-2">বর্ণনা</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    {!! nl2br(e($selectedPost->description ?? 'কোনো বর্ণনা নেই')) !!}
                </p>
            </div>

            <!-- Note -->
            @if ($selectedPost->note)
                <div class="mb-4 p-4 rounded-lg border border-zinc-400/25">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">নোট</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        {!! nl2br(e($selectedPost->note)) !!}
                    </p>
                </div>
            @endif

            <!-- Condition & Stock -->
            <div class="grid grid-cols-2 gap-4 p-4 rounded-lg border border-zinc-400/25">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">অবস্থা</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ ucfirst($selectedPost->condition ?? 'অজানা') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">স্টক</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $selectedPost->stock ?? '0' }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</flux:modal>
