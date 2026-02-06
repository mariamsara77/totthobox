<!-- Results Section -->
<div class="mb-8">
    {{-- @if (session('message'))
        <div
            class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 flex items-center">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('message') }}
        </div>
    @endif --}}

    @include('partials.toast')


    <!-- Results Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 sm:mb-0">
            পোস্ট সমূহ
            @if ($this->posts->total())
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                    (মোট {{ bn_num($this->posts->total()) }}টি)
                </span>
            @endif
        </h2>

        @if ($this->posts->count() > 0)
            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                <span>সাজানো:</span>
                <span class="font-medium">নতুন প্রথম</span>
            </div>
        @endif
    </div>

    <!-- Posts Grid -->
    @if ($this->posts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            @foreach ($this->posts as $post)
                {{-- <x-buy-sell-post-card :post="$post" wire:key="post-{{ $post->id }}" /> --}}
                @include('partials.buy-sell.card')
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $this->posts->links() }}
        </div>
    @else
        <flux:heading>কোন পোস্ট পাওয়া যায়নি</flux:heading>
        <flux:text>আপনার সার্চ বা ফিল্টারের সাথে মিলিয়ে কোন পোস্ট নেই</flux:text>

        <flux:button wire:click="resetFilters">
            সব ফিল্টার রিসেট করুন
        </flux:button>
    @endif
</div>
