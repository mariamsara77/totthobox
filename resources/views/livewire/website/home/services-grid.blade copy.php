<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cache;
use App\Models\ContactCategory;
use App\Models\SignCategory;

new class extends Component {
    public array $services = [];

    public function mount(): void
    {
        $this->services = Cache::remember('home_services_grid', now()->addDay(), function () {
            $firstContact = ContactCategory::query()->active()->first();
            $firstSign = SignCategory::query()->active()->first();

            return [
                [
                    'route' => 'calendar.calendar',
                    'icon' => 'calendar',
                    'label' => 'বাংলা ক্যালেন্ডার',
                    'details' => 'ছুটি ও বিশেষ দিবসের তালিকা।',
                ],
                [
                    'route' => 'converter.number-to-word',
                    'icon' => 'converter',
                    'label' => 'কনভার্টার',
                    'details' => 'মুদ্রা ও একক রূপান্তর টুলস।',
                ],
                [
                    'route' => 'tools.image-resizer',
                    'icon' => 'wrench-screwdriver',
                    'label' => 'বিভিন্ন টুলস',
                    'details' => 'ছবি রিসাইজ, বয়স ক্যালকুলেটর প্রভৃতি।',
                ],
                [
                    'route' => 'software.all',
                    'icon' => 'presentation-chart-bar',
                    'label' => 'সফটওয়্যার',
                    'details' => 'সফটওয়্যার পরিচিতি ও তথ্য।',
                ],
                [
                    'route' => 'contact.number',
                    'slug' => $firstContact?->slug ?? 'police',
                    'icon' => 'contact',
                    'label' => 'জরুরি সেবা',
                    'details' => 'হেল্পলাইন ও জরুরি নম্বর।',
                ],
                [
                    'route' => 'bangladesh.introduction',
                    'icon' => 'bd-map',
                    'label' => 'বাংলাদেশ',
                    'details' => 'দর্শনিয় স্থান, গুণীজন ও অন্যান্য তথ্য।',
                ],
                [
                    'route' => 'international.all-country',
                    'icon' => 'earth',
                    'label' => 'বিশ্বকোষ',
                    'details' => 'পতাকা, রাজধানী ও মুদ্রার তথ্য।',
                ],
                [
                    'route' => 'islam.basicislam',
                    'icon' => 'islamic',
                    'label' => 'ইসলামিক',
                    'details' => 'নামাজ, কালেমা ও দোয়া।',
                ],
                [
                    'route' => 'education.child.practice',
                    'icon' => 'child-edu',
                    'label' => 'শিশুশিক্ষা',
                    'details' => 'বর্ণমালা ও মৌলিক শিক্ষা।',
                ],
                [
                    'route' => 'signs.sign.all',
                    // 'slug' => 'signs.sign.all',
                    'icon' => 'sign',
                    'label' => 'সংকেত',
                    'details' => 'স্বাস্থ্য ও ট্রাফিক সংকেত।',
                ],
                // [
                //     'route' => 'news.headlines',
                //     'icon' => 'newspaper',
                //     'label' => 'সর্বশেষ সংবাদ',
                //     'details' => 'দেশ-বিদেশের গুরুত্বপূর্ণ শিরোনাম।',
                // ],
                [
                    'route' => 'ai.chat.show',
                    'icon' => 'sparkles',
                    'label' => 'Totthobox AI',
                    'details' => 'চ্যাটবট সহায়তা ও তথ্য সেবা।',
                ],
            ];
        });
    }
}; ?>

<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
    @foreach ($services as $service)
        <a @if (isset($service['route']) && Route::has($service['route']))
            href="{{ isset($service['slug']) ? route($service['route'], ['slug' => $service['slug']]) : route($service['route']) }}"
        wire:navigate @else href="#" @endif
            class="relative flex flex-col items-center h-full p-4 text-center transition-all duration-200 border border-transparent group rounded-3xl bg-gray-50 dark:bg-white/5 hover:border-zinc-600/50 hover:bg-gray-400/25">
            <div class="mb-3 transition-transform duration-200 transform group-hover:scale-110">
                <flux:icon name="{{ $service['icon'] }}" class="text-black size-14 dark:text-white" />
            </div>

            <flux:heading size="lg" class="transition-colors group-hover:font-semibold">
                {{ $service['label'] }}
            </flux:heading>

            <span class="mt-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                {{ $service['details'] }}
            </span>
        </a>
    @endforeach
</div>