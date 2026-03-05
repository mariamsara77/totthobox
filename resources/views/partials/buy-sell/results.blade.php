<!-- Results Section -->
<div>






    <!-- Posts Grid -->
    @if ($this->posts->count() > 0)
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
        <livewire-global.nodata-message :title="'ক্রয়বিক্রয়'" :search="$this->search" />
    @endif
</div>