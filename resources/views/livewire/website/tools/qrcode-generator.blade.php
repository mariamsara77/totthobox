<?php

use Livewire\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    public string $type = 'text';

    #[Validate('nullable|string|max:2000')]
    public string $content = '';

    // WiFi fields
    public string $wifiSsid = '';
    public string $wifiPassword = '';
    public string $wifiEncryption = 'WPA';
    public bool $wifiHidden = false;

    // vCard fields
    public string $vcardName = '';
    public string $vcardPhone = '';
    public string $vcardEmail = '';
    public string $vcardOrg = '';
    public string $vcardUrl = '';

    // SMS fields
    public string $smsNumber = '';
    public string $smsMessage = '';

    // Style / customization
    public int $size = 320;
    public int $margin = 10;
    public string $fgColor = '#000000';
    public string $fgColor2 = '#000000';
    public bool $useGradient = false;
    public string $gradientType = 'linear';
    public string $bgColor = '#ffffff';
    public string $ecc = 'M';
    public string $dotStyle = 'rounded';
    public string $cornerSquareStyle = 'extra-rounded';
    public string $cornerDotStyle = 'dot';

    public array $history = [];

    public function updatedType(): void
    {
        $this->content = '';
        $this->wifiSsid = '';
        $this->wifiPassword = '';
        $this->vcardName = '';
        $this->vcardPhone = '';
        $this->vcardEmail = '';
        $this->vcardOrg = '';
        $this->vcardUrl = '';
        $this->smsNumber = '';
        $this->smsMessage = '';
    }

    public function fillExample(string $type, string $content): void
    {
        $this->type = $type;
        $this->content = $content;
    }

    public function fillWifiExample(): void
    {
        $this->type = 'wifi';
        $this->wifiSsid = 'MyHomeWiFi';
        $this->wifiPassword = 'password123';
        $this->wifiEncryption = 'WPA';
        $this->wifiHidden = false;
    }

    public function fillVcardExample(): void
    {
        $this->type = 'vcard';
        $this->vcardName = 'রহিম উদ্দিন';
        $this->vcardPhone = '+8801712345678';
        $this->vcardEmail = 'rahim@example.com';
        $this->vcardOrg = 'Totthobox';
        $this->vcardUrl = 'https://totthobox.com';
    }

    public function fillSmsExample(): void
    {
        $this->type = 'sms';
        $this->smsNumber = '+8801712345678';
        $this->smsMessage = 'আসসালামু আলাইকুম';
    }

    public function resetContent(): void
    {
        $this->updatedType();
    }

    public function resetStyle(): void
    {
        $this->size = 320;
        $this->margin = 10;
        $this->fgColor = '#000000';
        $this->fgColor2 = '#000000';
        $this->useGradient = false;
        $this->gradientType = 'linear';
        $this->bgColor = '#ffffff';
        $this->ecc = 'M';
        $this->dotStyle = 'rounded';
        $this->cornerSquareStyle = 'extra-rounded';
        $this->cornerDotStyle = 'dot';
    }

    public function resetAll(): void
    {
        $this->resetContent();
        $this->resetStyle();
    }

    public function clearHistory(): void
    {
        $this->history = [];
        $this->js("localStorage.removeItem('qr_gen_history_v2')");
    }

    public function addToHistory(string $label): void
    {
        $entry = [
            'label' => \Illuminate\Support\Str::limit($label, 48),
            'time' => now()->format('H:i'),
        ];

        if (($this->history[0]['label'] ?? null) === $entry['label']) {
            return;
        }

        array_unshift($this->history, $entry);
        $this->history = array_slice($this->history, 0, 8);
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
QR CODE GENERATOR — Advanced / Professional
Reliable generation (auto-fallback CDN) · SEO · AdSense · Flux UI 2
═══════════════════════════════════════════════════════════════ --}}

