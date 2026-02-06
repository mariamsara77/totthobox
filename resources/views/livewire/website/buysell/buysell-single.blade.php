<?php

use Livewire\Volt\Component;
use App\Models\BuySellPost;
use App\Models\UserReport;

new class extends Component {
    public $slug;
    public $post;
    public $activeImageIndex = 0;
    public $reportModalOpen = false;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->post = BuySellPost::where('slug', $slug)
            ->where('status', 'published')
            ->with(['category', 'district', 'thana', 'division', 'user.buysellposts'])
            ->firstOrFail();
    }

    public function react($type)
    {
        if (!auth()->check()) {
            session()->flash('error', 'রিয়্যাকশন করার জন্য লগইন করতে হবে।');
            return redirect()->route('login');
        }

        $this->post->react($type);
        $this->post->refresh();
        session()->flash('success', 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!');
    }

    public function submitPostReport($reason)
    {
        if (!auth()->check()) {
            session()->flash('error', 'রিপোর্ট করতে লগইন প্রয়োজন।');
            return redirect()->route('login');
        }

        UserReport::create([
            'reported_by' => auth()->id(),
            'target_type' => BuySellPost::class,
            'target_id' => $this->post->id,
            'reason' => $reason,
        ]);

        $this->reportModalOpen = false;
        session()->flash('success', 'রিপোর্ট সফলভাবে গ্রহণ করা হয়েছে।');
    }

    public function reportPost()
    {
        $this->reportModalOpen = true;
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-4">
    @include('partials.toast')
    
    <!-- Breadcrumb -->
    <div class="mb-6">
        <flux:breadcrumbs class="text-nowrap overflow-x-auto">
            <flux:breadcrumbs.item href="{{ route('home') }}">
                হোম
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="#">
                {{ $post->category->name ?? 'ক্যাটাগরি' }}
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>
                {{ $post->title }}
            </flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Image Gallery -->
        <div class="lg:col-span-6 space-y-6">
            <div class="bg-zinc-400/10 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="relative max-w-full mx-auto" x-data="{
                    index: 0,
                    total: {{ $post->getMedia('posts')->count() }},
                    startX: 0,
                    endX: 0,
                    swipeThreshold: 15,
                    next() { 
                        if (this.total > 0) {
                            this.index = Math.min(this.total - 1, this.index + 1);
                        }
                    },
                    prev() { 
                        this.index = Math.max(0, this.index - 1);
                    },
                    startTouch(e) { 
                        if (this.total > 0) {
                            this.startX = e.touches[0].clientX;
                        }
                    },
                    moveTouch(e) { 
                        if (this.total > 0) {
                            this.endX = e.touches[0].clientX;
                        }
                    },
                    endTouch() {
                        if (this.total > 0 && Math.abs(this.startX - this.endX) > this.swipeThreshold) {
                            this.startX > this.endX ? this.next() : this.prev();
                        }
                        this.startX = this.endX = 0;
                    }
                }" @touchstart="startTouch($event)"
                    @touchmove="moveTouch($event)" @touchend="endTouch()">

                    <!-- Image Track -->
                    <div class="flex transition-transform duration-500 ease-in-out"
                        :style="{ transform: total > 0 ? `translateX(-${index * 100}%)` : 'translateX(0)' }" 
                        data-viewer-gallery="post">
                        @if($post->getMedia('posts')->count() > 0)
                            @foreach($post->getMedia('posts') as $media)
                                <div class="w-full flex-shrink-0 aspect-[4/3]">
                                    <img src="{{ $media->getUrl('large') }}" 
                                         alt="{{ $post->title }}"
                                         class="w-full h-full object-cover viewer-image"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';">
                                </div>
                            @endforeach
                        @else
                            <div class="w-full flex-shrink-0 aspect-[4/3] bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <flux:icon icon="photo" class="w-16 h-16 text-gray-400" />
                                <span class="sr-only">ছবি পাওয়া যায়নি</span>
                            </div>
                        @endif
                    </div>

                    <!-- Navigation Arrows -->
                    @if ($post->getMedia('posts')->count() > 1)
                        <button @click="prev()" :disabled="index === 0"
                            class="hidden md:flex absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-white/80 dark:bg-gray-800/80 rounded-full shadow hover:bg-white dark:hover:bg-gray-700 disabled:opacity-30">
                            <flux:icon icon="chevron-left" class="w-5 h-5" />
                        </button>
                        <button @click="next()" :disabled="index === total - 1"
                            class="hidden md:flex absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-white/80 dark:bg-gray-800/80 rounded-full shadow hover:bg-white dark:hover:bg-gray-700 disabled:opacity-30">
                            <flux:icon icon="chevron-right" class="w-5 h-5" />
                        </button>
                    @endif

                    <!-- Indicators -->
                    @if ($post->getMedia('posts')->count() > 1)
                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex space-x-2">
                            <template x-for="i in total" :key="i">
                                <button @click="index = i - 1"
                                    class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                                    :class="index === (i - 1) ? 'bg-blue-600 w-6' : 'bg-zinc-400 hover:bg-white'"></button>
                            </template>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <!-- Right Column: Product Details -->
        <div class="lg:col-span-6 space-y-6">
            <div class="overflow-hidden">
                <!-- Title & Meta -->
                <flux:heading class="text-xl">{{ $post->title }}</flux:heading>

                <div class="flex flex-wrap items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 mb-4">
                    <flux:icon name="clock" class="w-4 h-4" />
                    <span>
                        {{ bn_date(optional($post->updated_at)->format('d M, Y')) ?? 'N/A' }}
                    </span>
                </div>

                <!-- Price -->
                <div class="text-center mb-3">
                    <div class="flex justify-between gap-3 items-center">
                        <div class="flex items-end justify-center gap-3">
                            @if ($post->discount_price)
                                <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                    ৳{{ number_format($post->discount_price) }}
                                </span>
                                <span class="text-sm text-gray-400 line-through mb-1">
                                    ৳{{ number_format($post->price) }}
                                </span>
                            @else
                                <span class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                                    ৳{{ number_format($post->price) }}
                                </span>
                            @endif
                        </div>
                        <div>
                            @if ($post->is_negotiable)
                                <flux:badge size="sm" color="lime">আলোচনা সাপেক্ষ</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center overflow-x-auto gap-2">
                    {{-- Status (Condition) --}}
                    <div>
                        @php
                            $badgeColor = match ($post->condition) {
                                'new' => 'green',
                                'like_new' => 'blue',
                                'used_good' => 'yellow',
                                'used_fair' => 'red',
                                default => 'gray',
                            };
                        @endphp
                        <flux:badge icon="information-circle" color="{{ $badgeColor }}">
                            @switch($post->condition)
                                @case('new')
                                    ব্র্যান্ড নিউ
                                    @break
                                @case('like_new')
                                    নতুনের মত
                                    @break
                                @case('used_good')
                                    ব্যবহৃত (ভাল)
                                    @break
                                @case('used_fair')
                                    ব্যবহৃত (মোটামুটি)
                                    @break
                                @default
                                    {{ ucfirst($post->condition) }}
                            @endswitch
                        </flux:badge>
                    </div>

                    {{-- Category --}}
                    <div>
                        <flux:badge icon="tag">
                            {{ $post->category->name ?? 'N/A' }}
                        </flux:badge>
                    </div>

                    {{-- Location --}}
                    <div>
                        <flux:badge icon="map-pin">
                            {{ $post->district->name ?? 'N/A' }},
                            {{ $post->thana->name ?? '' }}
                        </flux:badge>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6 items-center justify-between">
                    <div class="flex justify-center gap-2">
                        <!-- LIKE -->
                        <flux:button variant="subtle" wire:click="react('like')" size="sm">
                            <div class="flex gap-2"
                                :class="{ 'text-blue-600': {{ $post->hasReaction('like') ? 'true' : 'false' }} }"
                                x-data="{ reacted: {{ $post->hasReaction('like') ? 'true' : 'false' }} }"
                                x-on:reaction-updated.window="reacted = event.detail.type == 'like' ? true : false">
                                <flux:icon name="thumb-up" class="w-5 h-5 mr-1" />
                                {{ $post->countReaction('like') }}
                            </div>
                        </flux:button>

                        <!-- DISLIKE -->
                        <flux:button variant="subtle" wire:click="react('dislike')" size="sm">
                            <div class="flex gap-2"
                                :class="{ 'text-red-600': {{ $post->hasReaction('dislike') ? 'true' : 'false' }} }"
                                x-data="{ reacted: {{ $post->hasReaction('dislike') ? 'true' : 'false' }} }"
                                x-on:reaction-updated.window="reacted = event.detail.type == 'dislike' ? true : false">
                                <flux:icon name="thumb-down" class="w-5 h-5 mr-1" />
                                {{ $post->countReaction('dislike') }}
                            </div>
                        </flux:button>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <flux:icon icon="eye" class="w-4 h-4" />
                        {{ bn_num($post->view_count ?? 0) }} ভিউ
                    </div>

                    <flux:button variant="subtle" size="sm" icon="share" data-share-button
                        data-url="{{ route('buysell.buysell-single', $post->slug) }}">
                        শেয়ার
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-4">
        <!-- Description -->
        @if ($post->description)
            <div class="space-y-2">
                <flux:heading level="3" class="m-0 p-0">বিস্তারিত বর্ণনা</flux:heading>
                <flux:separator />
                <flux:text>
                    {!! nl2br(e($post->description ?? 'কোনো বর্ণনা নেই')) !!}
                </flux:text>
            </div>
        @endif

        <!-- Special Note -->
        @if ($post->note)
            <div class="space-y-2">
                <flux:heading level="3" class="m-0">বিশেষ দ্রষ্টব্য</flux:heading>
                <flux:separator />
                <flux:text class="text-yellow-600">{!! nl2br(e($post->note)) !!}</flux:text>
            </div>
        @endif
    </div>

    <!-- Safety Tips -->
    <flux:callout color="indigo" icon="exclamation-circle" heading="নিরাপত্তা টিপস">
        <flux:callout.text>
            <li>অর্থ লেনদেনের আগে পণ্যটি ভালো করে দেখে নিন</li>
            <li>জনবহুল স্থানে দেখা করুন</li>
            <li>অগ্রিম টাকা পাঠাবেন না</li>
            <li>অপরিচিত ব্যক্তির সাথে একা দেখা করবেন না</li>
        </flux:callout.text>
    </flux:callout>

    <div class="space-y-4">
        <flux:heading level="3">
            বিক্রেতার সাথে যোগাযোগ
            <flux:separator />
        </flux:heading>

        <div class="flex gap-3 items-center justify-between overflow-auto">
            <flux:profile :chevron="false" name="{{ $post->user->name }}" avatar="{{ $post->user->avatar }}" />
            
            <flux:button size="xs" variant="ghost" icon="arrow-right" class="flex-shrink-0"
                href="{{route('users.show', $post->user->slug) }}">
                প্রোফাইল দেখুন
            </flux:button>
        </div>

        <div class="flex gap-3 items-center overflow-auto">
            <flux:badge color="blue" size="sm" icon="user">
                সদস্য: {{ optional($post->user->created_at)->format('M Y') ?? 'N/A' }}
            </flux:badge>

            <flux:badge color="blue" size="sm" icon="map-pin">
                {{ $post->district->name ?? 'N/A' }}
            </flux:badge>

            <flux:badge color="blue" size="sm" icon="tag">
                পোস্ট: {{ bn_num($post->user->buysellposts()->count()) }}
            </flux:badge>
        </div>

        <div class="flex gap-3 mt-3 items-center overflow-auto">
            @if ($post->phone)
                <flux:button size="sm" variant="primary" color="blue" href="tel:{{ $post->phone }}"
                    icon="phone">
                    কল করুন
                </flux:button>
            @endif

            @if ($post->whatsapp)
                @php
                    $whatsappNumber = preg_replace('/[^0-9]/', '', $post->whatsapp);
                    $whatsappNumber = ltrim($whatsappNumber, '0');
                    if (!str_starts_with($whatsappNumber, '880') && strlen($whatsappNumber) == 10) {
                        $whatsappNumber = '880' . $whatsappNumber;
                    }
                @endphp
                <flux:button size="sm" variant="primary" color="green"
                    href="https://wa.me/{{ $whatsappNumber }}" target="_blank" icon="whatsapp">
                    হোয়াটসঅ্যাপ
                </flux:button>
            @endif

            @if ($post->user->email)
                <flux:button size="sm" variant="primary" color="orange" href="mailto:{{ $post->user->email }}"
                    icon="envelope">
                    ইমেইল
                </flux:button>
            @endif

            <flux:button size="sm" variant="primary" color="red" icon="flag" wire:click="reportPost">
                রিপোর্ট করুন
            </flux:button>
        </div>
    </div>

    @if ($reportModalOpen)
        <flux:modal wire:model="reportModalOpen" size="sm">
            <div class="space-y-4 p-4">
                <h3 class="text-lg font-semibold">রিপোর্টের কারণ নির্বাচন করুন</h3>
                <flux:separator />

                <flux:button class="w-full justify-center" wire:click="submitPostReport('ভুল তথ্য')">
                    ভুল তথ্য
                </flux:button>

                <flux:button class="w-full justify-center" wire:click="submitPostReport('প্রতারনা / স্ক্যাম')">
                    প্রতারনা / স্ক্যাম
                </flux:button>

                <flux:button class="w-full justify-center" wire:click="submitPostReport('অপমানজনক কন্টেন্ট')">
                    অপমানজনক কন্টেন্ট
                </flux:button>

                <flux:button class="w-full justify-center" wire:click="submitPostReport('নিয়ম বিরোধী পোস্ট')">
                    নিয়ম বিরোধী পোস্ট
                </flux:button>

                <flux:button variant="subtle" class="w-full justify-center"
                    wire:click="$set('reportModalOpen', false)">
                    বাতিল করুন
                </flux:button>
            </div>
        </flux:modal>
    @endif

    <!-- Comments Section -->
    <div class="mt-8">
        <livewire:website.comments.comments-section :model="$post" />
    </div>
</div>