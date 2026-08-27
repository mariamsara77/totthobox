<?php

use App\Models\AppResource;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    use WithPagination;

    #[Url(history: true, keep: false)]
    public string $search = '';

    public $perPage = 10;

    public string $platform = '';

    public function mount($platform = ''): void
    {
        $this->platform = strtolower(urldecode($platform)) === 'all' ? '' : urldecode($platform);
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'platform'])) {
            $this->perPage = 10;
        }
    }

    #[Computed]
    public function apps()
    {
        return AppResource::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->platform, fn($q) => $q->where('platform', $this->platform))
            ->with(['media'])
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function creators()
    {
        return Cache::remember('app_resource_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()->where('subject_type', AppResource::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get();
        });
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'platform']);
        $this->perPage = 10;
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════
DIGITAL RESOURCE LIBRARY – ALL DATA LIST
SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

@php
    $seoPlatform = $this->platform ? ucfirst($this->platform) . ' ' : '';
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    <x-seo title="Latest Free {{ $seoPlatform }}Software & Apps Download | Verified Safe Resources | তথ্যবক্স"
        description="Download 100% free and verified {{ $seoPlatform }}software, apps, and digital resources on Totthobox. Safe, fast and malware-free."
        keywords="free {{ $this->platform }} software download, safe apk, windows software, free digital resources, Totthobox, free apps download" />

    {{-- Page Header --}}
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon icon="puzzle-piece" class="text-amber-600 dark:text-amber-500" />
                Digital Resource Library
            </flux:heading>

            <flux:text variant="subtle" class="mt-1">
                ১০০% ফ্রি ও ভেরিফাইড সফটওয়্যার এবং অ্যাপস — নিরাপদ, আপডেটেড ও সহজে ডাউনলোডযোগ্য
            </flux:text>
        </div>

        <div>
            <flux:tooltip toggleable>
                <flux:button icon="users" size="sm" variant="subtle"
                    aria-label="তথ্য প্রদানকারী ও কন্ট্রিবিউটরদের তালিকা দেখুন" />

                <flux:tooltip.content class="w-80 space-y-4 p-4">
                    {{-- Contributors Header --}}
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            তথ্য প্রদানকারীগণ ({{ bn_num($this->creators->count()) }})
                        </flux:heading>
                        <flux:text size="sm" variant="subtle">
                            এই ডিজিটাল রিসোর্স লাইব্রেরির কন্টেন্ট তৈরি ও যাচাইকরণে যারা অবদান রেখেছেন
                        </flux:text>
                    </div>

                    {{-- Contributors List --}}

                    @forelse ($this->creators as $creator)
                        <flux:card class="space-y-2">
                            <div class="flex items-start gap-4">
                                <flux:avatar src="{{ $creator->avatar_url }}" size="md" badge
                                    badge:color="{{ $creator->isOnline() ? 'green' : 'zinc' }}"
                                    alt="{{ $creator->name }} এর প্রোফাইল ছবি" />

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <flux:text variant="strong" class="truncate">
                                            {{ $creator->name }}
                                        </flux:text>

                                        @if ($creator->email_verified_at)
                                            <flux:icon.check-badge class="size-4 text-emerald-500" variant="solid"
                                                aria-label="ভেরিফাইড অ্যাকাউন্ট" />
                                        @endif
                                    </div>

                                    <flux:text size="sm" variant="subtle" class="truncate">
                                        {{ $creator->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর' }}
                                    </flux:text>
                                </div>
                            </div>

                            <flux:separator />

                            <div class="flex items-center justify-between">
                                <flux:text size="sm" variant="subtle">
                                    সর্বশেষ সক্রিয়:
                                    {{ $creator->last_active_at ? bn_num($creator->last_active_at->diffForHumans()) : 'অজানা' }}
                                </flux:text>

                                <flux:button href="{{ route('users.show', $creator->slug) }}" variant="ghost"
                                    size="xs" icon="arrow-right"
                                    aria-label="{{ $creator->name }} এর সম্পূর্ণ প্রোফাইল দেখুন" />
                            </div>
                        </flux:card>
                    @empty
                        <flux:text size="sm" variant="subtle" class="py-2 text-center">
                            এখনো কোনো কন্ট্রিবিউটর পাওয়া যায়নি।
                        </flux:text>
                    @endforelse

                    <flux:separator />

                    <flux:text size="sm" variant="subtle" class="text-center">
                        আমাদের সকল সফটওয়্যার ও অ্যাপ ১০০% ভেরিফাইড এবং নিরাপদ।
                    </flux:text>
                </flux:tooltip.content>
            </flux:tooltip>
        </div>
    </header>

    {{-- Filters --}}
    <div class="space-y-3">
        <nav class="flex items-center gap-4" aria-label="অ্যাপ সার্চ ও ফিল্টার">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="অ্যাপের নামে খুঁজুন..."
                icon="magnifying-glass" variant="filled" class="rounded-xl flex-1" aria-label="অ্যাপ সার্চ করুন" />
            @if ($search || $platform)
                <flux:button wire:click="resetFilter" variant="ghost" icon="x-mark" size="sm"
                    aria-label="ফিল্টার মুছুন" />
            @endif
        </nav>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <flux:select wire:model.live="platform" variant="listbox" placeholder="Platform" class="min-w-36">
                <flux:select.option value="">All Platforms</flux:select.option>
                <flux:select.option value="Windows">Windows</flux:select.option>
                <flux:select.option value="Android">Android</flux:select.option>
                <flux:select.option value="Mac">Mac</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Results count --}}
    @if ($search || $platform)
        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400" role="status" aria-live="polite">
            {{ bn_num($this->apps->total()) }}টি ফলাফল পাওয়া গেছে
        </p>
    @endif

    {{-- Apps List --}}
    <section class="space-y-4" aria-labelledby="apps-list-heading">
        <h2 id="apps-list-heading" class="sr-only">
            {{ $platform ? ucfirst($platform) . ' ' : '' }}Software & Apps List
        </h2>

        @forelse ($this->apps as $app)
            @php
                $thumb = $app->getFirstMediaUrl('app_icons', 'thumb') ?: $app->getFirstMediaUrl('app_icons');
            @endphp

            <flux:card>
                <div class="flex gap-4 items-start">
                    {{-- Image --}}
                    <div class="shrink-0">
                        <flux:avatar src="{{ $thumb }}" size="xl" name="{{ $app->name ?? 'App' }}"
                            class="rounded-xl" />
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 space-y-2">
                        <div class="flex flex-wrap items-center gap-4">
                            <flux:heading level="2" size="lg">
                                <flux:link variant="ghost" href="{{ route('software.show', $app->slug) }}"
                                    wire:navigate>
                                    {{ $app->name ?? 'Unknown App' }}
                                </flux:link>
                            </flux:heading>

                            @if ($app->platform)
                                <flux:badge size="sm" color="zinc" variant="outline" class="text-xs">
                                    {{ $app->platform }}
                                </flux:badge>
                            @endif
                        </div>

                        @if ($app->description)
                            <flux:text class="line-clamp-2 overflow-hidden text-base">
                                {{ strip_tags($app->description) }}
                            </flux:text>
                        @endif
                    </div>
                </div>

                {{-- Footer Action --}}
                <flux:separator class="opacity-50 my-2" />

                {{-- Footer Action --}}
                <div>
                    <flux:button icon="arrow-right" variant="subtle" size="xs"
                        href="{{ route('software.show', $app->slug) }}" wire:navigate>বিস্তারিত পড়ুন
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <livewire:global.nodata-message :title="'Digital Resource Library'" :search="$search" />
        @endforelse
    </section>

    {{-- Infinite Scroll Trigger --}}
    @if ($this->apps->hasMorePages())
        <div x-intersect="$wire.loadMore()" class="flex justify-center p-6" aria-live="polite"
            aria-label="আরও লোড হচ্ছে">
            <flux:icon.loading aria-hidden="true" />
        </div>
    @endif

    <flux:separator />

    {{-- About Section --}}
    <section aria-labelledby="about-library" class="space-y-4">
        <flux:heading id="about-library" level="2" size="lg" class="flex items-center gap-4">
            <flux:icon icon="information-circle" color="orange" />
            ফ্রি সফটওয়্যার ও অ্যাপ রিসোর্স লাইব্রেরি
        </flux:heading>

        <div class="space-y-3">
            <flux:text>
                এই পেজে আপনি পাবেন <strong>১০০% ফ্রি ও ভেরিফাইড</strong> Windows, Android এবং Mac প্ল্যাটফর্মের
                সফটওয়্যার ও অ্যাপ।
                আমরা প্রতিটি রিসোর্স সাবধানে যাচাই করি যাতে নিরাপদ ডাউনলোড নিশ্চিত থাকে এবং ব্যবহারকারীরা ম্যালওয়্যার বা
                অপ্রয়োজনীয় সফটওয়্যার থেকে মুক্ত থাকেন।
            </flux:text>

            <flux:text>
                লাইব্রেরিতে প্রোডাকটিভিটি টুল, মিডিয়া প্লেয়ার, সিস্টেম ইউটিলিটি, অ্যান্টিভাইরাস, ডিজাইন সফটওয়্যার,
                ডেভেলপমেন্ট টুলসহ বিভিন্ন ক্যাটাগরির অ্যাপ রয়েছে।
                প্রতিটি আইটেমের সাথে সংক্ষিপ্ত বিবরণ, প্ল্যাটফর্ম সাপোর্ট এবং অফিসিয়াল সোর্সের লিংক দেওয়া থাকে যাতে আপনি
                সহজে সিদ্ধান্ত নিতে পারেন।
            </flux:text>

            <flux:text>
                সার্চ বক্স বা প্ল্যাটফর্ম ফিল্টার ব্যবহার করে দ্রুত আপনার প্রয়োজনীয় টুল খুঁজে নিন।
                “Download Now” বাটনে ক্লিক করলে বিস্তারিত পেজে যাবেন, যেখানে সফটওয়্যারের ফিচার, সিস্টেম রিকোয়ারমেন্ট এবং
                ডাউনলোড অপশন পাবেন।
                নিয়মিত নতুন ও আপডেটেড রিসোর্স যোগ করা হয় যাতে কালেকশন সবসময় প্রাসঙ্গিক থাকে।
            </flux:text>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section aria-labelledby="faq-heading" class="space-y-4">
        <flux:heading id="faq-heading" level="2" size="lg">
            প্রায়শাই জিজ্ঞাসিত প্রশ্ন
        </flux:heading>

        <flux:accordion transition exclusive>
            <flux:accordion.item>
                <flux:accordion.heading>সব সফটওয়্যার কি ফ্রি?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। এই লাইব্রেরিতে তালিকাভুক্ত সব সফটওয়্যার ও অ্যাপ ১০০% ফ্রি ডাউনলোড করা যায়।
                        আমরা শুধুমাত্র ফ্রিওয়্যার, ওপেন সোর্স বা সম্পূর্ণ ফ্রি সংস্করণের অ্যাপ যুক্ত করি।
                        কোনো প্রিমিয়াম বা পেইড ভার্সন এখানে রাখা হয় না, যাতে ব্যবহারকারীরা কোনো লুকানো খরচ ছাড়াই ব্যবহার
                        করতে পারেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>অ্যাপগুলো কি নিরাপদ?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        হ্যাঁ। প্রতিটি রিসোর্স যাচাই করে তালিকাভুক্ত করা হয়।
                        ডাউনলোডের আগে অফিসিয়াল ওয়েবসাইট, ভেরিফাইড সোর্স এবং ব্যবহারকারী রিভিউ চেক করার পরামর্শ দেওয়া হয়।
                        আমরা নিয়মিত আপডেট ও সিকিউরিটি স্ক্যান করি যাতে কোনো ক্ষতিকর ফাইল না থাকে।
                        তবুও সর্বদা আপনার অ্যান্টিভাইরাস সফটওয়্যার চালু রেখে ডাউনলোড করুন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>কোন কোন প্ল্যাটফর্ম সাপোর্টেড?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        বর্তমানে Windows, Android এবং Mac প্ল্যাটফর্মের সফটওয়্যার ও অ্যাপ পাওয়া যায়।
                        উপরের ফিল্টার থেকে প্ল্যাটফর্ম বেছে নিয়ে সহজে দেখতে পারেন।
                        ভবিষ্যতে Linux এবং iOS সাপোর্ট যোগ করার পরিকল্পনা রয়েছে।
                        প্রতিটি অ্যাপে কোন কোন অপারেটিং সিস্টেম ভার্সন সাপোর্ট করে সেটিও উল্লেখ থাকে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>কীভাবে অ্যাপ খুঁজব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        উপরের সার্চ বক্সে অ্যাপের নাম বা কীওয়ার্ড লিখুন।
                        চাইলে প্ল্যাটফর্ম (Windows / Android / Mac) সিলেক্ট করে ফিল্টার করুন।
                        লিস্ট থেকে পছন্দের অ্যাপে ক্লিক করে বিস্তারিত পেজে যান এবং সেখান থেকে ডাউনলোড করুন।
                        ক্যাটাগরি অনুসারেও ব্রাউজ করতে পারেন যদি উপলব্ধ থাকে।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>ডাউনলোড করার পর ইনস্টলেশনে সমস্যা হলে কী করব?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        প্রথমে সিস্টেম রিকোয়ারমেন্ট চেক করুন।
                        Windows-এর ক্ষেত্রে Administrator হিসেবে রান করুন এবং অ্যান্টিভাইরাস সাময়িক বন্ধ করে দেখুন।
                        Android-এ “Unknown sources” থেকে ইনস্টল অনুমতি দিন।
                        সমস্যা থাকলে অফিসিয়াল সাইটের সাপোর্ট বা ফোরাম দেখুন।
                        প্রয়োজনে আমাদের সাথে যোগাযোগ করতে পারেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>

            <flux:accordion.item>
                <flux:accordion.heading>নতুন সফটওয়্যার কতদিন পরপর আপডেট হয়?</flux:accordion.heading>
                <flux:accordion.content>
                    <flux:text>
                        লাইব্রেরি নিয়মিত আপডেট করা হয়।
                        জনপ্রিয় অ্যাপের নতুন ভার্সন এবং নতুন ফ্রি টুল যোগ করা হয় প্রায় প্রতি সপ্তাহে।
                        পুরনো বা অকেজো লিংক সরিয়ে ফেলা হয় যাতে সবসময় কার্যকর রিসোর্স থাকে।
                        হোমপেজে বা রিসেন্ট সেকশনে সর্বশেষ যোগ করা আইটেম দেখতে পাবেন।
                    </flux:text>
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    </section>

</div>
