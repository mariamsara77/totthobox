<?php

use Livewire\Volt\Component;
use App\Models\ClassLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $selectedClassId;

    public function mount(): void
    {
        $user = Auth::user();

        // Optimized check: is_student check first to avoid Role query if possible
        $isStudent = $user && ($user->is_student || $user->hasRole('student'));

        if ($isStudent && $user->class_level_id) {
            $this->selectedClassId = $user->class_level_id;
        } else {
            // Use the property helper to avoid direct cache call here
            $this->selectedClassId = $this->classes->first()?->id;
        }
    }

    /**
     * Get All Active Classes
     */
    public function getClassesProperty()
    {
        return Cache::store('redis')->remember('active_class_levels', 3600, function () {
            return ClassLevel::where('is_active', true)
                ->orderBy('order')
                ->get();
        });
    }

    /**
     * Get Subjects for the selected class
     * Optimized to remove the extra ClassLevel lookup
     */
    public function getSubjectsProperty()
    {
        if (!$this->selectedClassId) {
            return collect();
        }

        $cacheKey = "subjects_v2_for_class_{$this->selectedClassId}";

        return Cache::store('redis')->remember($cacheKey, 1800, function () {
            // Directly query the Subject model via the class_level_id 
            // This replaces: ClassLevel::find($id)->subjects()...
            return \App\Models\Subject::where('class_level_id', $this->selectedClassId)
                ->where('is_active', true)
                ->withCount('tests')
                ->get();
        });
    }

    public function selectClass($id): void
    {
        $user = Auth::user();

        // Guard clause: Students cannot change their class view
        if ($user && ($user->is_student || $user->hasRole('student'))) {
            return;
        }

        $this->selectedClassId = $id;
    }
};
?>

<div class="max-w-2xl mx-auto space-y-4">
    <div class="px-6 pt-8 pb-4 flex items-center justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" class="!text-2xl font-black">
                @auth
                    {{ (Auth::user()->is_student || Auth::user()->hasRole('student')) ? 'আমার ড্যাশবোর্ড' : 'সব ক্লাস' }}
                @else
                    পরীক্ষার প্রস্তুতি
                @endauth
            </flux:heading>
            <flux:text class="text-zinc-500">আপনার মেধা যাচাই করুন</flux:text>
        </div>

        @auth
            <flux:avatar src="{{ Auth::user()->avatar_url }}" />
        @else
            <flux:button href="{{ route('login') }}" size="sm" variant="filled" class="!rounded-full">লগইন</flux:button>
        @endauth
    </div>
    @auth
        @if(!auth()->check() || !(auth()->user()->is_student || auth()->user()->hasRole('student')))
            <div class="">
                <div class="flex gap-2 overflow-x-auto no-scrollbar px-6">
                    @foreach ($this->classes as $class)
                        <flux:button wire:click="selectClass({{ $class->id }})" size="sm" class="!rounded-full"
                            :variant="$selectedClassId == $class->id ? 'primary' : 'filled'">

                            {{ $class->name }}

                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endif
    @endauth

    <div class="">
        @auth
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <flux:heading level="3" class="font-black !text-lg">বিষয়সমূহ</flux:heading>
                    <flux:badge size="sm" color="zinc" variant="solid">{{ count($this->subjects) }} টি</flux:badge>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    @forelse($this->subjects as $subject)
                        <a href="{{ route('mcq.subject', $subject->slug) }}"
                            class="group block active:scale-[0.98] transition-all">
                            <div
                                class="flex items-center p-4 rounded-[1.5rem] bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/50">
                                <div
                                    class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center text-white shadow-lg">
                                    <flux:icon.book-open variant="outline" />
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100">{{ $subject->name }}</h4>
                                    <p class="text-xs text-zinc-500">{{ $subject->tests_count }} টি এমসিকিউ টেস্ট</p>
                                </div>
                                <flux:icon.chevron-right variant="mini" class="text-zinc-400" />
                            </div>
                        </a>
                    @empty
                        <livewire:global.nodata-message :title="'বিষয়'" />
                    @endforelse
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 px-4">
                <div
                    class="w-full max-w-lg text-center p-8 rounded-[2.5rem] bg-zinc-400/10 border border-zinc-200/50 dark:border-zinc-800/50 shadow-sm">
                    <div
                        class="w-20 h-20 bg-white dark:bg-zinc-800 rounded-3xl shadow-lg flex items-center justify-center mx-auto">
                        <flux:icon.lock-closed size="xl" class="text-indigo-600" />
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-xl font-black text-zinc-900 dark:text-zinc-100">পরীক্ষা দিতে লগইন করুন</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            এমসিকিউ টেস্টে অংশগ্রহণ করতে আপনাকে প্রথমে লগইন করতে হবে। এরপর প্রোফাইল এডিট অপশনে গিয়ে <span
                                class="font-bold text-indigo-600">Student</span> সিলেক্ট করে আপনার <span
                                class="font-bold text-indigo-600">Class</span> সেট করুন।
                        </p>
                    </div>

                    <div class="pt-4 space-y-3">
                        <flux:button href="{{ route('login') }}" variant="filled"
                            class="w-full !rounded-2xl !py-4 font-bold shadow-lg shadow-primary-200">লগইন করুন</flux:button>
                        <flux:button href="{{ route('register') }}" variant="ghost" class="w-full !rounded-2xl font-bold">
                            নতুন
                            অ্যাকাউন্ট তৈরি করুন</flux:button>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</div>