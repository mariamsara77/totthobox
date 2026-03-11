<?php

use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $perPage = 5;
    public $page = 1;

    // নতুন ডাটা লোড করার মেথড
    public function loadMore()
    {
        $this->page++;
    }

    public function with(): array
    {
        return [
            'activities' => Activity::where('causer_id', Auth::id())
                ->latest()
                ->take($this->page * $this->perPage) // যতগুলো পেজ ততগুলো ডাটা
                ->get(),
            
            'hasMore' => Activity::where('causer_id', Auth::id())->count() > ($this->page * $this->perPage)
        ];
    }
};
?>

<section class="max-w-3xl mx-auto py-8" x-data @scroll-to-top.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <div class="mb-8">
        <flux:heading level="1" size="xl">আমার সাম্প্রতিক কার্যক্রম</flux:heading>
        <flux:subheading>আপনার অ্যাকাউন্টের সকল অ্যাক্টিভিটি এখানে তালিকাভুক্ত রয়েছে।</flux:subheading>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
        @forelse($activities as $activity)
            <div class="flex gap-4 p-6 border-b border-zinc-100 dark:border-zinc-700 last:border-0 hover:bg-zinc-50 dark:hover:bg-zinc-700/20 transition-all duration-300">
                <div class="flex-shrink-0">
                    <div class="size-10 rounded-full flex items-center justify-center 
                        {{ $activity->description === 'created' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }}">
                        @if($activity->description === 'created') 
                            <flux:icon.plus variant="mini" />
                        @elseif($activity->description === 'updated') 
                            <flux:icon.pencil variant="mini" />
                        @else 
                            <flux:icon.check variant="mini" /> 
                        @endif
                    </div>
                </div>

                <div class="flex-grow">
                    <div class="text-sm text-zinc-700 dark:text-zinc-200">
                        আপনি নতুন একটি 
                        <span class="font-semibold text-zinc-900 dark:text-white">
                            {{ class_basename($activity->subject_type) }}
                        </span> 
                        {{ $activity->description === 'created' ? 'তৈরি করেছেন' : 'আপডেট করেছেন' }}।
                    </div>
                    <div class="text-xs text-zinc-400 mt-1.5 flex items-center gap-1.5">
                        <flux:icon.clock variant="mini" class="size-3" />
                        {{ ($activity->created_at->diffForHumans()) }}
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-zinc-500">
                <flux:icon.inbox class="size-12 mx-auto mb-3 opacity-50" />
                <p>এখনও কোনো কার্যক্রম রেকর্ড করা হয়নি।</p>
            </div>
        @endforelse
    </div>

   <div x-data="{
        observe() {
            let observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        @this.call('loadMore');
                    }
                })
            }, { root: null });

            observer.observe(this.$el);
        }
    }" x-init="observe" class="mt-6 text-center">
        @if($hasMore)
            <div class="text-zinc-500">লোড হচ্ছে...</div>
        @endif
    </div>
</section>