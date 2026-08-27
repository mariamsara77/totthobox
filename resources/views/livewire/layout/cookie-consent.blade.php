<?php
use Livewire\Volt\Component;
new class extends Component {
    //
}; ?>
{{-- Cookie Consent — CLS-safe + 3 second delayed load --}}
<div x-data="{ show: false }" x-init="setTimeout(() => {
    if (!localStorage.getItem('cookie_consent')) {
        show = true;
    }
}, 3000);" x-show="show" x-cloak x-transition
    class="fixed bottom-0 inset-x-0 z-50 p-4 pointer-events-none" role="dialog" aria-label="কুকি সম্মতি">
    <div
        class="pointer-events-auto max-w-3xl mx-auto bg-zinc-50 dark:bg-zinc-700 border border-zinc-400/25 rounded-2xl p-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            {{-- Text --}}
            <div class="flex-1 space-y-1">
                <flux:heading size="sm">
                    কুকি ও গোপনীয়তা
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-300">
                    আমরা সাইটের অভিজ্ঞতা ভালো করতে কুকি ব্যবহার করি।
                    চালিয়ে গেলে আপনি আমাদের
                    <flux:link href="/privacy-policy" wire:navigate>গোপনীয়তা নীতি</flux:link>
                    মেনে নিচ্ছেন।
                </flux:text>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 shrink-0">
                <flux:button href="/privacy-policy" wire:navigate variant="ghost" size="sm">
                    বিস্তারিত
                </flux:button>
                <flux:button type="button" variant="primary" size="sm"
                    x-on:click="
                        localStorage.setItem('cookie_consent', '1');
                        show = false;
                    ">
                    সম্মত
                </flux:button>
            </div>
        </div>
    </div>
</div>
