<?php

use App\Models\TourismBd;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public $tourism;

    public $typeLabels = [
        'historical' => 'ঐতিহাসিক ও প্রত্নতাত্ত্বিক',
        'heritage' => 'রাজপ্রাসাদ ও জমিদার বাড়ি',
        'natural' => 'প্রাকৃতিক সৌন্দর্য',
        'waterfall' => 'ঝর্ণা ও জলপ্রপাত',
        'beach' => 'সমুদ্র সৈকত',
        'hill_station' => 'পাহাড় ও পার্বত্য এলাকা',
        'forest' => 'বন ও বন্যপ্রাণী',
        'religious' => 'ধর্মীয় ও পবিত্র স্থান',
        'cultural' => 'সাংস্কৃতিক ও জাদুঘর',
        'adventure' => 'অ্যাডভেঞ্চার ও ট্র্যাকিং',
        'resort' => 'রিসোর্ট ও বিনোদন কেন্দ্র',
        'riverine' => 'হাওর ও নদীকেন্দ্রিক',
        'picnic' => 'পিকনিক স্পট',
    ];

    public function mount($slug)
    {
        $cacheKey = 'tourism_show_' . $slug;

        $this->tourism = Cache::remember($cacheKey, now()->addDay(), function () use ($slug) {
            return TourismBd::with(['division', 'district', 'thana', 'creator', 'media'])
                ->where('slug', $slug)
                ->where('status', 1)
                ->firstOrFail();
        });

        $sessionKey = 'tourism_view_' . $this->tourism->id;
        if (!session()->has($sessionKey)) {
            $this->tourism->increment('view_count');
            session()->put($sessionKey, true);
        }
    }
}; ?>

@php
    $firstMediaUrl = $tourism->getFirstMediaUrl('images');
    $pageTitle = $tourism->title . ' | পর্যটন এলাকা';
    $pageDesc = Str::limit(strip_tags($tourism->description), 150);
    $keywords = $tourism->title . ', ' . ($tourism->district->name ?? '') . ', ' . ($tourism->division->name ?? '') . ', Totthobox';
@endphp

<x-seo :title="$pageTitle" :description="$pageDesc" :keywords="$keywords" :image="$firstMediaUrl" />

<section class="max-w-2xl mx-auto space-y-4">
    <flux:button href="{{ route('bangladesh.tourism') }}" variant="subtle" icon="arrow-long-left" size="xs">
    </flux:button>

    <header class="space-y-2">
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-2">
                <flux:heading size="xl" level="1">{{ $tourism->title }}</flux:heading>

                {{-- এখানে tourism_type চেক করছি এবং অ্যারে থেকে বাংলা মান নিচ্ছি --}}
                @if($tourism->tourism_type && array_key_exists($tourism->tourism_type, $this->typeLabels))
                    <div>
                        <flux:badge size="sm" color="zinc" variant="outline">
                            {{ $this->typeLabels[$tourism->tourism_type] }}
                        </flux:badge>
                    </div>
                @endif
            </div>

            @if($tourism->creator)
                <div>
                    <flux:tooltip toggleable>
                        <flux:button icon="user" size="sm" variant="subtle" />

                        <flux:tooltip.content class="rounded-2xl! space-y-4">
                            <flux:heading>তথ্য প্রদানকারী</flux:heading>
                            <flux:subheading size="sm">এই কন্টেন্ট তৈরিতে অবদান রেখেছেন</flux:subheading>

                            <div class="max-h-80 max-w-80 overflow-y-auto space-y-4 custom-scrollbar">
                                <div class="relative p-2 group rounded-2xl bg-zinc-400/25 transition-all">
                                    <div class="flex items-start gap-3">
                                        <div class="relative">
                                            <flux:avatar src="{{ $tourism->creator->avatar_url }}" size="md" badge
                                                badge:color="{{ $tourism->creator->isOnline() ? 'green' : 'zinc' }}" />
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <flux:heading>{{ $tourism->creator->name }}</flux:heading>
                                                @if ($tourism->creator->email_verified_at)
                                                    <flux:icon.check-badge class="size-4" variant="solid" />
                                                @endif
                                            </div>
                                            <flux:text size="sm">
                                                {{ $tourism->creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                            </flux:text>
                                        </div>
                                    </div>
                                    <flux:separator class="my-2" />
                                    <div class="flex items-center justify-between">
                                        <flux:text size="sm">
                                            একটিভ:
                                            {{ bn_num($tourism->creator->last_active_at?->diffForHumans()) ?? 'অজানা' }}
                                        </flux:text>
                                        <flux:button href="{{ route('users.show', $tourism->creator->slug) }}"
                                            variant="ghost" size="xs" icon="arrow-right"
                                            class="group-hover:translate-x-1 transition-transform" />
                                    </div>
                                </div>
                            </div>
                            <flux:text>আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।</flux:text>
                        </flux:tooltip.content>
                    </flux:tooltip>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 justify-between text-zinc-500 text-sm">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1">
                    <flux:icon.map-pin variant="mini" />
                    {{ $tourism->thana->name ?? 'N/A' }}, {{ $tourism->district->name ?? 'N/A' }}
                </span>
                <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                <span class="text-xs uppercase tracking-wider font-medium">
                    {{ $tourism->division->name ?? '' }}
                </span>
            </div>
            <span class="flex items-center gap-1">
                <flux:icon.eye variant="mini" />
                {{ bn_num($tourism->view_count) }}
            </span>
        </div>
    </header>

    @if($tourism->media->isNotEmpty())
        <div class="rounded-2xl overflow-hidden shadow-sm">
            <flux:media :media="$tourism->media" />
        </div>
    @endif

    <div>
        <flux:text>{!! $tourism->description !!}</flux:text>
    </div>
</section>