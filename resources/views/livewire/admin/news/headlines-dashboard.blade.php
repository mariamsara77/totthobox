<?php

use function Livewire\Volt\{state, computed};
use App\Models\NewsHeading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// ── STATE ──────────────────────────────────────────────────────────────
state([
    'expandedTrend' => null,
    'modalTrend' => null,
    'trendDateRange' => '600', // নতুন: কতটি সংবাদ বিশ্লেষণ করবে (সংখ্যা হিসেবে)
]);

// ── CORE STATS ─────────────────────────────────────────────────────────
$stats = computed(function () {
    return Cache::remember('ni_stats', 300, function () {
        $today = NewsHeading::whereDate('created_at', now())->count();
        $yesterday = NewsHeading::whereDate('created_at', now()->subDay())->count();
        return [
            'total' => NewsHeading::count(),
            'today' => $today,
            'yesterday' => $yesterday,
            'week' => NewsHeading::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'sources_count' => NewsHeading::distinct('source_name')->count(),
            'avg_per_hour' => round($today / max(now()->hour, 1), 1),
            'growth_rate' => $today - $yesterday,
            'growth_pct' => $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 1) : 0,
        ];
    });
});

$freshnessScore = computed(function () {
    $total = NewsHeading::count();
    $recent = NewsHeading::where('created_at', '>=', now()->subHours(24))->count();
    return $total > 0 ? round(($recent / $total) * 100, 1) : 0;
});

// ── INTELLIGENT TRENDING TOPICS ────────────────────────────────────────
$intelligentTrends = computed(function () {
    $range = $this->trendDateRange;
    return Cache::remember("ni_trends_{$range}", 300, function () use ($range) {
        $titles = NewsHeading::latest()->take((int) $range)->pluck('title')->toArray();
        $stopWords = ['ও', 'এবং', 'সংবাদ', 'হবে', 'করে', 'করা', 'নিয়ে', '-', '|', 'একটি', 'হয়েছে', 'দিয়ে', 'থেকে', 'জন্য', 'সঙ্গে', 'কেন', 'কী', 'কেউ', 'এই', 'ওই', 'আর', 'তার', 'তারা', 'সেই', 'যে', 'না', 'হয়', 'বলে', 'আছে', 'ছিল', 'যা', 'বা', 'তবে', 'কিন্তু', 'আজ', 'গতকাল', 'আগামী', 'প্রতি', 'সব', 'আরও', 'ই', 'তো', 'এখন', 'পরে', 'আগে', 'মধ্যে', 'বিষয়ে', 'কোনো', 'অনেক', 'প্রায়', 'খুব', 'বেশি', 'কম', 'হচ্ছে', 'করতে', 'যাচ্ছে', 'হলো', 'হওয়া', 'করেন', 'বলেন', 'জানান', 'জানিয়েছেন', 'দেওয়া', 'নেওয়া'];
        $counts = [];
        foreach ($titles as $title) {
            $words = preg_split('/[\s\-\|]+/u', trim($title));
            $words = array_values(array_filter($words, fn($w) => mb_strlen($w) > 2 && !in_array($w, $stopWords)));
            $n = count($words);
            for ($i = 0; $i < $n - 1; $i++) {
                $bi = $words[$i] . ' ' . $words[$i + 1];
                $counts[$bi] = ($counts[$bi] ?? 0) + 1;
            }
            for ($i = 0; $i < $n - 2; $i++) {
                $tri = $words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2];
                $counts[$tri] = ($counts[$tri] ?? 0) + 1;
            }
        }
        $counts = array_filter($counts, fn($c) => $c >= 3);
        arsort($counts);
        $top = array_slice($counts, 0, 18, true);
        $trends = [];
        $total = count($titles);
        foreach ($top as $phrase => $count) {
            $samples = array_values(array_filter($titles, fn($t) => mb_strpos($t, $phrase) !== false));
            $pct = round(($count / max($total, 1)) * 100, 1);
            $trends[] = [
                'topic' => $phrase,
                'count' => $count,
                'percentage' => $pct,
                'samples' => $samples, // ALL samples, not just 3
                'velocity' => $count > 20 ? 'hot' : ($count > 10 ? 'rising' : 'emerging'),
                'explanation' => count($samples) > 0 ? $samples[0] : '',
            ];
        }
        return $trends;
    });
});

// ── POLITICAL TENDENCY ─────────────────────────────────────────────────
$sourcePoliticalTendency = computed(function () {
    return Cache::remember('ni_political', 600, function () {
        $factions = [
            'আওয়ামী লীগপন্থী' => ['আওয়ামী', 'হাসিনা', 'শেখ মুজিব', 'বঙ্গবন্ধু', '১৪ দল', 'গণভবন', 'জয় বাংলা', 'ধানমন্ডি'],
            'বিএনপি/জামায়াতপন্থী' => ['বিএনপি', 'খালেদা', 'তারেক', 'জামায়াত', 'নয়াপল্টন', '২০ দল', 'আন্দোলন', 'অবরোধ'],
            'জাতীয় পার্টি' => ['জাতীয় পার্টি', 'এরশাদ', 'জাপা', 'রওশন'],
            'ইসলামপন্থী' => ['ইসলামী', 'হেফাজত', 'মাদ্রাসা', 'ইমাম', 'ওলামা', 'শরিয়া'],
            'সরকারপন্থী' => ['সরকার', 'মন্ত্রী', 'মন্ত্রণালয়', 'সংসদ', 'প্রধানমন্ত্রী', 'উন্নয়ন', 'প্রকল্প'],
            'সমালোচনামুখী' => ['দুর্নীতি', 'বিচার', 'গ্রেপ্তার', 'মামলা', 'অনিয়ম', 'ক্ষমতার অপব্যবহার'],
        ];
        // $sources = NewsHeading::select('source_name')->distinct()->pluck('source_name');
        $sources = NewsHeading::select('source_name', 'source_key')->distinct()->get();
        $results = [];
        foreach ($sources as $source) {
            $titles = NewsHeading::where('source_name', $source->source_name)->latest()->take(150)->pluck('title')->join(' ');
            $scores = [];
            foreach ($factions as $name => $kws) {
                $s = 0;
                foreach ($kws as $kw) {
                    $s += mb_substr_count($titles, $kw);
                }
                $scores[$name] = $s;
            }
            $total = array_sum($scores) ?: 1;
            $maxScore = max($scores);
            $dominant = array_key_first(array_filter($scores, fn($v) => $v === $maxScore));
            $results[] = [
                'source' => $source->source_name,
                'source_key' => $source->source_key,
                'scores' => $scores,
                'pcts' => array_map(fn($s) => round(($s / $total) * 100, 1), $scores),
                'dominant' => $dominant,
                'strength' => round(($maxScore / $total) * 100, 1),
                'balanced' => $maxScore / $total < 0.3,
                'total' => $total,
            ];
        }
        usort($results, fn($a, $b) => $b['strength'] <=> $a['strength']);
        return $results;
    });
});

