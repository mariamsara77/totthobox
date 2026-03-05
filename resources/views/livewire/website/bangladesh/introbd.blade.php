<?php

use Livewire\Volt\Component;
use App\Models\IntroBd;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed; // এই লাইনটি মিসিং ছিল

new class extends Component {
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    #[On('networkStatusChanged')]
    public function updateStatus($data)
    {
        if (!$data['online']) {
            session()->flash('warning', 'আপনি অফলাইন আছেন, কিছু ফিচার কাজ নাও করতে পারে।');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ইউনিক ক্রিয়েটরদের লিস্ট বের করার জন্য
    #[Computed]
    public function creators()
    {
        return IntroBd::query()
            ->with('creator.media')
            ->whereNotNull('created_by')
            ->get()
            ->pluck('creator')
            ->filter()
            ->unique('id')
            ->sortBy('id') // ছোট আইডি সবার আগে আসবে (Oldest First)
            ->values();
    }
    public function groupedIntros()
    {
        return IntroBd::query()
            ->with(['media', 'creator']) // creator ও এখানে লোড করে রাখা ভালো
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->latest('id')
            ->get()
            ->groupBy('intro_category');
    }

    public function resetFilter()
    {
        $this->reset('search');
        $this->resetPage();
    }
}; ?>

<x-seo title="বাংলাদেশের পরিচিতি"
    description="Totthobox-এ পাবেন বাংলাদেশ জেলা তথ্য, ইসলামিক শিক্ষা, স্বাস্থ্য জ্ঞান এবং প্রয়োজনীয় সকল সেবা।"
    keywords="তথ্যবক্স, সার্ভিস পোর্টাল, বাংলাদেশ, ইসলামিক সেবা" />
<section class="max-w-2xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <flux:heading size="xl">বাংলাদেশের পরিচিতি ও তথ্য</flux:heading>

        <div class="flex items-center gap-2">
            {{-- Main Creator Info Icon --}}
            <flux:tooltip toggleable class="">
                <flux:button icon="information-circle" size="sm" variant="ghost" />

                <flux:tooltip.content
                    class="max-w-[22rem] overflow-hidden border rounded-2xl! bg-white! dark:bg-zinc-700!">
                    {{-- Header of Tooltip --}}
                    <div class="p-2">
                        <flux:heading size="sm">তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:subheading size="xs">এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন</flux:subheading>
                    </div>

                    {{-- Scrollable List Area --}}
                    <div class="max-h-[350px] overflow-y-auto space-y-3 p-2 custom-scrollbar">
                        @foreach($this->creators as $creator)
                            <div class="relative p-2 group rounded-2xl bg-zinc-400/25 transition-all">
                                <div class="flex items-start gap-3">
                                    {{-- Avatar with Online Indicator --}}
                                    <div class="relative">
                                        <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                            badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}" />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <flux:heading>
                                                {{ $creator->name }}
                                            </flux:heading>
                                            @if ($creator->email_verified_at)
                                                <flux:icon.check-badge class="size-4" variant="solid" />
                                            @endif
                                            {{-- <flux:badge size="xs" variant="pill" class="shrink-0">
                                                {{ $creator->district?->name ?? 'বাংলাদেশ' }}
                                            </flux:badge> --}}
                                        </div>

                                        <flux:text size="sm">
                                            {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                        </flux:text>
                                        {{--
                                        @if($creator->bio)
                                        <flux:text size="sm">
                                            {!! $creator->bio !!}
                                        </flux:text>
                                        @endif --}}
                                    </div>
                                </div>
                                <flux:separator class="my-2" />
                                {{-- Hover Action (Optional: Profile Link) --}}
                                <div class="flex items-center justify-between">
                                    <flux:text size="xs">
                                        একটিভ: {{ bn_num($creator->last_active_at?->diffForHumans()) ?? 'অজানা' }}
                                    </flux:text>
                                    <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost" size="xs"
                                        icon="arrow-right" class="group-hover:translate-x-1 transition-transform" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    <div class="text-center">
                        <flux:text size="xs">
                            আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                        </flux:text>
                    </div>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </div>
    {{-- Header & Search --}}

    <div class="flex items-center gap-3">
        <flux:input wire:model.live.debounce.400ms="search" size="sm" placeholder="তথ্য খুঁজুন (যেমন: রাজধানী, নদী...)"
            icon="magnifying-glass" variant="filled" class="rounded-xl! flex-1" />

        @if($search)
            <flux:button wire:click="resetFilter" size="sm" variant="ghost" icon="x-mark">মুছে ফেলুন</flux:button>
        @endif
    </div>

    {{-- Content List with Grouping --}}
    @forelse ($this->groupedIntros() as $category => $intros)

        {{-- Category Badge --}}
        <div class="flex justify-center">
            <flux:badge size="lg" color="zinc" variant="solid">
                {{ $category ?: 'সাধারণ তথ্য' }}
            </flux:badge>
        </div>
        @foreach ($intros as $item)

            {{-- Title --}}
            <header>
                <flux:heading size="lg" level="2" class="font-bold">
                    {{ $item->title }}
                </flux:heading>
            </header>

            {{-- Grid Gallery --}}
            @if($item->hasMedia('intro_images'))
                <flux:media :media="$item->getMedia('intro_images')" />
            @endif

            <flux:longtext :content="$item->description" />
            <flux:separator variant="faint" />
        @endforeach

    @empty
        <livewire:global.nodata-message :title="'বাংলাদেশের তথ্য'" :search="$search" />
    @endforelse
</section>