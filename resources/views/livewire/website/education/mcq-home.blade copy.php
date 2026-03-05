<?php

use Livewire\Volt\Component;
use App\Models\ClassLevel;
use Illuminate\Support\Facades\Cache;
use function Livewire\Volt\{computed};

new class extends Component {
    public $selectedClassId;

    public function mount()
    {
        // প্রথম ক্লাসটিকে ডিফল্ট হিসেবে সেট করা
        $this->selectedClassId = $this->classes->first()?->id;
    }

    // Computed Property: এটি ক্যাশ হ্যান্ডেল করবে এবং ব্লেডে $this->classes দিবে
    public function getClassesProperty()
    {
        // Redis এ ডাটা ১ ঘণ্টার জন্য সেভ হবে
        return Cache::store('redis')->remember('active_class_levels', 3600, function () {
            return ClassLevel::where('is_active', true)->orderBy('order')->get();
        });
    }

    // Subjects এর জন্য আলাদা Computed Property (এটি বেশি ফাস্ট)
    public function getSubjectsProperty()
    {
        if (!$this->selectedClassId)
            return [];

        return ClassLevel::find($this->selectedClassId)
                ?->subjects()
            ->where('is_active', true)
            ->withCount('tests')
            ->get();
    }

    public function selectClass($id)
    {
        $this->selectedClassId = $id;
    }
};
?>

<div class="max-w-md mx-auto space-y-6 pb-10">
    <div class="px-4 pt-4">
        <flux:heading size="xl" class="!text-2xl font-extrabold tracking-tight">আপনার ক্লাস বেছে নিন</flux:heading>
        <flux:subheading>আপনার পড়ার বিষয়গুলো নিচে তালিকাভুক্ত করা হয়েছে</flux:subheading>
    </div>

    <div class="sticky top-0 backdrop-blur-md z-10 py-4 border-b border-zinc-100 dark:border-zinc-800">
        <div class="flex gap-3 overflow-x-auto no-scrollbar px-4">
            @foreach ($this->classes as $class)
                <flux:button wire:click="selectClass({{ $class->id }})" size="sm" class="!rounded-full"
                    :variant="$selectedClassId == $class->id ? 'primary' : 'filled'">

                    {{ $class->name }}

                </flux:button>
            @endforeach
        </div>
    </div>

    <div class="px-4 space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading level="2" class="!text-lg font-bold">বিষয়সমূহ</flux:heading>
            <flux:badge size="sm" color="zinc" variant="solid" class="rounded-lg">
                {{ count($this->subjects) }} টি বিষয়
            </flux:badge>
        </div>

        <div class="space-y-3" wire:loading.class="opacity-50 transition-opacity">
            @forelse($this->subjects as $subject)
                <a href="{{ route('mcq.subject', $subject->id) }}" class="group block">
                    <div
                        class="flex items-center justify-between p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/50 dark:border-zinc-700/50 hover:bg-white dark:hover:bg-zinc-800 hover:shadow-xl hover:shadow-zinc-200/50 dark:hover:shadow-none transition-all active:scale-[0.98]">

                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-200 dark:shadow-none">
                                <flux:icon.book-open class="w-6 h-6" />
                            </div>

                            <div>
                                <h4 class="font-bold text-zinc-800 dark:text-zinc-100 leading-tight">
                                    {{ $subject->name }}
                                </h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 flex items-center gap-1">
                                    <flux:icon.clipboard-document-list variant="mini" class="w-3 h-3" />
                                    মোট টেস্ট: {{ $subject->tests_count }} টি
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-zinc-700 p-2 rounded-xl border border-zinc-100 dark:border-zinc-600 shadow-sm group-hover:bg-primary-50 transition-colors">
                            <flux:icon.chevron-right variant="mini" class="text-zinc-400 group-hover:text-primary-600" />
                        </div>
                    </div>
                </a>
            @empty
                <div
                    class="py-16 text-center bg-zinc-50 dark:bg-zinc-800/30 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                    <div
                        class="bg-zinc-100 dark:bg-zinc-800 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon.magnifying-glass class="text-zinc-400" />
                    </div>
                    <flux:text>এই ক্লাসের জন্য কোনো বিষয় খুঁজে পাওয়া যায়নি।</flux:text>
                </div>
            @endforelse
        </div>
    </div>


    <style>
        /* কাস্টম স্ক্রলবার হাইড করার জন্য */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

</div>