// ── CATEGORY FOCUS ─────────────────────────────────────────────────────
$sourceTopicFocus = computed(function () {
    return Cache::remember('ni_topicfocus', 600, function () {
        $cats = [
            'রাজনীতি' => ['নির্বাচন', 'সংসদ', 'দল', 'ভোট', 'রাজনীতি', 'মন্ত্রী'],
            'অর্থনীতি' => ['ব্যবসা', 'বাজার', 'টাকা', 'ব্যাংক', 'শেয়ার', 'বাজেট', 'রপ্তানি'],
            'আন্তর্জাতিক' => ['ভারত', 'আমেরিকা', 'চীন', 'জাতিসংঘ', 'বিশ্ব', 'পাকিস্তান'],
            'খেলাধুলা' => ['ক্রিকেট', 'ফুটবল', 'খেলা', 'ম্যাচ', 'টুর্নামেন্ট'],
            'অপরাধ' => ['গ্রেপ্তার', 'মামলা', 'হত্যা', 'ধর্ষণ', 'দুর্নীতি', 'চুরি'],
            'বিনোদন' => ['চলচ্চিত্র', 'সংগীত', 'শিল্পী', 'উৎসব', 'নাটক'],
            'শিক্ষা' => ['স্কুল', 'বিশ্ববিদ্যালয়', 'পরীক্ষা', 'শিক্ষার্থী', 'ফলাফল'],
            'স্বাস্থ্য' => ['হাসপাতাল', 'চিকিৎসা', 'রোগ', 'ওষুধ', 'ডাক্তার'],
        ];
        return NewsHeading::select('source_name')
            ->distinct()
            ->pluck('source_name')
            ->mapWithKeys(function ($source) use ($cats) {
                $titles = NewsHeading::where('source_name', $source)->latest()->take(200)->pluck('title')->join(' ');
                $scores = [];
                foreach ($cats as $cat => $kws) {
                    $s = 0;
                    foreach ($kws as $kw) {
                        $s += mb_substr_count($titles, $kw);
                    }
                    $scores[$cat] = $s;
                }
                $total = max(array_sum($scores), 1);
                arsort($scores);
                return [$source => array_map(fn($s) => round(($s / $total) * 100, 1), array_slice($scores, 0, 4, true))];
            });
    });
});

// ── SENTIMENT ──────────────────────────────────────────────────────────
$sourceSentiment = computed(function () {
    return Cache::remember('ni_sentiment', 600, function () {
        $positive = ['উন্নয়ন', 'সাফল্য', 'অর্জন', 'প্রশংসা', 'ভালো', 'শান্তি', 'সমৃদ্ধি', 'বিজয়', 'সফল', 'অগ্রগতি'];
        $negative = ['সংঘর্ষ', 'হত্যা', 'দুর্নীতি', 'অনিয়ম', 'ব্যর্থ', 'ক্ষতি', 'ভাঙচুর', 'বিশৃঙ্খলা', 'হামলা', 'আটক'];
        return NewsHeading::select('source_name')
            ->distinct()
            ->pluck('source_name')
            ->map(function ($source) use ($positive, $negative) {
                $titles = NewsHeading::where('source_name', $source)->latest()->take(150)->pluck('title')->join(' ');
                $pos = array_sum(array_map(fn($w) => mb_substr_count($titles, $w), $positive));
                $neg = array_sum(array_map(fn($w) => mb_substr_count($titles, $w), $negative));
                $total = $pos + $neg ?: 1;
                $posP = round(($pos / $total) * 100);
                $negP = round(($neg / $total) * 100);
                $tone = $pos > $neg * 1.3 ? 'ইতিবাচক' : ($neg > $pos * 1.3 ? 'সমালোচনামুখী' : 'নিরপেক্ষ');
                return compact('source', 'pos', 'neg', 'posP', 'negP', 'tone');
            })
            ->values();
    });
});

// ── SOURCE STATS ───────────────────────────────────────────────────────
$sourceDistribution = computed(fn() => NewsHeading::select('source_name', DB::raw('count(*) as total'), DB::raw('AVG(CHAR_LENGTH(title)) as avg_len'), DB::raw('MAX(created_at) as last_post'))->groupBy('source_name')->orderByDesc('total')->take(10)->get());

$sourceReliability = computed(fn() => NewsHeading::select('source_name', DB::raw('count(*) as total_posts'), DB::raw('AVG(CHAR_LENGTH(title)) as avg_complexity'), DB::raw('COUNT(DISTINCT DATE(created_at)) as active_days'), DB::raw('MAX(created_at) as last_active'))->groupBy('source_name')->orderByDesc('active_days')->take(8)->get());

