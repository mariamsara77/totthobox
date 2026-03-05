<?php

use Livewire\Volt\Component;
use App\Models\BasicIslam;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    // পাবলিক প্রপার্টিতে ডাটা রাখা এড়িয়ে চলুন যদি সেটা আপডেট করার প্রয়োজন না হয়
    public $showForm = false;
    public $formType = 'create';

    /**
     * Computed Property ব্যবহার করলে এটি মেমরিতে ডাটা ধরে রাখে না, 
     * শুধু যখন রেন্ডার হয় তখন কল হয়।
     */
    public function basicislams()
    {
        return Cache::remember('basic_islams_list', 3600, function () {
            // শুধু টাইটেল এবং ডেসক্রিপশন নিন, অপ্রয়োজনীয় কলাম বাদ দিন
            return BasicIslam::select('title', 'description')
                ->latest()
                ->get();
        });
    }

    // ডাটাবেসে নতুন কিছু সেভ হলে ক্যাশ ক্লিয়ার করতে হবে
    public function clearCache()
    {
        Cache::forget('basic_islams_list');
    }
}; ?>

<section class="max-w-2xl mx-auto space-y-4">
    <div class="text-center">
        <flux:heading level="1" size="xl">ইসলামের মৌলিক জ্ঞান</flux:heading>
        <flux:subheading level="2">ইসলামের মূল ভিত্তি ও মৌলিক জ্ঞান সম্পর্কে সংক্ষিপ্ত ধারণা</flux:subheading>
    </div>

    <div class="space-y-8">
        {{-- Computed property কল করার সময় $this->basicislams() এভাবে করতে হয় --}}
        @forelse($this->basicislams() as $basicislam)
            <div class="group">
                <flux:heading level="2" size="lg" class="flex items-center gap-2">
                    <span class="opacity-50">•</span>
                    {{ $basicislam->title }}
                </flux:heading>

                <div class="mt-2 pl-5 md:pl-6 mb-8">
                    <flux:text class="text-sm md:text-base leading-relaxed">
                        {{ $basicislam->description }}
                    </flux:text>
                </div>

                @if (!$loop->last)
                    <flux:separator variant="subtle" />
                @endif
            </div>
        @empty
            <livewire:global.nodata-message :title="'ইসলামের মৌলিক জ্ঞান'" />
        @endforelse
    </div>
</section>