<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<footer class="p-4">

    <div class="flex flex-col items-center gap-4">

        {{-- Navigation + Cookie Button --}}
        <nav class="flex flex-wrap justify-center items-center gap-4" aria-label="ফুটার লিংক">
            <flux:link href="/about-us" variant="subtle" class="text-sm" wire:navigate>
                আমাদের সম্পর্কে
            </flux:link>
            <flux:link href="/privacy-policy" variant="subtle" class="text-sm" wire:navigate>
                গোপনীয়তা নীতি
            </flux:link>
            <flux:link href="/terms-of-service" variant="subtle" class="text-sm" wire:navigate>
                ব্যবহারের শর্তাবলী
            </flux:link>
            <flux:link href="/contact-us" variant="subtle" class="text-sm" wire:navigate>
                যোগাযোগ
            </flux:link>

            <flux:modal.trigger name="cookie-consent">
                <flux:button type="button" variant="primary" size="xs" color="blue"
                    icon="adjustments-horizontal">
                    কুকি সেটিংস
                </flux:button>
            </flux:modal.trigger>
        </nav>

        {{-- Social --}}
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" size="sm" square href="https://facebook.com/totthobox" target="_blank"
                rel="noopener noreferrer" aria-label="Facebook" icon="facebook" />
            <flux:button variant="ghost" size="sm" square href="https://x.com/totthobox" target="_blank"
                rel="noopener noreferrer" aria-label="X" icon="x" />
            <flux:button variant="ghost" size="sm" square href="https://t.me/totthobox" target="_blank"
                rel="noopener noreferrer" aria-label="Telegram" icon="paper-airplane" />
            <flux:button variant="ghost" size="sm" square href="mailto:admin@totthobox.com" aria-label="Email"
                icon="envelope" />
        </div>

        {{-- Copyright --}}
        <div class="text-center space-y-1">
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                &copy; {{ date('Y') }} Totthobox. সর্বস্বত্ব সংরক্ষিত।
            </flux:text>
            <flux:text size="xs" class="text-zinc-500">
                নির্ভরযোগ্য তথ্য ও সহজ ডিজিটাল সেবার প্রতিশ্রুতি।
            </flux:text>
        </div>
    </div>

    {{-- ====================== --}}
    {{-- Cookie Consent Modal   --}}
    {{-- ====================== --}}
    <div x-data="{
        analytics: true,
        marketing: true,
    
        init() {
            const saved = localStorage.getItem('cookie_consent');
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    this.analytics = data.analytics ?? true;
                    this.marketing = data.marketing ?? true;
                } catch (e) {
                    if (saved === 'necessary') {
                        this.analytics = false;
                        this.marketing = false;
                    }
                }
            }
        },
    
        save(all = false) {
            const preferences = {
                necessary: true,
                analytics: all ? true : this.analytics,
                marketing: all ? true : this.marketing,
                timestamp: Date.now()
            };
    
            localStorage.setItem('cookie_consent', JSON.stringify(preferences));
            $dispatch('modal-close', 'cookie-consent');
        },
    
        acceptNecessary() {
            this.analytics = false;
            this.marketing = false;
            this.save(false);
        },
    
        acceptAll() {
            this.analytics = true;
            this.marketing = true;
            this.save(true);
        }
    }">
        <flux:modal name="cookie-consent" class="max-w-md w-full">
            <div class="space-y-6">

                {{-- Header --}}
                <div>
                    <flux:heading size="sm">কুকি সেটিংস</flux:heading>
                    <flux:text size="xs" class="text-zinc-500 mt-1">
                        আপনার পছন্দ অনুযায়ী নিয়ন্ত্রণ করুন
                    </flux:text>
                </div>

                {{-- Options --}}
                <div class="space-y-5">

                    {{-- Necessary --}}
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <flux:text class="font-medium">প্রয়োজনীয় কুকি</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                সাইট সঠিকভাবে চালানোর জন্য আবশ্যক
                            </flux:text>
                        </div>
                        <flux:badge size="sm" color="zinc">সর্বদা চালু</flux:badge>
                    </div>

                    {{-- Analytics --}}
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <flux:text class="font-medium">অ্যানালিটিক্স</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                সাইট ব্যবহারের পরিসংখ্যান ও উন্নতি
                            </flux:text>
                        </div>
                        <flux:switch x-model="analytics" />
                    </div>

                    {{-- Marketing --}}
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <flux:text class="font-medium">মার্কেটিং / অ্যাডস</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                ব্যক্তিগতকৃত বিজ্ঞাপন দেখানোর জন্য
                            </flux:text>
                        </div>
                        <flux:switch x-model="marketing" />
                    </div>
                </div>

                <flux:text size="xs" class="text-zinc-500">
                    বিস্তারিত জানতে
                    <flux:link href="/privacy-policy" wire:navigate class="underline underline-offset-2">
                        গোপনীয়তা নীতি
                    </flux:link>
                    দেখুন।
                </flux:text>

                {{-- Actions --}}
                <div class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end pt-2">
                    <flux:button type="button" variant="ghost" size="sm" class="w-full"
                        @click="acceptNecessary()">
                        শুধু প্রয়োজনীয়
                    </flux:button>

                    <flux:button type="button" variant="filled" size="sm" class="w-full" @click="save(false)">
                        সংরক্ষণ করুন
                    </flux:button>

                    <flux:button type="button" variant="primary" size="sm" class="w-full" @click="acceptAll()">
                        সব গ্রহণ করুন
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</footer>