<div class="max-w-2xl mx-auto space-y-6" x-data="qrGenerator()" x-init="init()" wire:ignore.self>

    <x-seo title="QR কোড জেনারেটর — ফ্রি অ্যাডভান্সড অনলাইন QR Code Generator (বাংলা) | Free QR Maker"
        description="সবচেয়ে ফিচার-সমৃদ্ধ ফ্রি QR কোড জেনারেটর। টেক্সট, লিংক, ওয়াইফাই, ইমেইল, ফোন, SMS, ভিকার্ড থেকে তাৎক্ষণিক QR বানান। রঙ, গ্রেডিয়েন্ট, ডট স্টাইল, লোগো, সাইজ কাস্টমাইজ + PNG/SVG/JPEG ডাউনলোড। রেজিস্ট্রেশন লাগবে না।"
        keywords="qr code generator, QR কোড জেনারেটর, free qr maker, qr code online, wifi qr code, vcard qr code, logo qr code, qr code download, বাংলা QR জেনারেটর, অনলাইন QR কোড" />

    {{-- ══════════════════════════════════════
    Page Header (Only one H1)
    ══════════════════════════════════════ --}}
    <header class="text-center space-y-2">
        <flux:badge color="lime" size="sm">বিনামূল্যে · রেজিস্ট্রেশন লাগবে না</flux:badge>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
            QR কোড জেনারেটর
        </h1>
        <p class="text-base text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto">
            টেক্সট, লিংক, ওয়াইফাই, SMS, ভিকার্ড বা ফোন নম্বর লিখুন — সাথে সাথে প্রফেশনাল QR কোড তৈরি হবে।
        </p>
    </header>

    {{-- Library load failure banner --}}
    <div x-show="libFailed" x-cloak
        class="rounded-xl border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center justify-between gap-4">
        <p class="text-sm text-red-700 dark:text-red-300">QR লাইব্রেরি লোড হয়নি। ইন্টারনেট সংযোগ পরীক্ষা করুন।</p>
        <flux:button size="xs" variant="danger" @click="retryLib()">আবার চেষ্টা করুন</flux:button>
    </div>

    {{-- ══════════════════════════════════════
    Converter Tool
    ══════════════════════════════════════ --}}
    <section aria-labelledby="qr-tool-heading">
        <h2 id="qr-tool-heading" class="sr-only">QR কোড তৈরির টুল</h2>

        <flux:tab.group>
            <flux:tabs wire:model.live="type" scrollable scrollable:fade class="mb-6" aria-label="QR ধরন নির্বাচন">
                <flux:tab name="text" icon="document-text">টেক্সট</flux:tab>
                <flux:tab name="url" icon="link">লিংক / URL</flux:tab>
                <flux:tab name="wifi" icon="wifi">ওয়াইফাই</flux:tab>
                <flux:tab name="email" icon="envelope">ইমেইল</flux:tab>
                <flux:tab name="phone" icon="phone">ফোন</flux:tab>
                <flux:tab name="sms" icon="chat-bubble-left-right">SMS</flux:tab>
                <flux:tab name="vcard" icon="identification">ভিকার্ড</flux:tab>
            </flux:tabs>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {{-- Inputs --}}
                <div class="lg:col-span-3 space-y-5">
                    <flux:card class="space-y-5">
                        <div class="space-y-4">
                            <flux:tab.panel name="text">
                                <flux:textarea wire:model.live.debounce.300ms="content" label="যা লিখতে চান"
                                    placeholder="যেকোনো টেক্সট..." rows="4"
                                    aria-label="QR এর জন্য টেক্সট লিখুন" />
                            </flux:tab.panel>

                            <flux:tab.panel name="url">
                                <flux:input type="url" wire:model.live.debounce.300ms="content"
                                    label="ওয়েবসাইট লিংক" placeholder="https://example.com" icon="link"
                                    aria-label="URL লিখুন" />
                            </flux:tab.panel>

                            <flux:tab.panel name="wifi">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <flux:input wire:model.live.debounce.300ms="wifiSsid" label="নেটওয়ার্ক নাম (SSID)"
                                        placeholder="MyHomeWiFi" icon="wifi" />
                                    <flux:input type="text" wire:model.live.debounce.300ms="wifiPassword"
                                        label="পাসওয়ার্ড" placeholder="password123" icon="lock-closed" />
                                    <flux:select wire:model.live="wifiEncryption" label="এনক্রিপশন ধরন">
                                        <option value="WPA">WPA/WPA2</option>
                                        <option value="WEP">WEP</option>
                                        <option value="nopass">পাসওয়ার্ড নেই (Open)</option>
                                    </flux:select>
                                    <label class="flex items-center gap-2 mt-6">
                                        <flux:checkbox wire:model.live="wifiHidden" />
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">হিডেন নেটওয়ার্ক</span>
                                    </label>
                                </div>
                            </flux:tab.panel>

                            <flux:tab.panel name="email">
                                <flux:input type="email" wire:model.live.debounce.300ms="content" label="ইমেইল ঠিকানা"
                                    placeholder="name@example.com" icon="envelope" aria-label="ইমেইল ঠিকানা লিখুন" />
                            </flux:tab.panel>

                            <flux:tab.panel name="phone">
                                <flux:input type="tel" wire:model.live.debounce.300ms="content" label="ফোন নম্বর"
                                    placeholder="+8801XXXXXXXXX" icon="phone" aria-label="ফোন নম্বর লিখুন" />
                            </flux:tab.panel>

                            <flux:tab.panel name="sms">
                                <div class="grid grid-cols-1 gap-4">
                                    <flux:input type="tel" wire:model.live.debounce.300ms="smsNumber"
                                        label="প্রাপকের নম্বর" placeholder="+8801XXXXXXXXX" icon="phone" />
                                    <flux:textarea wire:model.live.debounce.300ms="smsMessage" label="বার্তা (ঐচ্ছিক)"
                                        placeholder="আপনার বার্তা লিখুন..." rows="3" />
                                </div>
                            </flux:tab.panel>

                            <flux:tab.panel name="vcard">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <flux:input wire:model.live.debounce.300ms="vcardName" label="পূর্ণ নাম"
                                        placeholder="রহিম উদ্দিন" icon="user" />
                                    <flux:input wire:model.live.debounce.300ms="vcardPhone" type="tel"
                                        label="ফোন নম্বর" placeholder="+8801XXXXXXXXX" icon="phone" />
                                    <flux:input wire:model.live.debounce.300ms="vcardEmail" type="email"
                                        label="ইমেইল" placeholder="name@example.com" icon="envelope" />
                                    <flux:input wire:model.live.debounce.300ms="vcardOrg" label="প্রতিষ্ঠান (ঐচ্ছিক)"
                                        placeholder="Totthobox" icon="building-office" />
                                    <flux:input wire:model.live.debounce.300ms="vcardUrl" type="url"
                                        label="ওয়েবসাইট (ঐচ্ছিক)" placeholder="https://example.com" icon="link"
                                        class="sm:col-span-2" />
                                </div>
                            </flux:tab.panel>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-zinc-400/25 pt-4">
                            <flux:button variant="subtle" size="sm" icon="arrow-path" wire:click="resetContent"
                                aria-label="কন্টেন্ট রিসেট">
                                রিসেট
                            </flux:button>

                            <flux:tab.panel name="text">
                                <flux:button size="sm" variant="ghost"
                                    wire:click="fillExample('text', 'আসসালামু আলাইকুম! এটি একটি টেস্ট QR কোড।')">
                                    বাংলা টেক্সট
                                </flux:button>
                            </flux:tab.panel>

                            <flux:tab.panel name="url">
                                <flux:button size="sm" variant="ghost"
                                    wire:click="fillExample('url', 'https://google.com')">Google</flux:button>
                                <flux:button size="sm" variant="ghost"
                                    wire:click="fillExample('url', 'https://youtube.com')">YouTube</flux:button>
                            </flux:tab.panel>

                            <flux:tab.panel name="wifi">
                                <flux:button size="sm" variant="ghost" wire:click="fillWifiExample">ওয়াইফাই
                                    উদাহরণ</flux:button>
                            </flux:tab.panel>

                            <flux:tab.panel name="email">
                                <flux:button size="sm" variant="ghost"
                                    wire:click="fillExample('email', 'hello@example.com')">ইমেইল</flux:button>
                            </flux:tab.panel>

                            <flux:tab.panel name="phone">
                                <flux:button size="sm" variant="ghost"
                                    wire:click="fillExample('phone', '+8801712345678')">ফোন</flux:button>
                            </flux:tab.panel>

                            <flux:tab.panel name="sms">
                                <flux:button size="sm" variant="ghost" wire:click="fillSmsExample">SMS উদাহরণ
                                </flux:button>
                            </flux:tab.panel>

                            <flux:tab.panel name="vcard">
                                <flux:button size="sm" variant="ghost" wire:click="fillVcardExample">ভিকার্ড
                                    উদাহরণ</flux:button>
                            </flux:tab.panel>
                        </div>
                    </flux:card>

                    {{-- Advanced styling --}}
                    <details
                        class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden"
                        open>
                        <summary
                            class="flex items-center justify-between cursor-pointer px-4 py-3 font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <span class="flex items-center gap-4">
                                <flux:icon name="adjustments-horizontal" variant="micro" aria-hidden="true" />
                                অ্যাডভান্সড কাস্টমাইজেশন
                            </span>
                            <flux:icon.chevron-down variant="micro"
                                class="text-zinc-400 group-open:rotate-180 transition" aria-hidden="true" />
                        </summary>

                        <div class="px-4 pb-4 space-y-5">
                            {{-- Size / margin / ecc --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <flux:label>সাইজ (পিক্সেল)</flux:label>
                                    <div class="flex items-center gap-4 mt-1.5">
                                        <input type="range" min="128" max="1024" step="8"
                                            wire:model.live="size" class="w-full accent-emerald-500"
                                            aria-label="QR সাইজ" />
                                        <span class="text-sm font-mono w-14 text-right tabular-nums"
                                            x-text="$wire.size"></span>
                                    </div>
                                    <div class="flex gap-2 mt-2">
                                        <flux:button size="xs" variant="ghost" wire:click="$set('size', 256)">
                                            ছোট</flux:button>
                                        <flux:button size="xs" variant="ghost" wire:click="$set('size', 512)">
                                            মাঝারি</flux:button>
                                        <flux:button size="xs" variant="ghost" wire:click="$set('size', 1024)">
                                            বড়</flux:button>
                                    </div>
                                </div>
                                <div>
                                    <flux:label>কোয়ায়েট জোন / মার্জিন</flux:label>
                                    <div class="flex items-center gap-4 mt-1.5">
                                        <input type="range" min="0" max="40" step="1"
                                            wire:model.live="margin" class="w-full accent-emerald-500"
                                            aria-label="মার্জিন" />
                                        <span class="text-sm font-mono w-14 text-right tabular-nums"
                                            x-text="$wire.margin"></span>
                                    </div>
                                </div>
                                <div>
                                    <flux:select wire:model.live="ecc" label="এরর করেকশন"
                                        aria-label="Error correction level">
                                        <option value="L">কম (L) — ~7%</option>
                                        <option value="M">মাঝারি (M) — ~15%</option>
                                        <option value="Q">উচ্চ (Q) — ~25%</option>
                                        <option value="H">সর্বোচ্চ (H) — ~30%</option>
                                    </flux:select>
                                    <p class="text-xs text-zinc-400 mt-1">লোগো যোগ করলে "সর্বোচ্চ (H)" বেছে নেওয়ার
                                        পরামর্শ দেওয়া হয়।</p>
                                </div>
                            </div>

                            {{-- Colors --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <flux:label>QR রঙ</flux:label>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <input type="color" wire:model.live="fgColor"
                                            class="h-10 w-14 rounded-lg border cursor-pointer" aria-label="QR রঙ" />
                                        <flux:input type="text" wire:model.live="fgColor"
                                            class="font-mono text-sm" />
                                    </div>
                                </div>
                                <div>
                                    <flux:label>ব্যাকগ্রাউন্ড</flux:label>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <input type="color" wire:model.live="bgColor"
                                            class="h-10 w-14 rounded-lg border cursor-pointer"
                                            aria-label="ব্যাকগ্রাউন্ড রঙ" />
                                        <flux:input type="text" wire:model.live="bgColor"
                                            class="font-mono text-sm" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="flex items-center gap-4">
                                    <flux:checkbox wire:model.live="useGradient" />
                                    <span class="text-sm text-zinc-600 dark:text-zinc-300">গ্রেডিয়েন্ট রঙ ব্যবহার
                                        করুন</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3" x-show="$wire.useGradient"
                                    x-cloak>
                                    <div>
                                        <flux:label>দ্বিতীয় রঙ</flux:label>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <input type="color" wire:model.live="fgColor2"
                                                class="h-10 w-14 rounded-lg border cursor-pointer"
                                                aria-label="গ্রেডিয়েন্ট দ্বিতীয় রঙ" />
                                            <flux:input type="text" wire:model.live="fgColor2"
                                                class="font-mono text-sm" />
                                        </div>
                                    </div>
                                    <flux:select wire:model.live="gradientType" label="গ্রেডিয়েন্ট ধরন">
                                        <option value="linear">লিনিয়ার</option>
                                        <option value="radial">রেডিয়াল</option>
                                    </flux:select>
                                </div>
                            </div>

                            {{-- Dot & corner styles --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <flux:select wire:model.live="dotStyle" label="ডট স্টাইল">
                                    <option value="square">স্কয়ার</option>
                                    <option value="dots">ডটস</option>
                                    <option value="rounded">রাউন্ডেড</option>
                                    <option value="classy">ক্ল্যাসি</option>
                                    <option value="classy-rounded">ক্ল্যাসি রাউন্ডেড</option>
                                    <option value="extra-rounded">এক্সট্রা রাউন্ডেড</option>
                                </flux:select>
                                <flux:select wire:model.live="cornerSquareStyle" label="কর্নার স্টাইল">
                                    <option value="square">স্কয়ার</option>
                                    <option value="dot">ডট</option>
                                    <option value="extra-rounded">এক্সট্রা রাউন্ডেড</option>
                                </flux:select>
                                <flux:select wire:model.live="cornerDotStyle" label="কর্নার ডট স্টাইল">
                                    <option value="square">স্কয়ার</option>
                                    <option value="dot">ডট</option>
                                </flux:select>
                            </div>

                            {{-- Logo upload --}}
                            <div class="space-y-2">
                                <flux:label>লোগো (ঐচ্ছিক)</flux:label>
                                <div class="flex flex-wrap items-center gap-4">
                                    <input type="file" accept="image/*" @change="onLogoChange($event)"
                                        class="text-sm text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file: file:bg-zinc-100 dark:file:bg-zinc-800 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700 cursor-pointer" />
                                    <flux:button size="xs" variant="ghost" x-show="logoDataUrl" x-cloak
                                        @click="removeLogo()">লোগো সরان</flux:button>
                                </div>
                                <p class="text-xs text-zinc-400">লোগো QR এর মাঝখানে বসবে। স্ক্যান করতে সমস্যা হলে এরর
                                    করেকশন "সর্বোচ্চ (H)" করুন।</p>
                            </div>

                            <div>
                                <flux:button variant="subtle" size="sm" icon="arrow-path"
                                    wire:click="resetStyle">স্টাইল
                                    রিসেট করুন</flux:button>
                            </div>
                        </div>
                    </details>
                </div>

                {{-- Preview --}}
                <div class="lg:col-span-2">
                    <flux:card class="sticky top-6 space-y-4">
                        <div class="text-center">
                            <flux:badge color="emerald" size="sm" class="mb-3">লাইভ প্রিভিউ</flux:badge>

                            <div class="flex justify-center items-center p-4 bg-zinc-400/10 rounded-xl border border-dashed border-zinc-400/25 min-h-[220px]"
                                wire:ignore>
                                <div x-show="!hasContent" class="text-center text-zinc-400" x-cloak>
                                    <flux:icon name="qr-code" class="mx-auto size-12 mb-2 opacity-40"
                                        aria-hidden="true" />
                                    <p class="text-sm">কন্টেন্ট লিখুন</p>
                                </div>
                                <div id="qr-preview-container"
                                    class="[&>canvas]:rounded-lg [&>canvas]:shadow-sm [&>canvas]:max-w-full [&>svg]:rounded-lg [&>svg]:shadow-sm [&>svg]:max-w-full"
                                    :class="hasContent ? '' : 'hidden'" role="img" aria-label="জেনারেটেড QR কোড">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4" x-show="hasContent" x-cloak>
                            <div class="grid grid-cols-3 gap-4">
                                <flux:button variant="primary" size="sm" @click="download('png')"
                                    aria-label="PNG ডাউনলোড">PNG</flux:button>
                                <flux:button variant="primary" size="sm" @click="download('jpeg')"
                                    aria-label="JPEG ডাউনলোড">JPEG</flux:button>
                                <flux:button variant="primary" size="sm" @click="download('svg')"
                                    aria-label="SVG ডাউনলোড">SVG</flux:button>
                            </div>
                            <flux:button variant="subtle" icon="clipboard-document" class="w-full"
                                @click="copyImage()" aria-label="QR কোড ছবি কপি">
                                ছবি কপি করুন
                            </flux:button>
                            <flux:button variant="subtle" icon="document-duplicate" class="w-full"
                                @click="copyText()" aria-label="ডেটা কপি">
                                ডেটা টেক্সট কপি করুন
                            </flux:button>
                            <flux:button variant="ghost" icon="share" class="w-full" x-show="canShare" x-cloak
                                @click="shareQR()" aria-label="শেয়ার করুন">
                                শেয়ার করুন
                            </flux:button>
                        </div>
                    </flux:card>
                </div>
            </div>
        </flux:tab.group>
    </section>

    {{-- History --}}
    @if (count($history) > 0)
        <section class="mt-2" aria-labelledby="history-heading">
            <div class="flex items-center justify-between mb-3">
                <h2 id="history-heading" class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">সাম্প্রতিক QR
                </h2>
                <flux:button size="xs" variant="ghost" wire:click="clearHistory"
                    aria-label="হিস্টরি মুছে ফেলুন">
                    সব মুছে ফেলুন
                </flux:button>
            </div>
            <div class="flex flex-wrap gap-4">
                @foreach ($history as $h)
                    <flux:badge color="zinc" size="sm">
                        {{ $h['label'] }}
                        <span class="opacity-50 ml-1">{{ $h['time'] }}</span>
                    </flux:badge>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ══════════════════════════════════════
    How to use
    ══════════════════════════════════════ --}}
    <section class="space-y-3" aria-labelledby="howto-heading">
        <h2 id="howto-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            কীভাবে ব্যবহার করবেন?
        </h2>
        <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
            ট্যাব থেকে ধরন বেছে নিন → তথ্য লিখুন → QR কোড সাথে সাথে দেখাবে → PNG/JPEG/SVG ফরম্যাটে ডাউনলোড করুন।
            রঙ, গ্রেডিয়েন্ট, ডট স্টাইল, কর্নার স্টাইল, লোগো, সাইজ ও এরর করেকশন ইচ্ছেমতো বদলাতে পারবেন।
        </p>
    </section>

    {{-- ══════════════════════════════════════
    About (AdSense + SEO)
    ══════════════════════════════════════ --}}
    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3" aria-labelledby="about-qr">
        <h2 id="about-qr" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            ফ্রি অ্যাডভান্সড অনলাইন QR কোড জেনারেটর
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                এই টুল দিয়ে <strong>টেক্সট, URL, WiFi, ইমেইল, ফোন নম্বর, SMS ও ভিকার্ড (vCard)</strong> থেকে তাৎক্ষণিক
                প্রফেশনাল QR কোড তৈরি করুন।
                সম্পূর্ণ ফ্রি, রেজিস্ট্রেশন লাগে না, আর সবকিছু আপনার ব্রাউজারেই হয় — কোনো ডেটা সার্ভারে যায় না।
            </p>
            <p>
                রঙ, গ্রেডিয়েন্ট, ডট ও কর্নার স্টাইল, লোগো এবং সাইজ কাস্টমাইজ করে PNG, JPEG বা SVG ফরম্যাটে ডাউনলোড করতে
                পারবেন। ফাইল নামে
                <strong>Totthobox_qr_generator</strong> থাকবে যাতে সহজে চিনতে পারেন।
            </p>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="space-y-3" aria-labelledby="faq-heading">
        <h2 id="faq-heading" class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            প্রায়শই জিজ্ঞাসিত প্রশ্ন
        </h2>

        <div class="space-y-2">
            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>এই QR জেনারেটর কি ফ্রি?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    হ্যাঁ। সম্পূর্ণ ফ্রি, কোনো রেজিস্ট্রেশন বা ওয়াটারমার্ক নেই।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>কোন কোন ধরনের QR বানানো যায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    টেক্সট, ওয়েব লিংক (URL), WiFi, ইমেইল, ফোন নম্বর, SMS এবং ভিকার্ড (vCard) — এই সাত ধরন সাপোর্টেড।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>লোগো সহ QR স্ক্যান হবে তো?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    হ্যাঁ, তবে এরর করেকশন লেভেল "সর্বোচ্চ (H)" রাখুন এবং লোগো খুব বড় না করাই ভালো, যাতে স্ক্যানার সহজে
                    পড়তে পারে।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>ডাটা কি সার্ভারে যায়?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    না। QR কোড সম্পূর্ণ আপনার ব্রাউজারে তৈরি হয়। কোনো কন্টেন্ট বা লোগো সার্ভারে আপলোড হয় না।
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-400/25 bg-white dark:bg-zinc-900 overflow-hidden">
                <summary
                    class="flex items-center justify-between cursor-pointer px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <span>কীভাবে ডাউনলোড করব?</span>
                    <flux:icon.chevron-down variant="micro" class="text-zinc-400 group-open:rotate-180 transition"
                        aria-hidden="true" />
                </summary>
                <div class="px-4 pb-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    QR দেখা গেলে PNG, JPEG বা SVG বাটনে ক্লিক করুন। ফাইল নাম হবে
                    Totthobox_qr_generator_তারিখ-সময়.[extension]।
                </div>
            </details>
        </div>
    </section>

</div>
@assets
    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <script>
        function qrGenerator() {
            return {
                hasContent: false,
                lastKey: '',
                _ready: false,
                libFailed: false,
                qrCode: null,
                logoDataUrl: null,
                canShare: typeof navigator !== 'undefined' && !!navigator.share,

                init() {
                    console.log('[QR] init() called');

                    try {
                        const saved = localStorage.getItem('qr_gen_history_v2');
                        if (saved) {
                            const parsed = JSON.parse(saved);
                            if (Array.isArray(parsed) && parsed.length) this.$wire.set('history', parsed);
                        }
                    } catch (e) {}

                    this.$wire.$watch('history', (value) => {
                        try {
                            localStorage.setItem('qr_gen_history_v2', JSON.stringify(value ?? []));
                        } catch (e) {}
                    });

                    const fields = [
                        'content', 'type',
                        'wifiSsid', 'wifiPassword', 'wifiEncryption', 'wifiHidden',
                        'vcardName', 'vcardPhone', 'vcardEmail', 'vcardOrg', 'vcardUrl',
                        'smsNumber', 'smsMessage',
                        'size', 'margin', 'fgColor', 'fgColor2', 'useGradient', 'gradientType',
                        'bgColor', 'ecc', 'dotStyle', 'cornerSquareStyle', 'cornerDotStyle',
                    ];
                    fields.forEach((f) => this.$wire.$watch(f, () => {
                        console.log('[QR] field changed:', f);
                        this.generate();
                    }));

                    this.waitForLib();
                },

                waitForLib(attempt = 0) {
                    console.log('[QR] waitForLib attempt', attempt, 'typeof QRCodeStyling =', typeof QRCodeStyling);
                    if (typeof QRCodeStyling !== 'undefined') {
                        console.log('[QR] library ready!');
                        this._ready = true;
                        this.generate();
                        return;
                    }
                    if (attempt > 50) {
                        console.error('[QR] library never became available after 50 attempts');
                        this.libFailed = true;
                        return;
                    }
                    setTimeout(() => this.waitForLib(attempt + 1), 100);
                },

                escapeWifi(v) {
                    return String(v ?? '').replace(/([\\;,:"])/g, '\\$1');
                },

                getFinalContent() {
                    const type = this.$wire.type;
                    switch (type) {
                        case 'url': {
                            let raw = (this.$wire.content || '').trim();
                            if (!raw) return '';
                            if (!/^https?:\/\//i.test(raw)) raw = 'https://' + raw;
                            return raw;
                        }
                        case 'email': {
                            const raw = (this.$wire.content || '').trim();
                            if (!raw) return '';
                            return raw.startsWith('mailto:') ? raw : 'mailto:' + raw;
                        }
                        case 'phone': {
                            const raw = (this.$wire.content || '').trim();
                            if (!raw) return '';
                            return raw.startsWith('tel:') ? raw : 'tel:' + raw.replace(/\s+/g, '');
                        }
                        case 'wifi': {
                            const ssid = (this.$wire.wifiSsid || '').trim();
                            if (!ssid) return '';
                            const pass = this.$wire.wifiPassword || '';
                            const enc = this.$wire.wifiEncryption || 'WPA';
                            const hidden = this.$wire.wifiHidden ? 'true' : 'false';
                            if (enc === 'nopass') return `WIFI:T:nopass;S:${this.escapeWifi(ssid)};H:${hidden};;`;
                            return `WIFI:T:${enc};S:${this.escapeWifi(ssid)};P:${this.escapeWifi(pass)};H:${hidden};;`;
                        }
                        case 'sms': {
                            const num = (this.$wire.smsNumber || '').trim();
                            if (!num) return '';
                            return `SMSTO:${num}:${this.$wire.smsMessage || ''}`;
                        }
                        case 'vcard': {
                            const name = (this.$wire.vcardName || '').trim();
                            if (!name) return '';
                            const lines = ['BEGIN:VCARD', 'VERSION:3.0', `N:${name}`, `FN:${name}`];
                            if (this.$wire.vcardOrg) lines.push(`ORG:${this.$wire.vcardOrg}`);
                            if (this.$wire.vcardPhone) lines.push(`TEL;TYPE=CELL:${this.$wire.vcardPhone}`);
                            if (this.$wire.vcardEmail) lines.push(`EMAIL:${this.$wire.vcardEmail}`);
                            if (this.$wire.vcardUrl) lines.push(`URL:${this.$wire.vcardUrl}`);
                            lines.push('END:VCARD');
                            return lines.join('\n');
                        }
                        default:
                            return (this.$wire.content || '').trim();
                    }
                },

                buildOptions(data) {
                    const dotsOptions = {
                        type: this.$wire.dotStyle || 'rounded'
                    };
                    if (this.$wire.useGradient) {
                        dotsOptions.gradient = {
                            type: this.$wire.gradientType || 'linear',
                            rotation: 0,
                            colorStops: [{
                                    offset: 0,
                                    color: this.$wire.fgColor || '#000000'
                                },
                                {
                                    offset: 1,
                                    color: this.$wire.fgColor2 || '#000000'
                                },
                            ],
                        };
                    } else {
                        dotsOptions.color = this.$wire.fgColor || '#000000';
                    }
                    const options = {
                        width: Number(this.$wire.size) || 320,
                        height: Number(this.$wire.size) || 320,
                        type: 'canvas',
                        data,
                        margin: Number(this.$wire.margin) || 0,
                        qrOptions: {
                            errorCorrectionLevel: this.$wire.ecc || 'M'
                        },
                        dotsOptions,
                        backgroundOptions: {
                            color: this.$wire.bgColor || '#ffffff'
                        },
                        cornersSquareOptions: {
                            type: this.$wire.cornerSquareStyle || 'extra-rounded',
                            color: this.$wire.fgColor || '#000000'
                        },
                        cornersDotOptions: {
                            type: this.$wire.cornerDotStyle || 'dot',
                            color: this.$wire.fgColor || '#000000'
                        },
                    };
                    if (this.logoDataUrl) {
                        options.image = this.logoDataUrl;
                        options.imageOptions = {
                            crossOrigin: 'anonymous',
                            margin: 6,
                            imageSize: 0.4,
                            hideBackgroundDots: true
                        };
                    }
                    return options;
                },

                labelFor(data) {
                    const map = {
                        url: 'লিংক',
                        wifi: 'ওয়াইফাই',
                        email: 'ইমেইল',
                        phone: 'ফোন',
                        sms: 'SMS',
                        vcard: 'ভিকার্ড',
                        text: 'টেক্সট'
                    };
                    return `[${map[this.$wire.type] || this.$wire.type}] ` + data.replace(/\n/g, ' ');
                },

                async generate() {
                    const data = this.getFinalContent();
                    console.log('[QR] generate() called, data =', JSON.stringify(data), 'ready =', this._ready);

                    if (!data) {
                        this.hasContent = false;
                        return;
                    }
                    if (!this._ready || typeof QRCodeStyling === 'undefined') {
                        console.warn('[QR] library not ready yet, skipping draw');
                        return;
                    }

                    // ★★★ এই লাইনটাই মূল ফিক্স — $refs এর বদলে সরাসরি DOM lookup ★★★
                    const container = document.getElementById('qr-preview-container');
                    if (!container) {
                        console.error('[QR] qr-preview-container element not found in DOM!');
                        return;
                    }

                    try {
                        const options = this.buildOptions(data);
                        if (!this.qrCode) {
                            console.log('[QR] creating new QRCodeStyling instance');
                            this.qrCode = new QRCodeStyling(options);
                            container.innerHTML = '';
                            this.qrCode.append(container);
                        } else {
                            this.qrCode.update(options);
                        }
                        this.hasContent = true;
                        console.log('[QR] draw complete, container children =', container.children.length);

                        const key = this.$wire.type + '::' + data;
                        if (key !== this.lastKey && data.length > 1) {
                            this.lastKey = key;
                            this.$wire.addToHistory(this.labelFor(data));
                        }
                    } catch (err) {
                        console.error('[QR] generation error:', err);
                        this.hasContent = false;
                    }
                },

                onLogoChange(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = () => {
                        this.logoDataUrl = reader.result;
                        this.generate();
                    };
                    reader.readAsDataURL(file);
                },
                removeLogo() {
                    this.logoDataUrl = null;
                    const input = this.$el.querySelector('input[type="file"]');
                    if (input) input.value = '';
                    this.generate();
                },
                timestamp() {
                    const now = new Date();
                    const pad = n => String(n).padStart(2, '0');
                    return now.getFullYear() + pad(now.getMonth() + 1) + pad(now.getDate()) + '_' + pad(now.getHours()) +
                        pad(now.getMinutes()) + pad(now.getSeconds());
                },
                async download(ext) {
                    if (!this.qrCode || !this.hasContent) return;
                    await this.qrCode.download({
                        name: `Totthobox_qr_generator_${this.timestamp()}`,
                        extension: ext
                    });
                },
                async copyImage() {
                    if (!this.qrCode || !this.hasContent) return;
                    try {
                        const blob = await this.qrCode.getRawData('png');
                        await navigator.clipboard.write([new ClipboardItem({
                            'image/png': blob
                        })]);
                        window.$flux?.toast?.('QR কোড কপি হয়েছে!');
                    } catch (e) {
                        window.$flux?.toast?.('কপি করা যায়নি, ডাউনলোড ব্যবহার করুন');
                    }
                },
                async copyText() {
                    const data = this.getFinalContent();
                    if (!data) return;
                    try {
                        await navigator.clipboard.writeText(data);
                        window.$flux?.toast?.('টেক্সট কপি হয়েছে!');
                    } catch (e) {}
                },
                async shareQR() {
                    if (!this.qrCode || !this.hasContent || !navigator.share) return;
                    try {
                        const blob = await this.qrCode.getRawData('png');
                        const file = new File([blob], 'qr-code.png', {
                            type: 'image/png'
                        });
                        await navigator.share({
                            files: [file],
                            title: 'QR কোড'
                        });
                    } catch (e) {}
                },
            };
        }
    </script>
@endassets