$activeSources = computed(
    fn() => NewsHeading::where('created_at', '>=', now()->subHours(24))
        ->select('source_name', DB::raw('count(*) as count'), DB::raw('count(DISTINCT HOUR(created_at)) as active_hours'))
        ->groupBy('source_name')
        ->orderByDesc('count')
        ->get(),
);

$hourlyActivity = computed(fn() => NewsHeading::whereDate('created_at', now())->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))->groupBy('hour')->orderBy('hour')->get());

$anomalies = computed(function () {
    $avg = NewsHeading::whereDate('created_at', now())->count() / max(now()->hour, 1);
    return NewsHeading::whereDate('created_at', now())
        ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
        ->groupBy('hour')
        ->having('count', '>', $avg * 2)
        ->orderByDesc('count')
        ->get();
});

// ── ACTIONS ────────────────────────────────────────────────────────────
$toggleTrend = function (int $index) {
    $this->expandedTrend = $this->expandedTrend === $index ? null : $index;
};

$openModal = function (int $index) {
    $this->modalTrend = $index;
};

$closeModal = function () {
    $this->modalTrend = null;
};

?>

{{-- ══════════════════════════════════════════════════════════════════
     DASHBOARD WRAPPER
══════════════════════════════════════════════════════════════════ --}}
<div class="space-y-4">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                বাংলাদেশ নিউজ ইন্টেলিজেন্স ড্যাশবোর্ড
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5 hidden sm:block">
                রিয়েল-টাইম সংবাদ বিশ্লেষণ · কৃত্রিম বুদ্ধিমত্তা চালিত · বাংলাদেশের সকল প্রধান পত্রিকার তথ্য
            </p>
        </div>
        <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-2 text-xs text-zinc-400">
                <span class="inline-block w-2 h-2 rounded-full bg-zinc-400/10 animate-pulse"></span>
                লাইভ আপডেট চলছে
            </div>
            <flux:badge color="green" size="sm">✔ {{ $this->freshnessScore }}% তাজা</flux:badge>
            <flux:button wire:click="$refresh" size="xs" icon="arrow-path" variant="ghost"
                class="text-white border border-zinc-600">রিফ্রেশ</flux:button>
        </div>
    </div>

    <div class="space-y-4">

        {{-- ══════════════════════════════════════════════════════════
             ১. TOP STATS
        ══════════════════════════════════════════════════════════ --}}
        <div>
            <flux:heading size="sm" class="text-zinc-500 uppercase tracking-widest text-xs mb-3">
                সামগ্রিক পরিসংখ্যান
            </flux:heading>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $statItems = [
                        [
                            'icon' => 'newspaper',
                            'label' => 'মোট সংবাদ',
                            'value' => number_format($this->stats['total']),
                            'sub' =>
                                ($this->stats['growth_rate'] >= 0 ? '+' : '') .
                                $this->stats['growth_rate'] .
                                ' গতকালের তুলনায় (' .
                                $this->stats['growth_pct'] .
                                '%)',
                            'color' => 'blue',
                            'desc' => 'এখন পর্যন্ত সকল পত্রিকা থেকে সংগৃহীত মোট সংবাদ',
                        ],
                        [
                            'icon' => 'calendar-days',
                            'label' => 'আজকের সংবাদ',
                            'value' => number_format($this->stats['today']),
                            'sub' => 'এই সপ্তাহে মোট ' . $this->stats['week'] . 'টি',
                            'color' => 'red',
                            'desc' => 'আজ মধ্যরাত থেকে এখন পর্যন্ত প্রকাশিত সংবাদ',
                        ],
                        [
                            'icon' => 'cursor-arrow-ripple',
                            'label' => 'সক্রিয় পত্রিকা',
                            'value' => $this->stats['sources_count'],
                            'sub' => 'প্রতি ঘণ্টায় গড়ে ' . $this->stats['avg_per_hour'] . 'টি',
                            'color' => 'green',
                            'desc' => 'বর্তমানে ট্র্যাক করা পত্রিকার সংখ্যা',
                        ],
                        [
                            'icon' => 'bolt',
                            'label' => 'তাজা সংবাদের হার',
                            'value' => $this->freshnessScore . '%',
                            'sub' => '৭০%+ মানে ড্যাশবোর্ড সর্বাধুনিক',
                            'color' => 'amber',
                            'desc' => 'গত ২৪ ঘণ্টার সংবাদের শতকরা হার',
                        ],
                    ];
                    $colorMap = [
                        'blue' => [
                            'bg' => 'bg-blue-50 dark:bg-blue-950',
                            'border' => 'border-l-blue-500',
                            'val' => 'text-blue-700 dark:text-blue-400',
                            'sub' => 'text-blue-600/70',
                        ],
                        'red' => [
                            'bg' => 'bg-red-50 dark:bg-red-950',
                            'border' => 'border-l-red-500',
                            'val' => 'text-red-700 dark:text-red-400',
                            'sub' => 'text-red-600/70',
                        ],
                        'green' => [
                            'bg' => 'bg-emerald-50 dark:bg-emerald-950',
                            'border' => 'border-l-emerald-500',
                            'val' => 'text-emerald-700 dark:text-emerald-400',
                            'sub' => 'text-emerald-600/70',
                        ],
                        'amber' => [
                            'bg' => 'bg-amber-50 dark:bg-amber-950',
                            'border' => 'border-l-amber-500',
                            'val' => 'text-amber-700 dark:text-amber-400',
                            'sub' => 'text-amber-600/70',
                        ],
                    ];
                @endphp
                @foreach ($statItems as $st)
                    @php $c = $colorMap[$st['color']]; @endphp
                    <flux:card
                        class="{{ $c['bg'] }} border-l-4 {{ $c['border'] }} rounded-xl shadow-sm space-y-4">
                        <flux:icon name="{{ $st['icon'] }}" variant="solid" />
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-2">{{ $st['label'] }}
                        </div>
                        <div class="text-3xl font-bold {{ $c['val'] }} leading-none mb-2">{{ $st['value'] }}</div>
                        <div class="text-xs {{ $c['sub'] }}">{{ $st['sub'] }}</div>
                    </flux:card>
                @endforeach
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             ২. TRENDING TOPICS (collapsible + modal)
        ══════════════════════════════════════════════════════════ --}}
        <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
            <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <flux:heading size="base" class="text-white font-bold">
                            🔥 এখন কোন বিষয়গুলো সবচেয়ে বেশি আলোচিত?
                        </flux:heading>
                        <p class="text-xs text-zinc-400 mt-1">
                            সর্বশেষ {{ $this->trendDateRange }}টি সংবাদ বিশ্লেষণ · প্রতিটি কার্ডে ক্লিক করলে সব শিরোনাম
                            দেখা যাবে
                        </p>
                    </div>

                    {{-- Date Range Filter --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <flux:icon name="funnel" class="text-zinc-400 w-4 h-4 shrink-0" />
                        <span class="text-xs text-zinc-400 shrink-0">বিশ্লেষণ পরিসর:</span>
                        @foreach ([
        '200' => 'সাম্প্রতিক',
        '600' => 'মানক',
        '1200' => 'বিস্তারিত',
        '3000' => 'সম্পূর্ণ',
    ] as $val => $label)
                            <flux:button wire:click="$set('trendDateRange', '{{ $val }}')" size="xs"
                                variant="{{ $this->trendDateRange === $val ? 'filled' : 'ghost' }}"
                                class="{{ $this->trendDateRange === $val ? 'text-white' : 'text-zinc-400' }}">
                                {{ $label }}
                                <span class="text-xs opacity-60 ml-0.5">({{ number_format((int) $val) }})</span>
                            </flux:button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="p-5">

                {{-- Legend --}}
                <div class="flex flex-wrap gap-2 mb-5 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-xs items-center">
                    <span class="font-bold text-zinc-500">বেগ:</span>
                    <flux:badge color="red" size="sm">🔥 উত্তপ্ত</flux:badge>
                    <flux:badge color="yellow" size="sm">📈 উঠছে</flux:badge>
                    <flux:badge color="blue" size="sm">🌱 নতুন</flux:badge>
                    <span class="text-zinc-400 ml-auto hidden sm:inline">কার্ডে ক্লিক → সব শিরোনাম · "সব দেখুন" →
                        মডাল</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($this->intelligentTrends as $i => $trend)
                        @php
                            $vel = $trend['velocity'];
                            $velBorderMap = [
                                'hot' => 'border-l-red-500 bg-red-50/50 dark:bg-red-950/30',
                                'rising' => 'border-l-amber-500 bg-amber-50/50 dark:bg-amber-950/30',
                                'emerging' => 'border-l-blue-500 bg-blue-50/50 dark:bg-blue-950/30',
                            ];
                            $velBarMap = [
                                'hot' => 'bg-red-500',
                                'rising' => 'bg-amber-500',
                                'emerging' => 'bg-blue-500',
                            ];
                            $velBadge = ['hot' => 'red', 'rising' => 'yellow', 'emerging' => 'blue'];
                            $velLabel = ['hot' => '🔥 উত্তপ্ত', 'rising' => '📈 উঠছে', 'emerging' => '🌱 নতুন'];
                            $isOpen = $this->expandedTrend === $i;
                        @endphp

                        <div
                            class="border-l-4 {{ $velBorderMap[$vel] }} border border-zinc-400/25 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">

                            {{-- Card header — always visible --}}
                            <button wire:click="toggleTrend({{ $i }})"
                                class="w-full text-left p-4 focus: focus-visible:ring-2 focus-visible:ring-blue-500">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <span class="font-bold text-zinc-900 dark:text-white text-base leading-snug flex-1">
                                        {{ $trend['topic'] }}
                                    </span>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <flux:badge color="{{ $velBadge[$vel] }}" size="sm">{{ $velLabel[$vel] }}
                                        </flux:badge>
                                        <flux:icon :name="$isOpen ? 'chevron-up' : 'chevron-down'"
                                            class="text-zinc-400 w-4 h-4" />
                                    </div>
                                </div>

                                {{-- Progress bar --}}
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="flex-1 h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                        <div class="{{ $velBarMap[$vel] }} h-full rounded-full transition-all duration-200"
                                            style="width: {{ min($trend['count'] * 3, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-zinc-500 whitespace-nowrap">
                                        {{ $trend['count'] }}টি সংবাদ
                                    </span>
                                </div>

                                {{-- First sample always visible --}}
                                @if ($trend['explanation'])
                                    <div
                                        class="bg-white dark:bg-zinc-800 border border-zinc-400/25 rounded-lg px-3 py-2 text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed">
                                        <span class="font-semibold text-zinc-400 block mb-2">📄 নমুনা:</span>
                                        "{{ Str::limit($trend['explanation'], 90) }}"
                                    </div>
                                @endif
                            </button>

                            {{-- Collapsible: all headlines --}}
                            @if ($isOpen)
                                <div
                                    class="border-t border-zinc-400/25 bg-white dark:bg-zinc-800/50 px-4 py-3 space-y-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">
                                            সব শিরোনাম ({{ count($trend['samples']) }}টি)
                                        </span>
                                        <flux:button wire:click="openModal({{ $i }})" size="xs"
                                            variant="ghost" icon="arrows-pointing-out">
                                            সব দেখুন
                                        </flux:button>
                                    </div>
                                    @foreach (array_slice($trend['samples'], 0, 5) as $idx => $sample)
                                        <div
                                            class="flex gap-2 items-start text-xs text-zinc-700 dark:text-zinc-300 py-2 border-b border-zinc-100 dark:border-zinc-700 last:">
                                            <span class="text-zinc-400 font-mono shrink-0">{{ $idx + 1 }}.</span>
                                            <span class="leading-relaxed">{{ $sample }}</span>
                                        </div>
                                    @endforeach
                                    @if (count($trend['samples']) > 5)
                                        <flux:button wire:click="openModal({{ $i }})" size="xs"
                                            variant="filled" class="w-full mt-1">
                                            আরও {{ count($trend['samples']) - 5 }}টি শিরোনাম দেখুন →
                                        </flux:button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </flux:card>

        {{-- ══════════════════════════════════════════════════════════
             MODAL — all headlines for selected trend
        ══════════════════════════════════════════════════════════ --}}
        @if ($this->modalTrend !== null && isset($this->intelligentTrends[$this->modalTrend]))
            @php $mt = $this->intelligentTrends[$this->modalTrend]; @endphp
            <flux:modal name="trend-modal" class="" wire:model="modalTrend">
                <div class="p-0">
                    <div class="bg-zinc-900 px-6 py-4 rounded-t-xl flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="base" class="text-white font-bold">
                                🔥 {{ $mt['topic'] }}
                            </flux:heading>
                            <p class="text-xs text-zinc-400 mt-1">
                                মোট {{ count($mt['samples']) }}টি সংবাদ শিরোনামে এই বিষয় পাওয়া গেছে
                            </p>
                        </div>
                        <flux:button wire:click="closeModal" variant="ghost" icon="x-mark" size="sm"
                            class="text-zinc-400 shrink-0" />
                    </div>
                    <div class="p-5 max-h-[60vh] overflow-y-auto space-y-2">
                        @foreach ($mt['samples'] as $idx => $sample)
                            <div
                                class="flex gap-4 items-start p-3 rounded-lg
                                {{ $idx % 2 === 0 ? 'bg-zinc-50 dark:bg-zinc-800' : 'bg-white dark:bg-zinc-900' }}
                                border border-zinc-100 dark:border-zinc-700 text-sm text-zinc-800 dark:text-zinc-200 leading-relaxed">
                                <span
                                    class="text-xs font-mono text-zinc-400 shrink-0 mt-0.5">{{ $idx + 1 }}</span>
                                <span>{{ $sample }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </flux:modal>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             ৩. POLITICAL TENDENCY + TOPIC FOCUS
        ══════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Political tendency --}}
            <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
                <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                    <flux:heading size="base" class="text-white font-bold">🏛️ রাজনৈতিক ঝোঁক বিশ্লেষণ</flux:heading>
                    <p class="text-xs text-zinc-400 mt-1">শেষ ১৫০টি সংবাদে কীওয়ার্ড ফ্রিকোয়েন্সির উপর ভিত্তি করে</p>
                </div>
                <div class="p-5">
                    <flux:callout color="yellow" icon="information-circle" class="mb-4 text-xs">
                        এটি সম্পাদকীয় পক্ষপাত নয় — কেবল কীওয়ার্ড গণনা বিশ্লেষণ। "ভারসাম্যপূর্ণ" মানে সব পক্ষের কথা
                        সমানভাবে এসেছে।
                    </flux:callout>

                    @php
                        $facColors = [
                            'আওয়ামী লীগপন্থী' => [
                                'bar' => 'bg-zinc-400/10',
                                'text' => 'text-emerald-600',
                                'badge' => 'green',
                            ],
                            'বিএনপি/জামায়াতপন্থী' => [
                                'bar' => 'bg-blue-500',
                                'text' => 'text-blue-600',
                                'badge' => 'blue',
                            ],
                            'জাতীয় পার্টি' => [
                                'bar' => 'bg-amber-500',
                                'text' => 'text-amber-600',
                                'badge' => 'yellow',
                            ],
                            'ইসলামপন্থী' => ['bar' => 'bg-cyan-500', 'text' => 'text-cyan-600', 'badge' => 'zinc'],
                            'সরকারপন্থী' => [
                                'bar' => 'bg-purple-500',
                                'text' => 'text-purple-600',
                                'badge' => 'purple',
                            ],
                            'সমালোচনামুখী' => ['bar' => 'bg-red-500', 'text' => 'text-red-600', 'badge' => 'red'],
                        ];
                    @endphp

                    {{-- Color legend --}}
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mb-4">
                        @foreach ($facColors as $name => $meta)
                            <div class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                <span class="w-2.5 h-2 rounded-sm {{ $meta['bar'] }} inline-block shrink-0"></span>
                                {{ $name }}
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-4">
                        @foreach ($this->sourcePoliticalTendency as $src)
                            <div>
                                <div class="flex justify-between items-center mb-2.5">
                                    <span
                                        class="font-bold text-sm text-zinc-900 dark:text-white flex items-center gap-2">
                                        <flux:icon name="{{ $src['source_key'] ?? 'newspaper' }}"
                                            class="size-6 text-zinc-400 shrink-0" />
                                        {{ $src['source'] }}
                                    </span>
                                    @if ($src['balanced'])
                                        <flux:badge color="zinc" size="sm">⚖️ ভারসাম্যপূর্ণ</flux:badge>
                                    @else
                                        <flux:badge color="{{ $facColors[$src['dominant']]['badge'] ?? 'zinc' }}"
                                            size="sm">
                                            {{ $src['dominant'] }} · {{ $src['strength'] }}%
                                        </flux:badge>
                                    @endif
                                </div>
                                {{-- Stacked bar --}}
                                <div class="flex h-3 rounded-full overflow-hidden gap-px">
                                    @foreach ($src['pcts'] as $faction => $pct)
                                        @if ($pct > 0)
                                            <div title="{{ $faction }}: {{ $pct }}%"
                                                class="{{ $facColors[$faction]['bar'] ?? 'bg-zinc-400' }} opacity-80 transition-all duration-200"
                                                style="width:{{ $pct }}%"></div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach (array_filter($src['pcts'], fn($v) => $v > 3) as $faction => $pct)
                                        <span
                                            class="text-xs font-semibold {{ $facColors[$faction]['text'] ?? 'text-zinc-400' }}">
                                            {{ $faction }} {{ $pct }}%
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </flux:card>

            {{-- Topic focus --}}
            <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
                <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                    <flux:heading size="base" class="text-white font-bold">📂 পত্রিকার বিষয়ভিত্তিক মনোযোগ
                    </flux:heading>
                    <p class="text-xs text-zinc-400 mt-1">শেষ ২০০টি সংবাদ বিশ্লেষণ করে বিষয়ভিত্তিক শতকরা ভাগ</p>
                </div>
                <div class="p-5 overflow-x-auto">
                    @php
                        $catMeta = [
                            'রাজনীতি' => [
                                'icon' => '🏛️',
                                'bg' => 'bg-red-100 dark:bg-red-900/30',
                                'text' => 'text-red-700 dark:text-red-300',
                            ],
                            'অর্থনীতি' => [
                                'icon' => '💹',
                                'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
                                'text' => 'text-emerald-700 dark:text-emerald-300',
                            ],
                            'আন্তর্জাতিক' => [
                                'icon' => '🌍',
                                'bg' => 'bg-blue-100 dark:bg-blue-900/30',
                                'text' => 'text-blue-700 dark:text-blue-300',
                            ],
                            'খেলাধুলা' => [
                                'icon' => '🏏',
                                'bg' => 'bg-amber-100 dark:bg-amber-900/30',
                                'text' => 'text-amber-700 dark:text-amber-300',
                            ],
                            'অপরাধ' => [
                                'icon' => '🚔',
                                'bg' => 'bg-purple-100 dark:bg-purple-900/30',
                                'text' => 'text-purple-700 dark:text-purple-300',
                            ],
                            'বিনোদন' => [
                                'icon' => '🎭',
                                'bg' => 'bg-pink-100 dark:bg-pink-900/30',
                                'text' => 'text-pink-700 dark:text-pink-300',
                            ],
                            'শিক্ষা' => [
                                'icon' => '📚',
                                'bg' => 'bg-cyan-100 dark:bg-cyan-900/30',
                                'text' => 'text-cyan-700 dark:text-cyan-300',
                            ],
                            'স্বাস্থ্য' => [
                                'icon' => '🏥',
                                'bg' => 'bg-teal-100 dark:bg-teal-900/30',
                                'text' => 'text-teal-700 dark:text-teal-300',
                            ],
                        ];
                    @endphp
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>স্বাস্থ্য পত্রিকা</flux:table.column>
                            <flux:table.column>শীর্ষ বিষয়সমূহ</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->sourceTopicFocus as $source => $topics)
                                <flux:table.row>
                                    <flux:table.cell class="font-bold text-sm whitespace-nowrap">{{ $source }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($topics as $cat => $pct)
                                                @if ($pct > 0)
                                                    @php $m = $catMeta[$cat] ?? ['icon' => '📌', 'bg' => 'bg-zinc-100', 'text' => 'text-zinc-700']; @endphp
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold {{ $m['bg'] }} {{ $m['text'] }}">
                                                        {{ $m['icon'] }} {{ $cat }}
                                                        <strong>{{ $pct }}%</strong>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             ৪. SENTIMENT + SOURCE DIST + HOURLY
        ══════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Sentiment --}}
            <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
                <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                    <flux:heading size="base" class="text-white font-bold">😊 পত্রিকার ভাষার ধরন</flux:heading>
                    <p class="text-xs text-zinc-400 mt-1">ইতিবাচক বনাম সমালোচনামুখী শব্দ বিশ্লেষণ</p>
                </div>
                <div class="p-5">
                    <div
                        class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-xs text-zinc-500 dark:text-zinc-400 mb-4 leading-relaxed">
                        💡 <strong>সবুজ</strong> = ইতিবাচক (উন্নয়ন, সাফল্য) · <strong>লাল</strong> = সমালোচনামুখী
                        (দুর্নীতি, হত্যা) · <strong>হলুদ</strong> = নিরপেক্ষ
                    </div>
                    <div class="space-y-4">
                        @foreach ($this->sourceSentiment as $s)
                            @php
                                $toneColor = match ($s['tone']) {
                                    'ইতিবাচক' => 'text-emerald-600',
                                    'সমালোচনামুখী' => 'text-red-600',
                                    default => 'text-amber-600',
                                };
                                $toneIcon = match ($s['tone']) {
                                    'ইতিবাচক' => '😊',
                                    'সমালোচনামুখী' => '⚠️',
                                    default => '⚖️',
                                };
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-2.5">
                                    <span
                                        class="text-sm font-bold text-zinc-900 dark:text-white">{{ $s['source'] }}</span>
                                    <span class="text-xs font-semibold {{ $toneColor }}">{{ $toneIcon }}
                                        {{ $s['tone'] }}</span>
                                </div>
                                <div class="flex h-2 rounded-full overflow-hidden gap-px">
                                    <div class="bg-zinc-400/10 opacity-75 rounded-l-full"
                                        style="width:{{ $s['posP'] }}%"></div>
                                    <div class="bg-red-500 opacity-75 rounded-r-full"
                                        style="width:{{ $s['negP'] }}%"></div>
                                    <div class="flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                                </div>
                                <div class="flex justify-between text-xs mt-1">
                                    <span class="text-emerald-500">✔ ইতিবাচক {{ $s['posP'] }}%</span>
                                    <span class="text-red-500">✖ সমালোচনা {{ $s['negP'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </flux:card>

            {{-- Source distribution --}}
            <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
                <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                    <flux:heading size="base" class="text-white font-bold">📊 পত্রিকাভিত্তিক সংবাদ বিতরণ
                    </flux:heading>
                    <p class="text-xs text-zinc-400 mt-1">মোট সংবাদের কত শতাংশ কোন পত্রিকা থেকে এসেছে</p>
                </div>
                <div class="p-5">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">💡 বার যত বড়, সেই পত্রিকা তত বেশি সংবাদ
                        প্রকাশ করেছে।</p>
                    @php $maxS = $this->sourceDistribution->max('total') ?: 1; @endphp
                    <div class="space-y-3">
                        @foreach ($this->sourceDistribution as $source)
                            @php $pct = round(($source->total / max($this->stats['total'], 1)) * 100, 1); @endphp
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span
                                        class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                                        <flux:icon name="{{ $source->source_key ?? 'newspaper' }}" variant="mini"
                                            class="w-4  h-4 text-zinc-400 shrink-0" />
                                        {{ $source->source_name }}
                                    </span>
                                    <span class="text-xs text-zinc-500">{{ number_format($source->total) }}টি ·
                                        {{ $pct }}%</span>
                                </div>
                                <div class="h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-cyan-500 transition-all duration-200"
                                        style="width:{{ ($source->total / $maxS) * 100 }}%"></div>
                                </div>
                                <div class="text-xs text-zinc-400 mt-0.5">গড় শিরোনাম:
                                    {{ round($source->avg_len) }} অক্ষর</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </flux:card>

            {{-- Hourly --}}
            <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
                <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                    <flux:heading size="base" class="text-white font-bold">🕐 ঘণ্টাভিত্তিক কার্যকলাপ</flux:heading>
                    <p class="text-xs text-zinc-400 mt-1">আজ কখন কতটি সংবাদ প্রকাশিত হয়েছে</p>
                </div>
                <div class="p-5">
                    <div
                        class="flex flex-wrap gap-4 text-xs text-zinc-500 mb-4 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <span><span class="inline-block w-2 h-2 rounded-sm bg-red-500 mr-1"></span>খুব ব্যস্ত</span>
                        <span><span class="inline-block w-2 h-2 rounded-sm bg-amber-500 mr-1"></span>মাঝারি</span>
                        <span><span class="inline-block w-2 h-2 rounded-sm bg-blue-500 mr-1"></span>স্বাভাবিক</span>
                        <span><span class="inline-block w-2 h-2 rounded-sm bg-purple-500 mr-1"></span>কম</span>
                    </div>
                    @php $maxH = $this->hourlyActivity->max('count') ?: 1; @endphp
                    <div class="space-y-1.5">
                        @foreach ($this->hourlyActivity as $h)
                            @php
                                $p = ($h->count / $maxH) * 100;
                                $bar =
                                    $p > 80
                                        ? 'bg-red-500'
                                        : ($p > 50
                                            ? 'bg-amber-500'
                                            : ($p > 25
                                                ? 'bg-blue-500'
                                                : 'bg-purple-400'));
                            @endphp
                            <div class="flex items-center gap-4">
                                <span
                                    class="text-xs font-mono text-zinc-400 w-10 shrink-0">{{ sprintf('%02d:০০', $h->hour) }}</span>
                                <div class="flex-1 h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="{{ $bar }} h-full rounded-full opacity-80"
                                        style="width:{{ $p }}%"></div>
                                </div>
                                <span class="text-xs text-zinc-500 w-5 text-right shrink-0">{{ $h->count }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if ($this->anomalies->isNotEmpty())
                        <div
                            class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-lg">
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-400 mb-2">⚡ অস্বাভাবিক বৃদ্ধি!
                            </p>
                            @foreach ($this->anomalies as $a)
                                <p class="text-xs text-amber-600 dark:text-amber-300">
                                    {{ sprintf('%02d:০০', $a->hour) }} ঘণ্টায় {{ $a->count }}টি — হয়তো বড় ঘটনা
                                    ঘটেছে
                                </p>
                            @endforeach
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             ৫. RELIABILITY SCORE
        ══════════════════════════════════════════════════════════ --}}
        <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
            <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                <flux:heading size="base" class="text-white font-bold">🏅 পত্রিকার ধারাবাহিকতার স্কোর
                </flux:heading>
                <p class="text-xs text-zinc-400 mt-1">যত বেশিদিন নিরবচ্ছিন্নভাবে সংবাদ দিচ্ছে, স্কোর তত বেশি। এটি
                    সাংবাদিকতার মান পরিমাপ নয়।</p>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($this->sourceReliability as $src)
                    @php
                        $score = min(100, round(($src->active_days / 30) * 100));
                        $scoreColor =
                            $score > 70 ? 'text-emerald-600' : ($score > 40 ? 'text-amber-600' : 'text-red-600');
                        $barColor = $score > 70 ? 'bg-zinc-400/10' : ($score > 40 ? 'bg-amber-500' : 'bg-red-500');
                        $borderColor =
                            $score > 70
                                ? 'border-l-emerald-500'
                                : ($score > 40
                                    ? 'border-l-amber-500'
                                    : 'border-l-red-500');
                        $label = $score > 70 ? 'প্রতিষ্ঠিত' : ($score > 40 ? 'মাঝারি' : 'নতুন/অনিয়মিত');
                        $badge = $score > 70 ? 'green' : ($score > 40 ? 'yellow' : 'red');
                    @endphp
                    <div class="border border-zinc-400/25 border-l-4 {{ $borderColor }} rounded-xl p-4">
                        <div class="flex justify-between items-start mb-2">
                            <span
                                class="font-bold text-sm text-zinc-900 dark:text-white leading-tight flex items-center gap-1">
                                <flux:icon name="{{ $src->icon ?? 'newspaper' }}" variant="mini"
                                    class="w-3.5 h-3.5 text-zinc-400 shrink-0" />
                                {{ $src->source_name }}
                            </span>
                            <flux:badge color="{{ $badge }}" size="sm">{{ $label }}</flux:badge>
                        </div>
                        <div class="flex items-end justify-between mb-2">
                            <span class="text-3xl font-bold {{ $scoreColor }}">{{ $score }}<span
                                    class="text-lg">%</span></span>
                            <div class="text-right text-xs text-zinc-400">
                                <div>{{ $src->active_days }} দিন সক্রিয়</div>
                                <div>{{ number_format($src->total_posts) }}টি সংবাদ</div>
                            </div>
                        </div>
                        <div class="h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full" style="width:{{ $score }}%">
                            </div>
                        </div>
                        <div class="text-xs text-zinc-400 mt-1">গড় শিরোনাম: {{ round($src->avg_complexity) }}
                            অক্ষর</div>
                    </div>
                @endforeach
            </div>
        </flux:card>

        {{-- ══════════════════════════════════════════════════════════
             ৬. LIVE NEWS FEED
        ══════════════════════════════════════════════════════════ --}}
        <flux:card class="!p-0 overflow-hidden shadow-sm rounded-xl">
            <div class="bg-zinc-900 px-5 py-4 border-b border-zinc-700">
                <div class="flex items-center gap-4">
                    <span class="inline-block w-2.5 h-2 rounded-full bg-zinc-400/10 animate-pulse shrink-0"></span>
                    <div>
                        <flux:heading size="base" class="text-white font-bold">📡 সর্বশেষ সংবাদ — লাইভ ফিড
                        </flux:heading>
                        <p class="text-xs text-zinc-400 mt-0.5">সবচেয়ে সাম্প্রতিক ১৫টি সংবাদ · ⚡ ব্রেকিং = ১৫ মিনিটের
                            মধ্যে · 🔥 হট = ১ ঘণ্টার মধ্যে</p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach (NewsHeading::latest()->take(15)->get() as $news)
                        @php
                            $isBreaking = $news->created_at->diffInMinutes() < 15;
                            $isHot = $news->created_at->diffInMinutes() < 60;
                            $cardBg = $isBreaking
                                ? 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800'
                                : ($isHot
                                    ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800'
                                    : 'bg-white dark:bg-zinc-800 border-zinc-400/25');
                            $kws = array_slice(
                                array_filter(explode(' ', $news->title), fn($w) => mb_strlen($w) > 3),
                                0,
                                4,
                            );
                        @endphp
                        <div class="border {{ $cardBg }} rounded-xl p-4 flex flex-col gap-4">
                            <div class="flex items-start justify-between gap-2 flex-wrap">
                                {{-- আগের কোড --}}
                                {{-- <flux:badge color="zinc" size="sm">{{ $news->source_name }}</flux:badge> --}}

                                {{-- নতুন কোড --}}
                                <flux:badge color="zinc" size="sm">
                                    <flux:icon name="{{ $news->source_key ?? 'newspaper' }}" class="size-4 mr-3" />
                                    {{ $news->source_name }}
                                </flux:badge>
                                <div class="flex gap-2">
                                    @if ($isBreaking)
                                        <flux:badge color="red" size="sm">⚡ ব্রেকিং</flux:badge>
                                    @elseif ($isHot)
                                        <flux:badge color="yellow" size="sm">🔥 হট</flux:badge>
                                    @endif
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white leading-snug flex-1">
                                {{ $news->title }}
                            </p>
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($kws as $kw)
                                        <span
                                            class="text-xs px-2 py-0.5 bg-zinc-100 dark:bg-zinc-700 rounded-full text-zinc-500 dark:text-zinc-400">{{ $kw }}</span>
                                    @endforeach
                                </div>
                                <span class="text-xs text-zinc-400 shrink-0">🕐
                                    {{ $news->created_at->diffForHumans() }}</span>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </flux:card>

        {{-- ══════════════════════════════════════════════════════════
             ৭. BOTTOM SUMMARY BAR
        ══════════════════════════════════════════════════════════ --}}
        <flux:card class="bg-zinc-900 !border-zinc-800 !p-0 overflow-hidden rounded-xl">
            @php
                $peakHour = $this->hourlyActivity->sortByDesc('count')->first();
                $topSrc = $this->activeSources->first();
                $avgLen = NewsHeading::whereDate('created_at', now())->avg(DB::raw('CHAR_LENGTH(title)'));
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-zinc-800">
                @php
                    $summaryItems = [
                        [
                            'icon' => '🏆',
                            'label' => 'সবচেয়ে ব্যস্ত সময়',
                            'value' => $peakHour
                                ? sprintf('%02d:০০ – %02d:৫৯', $peakHour->hour, $peakHour->hour)
                                : 'তথ্য নেই',
                            'sub' => $peakHour ? $peakHour->count . 'টি সংবাদ' : '',
                        ],
                        [
                            'icon' => '📰',
                            'label' => 'আজকের সবচেয়ে সক্রিয়',
                            'value' => $topSrc?->source_name ?? 'তথ্য নেই',
                            'sub' => ($topSrc?->count ?? 0) . 'টি সংবাদ · ' . ($topSrc?->active_hours ?? 0) . ' ঘণ্টা',
                        ],
                        [
                            'icon' => '📏',
                            'label' => 'গড় শিরোনাম দৈর্ঘ্য',
                            'value' => $avgLen ? round($avgLen) . '  অক্ষর' : '০',
                            'sub' => '৩০-৬০ অক্ষর আদর্শ',
                        ],
                        [
                            'icon' => '⚙️',
                            'label' => 'সিস্টেম অবস্থা',
                            'value' => 'সব ঠিকঠাক',
                            'sub' => 'ক্যাশ: ৫ মিনিট · ডেটাবেস: সংযুক্ত',
                        ],
                    ];
                @endphp
                @foreach ($summaryItems as $item)
                    <div class="p-5">
                        <div class="text-xs text-zinc-500 font-bold uppercase tracking-widest mb-2.5">
                            {{ $item['icon'] }} {{ $item['label'] }}</div>
                        <div class="text-lg font-bold text-white leading-tight mb-2">{{ $item['value'] }}</div>
                        <div class="text-xs text-zinc-500">{{ $item['sub'] }}</div>
                    </div>
                @endforeach
            </div>
        </flux:card>

    </div>{{-- /container --}}
</div>
