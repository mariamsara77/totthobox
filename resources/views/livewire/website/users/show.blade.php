<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app.header')] class extends Component {
    public $user;
    public bool $fromSearch = false;

    public function mount($slug)
    {
        $this->user = User::where('slug', $slug)->firstOrFail();
    }
};
?>

<section class="max-w-2xl mx-auto pt-6 px-3">

    <div class="overflow-hidden">
        <div class="flex flex-col md:flex-row gap-8 items-start">

            {{-- অ্যাভাটার সেকশন --}}
            <div class="relative">
                <flux:avatar name="{{ $user->name }}" badge badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}"
                    src="{{ $user->getFirstMediaUrl('avatars', 'thumb') }}" class="size-32 md:size-40 text-4xl" />
            </div>

            {{-- সাধারণ তথ্য --}}
            <div class="flex-1 space-y-4">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="xl" level="1">{{ $user->name }}</flux:heading>
                        @if($user->hasRole(['admin', 'super admin']))
                            <flux:badge color="teal" size="sm" inset="top bottom">ভেরিফাইড এডমিন</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 text-sm">
                    @if($user->location)
                        <div class="flex items-center gap-2 text-zinc-500">
                            <flux:icon.map-pin variant="mini" />
                            {{ $user->location }}
                        </div>
                    @endif

                    <div class="flex items-center gap-2 text-zinc-500">
                        <flux:icon.briefcase variant="mini" />
                        @php
                            $roleName = $user->getRoleNames()->first();
                        @endphp

                        <span>
                            @if($roleName === 'Student')
                                শিক্ষার্থী
                            @elseif($roleName === 'Admin')
                                এডমিন
                            @else
                                {{ $user->profession ?? 'ব্যবহারকারী' }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <flux:button size="sm" icon="chat-bubble-left-right" variant="filled"
                        href="{{ route('messages', $user->slug)}}">মেসেজ পাঠান</flux:button>
                    <flux:button size="sm" icon="share" variant="ghost" data-share-button
                        data-url="{{ route('users.show', $user->slug) }}">প্রোফাইল শেয়ার</flux:button>
                </div>
            </div>
        </div>

        <flux:separator class="my-8" />

        {{-- বিস্তারিত সেকশন --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- বাম কলাম: আমার সম্পর্কে --}}
            <div class="md:col-span-2 space-y-6">
                <div>
                    <flux:heading level="2" size="lg">{{ $user->name }} সম্পর্কে</flux:heading>
                    <flux:text class="mt-2 leading-relaxed ql-text-format">
                        {!! $user->bio ?? 'এখনও কোনো তথ্য যোগ করা হয়নি।' !!}
                    </flux:text>
                </div>

                @if($user->description)
                    <div>
                        <flux:heading level="2" size="lg">বিস্তারিত বিবরণ</flux:heading>
                        <flux:text class="mt-2 prose dark:prose-invert">
                            {!! nl2br(e($user->description)) !!}
                        </flux:text>
                    </div>
                @endif
            </div>

            {{-- ডান কলাম: যোগাযোগের তথ্য --}}
            <div class="space-y-6">
                <flux:card variant="subtle" class="space-y-4">
                    <flux:heading size="md">যোগাযোগের তথ্য</flux:heading>

                    <div class="space-y-3">
                        <flux:description>
                            <span class="block font-medium text-zinc-800 dark:text-zinc-200">ইমেইল</span>
                            {{ $user->email }}
                        </flux:description>

                        @if($user->thana || $user->district)
                            <flux:description>
                                <span class="block font-medium text-zinc-800 dark:text-zinc-200">ঠিকানা</span>
                                {{ $user->address ? $user->address . ', ' : '' }}
                                {{ $user->thana?->name }}{{ $user->thana ? ', ' : '' }}
                                {{ $user->district?->name }}
                            </flux:description>
                        @endif

                        @if($user->education)
                            <flux:description>
                                <span class="block font-medium text-zinc-800 dark:text-zinc-200">শিক্ষা</span>
                                {{ $user->education }}
                                @if($user->is_student && $user->classLevel)
                                    <br><span class="text-xs text-zinc-500">শ্রেণী: {{ $user->classLevel->name }}</span>
                                @endif
                            </flux:description>
                        @endif
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</section>