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

       <section class="space-y-3">
                <flux:label>ভাষা পরিবর্তন করুন (Change Language)</flux:label>

                <div wire:ignore x-data="{ 
                // বর্তমানে কুকিতে কোন ভাষা আছে তা বের করা, না থাকলে ডিফল্ট 'bn'
                currentLang: (document.cookie.match(/googtrans=\/bn\/([^;]+)/) || [null, 'bn'])[1],
                
                toggleTranslate(val) {
                    if (val === 'bn') {
                        // বাংলা সিলেক্ট করলে কুকি মুছে পেজ রিলোড হবে
                        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + window.location.hostname;
                        window.location.reload();
                    } else {
                        // অন্য ভাষা হলে গুগল ট্রান্সলেটের ড্রপডাউন ট্রিগার হবে
                        let checkExist = setInterval(function() {
                            let select = document.querySelector('.goog-te-combo');
                            if (select) { 
                                select.value = val; 
                                select.dispatchEvent(new Event('change')); 
                                clearInterval(checkExist); 
                            }
                        }, 200);
                    }
                }
            }">
                    <flux:select x-model="currentLang" x-on:change="toggleTranslate($event.target.value)">
                        <flux:select.option value="bn">বাংলা</flux:select.option>
                        <flux:select.option value="en">English</flux:select.option>
                        <flux:select.option value="hi">हिन्दी (Hindi)</flux:select.option>
                        <flux:select.option value="ar">العربية (Arabic)</flux:select.option>
                        <flux:select.option value="fr">Français (French)</flux:select.option>
                        <flux:select.option value="es">Español (Spanish)</flux:select.option>
                    </flux:select>

                    <div id="google_translate_element" style="display:none !important;"></div>
                </div>
        </section>

    </x-settings.layout>
</section>