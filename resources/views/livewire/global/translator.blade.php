<?php
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return ['locales' => config('translator.supported', [])];
    }
}; ?>

<div x-data="{
    current: 'bn',
    init() {
        // গুগল ট্রান্সলেটের কুকি ফরম্যাট রিড করা
        const match = document.cookie.match(/googtrans=\/[^/]+\/(\w+)/);
        this.current = match ? match[1] : 'bn';
    },
    setLang(lang) {
        const host = window.location.hostname;
        // রিমুভ করার জন্য সঠিক ব্যাকওয়ার্ড ডেট এক্সপায়ারি
        const expireBase = 'path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
        
        if (lang === 'bn') {
            document.cookie = `googtrans=; domain=.${host}; ${expireBase}`;
            document.cookie = `googtrans=; ${expireBase}`;
        } else {
            // বেইজ ল্যাঙ্গুয়েজ 'bn' থেকে টার্গেটেড ল্যাঙ্গুয়েজে ট্রান্সলেশন সেট করা
            const val = `/bn/${lang}`;
            document.cookie = `googtrans=${val}; domain=.${host}; path=/; SameSite=Lax;`;
            document.cookie = `googtrans=${val}; path=/; SameSite=Lax;`;
        }
        
        // রিলোড দিয়ে ট্রান্সলেশন ক্যাশ এবং ডম রি-ইনিশিয়েট করা
        window.location.reload();
    }
}" data-navigate-ignore wire:ignore class="notranslate">
    {{-- Flux UI 2 Select Component --}}
    <flux:select x-model="current" x-on:change="setLang($event.target.value)">
        @foreach ($locales as $code => $lang)
            <flux:select.option value="{{ $code }}">
                {{ $lang['flag'] ?? '' }} {{ $lang['name'] }}
            </flux:select.option>
        @endforeach
    </flux:select>
</div>

{{-- স্ক্রিপ্ট এবং হিডেন ট্রান্সলেট এলিমেন্ট --}}
@push('scripts')
    <div id="google_translate_element" style="display:none;" data-navigate-ignore></div>

    <script data-navigate-ignore>
        window.googleTranslateElementInit = function () {
            // ১. সেফটি চেক: google.translate লোড হয়েছে কিনা নিশ্চিত করা
            if (typeof google !== 'undefined' && google.translate && google.translate.TranslateElement) {
                new google.translate.TranslateElement({
                    pageLanguage: 'bn',
                    includedLanguages: 'bn,en,ar,hi',
                    // টেম্পোরারি ফিক্স: এরর এড়াতে InlineLayout অবজেক্ট অ্যাক্সেস সেফ রাখা
                    layout: google.translate.TranslateElement.InlineLayout ? google.translate.TranslateElement.InlineLayout.SIMPLE : 0,
                    autoDisplay: false
                }, 'google_translate_element');
            }
        };

        // লাইভওয়্যার SPA নেভিগেশনের পর সেফলি রান করা
        document.addEventListener('livewire:navigated', () => {
            if (typeof window.googleTranslateElementInit === 'function') {
                setTimeout(window.googleTranslateElementInit, 100);
            }
        });
    </script>
    {{-- async এবং defer দুটোই ব্যবহার করে ব্রাউজারকে সেফলি ব্যাকগ্রাউন্ডে লোড করতে দেওয়া --}}
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer
        data-navigate-ignore></script>
@endpush