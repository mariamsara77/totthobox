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
                    'url' => 'https://totthobox.com/bangla/calendar',
                    'icon' => 'calendar',
                    'label' => 'বাংলা ক্যালেন্ডার',
                    'details' => 'ছুটি ও বিশেষ দিবসের তালিকা।',
                ],
                [
                    'url' => 'https://totthobox.com/converter/number-to-word',
                    'icon' => 'converter',
                    'label' => 'কনভার্টার',
                    'details' => 'মুদ্রা ও একক রূপান্তর টুলস।',
                ],
                [
                    'url' => 'https://totthobox.com/tools/image-resizer',
                    'icon' => 'wrench-screwdriver',
                    'label' => 'বিভিন্ন টুলস',
                    'details' => 'ছবি রিসাইজ, বয়স ক্যালকুলেটর প্রভৃতি।',
                ],
                [
                    'url' => 'https://totthobox.com/software/all',
                    'icon' => 'presentation-chart-bar',
                    'label' => 'সফটওয়্যার',
                    'details' => 'সফটওয়্যার পরিচিতি ও তথ্য।',
                ],
                [
                    // ডাটাবেজের slug ডাইনামিকভাবে যুক্ত করা হয়েছে
                    'url' => 'https://totthobox.com/contact/' . ($firstContact?->slug ?? 'police'),
                    'icon' => 'contact',
                    'label' => 'জরুরি সেবা',
                    'details' => 'হেল্পলাইন ও জরুরি নম্বর।',
                ],
                [
                    'url' => 'https://totthobox.com/bangladesh/introduction',
                    'icon' => 'bd-map',
                    'label' => 'বাংলাদেশ',
                    'details' => 'দর্শনিয় স্থান, গুণীজন ও অন্যান্য তথ্য।',
                ],
                [
                    'url' => 'https://totthobox.com/international/all-country',
                    'icon' => 'earth',
                    'label' => 'বিশ্বকোষ',
                    'details' => 'পতাকা, রাজধানী ও মুদ্রার তথ্য।',
                ],
                [
                    'url' => 'https://totthobox.com/islam/basic',
                    'icon' => 'islamic',
                    'label' => 'ইসলামিক',
                    'details' => 'নামাজ, কালেমা ও দোয়া।',
                ],
                [
                    // Sign এর ক্ষেত্রেও slug ডাইনামিক করা হলো
                    'url' => 'https://totthobox.com/signs/' . ($firstSign?->slug ?? 'all'),
                    'icon' => 'sign',
                    'label' => 'সংকেত',
                    'details' => 'স্বাস্থ্য ও ট্রাফিক সংকেত।',
                ],
                [
                    'url' => 'https://totthobox.com/ai/chat',
                    'icon' => 'sparkles',
                    'label' => 'Totthobox AI',
                    'details' => 'চ্যাটবট সহায়তা ও তথ্য সেবা।',
                ],
            ];
        });
    }
}; ?>

<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
    @foreach ($services as $service)
    <a href="{{ $service['url'] ?? '#' }}" wire:navigate
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