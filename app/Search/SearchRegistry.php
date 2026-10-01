<?php

namespace App\Search;

use App\Models\BasicHealth;
use App\Models\BasicIslam;
use App\Models\BuySellPost;
use App\Models\Dowa;
use App\Models\EstablishmentBd;
use App\Models\ExcelTutorial;
use App\Models\Food;
use App\Models\HistoryBd;
use App\Models\Holiday;
use App\Models\IntroBd;
use App\Models\NewsHeading;
use App\Models\Person;
use App\Models\Question;
use App\Models\Quran;
use App\Models\Sign;
use App\Models\Sura;
use App\Models\TourismBd;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SearchRegistry
{
    private static array $aliases = [
        'tourism' => 'পর্যটন',
        'tour' => 'পর্যটন',
        'introduction' => 'পরিচিতি',
        'intro' => 'পরিচিতি',
        'establishment' => 'প্রতিষ্ঠান',
        'institution' => 'প্রতিষ্ঠান',
        'person' => 'গুনীজন',
        'people' => 'গুনীজন',
        'history' => 'ইতিহাস',
        'food' => 'খাবার',
        'health' => 'স্বাস্থ্য',
        'islam' => 'ইসলাম',
        'dua' => 'দোয়া',
        'dowa' => 'দোয়া',
        'quran' => 'কুরআন',
        'sura' => 'সূরা',
        'question' => 'প্রশ্ন',
        'mcq' => 'প্রশ্ন',
        'tutorial' => 'টিউটোরিয়াল',
        'excel' => 'টিউটোরিয়াল',
        'holiday' => 'ছুটির দিন',
        'sign' => 'সাইন ভাষা',
        'signs' => 'সাইন ভাষা',
        'news' => 'খবর',
        'market' => 'বাজার',
        'buysell' => 'বাজার',
        'shop' => 'বাজার',
    ];

    public static function all(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        return $cache = self::buildRegistry();
    }

    private static function buildRegistry(): array
    {
        return [
            // ============================================
            // GROUP 1: Bangladesh Information
            // ============================================
            'পর্যটন' => [
                'key'       => 'tourism',
                'model'     => TourismBd::class,
                'icon'      => 'camera',
                'label'     => 'পর্যটন এলাকা',
                'color'     => 'emerald',
                'relations' => ['division', 'district', 'media'],
                'url'       => fn($item) => '/bangladesh/tourism/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    $location = implode(' • ', array_filter([
                        optional($item->district)->name,
                        optional($item->division)->name,
                    ]));
                    $description = Str::limit(strip_tags($item->description ?? $item->details ?? ''), 80);

                    return $location . ($location ? ' | ' : '') . $description;
                },
            ],

            'পরিচিতি' => [
                'key'       => 'introduction',
                'model'     => IntroBd::class,
                'icon'      => 'map-pin',
                'label'     => 'পরিচিতি',
                'color'     => 'blue',
                'relations' => ['division', 'district', 'media'],
                'url'       => fn($item) => '/bangladesh/introduction/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    return Str::limit(strip_tags($item->description ?? $item->details ?? ''), 100);
                },
            ],

            'প্রতিষ্ঠান' => [
                'key'       => 'establishment',
                'model'     => EstablishmentBd::class,
                'icon'      => 'building-office-2',
                'label'     => 'প্রতিষ্ঠান',
                'color'     => 'teal',
                'relations' => ['division', 'district', 'media'],
                'url'       => fn($item) => '/bangladesh/establishment/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    $location = implode(' • ', array_filter([
                        optional($item->district)->name,
                        optional($item->division)->name,
                    ]));
                    $description = Str::limit(strip_tags($item->description ?? $item->details ?? ''), 80);

                    return $location . ($location ? ' | ' : '') . $description;
                },
            ],

            'গুনীজন' => [
                'key'       => 'person',
                'model'     => Person::class,
                'icon'      => 'user-circle',
                'label'     => 'গুনীজন',
                'color'     => 'blue',
                'relations' => ['media'],
                'url'       => fn($item) => '/bangladesh/public-figure/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    return Str::limit(strip_tags($item->bio ?? $item->details ?? ''), 100);
                },
            ],

            'ইতিহাস' => [
                'key'       => 'history',
                'model'     => HistoryBd::class,
                'icon'      => 'book-open',
                'label'     => 'ইতিহাস',
                'color'     => 'purple',
                'relations' => ['division', 'district'],
                'url'       => fn($item) => '/bangladesh/history/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    $location = implode(' • ', array_filter([
                        optional($item->district)->name,
                        optional($item->division)->name,
                    ]));
                    $description = Str::limit(strip_tags($item->description ?? ''), 100);

                    return $location . ($location ? ' | ' : '') . $description;
                },
            ],

            // ============================================
            // GROUP 2: Food, Nutrition & Health
            // ============================================
            'খাবার' => [
                'key'       => 'food',
                'model'     => Food::class,
                'icon'      => 'cake',
                'label'     => 'খাবার',
                'color'     => 'amber',
                'relations' => ['category'],
                'url'       => fn($item) => '/health/food-nutrients',
                'subtitle'  => function ($item) {
                    $category = optional($item->category)->name ?? '';
                    $description = Str::limit(strip_tags($item->description ?? ''), 80);

                    return $category . ($category ? ' | ' : '') . $description;
                },
            ],

            'স্বাস্থ্য' => [
                'key'       => 'health',
                'model'     => BasicHealth::class,
                'icon'      => 'heart',
                'label'     => 'স্বাস্থ্য',
                'color'     => 'red',
                'relations' => [],
                'url'       => fn($item) => '/health/basic-health',
                'subtitle'  => function ($item) {
                    return Str::limit(strip_tags($item->description ?? $item->details ?? ''), 100);
                },
            ],

            // ============================================
            // GROUP 3: Islamic Content
            // ============================================
            'ইসলাম' => [
                'key'       => 'islam',
                'model'     => BasicIslam::class,
                'icon'      => 'sparkles',
                'label'     => 'ইসলাম',
                'color'     => 'green',
                'relations' => [],
                'url'       => fn($item) => '/islam/basic/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    return Str::limit(strip_tags($item->description ?? ''), 100);
                },
            ],

            'দোয়া' => [
                'key'       => 'dowa',
                'model'     => Dowa::class,
                'icon'      => 'hand-raised',
                'label'     => 'দোয়া',
                'color'     => 'lime',
                'relations' => [],
                'url'       => fn($item) => '/islam/dowan/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    return Str::limit(strip_tags($item->bangla_text ?? $item->details ?? ''), 100);
                },
            ],

            'কুরআন' => [
                'key'       => 'quran',
                'model'     => Quran::class,
                'icon'      => 'book-open',
                'label'     => 'কুরআন',
                'color'     => 'emerald',
                'relations' => ['sura', 'para'],
                'url'       => fn($item) => '/islam/al-quran',
                'subtitle'  => function ($item) {
                    $sura = optional($item->sura)->name ?? 'সূরা';
                    $ayat = $item->ayat_no ?? '';

                    return "সূরা: $sura (আয়াত: $ayat)";
                },
            ],

            'সূরা' => [
                'key'       => 'sura',
                'model'     => Sura::class,
                'icon'      => 'book',
                'label'     => 'সূরা',
                'color'     => 'sky',
                'relations' => [],
                'url'       => fn($item) => '/islam/al-quran',
                'subtitle'  => fn($item) => 'আয়াত সংখ্যা: ' . ($item->total_ayat ?? 0),
            ],

            // ============================================
            // GROUP 4: Education
            // ============================================
            'প্রশ্ন' => [
                'key'       => 'question',
                'model'     => Question::class,
                'icon'      => 'light-bulb',
                'label'     => 'প্রশ্ন',
                'color'     => 'yellow',
                'relations' => ['subject', 'classLevel'],
                'url'       => fn($item) => '/mcq',
                'subtitle'  => function ($item) {
                    $subject = optional($item->subject)->name ?? '';
                    $class = optional($item->classLevel)->name ?? '';

                    return implode(' • ', array_filter([$class, $subject]));
                },
            ],

            'টিউটোরিয়াল' => [
                'key'       => 'tutorial',
                'model'     => ExcelTutorial::class,
                'icon'      => 'beaker',
                'label'     => 'টিউটোরিয়াল',
                'color'     => 'indigo',
                'relations' => [],
                'url'       => fn($item) => '/excel-expert/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    return $item->chapter_name ?? Str::limit(strip_tags($item->description ?? ''), 80);
                },
            ],

            'ছুটির দিন' => [
                'key'       => 'holiday',
                'model'     => Holiday::class,
                'icon'      => 'calendar',
                'label'     => 'ছুটির দিন',
                'color'     => 'rose',
                'relations' => [],
                'url'       => fn($item) => '/bangla/holiday/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    if (empty($item->date)) {
                        return 'তারিখ অজ্ঞাত';
                    }
                    try {
                        $date = $item->date instanceof Carbon
                            ? $item->date
                            : Carbon::parse($item->date);

                        return function_exists('bn_date')
                            ? bn_date($date->translatedFormat('j F Y, l'))
                            : $date->format('j F Y');
                    } catch (\Throwable) {
                        return (string) $item->date;
                    }
                },
            ],

            // ============================================
            // GROUP 5: Other Content
            // ============================================
            'সাইন ভাষা' => [
                'key'       => 'sign',
                'model'     => Sign::class,
                'icon'      => 'hand-raised',
                'label'     => 'সাইন ভাষা',
                'color'     => 'violet',
                'relations' => ['category'],
                'url'       => function ($item) {
                    $cat = $item->category?->slug ?? null;
                    $slug = $item->slug ?? '';

                    return $cat ? "/signs/{$cat}/{$slug}" : "/signs/{$slug}";
                },
                'subtitle'  => function ($item) {
                    return $item->category?->name ?? 'সাইন ভাষা';
                },
            ],

            'খবর' => [
                'key'       => 'news',
                'model'     => NewsHeading::class,
                'icon'      => 'newspaper',
                'label'     => 'খবর',
                'color'     => 'orange',
                'relations' => [],
                'url'       => fn($item) => '/news',
                'subtitle'  => function ($item) {
                    $time = $item->published_at ? $item->published_at->diffForHumans() : '';

                    return "{$item->source_name} • {$time}";
                },
            ],

            'বাজার' => [
                'key'       => 'market',
                'model'     => BuySellPost::class,
                'icon'      => 'shopping-cart',
                'label'     => 'বিক্রয়',
                'color'     => 'cyan',
                'relations' => ['category', 'user'],
                'url'       => fn($item) => '/buysell/prodict/' . ($item->slug ?? ''),
                'subtitle'  => function ($item) {
                    $category = optional($item->category)->name ?? '';
                    $price = $item->price ?? 'মূল্য নির্ধারণ করা হয়নি';

                    return $category . ($category ? ' | ' : '') . $price;
                },
            ],
        ];
    }

    public static function resolvePrefix(string $prefix): ?string
    {
        $prefix = trim(mb_strtolower($prefix));

        foreach (array_keys(self::all()) as $key) {
            if (mb_strtolower($key) === $prefix) {
                return $key;
            }
        }

        return self::$aliases[$prefix] ?? null;
    }

    public static function get(string $key): ?array
    {
        $resolved = self::resolvePrefix($key) ?? $key;

        return static::all()[$resolved] ?? null;
    }

    public static function keys(): array
    {
        return array_keys(static::all());
    }

    public static function prefixHints(): array
    {
        return [
            'পর্যটন' => 'tourism',
            'খাবার'  => 'food',
            'স্বাস্থ্য' => 'health',
            'দোয়া'   => 'dua',
            'কুরআন'  => 'quran',
            'প্রশ্ন'  => 'question',
            'বাজার'  => 'market',
        ];
    }
}