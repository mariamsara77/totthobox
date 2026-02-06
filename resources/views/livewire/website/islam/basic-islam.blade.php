<?php

use Livewire\Volt\Component;
use App\Models\BasicIslam;

new class extends Component {
    public $basicislams;
    public $showForm = false;
    public $formType = 'create';

    public function mount()
    {
        // ল্যাটেস্ট ডেটা আগে দেখানোর জন্য চাইলে sort করতে পারেন
        $this->basicislams = BasicIslam::latest()->get();
    }
}; ?>
<section class="max-w-2xl mx-auto px-4 py-8 md:py-12">
    <div class="mb-10 text-center">
        <flux:heading level="1" size="xl" class="md:text-3xl font-bold">
            ইসলামের মৌলিক জ্ঞান
        </flux:heading>

        {{-- এটি অটোমেটিক লাইট/ডার্ক মুডে অ্যাডজাস্ট হবে --}}
        <flux:separator variant="subtle" class="max-w-[100px] mx-auto mt-4" />
    </div>

    <div class="space-y-8">
        @forelse($basicislams as $basicislam)
            <div class="group">
                {{-- টাইটেলের জন্য Flux Heading ব্যবহার করা হয়েছে --}}
                <flux:heading level="2" size="lg" class="flex items-center gap-2">
                    <span class="opacity-50">•</span>
                    {{ $basicislam->title }}
                </flux:heading>

                <div class="mt-2 pl-5 md:pl-6 mb-8">
                    {{-- ডেসক্রিপশনের জন্য Flux Text ব্যবহার করা হয়েছে যা থিম অনুযায়ী কালার পরিবর্তন করে --}}
                    <flux:text class="text-sm md:text-base leading-relaxed">
                        {{ $basicislam->description }}
                    </flux:text>
                </div>

                {{-- প্রতিটি বিষয়ের মাঝে ডিভাইডার --}}
                @if (!$loop->last)
                    <flux:separator variant="subtle" />
                @endif
            </div>
        @empty
            <div class="text-center py-10">
                <flux:text>কোনো তথ্য পাওয়া যায়নি।</flux:text>
            </div>
        @endforelse
    </div>
</section>