<?php

use App\Models\BasicIslam;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    public string $search = '';

    #[Computed]
    public function allData()
    {
        return Cache::remember('basic_islams_list', now()->addHour(), function () {
            return BasicIslam::query()->with('media')->latest()->get();
        });
    }

    #[Computed]
    public function filteredData()
    {
        if (blank($this->search)) {
            return $this->allData;
        }

        $term = mb_strtolower($this->search, 'UTF-8');

        return $this->allData->filter(function ($item) use ($term) {
            return str_contains(mb_strtolower($item->title ?? '', 'UTF-8'), $term) || str_contains(mb_strtolower(strip_tags($item->description ?? ''), 'UTF-8'), $term);
        });
    }

 #[Computed]
public function creators()
{
    return Cache::remember('basic_islams_contributors', now()->addHour(), function () {
        $causerIds = Activity::query()
            ->where('subject_type', BasicIslam::class)
            ->whereNotNull('causer_id')
            ->distinct()
            ->pluck('causer_id');

        return User::query()
            ->whereIn('id', $causerIds)
            // ডাটাবেসে avatar কলাম না থাকলে এখান থেকে 'avatar' বাদ দিন
            ->select(['id', 'name', 'slug', 'email_verified_at', 'last_active_at', 'profession']) 
            ->with(['media'])
            ->get();
    });
}

    public function resetFilter(): void
    {
        $this->reset('search');
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
BASIC ISLAM – LIST PAGE
SEO Optimized · AdSense Policy Compliant · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="ইসলামের মৌলিক জ্ঞান | ঈমান, নামাজ, যাকাত, হজ, রোজা"
        description="ইসলামের মূল ভিত্তি, আরকান এবং মৌলিক জ্ঞান সম্পর্কে সঠিক ও যাচাইকৃত তথ্য। ঈমান, নামাজ, যাকাত, রোজা ও হজসহ দ্বীনের সঠিক ধারণা এক জায়গায়।"
        keywords="ইসলামিক জ্ঞান, ইসলামের মূলভিত্তি, ঈমান, নামাজ, যাকাত, হজ, রোজা, তথ্যবক্স ইসলাম, ইসলামের মৌলিক জ্ঞান, দ্বীনের মৌলিক ধারণা" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-4">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-4">
                <flux:icon icon="book-open" variant="mini" class="text-green-600" aria-hidden="true" />
                ইসলামের মৌলিক জ্ঞান
            </flux:heading>
            <flux:text>
                দ্বীনের সঠিক পথ ও মৌলিক ধারণা
            </flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle" aria-label="তথ্য প্রদানকারীগণ দেখুন" />

                <flux:tooltip.content class="rounded-2xl! space-y-4 p-4">
                    <div class="space-y-1">
                        <flux:heading level="2">
                            তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:text class="text-sm">এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন</flux:text>
                    </div>

                    <div class="max-h-60 overflow-y-auto space-y-3">
                        @forelse ($this->creators as $creator)
                        <flux:card class=" space-y-2">
                            <div class="flex items-start gap-2">
                                <flux:avatar src="{{ $creator->avatar_url ?? '' }}" size="md" badge
                                    badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                    alt="{{ $creator->name }}" />

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-sm text-zinc-800 dark:text-zinc-200 truncate">
                                            {{ $creator->name }}
                                        </span>
                                        @if ($creator->email_verified_at)
                                        <flux:icon.check-badge class="size-4 text-emerald-500" variant="solid"
                                            aria-hidden="true" />
                                        @endif
                                    </div>
                                    <p class="text-xs text-zinc-500 truncate">
                                        {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                    </p>
                                </div>
                            </div>
                            <flux:separator class="opacity-50" />
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span>
                                    একটিভ:
                                    {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                </span>
                                <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost" size="xs"
                                    icon="arrow-right" aria-label="{{ $creator->name }} প্রোফাইল দেখুন" />
                            </div>
                        </flux:card>
                        @empty
                        <flux:text class="text-xs">কোনো কন্ট্রিবিউটর পাওয়া যায়নি।</flux:text>
                        @endforelse
                    </div>
                    <flux:separator class="opacity-50" />
                    <flux:text class="text-xs">
                        আমাদের সকল তথ্য ভেরিফাইড এবং যাচাইকৃত।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </header>

    {{-- Search --}}
    <nav class="flex items-center gap-2" aria-label="বিষয় সার্চ">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="বিষয় খুঁজুন (যেমন: নামাজ, যাকাত)..."
            icon="magnifying-glass" variant="filled" class="rounded-xl flex-1" aria-label="বিষয় খুঁজুন" />
        @if ($search)
        <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm" aria-label="সার্চ মুছুন" />
        @endif
    </nav>

    {{-- Results count --}}
    @if ($search)
    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
        “{{ $search }}” এর জন্য {{ bn_num($this->filteredData->count()) }}টি ফলাফল পাওয়া গেছে
    </p>
    @endif

    {{-- Content List --}}
    <section class="space-y-4" aria-labelledby="content-list-heading">
        <h2 id="content-list-heading" class="sr-only">ইসলামের মৌলিক জ্ঞানের তালিকা</h2>

        @forelse ($this->filteredData as $item)
        @php
        $mediaItems = $item->getMedia('images');
        $mediaCount = $mediaItems->count();
        @endphp

        <flux:card>
            {{-- Header & Media Preview Section --}}
            <div class="flex gap-6">

                <div>
                    {{-- Avatar Group --}}
                    <flux:avatar.group>
                        @foreach ($mediaItems->take(1) as $media)
                        <flux:avatar src="{{ $media->getUrl('thumb') }}" alt="Media Image" />
                        @endforeach
                        @if ($mediaCount > 1)
                        <flux:avatar initials="+{{ bn_num($mediaCount - 1) }}" />
                        @endif
                    </flux:avatar.group>
                </div>


                <div class="">


                    <div class="space-y-2">
                        @if (!empty($item->type))
                        <flux:badge size="xs" color="green">
                            {{ $item->typeName }}
                        </flux:badge>
                        @endif

                        <flux:heading size="lg" level="2">
                            @if (!empty($item->slug))
                            <flux:link variant="ghost" href="{{ route('islam.basicislam.show', $item->slug) }}">
                                {{ strip_tags($item->title) }}
                            </flux:link>
                            @else
                            {{ strip_tags($item->title) }}
                            @endif
                        </flux:heading>

                        {{-- Content Snippet --}}
                        @if ($item->description)
                        <flux:text class="line-clamp-2 overflow-hidden">
                            {{ strip_tags($item->description) }}
                        </flux:text>
                        @endif
                    </div>
                </div>
            </div>

            <flux:separator class="opacity-50 my-2" />

            {{-- Footer Action --}}
            <div>
                <flux:button icon="arrow-right" variant="subtle" size="xs"
                    href="{{ route('islam.basicislam.show', $item->slug) }}" wire:navigate>
                    বিস্তারিত পড়ুন
                </flux:button>
            </div>
        </flux:card>
        @empty
        <livewire:global.nodata-message :title="'ইসলামিক তথ্য'" :search="$search" />
        @endforelse
    </section>

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-islam" class="space-y-4">
        <flux:heading id="about-islam" level="2" size="lg" class="flex items-center gap-2">
            <flux:icon icon="information-circle" class="size-5 text-green-600" />
            ইচ্ছাকৃত তথ্য ও মূলভিত্তি
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                আমাদের প্ল্যাটফর্মে ইসলামের মূল ভিত্তি ও আরকান সম্পর্কে সঠিক ও যাচাইকৃত তথ্য প্রকাশ করা হয়।
                <strong>ঈমান, নামাজ, যাকাত, রোজা ও হজ</strong> সহ দ্বীনের মৌলিক বিষয়গুলো সহজ ভাষায় উপস্থাপন করা হয়েছে।
                এখানে ইসলামের মূল স্তম্ভ, বিশ্বাস এবং আমল সম্পর্কে নির্ভরযোগ্য তথ্য একত্রিত করা হয়েছে।
            </flux:text>

            <flux:text>
                শিক্ষার্থী, নতুন শিক্ষার্থী এবং সাধারণ মানুষ যারা ইসলামের মৌলিক বিষয়গুলো সহজে বুঝতে চান, তাদের জন্য এই
                সংকলন খুবই উপযোগী।
                “বিস্তারিত পড়ুন” বাটনে ক্লিক করে প্রতিটি বিষয়ের পূর্ণাঙ্গ ব্যাখ্যা ও প্রাসঙ্গিক তথ্য জানতে পারবেন।
            </flux:text>

            <flux:text>
                এই তথ্যগুলো নিয়মিত যাচাই ও আপডেট করা হয় যাতে সঠিক ও নির্ভরযোগ্য কন্টেন্ট থাকে।
                সহজ ভাষায় উপস্থাপিত হওয়ায় সবাই সহজে বুঝতে ও শিখতে পারেন।
            </flux:text>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section aria-labelledby="faq-heading" class="space-y-4">
        <flux:heading id="faq-heading" level="2" size="lg">
            প্রায়শই জিজ্ঞাসিত প্রশ্ন
        </flux:heading>

        <flux:accordion transition exclusive>
            <flux:accordion.item>
                <flux:accordion.heading>ইসলামের মৌলিক আরকান কী কী?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        ইসলামের পাঁচটি মূল স্তম্ভ হলো: শাহাদাহ (ঈমান), নামাজ, যাকাত, রোজা এবং হজ।
                        এগুলো ইসলামের ভিত্তি এবং প্রত্যেক মুসলিমের উপর ফরজ।
                        এই পাঁচটি আরকান পালনের মাধ্যমে একজন মুসলিম তার দ্বীন পূর্ণাঙ্গভাবে পালন করে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>তথ্যগুলো কি যাচাইকৃত?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। আমাদের কন্টেন্ট ভেরিফাইড কন্ট্রিবিউটরদের মাধ্যমে নির্ভরযোগ্য উৎস থেকে তৈরি ও যাচাই করা
                        হয়।
                        কুরআন, সহীহ হাদিস এবং স্বীকৃত ইসলামী স্কলারদের মতামতের ভিত্তিতে তথ্য উপস্থাপন করা হয়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>কে এই তথ্যগুলো পড়তে পারে?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        যে কেউ এই তথ্যগুলো পড়তে ও শিখতে পারেন।
                        নতুন শিক্ষার্থী, সাধারণ মানুষ এবং যারা ইসলামের মৌলিক বিষয়গুলো সহজে বুঝতে চান, তাদের জন্য এটি
                        বিশেষভাবে উপযোগী।
                        সহজ ভাষায় লেখা হওয়ায় সবাই সহজে বুঝতে পারবেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিস্তারিত তথ্য কোথায় পাব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি বিষয়ের নিচে “বিস্তারিত পড়ুন” বাটনে ক্লিক করলে আলাদা পেজে পূর্ণাঙ্গ ব্যাখ্যা দেখা যাবে।
                        সেখানে সংশ্লিষ্ট আয়াত, হাদিস এবং প্রাসঙ্গিক তথ্যসহ বিস্তারিত বিবরণ পাওয়া যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>