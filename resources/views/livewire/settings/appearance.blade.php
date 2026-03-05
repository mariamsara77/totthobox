<?php

use Livewire\Volt\Component;

new class extends Component {
    // প্রয়োজন হলে এখানে লজিক যোগ করতে পারেন
}; ?>

<section class="max-w-2xl mx-auto">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('পছন্দসমূহ (Appearance)')" :subheading="__('আপনার অ্যাকাউন্টের প্রদর্শন সেটিংস আপডেট করুন')">

        <div class="space-y-3">
            <flux:label>থিম মোড</flux:label>
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('লাইট') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('ডার্ক') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('সিস্টেম') }}</flux:radio>
            </flux:radio.group>
        </div>

        <hr class="my-6 border-gray-200 dark:border-gray-700">

        @livewire('global.translator')
    </x-settings.layout>
</section>