<?php

use App\Models\Dowa;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    #[Url(history: true, keep: false)]
    public string $search = '';

    #[Computed]
    public function allData()
    {
        return Cache::remember('dowas_list', now()->addHour(), function () {
            return Dowa::query()
                ->active()
                ->latest()
                ->select(['id', 'bangla_name', 'slug', 'type', 'bangla_text']) // adjust columns as needed
                ->with('media') // if you have media
                ->get();
        });
    }

    #[Computed]
    public function filteredData()
    {
        if (blank($this->search)) {
            return $this->allData;
        }

        $term = mb_strtolower(trim($this->search), 'UTF-8');

        return $this->allData->filter(function ($item) use ($term) {
            return str_contains(mb_strtolower($item->bangla_name ?? '', 'UTF-8'), $term) || str_contains(mb_strtolower(strip_tags($item->bangla_fojilot ?? ''), 'UTF-8'), $term);
        });
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember('dowas_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', Dowa::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
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
DOWA – LIST PAGE
SEO Optimized · AdSense Policy Compliant · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo
        title="{{ trim($search) ? 'দোয়া অনুসন্ধান: ' . $search . ' | দোয়া সংগ্রহ' : 'দৈনন্দিন জীবনের প্রয়োজনীয় দোয়া ও আমল সংগ্রহ' }}"
        description="{{ trim($search) ? 'টপিক: ' . $search . ' এর সাথে সম্পর্কিত প্রয়োজনীয় দোয়া ও আমলসমূহ অর্থসহ খুঁজুন।' : 'দৈনন্দিন জীবনের প্রয়োজনীয় ও নিত্যদিনের গুরুত্বপূর্ণ দোয়া, জিকির ও আমলসমূহের সম্পূর্ণ বাংলা তালিকা।' }}"
        keywords="{{ 'dowa bangla, islamic dowa list, প্রয়োজনীয় দোয়া, প্রতিদিনের আমল, দোয়া সংগ্রহ' . (trim($search) ? ', ' . $search : '') }}" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-4">
        <div>
            <flux:heading size="xl" level="1">
                দোয়া সংগ্রহ
            </flux:heading>
            <flux:text>
                দৈনন্দিন জীবনের প্রয়োজনীয় দোয়া ও আমল
            </flux:text>
        </div>

        <div class="flex items-center gap-4">
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle" aria-label="তথ্য প্রদানকারীগণ দেখুন" />

                <flux:tooltip.content class="rounded-2xl! space-y-4 p-4">
                    <div class="space-y-1">
                        <flux:heading level="2">
                            তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:text>এই কন্টেন্ট তৈরিতে যারা অবদান রেখেছেন</flux:text>
                    </div>

                    <div class="max-h-60 overflow-y-auto space-y-3">
                        @forelse ($this->creators as $creator)
                            <flux:card class="space-y-2">
                                <div class="flex items-start gap-4">
                                    <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                        badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                        alt="{{ $creator->name }}" />

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <flux:text>
                                                {{ $creator->name }}
                                            </flux:text>
                                            @if ($creator->email_verified_at)
                                                <flux:icon.check-badge class="size-4 text-emerald-500" variant="solid"
                                                    aria-hidden="true" />
                                            @endif
                                        </div>
                                        <flux:text class="text-xs">
                                            {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                        </flux:text>
                                    </div>
                                </div>
                                <flux:separator class="opacity-50" />
                                <div class="flex items-center justify-between">
                                    <flux:text>
                                        একটিভ:
                                        {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                    </flux:text>
                                    <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost"
                                        size="xs" icon="arrow-right"
                                        aria-label="{{ $creator->name }} প্রোফাইল দেখুন" />
                                </div>
                            </flux:card>
                        @empty
                            <flux:text class="text-xs">কোনো কন্ট্রিবিউটর পাওয়া যায়নি।
                            </flux:text>
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
    <flux:input wire:model.live.debounce.400ms="search" placeholder="দোয়া খুঁজুন (যেমন: ঘুমানোর দোয়া, খাবারের দোয়া)..."
        icon="magnifying-glass" variant="filled" aria-label="দোয়া খুঁজুন" clearable />

    {{-- Content List --}}
    <section class="space-y-4" aria-labelledby="content-list-heading">
        @forelse ($this->filteredData as $item)
            @php
                $mediaItems = $item->getMedia('images') ?? collect();
                $mediaCount = $mediaItems->count();
            @endphp

            <flux:card>
                {{-- Header & Media Preview --}}
                <div class="flex gap-6 mb-4 items-center justify-between">
                    <div>
                        <flux:avatar.group>
                            @foreach ($mediaItems->take(1) as $media)
                                <flux:avatar src="{{ $media->getUrl('thumb') }}" alt="Media Image" />
                            @endforeach
                            @if ($mediaCount > 1)
                                <flux:avatar initials="+{{ bn_num($mediaCount - 1) }}" />
                            @endif
                        </flux:avatar.group>
                    </div>

                    <div class="flex-1 space-y-2">
                        @if (!empty($item->type))
                            <flux:badge color="green" size="xs">
                                {{ $item->typeName ?? $item->type }}
                            </flux:badge>
                        @endif

                        <flux:heading size="xl" level="2">
                            @if (!empty($item->slug))
                                <flux:link href="{{ route('islam.dowan.show', $item->slug) }}" variant="ghost">
                                    {{ strip_tags($item->bangla_name) }}
                                </flux:link>
                            @else
                                {{ strip_tags($item->bangla_name) }}
                            @endif
                        </flux:heading>

                        {{-- Content Snippet --}}
                        @if ($item->bangla_text)
                            <flux:text class="line-clamp-2 overflow-hidden">
                                {{ strip_tags($item->bangla_text) }}
                            </flux:text>
                        @endif

                    </div>
                </div>
                <flux:separator class="opacity-50 my-2" />
                {{-- Footer Action --}}
                <div class="">
                    <flux:button icon="arrow-right" variant="subtle"
                        href="{{ route('islam.dowan.show', $item->slug) }}" size="xs" wire:navigate>
                        বিস্তারিত পড়ুন
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <livewire:global.nodata-message :title="'দোয়া সংগ্রহ'" :search="$search" />
        @endforelse
    </section>

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-dowa" class="space-y-4">
        <flux:heading id="about-dowa" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" class="size-5 text-green-600" />
            দোয়া সংগ্রহ সম্পর্কে
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                আমাদের প্ল্যাটফর্মে দৈনন্দিন জীবনের প্রয়োজনীয় দোয়া, জিকির ও আমলসমূহ সঠিক ও যাচাইকৃত উৎস থেকে সংগ্রহ করে
                সহজ বাংলায় উপস্থাপন করা হয়েছে।
                ঘুমানো, খাওয়া, সফর, বিপদ-আপদসহ বিভিন্ন পরিস্থিতির জন্য গুরুত্বপূর্ণ দোয়া এক জায়গায় পাবেন।
                প্রতিটি দোয়ার সাথে আরবি, উচ্চারণ এবং বাংলা অর্থ দেওয়া হয়েছে যাতে সহজে মুখস্থ ও বুঝে পড়া যায়।
            </flux:text>

            <flux:text>
                নতুন শিক্ষার্থী থেকে শুরু করে সাধারণ মানুষ সবাই এই সংগ্রহ থেকে উপকৃত হতে পারেন।
                “বিস্তারিত পড়ুন” বাটনে ক্লিক করে প্রতিটি দোয়ার পূর্ণাঙ্গ তথ্য, ফজিলত এবং প্রাসঙ্গিক হাদিস জানতে পারবেন।
            </flux:text>

            <flux:text>
                এই তথ্যগুলো নিয়মিত যাচাই ও আপডেট করা হয় যাতে সঠিক ও নির্ভরযোগ্য কন্টেন্ট থাকে।
                সহজ ভাষায় উপস্থাপিত হওয়ায় সবাই সহজে শিখতে ও আমল করতে পারেন।
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
                <flux:accordion.heading>এখানে কোন ধরনের দোয়া পাওয়া যায়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        ঘুমানোর দোয়া, খাবারের দোয়া, সফরের দোয়া, বিপদের দোয়া, শুকরিয়ার দোয়াসহ দৈনন্দিন জীবনের প্রয়োজনীয়
                        সব দোয়া ও আমল এখানে পাওয়া যাবে।
                        সকাল-সন্ধ্যার জিকির, নামাজের দোয়া এবং বিভিন্ন পরিস্থিতির জন্য গুরুত্বপূর্ণ দোয়াও অন্তর্ভুক্ত
                        রয়েছে।
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
                <flux:accordion.heading>দোয়াগুলো কীভাবে শিখব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি দোয়ার সাথে আরবি টেক্সট, সহজ উচ্চারণ এবং বাংলা অর্থ দেওয়া আছে।
                        নিয়মিত পড়ে ও মুখস্থ করে আমল করতে পারেন।
                        “বিস্তারিত পড়ুন” বাটনে ক্লিক করে ফজিলত ও প্রাসঙ্গিক তথ্যও জানতে পারবেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>বিস্তারিত তথ্য কোথায় পাব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রতিটি দোয়ার নিচে “বিস্তারিত পড়ুন” বাটনে ক্লিক করলে আলাদা পেজে পূর্ণাঙ্গ তথ্য দেখা যাবে।
                        সেখানে আরবি, উচ্চারণ, অর্থ, ফজিলত এবং সংশ্লিষ্ট হাদিসসহ বিস্তারিত বিবরণ পাওয়া যায়।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>
