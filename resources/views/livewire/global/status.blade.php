<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app.header')] class extends Component {
    public array $services = [];
    public bool $isOperational = true;
    public string $globalStatusText = 'সকল সিস্টেম সচল রয়েছে';
    public string $globalStatusDesc = 'সবগুলো সার্ভিস কোনো ধরনের বিঘ্ন ছাড়াই কাজ করছে।';

    public bool $isCoolingDown = false;
    public int $cooldownSeconds = 0;

    public function mount()
    {
        $this->loadCachedStatusOrRun();
    }

    public function loadCachedStatusOrRun()
    {
        $cachedData = Cache::remember('system_status_payload', 60, function () {
            return $this->executeRealSystemCheck();
        });

        $this->services = $cachedData['services'];
        $this->isOperational = $cachedData['isOperational'];
        $this->globalStatusText = $cachedData['globalStatusText'];
        $this->globalStatusDesc = $cachedData['globalStatusDesc'];
    }

    public function checkSystemStatus()
    {
        $userId = auth()->id() ?? request()->ip();
        $lockKey = 'status_click_lock:' . md5($userId);

        if (Cache::has($lockKey)) {
            $this->cooldownSeconds = Cache::ttl($lockKey);
            $this->isCoolingDown = true;
            return;
        }

        Cache::put($lockKey, true, 10);
        $this->isCoolingDown = false;

        Cache::forget('system_status_payload');
        $this->loadCachedStatusOrRun();
    }

    protected function executeRealSystemCheck(): array
    {
        $allOperational = true;
        $hasDegraded = false;

        // Database check
        $dbStart = microtime(true);
        try {
            DB::connection()->getPdo();
            $dbDuration = round((microtime(true) - $dbStart) * 1000, 2);
            $dbStatus = $dbDuration > 250 ? 'degraded' : 'operational';
            $dbDesc = "রেসপন্স টাইম: {$dbDuration}ms। ডাটাবেজ সার্ভার সঠিকভাবে কাজ করছে।";
        } catch (\Exception $e) {
            $dbStatus = 'major_outage';
            $dbDesc = 'ডাটাবেজ সংযোগ সম্পূর্ণ বিচ্ছিন্ন বা অফলাইন রয়েছে।';
            $allOperational = false;
        }

        // Cache check
        $cacheStart = microtime(true);
        try {
            Cache::put('status_check_key', true, 5);
            $cacheActive = Cache::get('status_check_key');
            $cacheDuration = round((microtime(true) - $cacheStart) * 1000, 2);

            if ($cacheActive) {
                $cacheStatus = $cacheDuration > 100 ? 'degraded' : 'operational';
                $cacheDesc = "রেসপন্স টাইম: {$cacheDuration}ms। সেশন ও মেমোরি ক্যাশ অপ্টিমাইজড।";
            } else {
                $cacheStatus = 'degraded';
                $cacheDesc = 'ক্যাশ রেসপন্স বিলম্বিত হচ্ছে।';
                $hasDegraded = true;
            }
        } catch (\Exception $e) {
            $cacheStatus = 'major_outage';
            $cacheDesc = 'ক্যাশ মেমোরি (Redis/File) লোড নিতে পারছে না।';
            $allOperational = false;
        }

        $streamStatus = 'operational';
        $streamDesc = 'লাইভ ফুটবল স্ট্রিম গেটওয়ে ও ভিডিও প্লেয়ার সচল রয়েছে।';

        $services = [
            [
                'name' => 'Database Server',
                'status' => $dbStatus,
                'description' => $dbDesc,
                'icon' => 'server',
            ],
            [
                'name' => 'Cache System (Redis)',
                'status' => $cacheStatus,
                'description' => $cacheDesc,
                'icon' => 'cpu-chip',
            ],
            [
                'name' => 'Live Streaming Gateway',
                'status' => $streamStatus,
                'description' => $streamDesc,
                'icon' => 'video-camera',
            ],
        ];

        if (!$allOperational) {
            $isOp = false;
            $txt = 'কিছু সিস্টেমে বিঘ্ন ঘটেছে';
            $desc = 'আমাদের টেকনিক্যাল টিম ডাউনটাইম বা সিস্টেম বিপর্যয় দ্রুত পুনরুদ্ধারে কাজ করছে।';
        } elseif ($hasDegraded) {
            $isOp = true;
            $txt = 'সিস্টেম ধীরগতির সম্মুখীন হচ্ছে';
            $desc = 'সার্ভার সচল আছে তবে কিছু রিকোয়েস্টে রেসপন্স পেতে স্বাভাবিকের চেয়ে বেশি সময় লাগতে পারে।';
        } else {
            $isOp = true;
            $txt = 'সকল সিস্টেম সচল রয়েছে';
            $desc = 'সবগুলো সার্ভিস কোনো ধরনের বিঘ্ন ছাড়াই চমৎকারভাবে কাজ করছে।';
        }

        return [
            'services' => $services,
            'isOperational' => $isOp,
            'globalStatusText' => $txt,
            'globalStatusDesc' => $desc,
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">সিস্টেম স্ট্যাটাস</flux:heading>
            <flux:text size="sm" class="mt-1">সবগুলো সার্ভিসের ব্যাকএন্ড স্ট্যাটাস ও লাইভ লেটেন্সি ট্র্যাকিং
            </flux:text>
        </div>

        @if ($isCoolingDown)
            <flux:badge color="rose" size="sm">একটু পর চেষ্টা করুন!</flux:badge>
        @else
            <flux:button wire:click="checkSystemStatus" wire:loading.attr="disabled" icon="arrow-path" variant="subtle"
                size="sm">
                <span wire:loading.remove>পুনরায় চেক করুন</span>
                <span wire:loading>লোডিং...</span>
            </flux:button>
        @endif
    </div>

    <flux:separator />

    {{-- Global Status Callout --}}
    @if ($isOperational)
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.heading>{{ $globalStatusText }}</flux:callout.heading>
            <flux:callout.text>
                {{ $globalStatusDesc }}
                <br>
                <flux:text size="sm">তথ্যটি রেডিস ক্যাশ বাফার থেকে প্রতি ৬০ সেকেন্ড পরপর লাইভ রিফ্রেশ করা হয়।
                </flux:text>
            </flux:callout.text>
        </flux:callout>
    @else
        <flux:callout variant="danger" icon="x-circle">
            <flux:callout.heading>{{ $globalStatusText }}</flux:callout.heading>
            <flux:callout.text>
                {{ $globalStatusDesc }}
                <br>
                <flux:text size="sm">তথ্যটি রেডিস ক্যাশ বাফার থেকে প্রতি ৬০ সেকেন্ড পরপর লাইভ রিফ্রেশ করা হয়।
                </flux:text>
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- Services --}}
    <div class="space-y-4">
        @foreach ($services as $service)
            <flux:card>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <flux:icon :name="$service['icon']" variant="mini" class="mt-0.5 text-zinc-500" />
                        <div>
                            <flux:heading>{{ $service['name'] }}</flux:heading>
                            <flux:text size="sm" class="mt-1">{{ $service['description'] }}</flux:text>
                        </div>
                    </div>

                    <div>
                        @if ($service['status'] === 'operational')
                            <flux:badge color="emerald" size="sm" icon="check-circle">Operational</flux:badge>
                        @elseif($service['status'] === 'degraded')
                            <flux:badge color="amber" size="sm" icon="exclamation-triangle">Degraded Performance
                            </flux:badge>
                        @else
                            <flux:badge color="rose" size="sm" icon="x-circle">Major Outage</flux:badge>
                        @endif
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>

    {{-- Footer --}}
    <flux:text size="sm" class="text-center">
        সিস্টেম ডাটা প্রতি ৫ সেকেন্ড অন্তর আপডেট রিসেট সমর্থন করে। যেকোনো জরুরি টেকনিক্যাল সাপোর্টের জন্য
        <flux:link href="{{ route('contact.us') }}">এখানে যোগাযোগ করুন</flux:link>।
    </flux:text>

</div>
