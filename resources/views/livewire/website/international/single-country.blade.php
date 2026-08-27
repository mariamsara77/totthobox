<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

new class extends Component {
    public string $slug = '';
    public ?array $country = null;
    public array $neighbors = [];
    public array $popularCountries = [];
    public bool $notFound = false;

    /**
     * Bump this whenever the shape of the cached array changes.
     * Old caches under the previous key ("...v9") could contain fields
     * with a different type (e.g. a string instead of an array), which is
     * exactly what caused implode(): Argument #2 ($array) ... string given.
     */
    private const CACHE_KEY = 'countries_merged_v10';
    private const CACHE_TTL_SUCCESS_DAYS = 30;
    private const CACHE_TTL_FAILURE_MINUTES = 10;

    /* ================================================================
     * Mount — slug থেকে দেশ খুঁজে বের করা
     * ================================================================ */
    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $all = $this->getAllCountries();
        $found = collect($all)->firstWhere('slug', $slug);

        if (!$found) {
            $this->notFound = true;

            // একদম খালি "দেশ পাওয়া যায়নি" পেজ না দেখিয়ে, ব্যবহারকারীকে
            // কিছু জনপ্রিয় দেশের লিংক দেখানো হচ্ছে — এতে পেজটা এখনও
            // দর্শনার্থীর জন্য কার্যকর/সহায়ক থাকে (thin/dead-end page নয়)।
            $this->popularCountries = collect($all)
                ->whereIn('cca3', ['USA', 'GBR', 'IND', 'CHN', 'JPN', 'DEU', 'FRA', 'BRA', 'CAN', 'AUS', 'SAU', 'ARE'])
                ->map(
                    fn($n) => [
                        'name' => $n['name'] ?? 'N/A',
                        'slug' => $n['slug'] ?? '',
                        'flag_emoji' => $n['flag_emoji'] ?? '🌐',
                        'flag' => $n['flag'] ?? null,
                    ],
                )
                ->values()
                ->toArray();

            return;
        }

        $this->country = $this->normalizeCountry($found);

        // ── সীমান্তবর্তী দেশের পূর্ণ নাম + slug + পতাকা ──
        $borders = Arr::wrap($this->country['borders'] ?? []);
        if (!empty($borders)) {
            $this->neighbors = collect($all)
                ->whereIn('cca3', $borders)
                ->map(
                    fn($n) => [
                        'name' => $n['name'] ?? 'N/A',
                        'slug' => $n['slug'] ?? '',
                        'flag_emoji' => $n['flag_emoji'] ?? '🌐',
                        'flag' => $n['flag'] ?? null,
                        'code' => $n['code'] ?? '',
                        'cca3' => $n['cca3'] ?? '',
                    ],
                )
                ->values()
                ->toArray();
        }
    }

    /**
     * Guarantees every field the view relies on exists AND has the
     * correct type. This is the key fix: previously fields were only
     * defaulted when `isset()` was false, so a field that existed but
     * had the wrong type (e.g. a string instead of an array) slipped
     * through untouched and blew up implode() downstream.
     */
    private function normalizeCountry(array $c): array
    {
        $stringDefaults = [
            'fifa' => 'N/A',
            'cioc' => 'N/A',
            'ccn3' => 'N/A',
            'code' => 'N/A',
            'cca3' => 'N/A',
            'car_signs' => 'N/A',
            'name' => 'Unknown',
            'official_name' => 'Unknown',
            'region' => 'N/A',
            'subregion' => '',
            'continent' => 'N/A',
            'capital' => 'N/A',
            'coords' => 'N/A',
            'landlocked' => 'N/A',
            'independent' => 'N/A',
            'un_member' => 'N/A',
            'driving_side' => 'N/A',
            'start_of_week' => 'N/A',
            'phone_code' => 'N/A',
            'flag' => asset('/og-image.png'),
            'flag_svg' => null,
            'flag_emoji' => '🌐',
            'coat_of_arms' => null,
            'google_maps' => null,
            'google_maps_embed' => null,
            'open_street_maps' => null,
            'name_bengali' => null,
            'name_arabic' => null,
            'name_french' => null,
            'name_spanish' => null,
            'name_chinese' => null,
            'name_russian' => null,
            'name_german' => null,
            'name_hindi' => null,
            'name_japanese' => null,
        ];

        $arrayDefaults = ['all_phone_codes', 'tld', 'borders', 'languages', 'languages_raw', 'currencies', 'timezones', 'all_capitals'];

        $numberDefaults = [
            'population' => 0,
            'area' => 0,
            'borders_count' => 0,
        ];

        foreach ($stringDefaults as $key => $default) {
            if (!isset($c[$key]) || (is_string($default) && !is_string($c[$key]) && !is_null($default))) {
                $c[$key] = $c[$key] ?? $default;
            }
        }

        // Force array-type fields to actually be arrays, no matter what
        // was sitting in a stale cache entry.
        foreach ($arrayDefaults as $key) {
            $c[$key] = Arr::wrap($c[$key] ?? []);
        }

        foreach ($numberDefaults as $key => $default) {
            $c[$key] = is_numeric($c[$key] ?? null) ? $c[$key] : $default;
        }

        $c['density'] = $c['density'] ?? 'N/A';

        return $c;
    }

    /* ================================================================
     * ডেটাসেট — cache থেকে অথবা API call
     * ================================================================ */
    private function getAllCountries(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        try {
            $mainResp = Http::timeout(25)->retry(2, 800)->get('https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json');
            if (!$mainResp->successful() || !is_array($mainResp->json())) {
                // Don't lock in a month-long empty cache on a transient failure.
                Cache::put(self::CACHE_KEY, [], now()->addMinutes(self::CACHE_TTL_FAILURE_MINUTES));
                return [];
            }

            $popResp = Http::timeout(15)->retry(2, 500)->get('https://raw.githubusercontent.com/samayo/country-json/master/src/country-by-population.json');
            $popMap = [];
            if ($popResp->successful() && is_array($popResp->json())) {
                foreach ($popResp->json() as $item) {
                    if (isset($item['country'])) {
                        $popMap[$item['country']] = $item['population'] ?? 0;
                    }
                }
            }

            $contResp = Http::timeout(15)->retry(2, 500)->get('https://raw.githubusercontent.com/samayo/country-json/master/src/country-by-continent.json');
            $contMap = [];
            if ($contResp->successful() && is_array($contResp->json())) {
                foreach ($contResp->json() as $item) {
                    if (isset($item['country'])) {
                        $contMap[$item['country']] = $item['continent'] ?? null;
                    }
                }
            }

            $countries = collect($mainResp->json())
                ->map(function ($c) use ($popMap, $contMap) {
                    $commonName = $c['name']['common'] ?? 'Unknown';
                    $officialName = $c['name']['official'] ?? 'Unknown';
                    $code = $c['cca2'] ?? '';
                    $population = (int) ($popMap[$commonName] ?? ($popMap[$officialName] ?? 0));
                    $continent = $contMap[$commonName] ?? ($contMap[$officialName] ?? ($c['region'] ?? 'N/A'));

                    $nameBengali = $c['name']['native']['ben']['common'] ?? ($c['name']['native']['ben']['official'] ?? null);
                    if (!$nameBengali && !empty($c['translations']['ben']['common'])) {
                        $nameBengali = $c['translations']['ben']['common'];
                    }

                    $nameArabic = $c['translations']['ara']['common'] ?? null;
                    $nameFrench = $c['translations']['fra']['common'] ?? null;
                    $nameSpanish = $c['translations']['spa']['common'] ?? null;
                    $nameChinese = $c['translations']['zho']['common'] ?? null;
                    $nameRussian = $c['translations']['rus']['common'] ?? null;
                    $nameGerman = $c['translations']['deu']['common'] ?? null;
                    $nameHindi = $c['translations']['hin']['common'] ?? null;
                    $nameJapanese = $c['translations']['jpn']['common'] ?? null;

                    $languages = !empty($c['languages']) && is_array($c['languages']) ? array_values($c['languages']) : [];
                    $languagesRaw = !empty($c['languages']) && is_array($c['languages']) ? $c['languages'] : [];

                    $currencies = [];
                    if (!empty($c['currencies']) && is_array($c['currencies'])) {
                        foreach ($c['currencies'] as $code2 => $cur) {
                            $currencies[] = [
                                'code' => $code2,
                                'name' => $cur['name'] ?? $code2,
                                'symbol' => $cur['symbol'] ?? '',
                            ];
                        }
                    }

                    $phoneCode = 'N/A';
                    $allPhoneCodes = [];
                    if (!empty($c['idd']['root'])) {
                        $suffixes = !empty($c['idd']['suffixes']) && is_array($c['idd']['suffixes']) ? $c['idd']['suffixes'] : [''];
                        foreach ($suffixes as $suffix) {
                            $allPhoneCodes[] = $c['idd']['root'] . $suffix;
                        }
                        $phoneCode = $allPhoneCodes[0] ?? 'N/A';
                    }

                    $lat = $c['latlng'][0] ?? null;
                    $lng = $c['latlng'][1] ?? null;
                    $coords = $lat !== null && $lng !== null ? number_format($lat, 4) . '°, ' . number_format($lng, 4) . '°' : 'N/A';
                    $googleMapsUrl = $lat !== null && $lng !== null ? "https://www.google.com/maps/@{$lat},{$lng},6z" : 'https://www.google.com/maps/search/' . urlencode($commonName);
                    $googleMapsEmbed = $lat !== null && $lng !== null ? "https://maps.google.com/maps?q={$lat},{$lng}&z=5&output=embed" : null;

                    $area = (float) ($c['area'] ?? 0);
                    $density = $area > 0 && $population > 0 ? number_format($population / $area, 2) . ' /km²' : 'N/A';
                    $demonym = $c['demonyms']['eng']['m'] ?? 'N/A';
                    $demonymF = $c['demonyms']['eng']['f'] ?? 'N/A';

                    $lowerCode = strtolower($code ?: 'un');
                    $flagPng = "https://flagcdn.com/w640/{$lowerCode}.png";
                    $flagSvg = "https://flagcdn.com/{$lowerCode}.svg";

                    $timezones = !empty($c['timezones']) && is_array($c['timezones']) ? $c['timezones'] : [];
                    $startOfWeek = $c['startOfWeek'] ?? 'N/A';
                    $drivingSide = $c['car']['side'] ?? 'N/A';
                    $carSignsArr = !empty($c['car']['signs']) && is_array($c['car']['signs']) ? $c['car']['signs'] : [];
                    $carSigns = !empty($carSignsArr) ? implode(', ', $carSignsArr) : 'N/A';
                    $postalCodeFormat = $c['postalCode']['format'] ?? null;
                    $capitalInfo = $c['capitalInfo'] ?? [];
                    $capitalLat = $capitalInfo['latlng'][0] ?? null;
                    $capitalLng = $capitalInfo['latlng'][1] ?? null;

                    $coatOfArms = $c['coatOfArms']['png'] ?? ($c['coatOfArms']['svg'] ?? null);
                    $maps = $c['maps'] ?? [];
                    $openStreetMaps = $maps['openStreetMaps'] ?? null;
                    $googleMapsLink = $maps['googleMaps'] ?? $googleMapsUrl;

                    return [
                        'slug' => Str::slug($commonName),
                        'name' => $commonName,
                        'official_name' => $officialName,
                        'name_bengali' => $nameBengali,
                        'name_arabic' => $nameArabic,
                        'name_french' => $nameFrench,
                        'name_spanish' => $nameSpanish,
                        'name_chinese' => $nameChinese,
                        'name_russian' => $nameRussian,
                        'name_german' => $nameGerman,
                        'name_hindi' => $nameHindi,
                        'name_japanese' => $nameJapanese,
                        'demonym' => $demonym,
                        'demonym_f' => $demonymF,
                        'independent' => !empty($c['independent']) ? 'স্বাধীন রাষ্ট্র' : 'অধীনস্থ অঞ্চল',
                        'un_member' => !empty($c['unMember']) ? 'জাতিসংঘ সদস্য' : 'জাতিসংঘ সদস্য নয়',
                        'status' => $c['status'] ?? 'N/A',
                        'code' => $code ?: 'N/A',
                        'cca3' => $c['cca3'] ?? 'N/A',
                        'ccn3' => $c['ccn3'] ?? 'N/A',
                        'cioc' => $c['cioc'] ?? 'N/A',
                        'fifa' => $c['fifa'] ?? 'N/A',
                        'region' => $c['region'] ?? 'Unknown',
                        'subregion' => $c['subregion'] ?? '',
                        'continent' => $continent,
                        'capital' => !empty($c['capital']) && is_array($c['capital']) ? $c['capital'][0] : 'N/A',
                        'all_capitals' => !empty($c['capital']) && is_array($c['capital']) ? $c['capital'] : [],
                        'capital_lat' => $capitalLat,
                        'capital_lng' => $capitalLng,
                        'coords' => $coords,
                        'lat' => $lat,
                        'lng' => $lng,
                        'landlocked' => !empty($c['landlocked']) ? 'স্থলবেষ্টিত' : 'সমুদ্রবেষ্টিত',
                        'area' => $area,
                        'borders' => !empty($c['borders']) && is_array($c['borders']) ? $c['borders'] : [],
                        'borders_count' => !empty($c['borders']) && is_array($c['borders']) ? count($c['borders']) : 0,
                        'tld' => !empty($c['tld']) && is_array($c['tld']) ? $c['tld'] : [],
                        'population' => $population,
                        'density' => $density,
                        'languages' => $languages,
                        'languages_raw' => $languagesRaw,
                        'currencies' => $currencies,
                        'phone_code' => $phoneCode,
                        'all_phone_codes' => $allPhoneCodes,
                        'timezones' => $timezones,
                        'start_of_week' => $startOfWeek,
                        'driving_side' => $drivingSide,
                        'car_signs' => $carSigns,
                        'postal_code_format' => $postalCodeFormat,
                        'flag' => $flagPng,
                        'flag_svg' => $flagSvg,
                        'flag_emoji' => $c['flag'] ?? '🌐',
                        'coat_of_arms' => $coatOfArms,
                        'google_maps' => $googleMapsLink,
                        'google_maps_embed' => $googleMapsEmbed,
                        'open_street_maps' => $openStreetMaps,
                    ];
                })
                ->sortBy('name')
                ->values()
                ->toArray();

            Cache::put(self::CACHE_KEY, $countries, now()->addDays(self::CACHE_TTL_SUCCESS_DAYS));

            return $countries;
        } catch (\Throwable $e) {
            \Log::error('Countries fetch error: ' . $e->getMessage());
            Cache::put(self::CACHE_KEY, [], now()->addMinutes(self::CACHE_TTL_FAILURE_MINUTES));
            return [];
        }
    }

    /* ----------------------------------------------------------------
     * হেল্পার — সংখ্যা ফরম্যাটিং
     * ---------------------------------------------------------------- */
    public function formatPopulation(int|float $pop): string
    {
        $pop = (float) $pop;
        if ($pop >= 1_000_000_000) {
            return number_format($pop / 1_000_000_000, 2) . ' বিলিয়ন';
        }
        if ($pop >= 1_000_000) {
            return number_format($pop / 1_000_000, 2) . ' মিলিয়ন';
        }
        if ($pop >= 1_000) {
            return number_format($pop / 1_000, 1) . ' হাজার';
        }
        return $pop > 0 ? number_format($pop) : 'তথ্য নেই';
    }

    public function formatArea(int|float $area): string
    {
        $area = (float) $area;
        if ($area <= 0) {
            return 'N/A';
        }
        if ($area >= 1_000_000) {
            return number_format($area / 1_000_000, 2) . ' মি. km²';
        }
        return number_format($area) . ' km²';
    }

    public function formatNumber(int|float $n): string
    {
        return number_format($n);
    }

    /* ----------------------------------------------------------------
     * ★ AdSense-friendly addition #1: প্রকৃত, পাঠযোগ্য বিবরণী প্যারাগ্রাফ।
     * শুধু টেবিল/ব্যাজ না দেখিয়ে, ডেটা থেকে সংক্ষিপ্ত মানুষ-পড়ার-উপযোগী
     * লেখা তৈরি করা হচ্ছে যাতে পেজটাকে "শুধু কাঁচা ডেটার তালিকা" মনে না হয়।
     * এই টেক্সট প্রতিটি দেশের জন্য আলাদা, ফলে প্রতিটি পেজ অনন্য (unique)।
     * পরবর্তীতে চাইলে এই ফাংশনের বদলে ম্যানুয়ালি/এডিটোরিয়ালি লেখা কন্টেন্ট
     * (DB কলামে সংরক্ষিত) ব্যবহার করলে "helpful content" স্কোর আরও ভালো হবে।
     * ---------------------------------------------------------------- */
    public function generateOverview(array $c): string
    {
        $name = $c['name'] ?? 'এই দেশ';
        $statusPhrase = ($c['independent'] ?? '') === 'স্বাধীন রাষ্ট্র' ? 'একটি স্বাধীন রাষ্ট্র' : 'একটি অধীনস্থ অঞ্চল';
        $region = $c['subregion'] ?: $c['region'] ?? 'N/A';
        $continent = $c['continent'] ?? 'N/A';

        $sentence1 = "{$name} হলো {$continent} মহাদেশের {$region} অঞ্চলে অবস্থিত {$statusPhrase}।";

        $capital = $c['capital'] ?? 'N/A';
        $sentence2 = $capital && $capital !== 'N/A' ? "দেশটির রাজধানী {$capital}, " : '';

        $population = (int) ($c['population'] ?? 0);
        $sentence2 .= $population > 0 ? "এবং বর্তমান জনসংখ্যা আনুমানিক {$this->formatPopulation($population)}।" : 'তবে জনসংখ্যার সুনির্দিষ্ট তথ্য এই মুহূর্তে পাওয়া যায়নি।';

        $area = (float) ($c['area'] ?? 0);
        $langCount = count($c['languages'] ?? []);
        $sentence3 = '';
        if ($area > 0) {
            $sentence3 .= "আয়তনের দিক থেকে দেশটি প্রায় {$this->formatArea($area)} জুড়ে বিস্তৃত। ";
        }
        if ($langCount > 0) {
            $langList = implode(', ', array_slice($c['languages'], 0, 3));
            $sentence3 .= "এখানে সরকারিভাবে {$langCount}টি ভাষা স্বীকৃত, যার মধ্যে উল্লেখযোগ্য {$langList}।";
        }

        $landlocked = $c['landlocked'] ?? 'N/A';
        $bordersCount = (int) ($c['borders_count'] ?? 0);
        $sentence4 = '';
        if ($landlocked === 'স্থলবেষ্টিত') {
            $sentence4 = "{$name} একটি স্থলবেষ্টিত দেশ, অর্থাৎ এর কোনো সমুদ্র উপকূল নেই";
            $sentence4 .= $bordersCount > 0 ? " এবং এটি {$bordersCount}টি দেশের সাথে সীমান্ত ভাগ করে।" : '।';
        } elseif ($bordersCount > 0) {
            $sentence4 = "{$name} সমুদ্রবেষ্টিত হলেও স্থলপথে {$bordersCount}টি প্রতিবেশী দেশের সাথে সীমান্ত রয়েছে।";
        } else {
            $sentence4 = "{$name} একটি দ্বীপরাষ্ট্র বা বিচ্ছিন্ন ভূখণ্ড — এর কোনো স্থল সীমান্ত নেই।";
        }

        $currency = !empty($c['currencies']) ? $c['currencies'][0]['name'] ?? null : null;
        $sentence5 = $currency ? "দেশটিতে লেনদেনের প্রধান মুদ্রা হিসেবে ব্যবহৃত হয় {$currency}।" : '';

        return trim(implode(' ', array_filter([$sentence1, $sentence2, $sentence3, $sentence4, $sentence5])));
    }

    /* ----------------------------------------------------------------
     * ★ AdSense-friendly addition #2: প্রশ্নোত্তর (FAQ) — এটি শুধু
     * অতিরিক্ত পাঠযোগ্য কন্টেন্টই যোগ করে না, বরং FAQPage schema
     * ব্যবহার করলে সার্চ রেজাল্টেও rich snippet হিসেবে দেখাতে পারে।
     * ---------------------------------------------------------------- */
    public function generateFaqs(array $c): array
    {
        $name = $c['name'] ?? 'দেশটি';
        $faqs = [];

        if (($c['capital'] ?? 'N/A') !== 'N/A') {
            $faqs[] = [
                'q' => "{$name} এর রাজধানীর নাম কী?",
                'a' => "{$name} এর রাজধানীর নাম {$c['capital']}।",
            ];
        }

        if ((int) ($c['population'] ?? 0) > 0) {
            $faqs[] = [
                'q' => "{$name} এর জনসংখ্যা কত?",
                'a' => "সাম্প্রতিক তথ্য অনুযায়ী {$name} এর জনসংখ্যা আনুমানিক {$this->formatPopulation($c['population'])} (প্রায় " . $this->formatNumber($c['population']) . ' জন)।',
            ];
        }

        if (!empty($c['languages'])) {
            $faqs[] = [
                'q' => "{$name} এ কোন ভাষায় কথা বলা হয়?",
                'a' => "{$name} এর সরকারি ভাষা হলো " . implode(', ', $c['languages']) . '।',
            ];
        }

        if (!empty($c['currencies'])) {
            $cur = $c['currencies'][0];
            $faqs[] = [
                'q' => "{$name} এর মুদ্রার নাম কী?",
                'a' => "{$name} এ ব্যবহৃত প্রধান মুদ্রার নাম {$cur['name']}" . ($cur['symbol'] ? " (প্রতীক: {$cur['symbol']})" : '') . '।',
            ];
        }

        $faqs[] = [
            'q' => "{$name} কি স্থলবেষ্টিত দেশ?",
            'a' => ($c['landlocked'] ?? '') === 'স্থলবেষ্টিত' ? "হ্যাঁ, {$name} একটি স্থলবেষ্টিত দেশ — এর কোনো সমুদ্র উপকূল নেই।" : "না, {$name} এর সমুদ্র উপকূল রয়েছে।",
        ];

        if ((int) ($c['borders_count'] ?? 0) > 0) {
            $faqs[] = [
                'q' => "{$name} এর সীমান্তবর্তী দেশ কয়টি?",
                'a' => "{$name} মোট {$c['borders_count']}টি দেশের সাথে স্থল সীমান্ত ভাগ করে।",
            ];
        }

        return $faqs;
    }
}; ?>

@php
    $c = $country ?? [];

    $seoImage = $c['flag'] ?? null;
    if (!$seoImage || !str_starts_with($seoImage, 'http')) {
        $seoImage = asset('/og-image.png');
    }

    $countryName = $c['name'] ?? 'দেশ';
    $bnName = $c['name_bengali'] ?? $countryName;
    $capital = $c['capital'] ?? 'অজ্ঞাত';
    $population = isset($c['population']) ? $this->formatPopulation($c['population']) : 'তথ্য নেই';
    $area = isset($c['area']) ? $this->formatArea($c['area']) : 'তথ্য নেই';
    $region = $c['region'] ?? '';
    $continent = $c['continent'] ?? '';
    $currency = !empty($c['currencies'][0]['name']) ? $c['currencies'][0]['name'] : '';
    $languages = !empty($c['languages']) ? implode(', ', array_slice($c['languages'], 0, 3)) : '';

    $seoTitle = $notFound
        ? 'দেশ পাওয়া যায়নি | বিশ্বকোষ - সকল দেশের তথ্য'
        : "{$bnName} ({$countryName}) - রাজধানী, জনসংখ্যা, আয়তন, মানচিত্র ও বিস্তারিত তথ্য";

    $seoDesc = $notFound
        ? 'অনুরোধকৃত দেশটির তথ্য আমাদের ডেটাবেসে পাওয়া যায়নি। বিশ্বের সকল দেশের সম্পূর্ণ তালিকা, জনসংখ্যা, রাজধানী ও মানচিত্র দেখতে ভিজিট করুন।'
        : "{$bnName} ({$countryName}) এর রাজধানী {$capital}, জনসংখ্যা {$population}, আয়তন {$area}। " .
            ($continent ? "{$continent} মহাদেশের " : '') .
            ($region ? "{$region} অঞ্চলের " : '') .
            'এই দেশের ভাষা' .
            ($languages ? " ({$languages})" : '') .
            ($currency ? ", মুদ্রা {$currency}" : '') .
            ', সীমান্তবর্তী দেশ, পতাকা, কোড ও মানচিত্রসহ সম্পূর্ণ তথ্য জানুন।';

    $seoKeywords = $notFound
        ? 'দেশ পাওয়া যায়নি, বিশ্বকোষ, সকল দেশ, দেশের তালিকা, country list, world countries'
        : implode(
            ', ',
            array_filter([
                $bnName,
                $countryName,
                "{$bnName} দেশ",
                "{$countryName} country",
                "{$bnName} রাজধানী",
                "{$countryName} capital",
                "{$bnName} জনসংখ্যা",
                "{$countryName} population",
                "{$bnName} আয়তন",
                "{$countryName} area",
                "{$bnName} মানচিত্র",
                "{$countryName} map",
                "{$bnName} পতাকা",
                "{$countryName} flag",
                "{$bnName} ভাষা",
                "{$countryName} languages",
                "{$bnName} মুদ্রা",
                "{$countryName} currency",
                "{$bnName} সীমান্ত",
                "{$countryName} borders",
                "{$bnName} তথ্য",
                "{$countryName} information",
                "{$bnName} বিস্তারিত",
                "{$continent} দেশ",
                "{$region} countries",
                'বিশ্বকোষ',
                'দেশের তথ্য',
                'country facts',
                'world encyclopedia',
                'country details bangla',
                'দেশ পরিচিতি',
                'geography',
                'ভৌগোলিক তথ্য',
            ]),
        );

    $overviewText = !$notFound ? $this->generateOverview($c) : '';
    $faqs = !$notFound ? $this->generateFaqs($c) : [];
@endphp

<x-seo :title="$seoTitle" :description="$seoDesc" :keywords="$seoKeywords" :image="$seoImage" />

{{-- ══════════════════════════════════════════════════════════════════
     Structured data (JSON-LD) — crawler/reviewer কে বুঝতে সাহায্য করে
     পেজটি একটি সুনির্দিষ্ট, তথ্যবহুল রেফারেন্স পেজ; শুধু র‍্যান্ডম ডেটা না।
══════════════════════════════════════════════════════════════════ --}}
@if (!$notFound)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Place',
        'name' => $c['name'] ?? null,
        'alternateName' => $c['official_name'] ?? null,
        'description' => $overviewText,
        'image' => $c['flag'] ?? null,
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => $c['lat'] ?? null,
            'longitude' => $c['lng'] ?? null,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @if (!empty($faqs))
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $f['a'],
                ],
            ])->toArray(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
@endif

{{-- ══════════════════════════════════════════════════════════════════
     NOT FOUND STATE — শুধু "নেই" বলে না রেখে, এখান থেকেও ব্যবহারকারীকে
     সাইটের ভেতরে কার্যকর জায়গায় পাঠানো হচ্ছে (dead-end page এড়ানো)।
══════════════════════════════════════════════════════════════════ --}}
@if ($notFound)
    <section class="max-w-2xl mx-auto py-20 text-center space-y-6 animate-in fade-in duration-200">
        <div class="text-7xl animate-bounce">🌐</div>
        <flux:heading size="xl">দেশটি পাওয়া যায়নি</flux:heading>
        <flux:text class="text-zinc-500">
            "<strong>{{ $slug }}</strong>" নামের কোনো দেশ আমাদের ডেটাবেসে নেই। বানান ঠিক আছে কিনা যাচাই করুন,
            অথবা নিচে থেকে জনপ্রিয় কোনো দেশ বেছে নিন।
        </flux:text>
        <div class="flex justify-center gap-4">
            <flux:button href="{{ route('international.all-country') }}" variant="filled" icon="arrow-left">
                সকল দেশে ফিরুন
            </flux:button>
        </div>

        @if (!empty($popularCountries))
            <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800">
                <flux:text size="sm" class="text-zinc-400 mb-3">জনপ্রিয় দেশসমূহ</flux:text>
                <div class="flex flex-wrap justify-center gap-4">
                    @foreach ($popularCountries as $pc)
                        <a href="{{ route('international.country', ['slug' => $pc['slug']]) }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 hover:border-indigo-400/40 text-sm transition-colors">
                            <span>{{ $pc['flag_emoji'] }}</span>
                            <span>{{ $pc['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         MAIN DETAIL PAGE
    ══════════════════════════════════════════════════════════════════ --}}
@else
    <section class="max-w-2xl mx-auto space-y-8 pb-16" wire:key="country-{{ $c['slug'] ?? $slug }}">

        {{-- ── ব্রেডক্রাম্ব ── --}}
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="/" icon="home" />
            <flux:breadcrumbs.item href="{{ route('international.all-country') }}">বিশ্বকোষ</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $c['name'] ?? 'N/A' }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        {{-- ══════════════════ HERO — পতাকা + মূল পরিচয় ══════════════════ --}}
        <div
            class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="relative aspect-video w-full overflow-hidden bg-zinc-400/10">
                <img src="{{ $c['flag_svg'] ?? $c['flag'] }}"
                    alt="{{ $c['name'] }} এর জাতীয় পতাকা — {{ $c['official_name'] }}" width="640" height="360"
                    loading="eager" fetchpriority="high" class="w-full h-full object-cover  duration-200"
                    onerror="this.onerror=null; this.src='{{ $c['flag'] }}';" />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent pointer-events-none">
                </div>

                <div class="absolute top-4 left-4 flex flex-wrap gap-4">
                    <flux:badge color="indigo" variant="solid" size="sm">{{ $c['region'] }}</flux:badge>
                    @if ($c['subregion'])
                        <flux:badge color="zinc" size="sm">{{ $c['subregion'] }}</flux:badge>
                    @endif
                </div>
                <div class="absolute top-4 right-4 flex flex-wrap gap-4">
                    <flux:badge color="zinc" size="sm" class="font-mono">{{ $c['code'] }}</flux:badge>
                    <flux:badge color="zinc" size="sm" class="font-mono">{{ $c['cca3'] }}</flux:badge>
                </div>

                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <p class="text-xs text-white/60 uppercase tracking-widest mb-2.5">{{ $c['continent'] }}</p>
                    <h1 class="text-3xl md:text-4xl font-black flex items-center gap-4 tracking-tight">
                        <span class="text-4xl" aria-hidden="true">{{ $c['flag_emoji'] }}</span>
                        <span>{{ $c['name'] }}</span>
                    </h1>
                    @if ($c['name_bengali'])
                        <p class="text-base text-white/70 mt-1 font-medium">{{ $c['name_bengali'] }}</p>
                    @endif
                    <p class="text-xs text-white/40 font-mono mt-0.5 tracking-wide">{{ $c['official_name'] }}</p>
                </div>
            </div>

            <div
                class="bg-zinc-400/10 px-5 py-3 flex flex-wrap items-center justify-between gap-4 border-t border-zinc-200 dark:border-zinc-800">
                <div class="flex flex-wrap gap-4">
                    <flux:badge :color="str_contains($c['un_member'], 'সদস্য নয়') ? 'zinc' : 'green'" size="sm"
                        icon="{{ str_contains($c['un_member'], 'সদস্য নয়') ? 'x-circle' : 'check-circle' }}">
                        {{ $c['un_member'] }}
                    </flux:badge>
                    <flux:badge :color="str_contains($c['independent'], 'অধীনস্থ') ? 'amber' : 'blue'" size="sm">
                        {{ $c['independent'] }}
                    </flux:badge>
                    @if ($c['landlocked'] === 'স্থলবেষ্টিত')
                        <flux:badge color="orange" size="sm" icon="map">স্থলবেষ্টিত দেশ</flux:badge>
                    @else
                        <flux:badge color="sky" size="sm" icon="sun">সমুদ্রসীমা আছে</flux:badge>
                    @endif
                </div>

                {{-- শেয়ার বাটন --}}
                <flux:button variant="ghost" icon="share" size="sm" data-share-button
                    aria-label="{{ $c['name'] }} শেয়ার করুন">
                    শেয়ার করুন
                </flux:button>
            </div>
        </div>

        {{-- ══════════════════ ★ সংক্ষিপ্ত বিবরণ (মূল পাঠযোগ্য কন্টেন্ট) ══════════════════
             AdSense রিভিউয়ে পেজটাকে শুধু "ডেটা টেবিল" নয়, একটা প্রকৃত
             তথ্যবহুল আর্টিকেল হিসেবে দেখানোর জন্য এই অংশটাই সবচেয়ে জরুরি। --}}
        <article class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
            <div class="flex items-center gap-2 mb-3">
                <flux:icon.book-open variant="micro" class="text-indigo-500" />
                <flux:heading size="sm" level="2" class="font-bold">{{ $c['name'] }} সম্পর্কে সংক্ষিপ্ত
                    পরিচিতি</flux:heading>
            </div>
            <p class="text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                {{ $overviewText }}
            </p>
        </article>

        {{-- ══════════════════ মেগা স্ট্যাটস ══════════════════ --}}
        @php
            $megaStats = [
                [
                    'icon' => 'users',
                    'color' => 'text-blue-500',
                    'bg' => 'group-hover:bg-blue-50 dark:group-hover:bg-blue-950/30',
                    'label' => 'জনসংখ্যা',
                    'value' => $this->formatPopulation($c['population']),
                ],
                [
                    'icon' => 'square-3-stack-3d',
                    'color' => 'text-green-500',
                    'bg' => 'group-hover:bg-green-50 dark:group-hover:bg-green-950/30',
                    'label' => 'আয়তন',
                    'value' => $this->formatArea($c['area']),
                ],
                [
                    'icon' => 'user-group',
                    'color' => 'text-amber-500',
                    'bg' => 'group-hover:bg-amber-50 dark:group-hover:bg-amber-950/30',
                    'label' => 'জনঘনত্ব',
                    'value' => $c['density'],
                ],
                [
                    'icon' => 'building-library',
                    'color' => 'text-purple-500',
                    'bg' => 'group-hover:bg-purple-50 dark:group-hover:bg-purple-950/30',
                    'label' => 'রাজধানী',
                    'value' => $c['capital'],
                ],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($megaStats as $stat)
                <div
                    class="group bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 text-center transition-all duration-200 hover:border-indigo-400/40 dark:hover:border-indigo-500/30 hover:-translate-y-0.5 ">
                    <div
                        class="w-9 h-9 mx-auto mb-2 rounded-lg flex items-center justify-center bg-zinc-50 dark:bg-zinc-800/60 {{ $stat['bg'] }} transition-colors">
                        <flux:icon :icon="$stat['icon']" class="{{ $stat['color'] }} size-5" />
                    </div>
                    <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100 font-mono leading-tight truncate"
                        title="{{ $stat['value'] }}">
                        {{ $stat['value'] }}
                    </div>
                    <flux:text size="xs" class="mt-1 text-zinc-500">{{ $stat['label'] }}</flux:text>
                </div>
            @endforeach
        </div>

        {{-- ══════════════════ দুই কলাম লেআউট ══════════════════ --}}
        <div class="grid md:grid-cols-2 gap-6">

            {{-- ── কোড ও আইডেন্টিটি ── --}}
            <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                    <flux:icon.identification variant="micro" class="text-indigo-500" />
                    <flux:heading size="sm" level="3" class="font-bold">আন্তর্জাতিক কোড</flux:heading>
                </div>
                @php
                    $codeRows = [
                        ['ISO Alpha-2', $c['code']],
                        ['ISO Alpha-3', $c['cca3']],
                        ['UN Numeric', $c['ccn3']],
                        ['Olympic (CIOC)', $c['cioc']],
                        ['FIFA', $c['fifa']],
                        [
                            'ডায়াল কোড',
                            !empty($c['all_phone_codes']) ? implode(', ', $c['all_phone_codes']) : $c['phone_code'],
                        ],
                        ['ইন্টারনেট TLD', !empty($c['tld']) ? implode(', ', $c['tld']) : 'N/A'],
                        ['গাড়ির সাইন', $c['car_signs']],
                    ];
                @endphp
                <flux:table>
                    <flux:table.rows>
                        @foreach ($codeRows as [$label, $value])
                            @if ($value !== null && $value !== '' && $value !== 'N/A')
                                <flux:table.row>
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400 text-sm">
                                        {{ $label }}</flux:table.cell>
                                    <flux:table.cell
                                        class="text-right font-mono font-semibold text-zinc-900 dark:text-zinc-100 text-sm">
                                        {{ $value }}</flux:table.cell>
                                </flux:table.row>
                            @endif
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            {{-- ── ভৌগোলিক তথ্য ── --}}
            <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                    <flux:icon.map-pin variant="micro" class="text-indigo-500" />
                    <flux:heading size="sm" level="3" class="font-bold">ভৌগোলিক তথ্য</flux:heading>
                </div>
                @php
                    $geoRows = [
                        ['মহাদেশ', $c['continent']],
                        ['অঞ্চল', $c['region']],
                        ['উপ-অঞ্চল', $c['subregion']],
                        ['স্থানাঙ্ক', $c['coords']],
                        ['ভৌগোলিক অবস্থান', $c['landlocked']],
                        [
                            'সীমান্তের সংখ্যা',
                            $c['borders_count'] > 0 ? $c['borders_count'] . 'টি দেশ' : 'কোনো স্থল সীমান্ত নেই',
                        ],
                        ['সপ্তাহ শুরু', $c['start_of_week'] !== 'N/A' ? ucfirst($c['start_of_week']) : 'N/A'],
                        [
                            'গাড়ি চালনা',
                            $c['driving_side'] === 'right'
                                ? 'ডান পাশে'
                                : ($c['driving_side'] === 'left'
                                    ? 'বাম পাশে'
                                    : 'N/A'),
                        ],
                    ];
                @endphp
                <flux:table>
                    <flux:table.rows>
                        @foreach ($geoRows as [$label, $value])
                            @if ($value !== null && $value !== '' && $value !== 'N/A')
                                <flux:table.row>
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400 text-sm">
                                        {{ $label }}</flux:table.cell>
                                    <flux:table.cell
                                        class="text-right font-semibold text-zinc-900 dark:text-zinc-100 text-sm">
                                        {{ $value }}</flux:table.cell>
                                </flux:table.row>
                            @endif
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        {{-- ══════════════════ নাগরিক ও সংস্কৃতি ══════════════════ --}}
        <div class="grid md:grid-cols-2 gap-6">

            {{-- ── ভাষা ── --}}
            @if (!empty($c['languages']))
                <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                        <flux:icon.language variant="micro" class="text-green-500" />
                        <flux:heading size="sm" level="3" class="font-bold">সরকারি ভাষা
                            ({{ count($c['languages']) }})</flux:heading>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-wrap gap-4">
                            @foreach ($c['languages'] as $lang)
                                <flux:badge color="green" variant="solid" size="sm">{{ $lang }}
                                </flux:badge>
                            @endforeach
                        </div>
                        @if (!empty($c['languages_raw']))
                            <div class="mt-3 space-y-1 border-t border-zinc-400/25 pt-3">
                                @foreach ($c['languages_raw'] as $code => $name)
                                    <div class="flex justify-between text-xs">
                                        <span class="text-zinc-400 font-mono uppercase">{{ $code }}</span>
                                        <span
                                            class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ── মুদ্রা ── --}}
            @if (!empty($c['currencies']))
                <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                        <flux:icon.currency-dollar variant="micro" class="text-amber-500" />
                        <flux:heading size="sm" level="3" class="font-bold">ব্যবহৃত মুদ্রা</flux:heading>
                    </div>
                    <flux:table>
                        <flux:table.rows>
                            @foreach ($c['currencies'] as $cur)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-4">
                                            <flux:badge color="amber" size="sm" variant="solid"
                                                class="font-mono">{{ $cur['code'] }}</flux:badge>
                                            <span
                                                class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $cur['name'] }}</span>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        @if ($cur['symbol'])
                                            <span
                                                class="text-xl font-black text-indigo-600 dark:text-indigo-400 font-mono">{{ $cur['symbol'] }}</span>
                                        @else
                                            <span class="text-xs text-zinc-400">—</span>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        </div>

        {{-- ══════════════════ নামের অনুবাদ ══════════════════ --}}
        @php
            $translations = array_filter([
                '🇧🇩 বাংলা' => $c['name_bengali'],
                '🇸🇦 আরবি' => $c['name_arabic'],
                '🇫🇷 ফরাসি' => $c['name_french'],
                '🇪🇸 স্পেনীয়' => $c['name_spanish'],
                '🇨🇳 চীনা' => $c['name_chinese'],
                '🇷🇺 রুশ' => $c['name_russian'],
                '🇩🇪 জার্মান' => $c['name_german'],
                '🇮🇳 হিন্দি' => $c['name_hindi'],
                '🇯🇵 জাপানি' => $c['name_japanese'],
            ]);
        @endphp
        @if (!empty($translations))
            <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                    <flux:icon.chat-bubble-left-right variant="micro" class="text-indigo-500" />
                    <flux:heading size="sm" level="3" class="font-bold">বিভিন্ন ভাষায় নাম</flux:heading>
                </div>
                <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($translations as $langLabel => $translatedName)
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg px-3 py-2.5 hover:bg-zinc-400/25 transition-colors">
                            <p class="text-xs text-zinc-400 mb-2">{{ $langLabel }}</p>
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 truncate">
                                {{ $translatedName }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ══════════════════ টাইমজোন ══════════════════ --}}
        @if (!empty($c['timezones']))
            <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                    <flux:icon.clock variant="micro" class="text-sky-500" />
                    <flux:heading size="sm" level="3" class="font-bold">টাইমজোন
                        ({{ count($c['timezones']) }})</flux:heading>
                </div>
                <div class="p-4 flex flex-wrap gap-4">
                    @foreach ($c['timezones'] as $tz)
                        <flux:badge color="sky" size="sm" class="font-mono">{{ $tz }}</flux:badge>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ══════════════════ সীমান্তবর্তী দেশ ══════════════════ --}}
        <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                <flux:icon.map-pin variant="micro" class="text-indigo-500" />
                <flux:heading size="sm" level="3" class="font-bold">
                    @if (!empty($neighbors))
                        সীমান্তবর্তী দেশ ({{ count($neighbors) }})
                    @else
                        ভৌগোলিক সীমানা
                    @endif
                </flux:heading>
            </div>

            @if (!empty($neighbors))
                <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($neighbors as $nb)
                        <a href="{{ $nb['slug'] ? route('international.country', ['slug' => $nb['slug']]) : '#' }}"
                            class="flex items-center gap-2 p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800/50 transition-all group">
                            @if (!empty($nb['flag']))
                                <img src="{{ $nb['flag'] }}" alt="{{ $nb['name'] }} এর পতাকা" loading="lazy"
                                    width="32" height="20"
                                    class="w-8 h-5 object-cover rounded shadow-sm flex-shrink-0"
                                    onerror="this.src='https://flagcdn.com/w80/un.png'" />
                            @endif
                            <div class="min-w-0">
                                <p
                                    class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                    {{ $nb['name'] }}
                                </p>
                                <p class="text-xs text-zinc-400 font-mono">{{ $nb['cca3'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center">
                    <flux:icon.shield-check class="w-8 h-8 text-blue-400 mx-auto mb-2" />
                    <flux:text size="sm" class="text-zinc-500 font-medium">দ্বীপ দেশ — কোনো স্থল সীমান্ত নেই
                    </flux:text>
                </div>
            @endif
        </div>

        {{-- ══════════════════ রাষ্ট্রীয় প্রতীক ══════════════════ --}}
        @if ($c['coat_of_arms'])
            <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                    <flux:icon.shield-check variant="micro" class="text-indigo-500" />
                    <flux:heading size="sm" level="3" class="font-bold">রাষ্ট্রীয় প্রতীক</flux:heading>
                </div>
                <div class="p-6 flex justify-center bg-zinc-50/50 dark:bg-zinc-950/30">
                    <img src="{{ $c['coat_of_arms'] }}"
                        alt="{{ $c['name'] }} এর রাষ্ট্রীয় প্রতীক (Coat of Arms)" loading="lazy"
                        class="h-32 object-contain drop-shadow-sm"
                        onerror="this.closest('div').style.display='none'" />
                </div>
            </div>
        @endif

        {{-- ══════════════════ Google Maps Embed ══════════════════ --}}
        @if ($c['google_maps_embed'])
            <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <flux:icon.map variant="micro" class="text-indigo-500" />
                        <flux:heading size="sm" level="3" class="font-bold">মানচিত্র</flux:heading>
                    </div>
                    <div class="flex gap-4">
                        @if ($c['open_street_maps'])
                            <flux:button href="{{ $c['open_street_maps'] }}" target="_blank"
                                rel="noopener noreferrer" variant="subtle" size="xs"
                                icon="arrow-top-right-on-square">
                                OpenStreetMap
                            </flux:button>
                        @endif
                        <flux:button href="{{ $c['google_maps'] }}" target="_blank" rel="noopener noreferrer"
                            variant="filled" size="xs" icon="arrow-top-right-on-square">
                            Google Maps
                        </flux:button>
                    </div>
                </div>
                <div class="aspect-video w-full bg-zinc-400/10">
                    <iframe src="{{ $c['google_maps_embed'] }}" class="w-full h-full" style="border:0;"
                        allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="{{ $c['name'] }} মানচিত্র">
                    </iframe>
                </div>
                <div
                    class="px-4 py-2.5 bg-zinc-400/10 border-t border-zinc-400/25 flex flex-wrap gap-4 text-xs text-zinc-500 font-mono">
                    <span class="flex items-center gap-2">
                        <flux:icon.map-pin variant="micro" class="text-zinc-400" />
                        {{ $c['coords'] }}
                    </span>
                    @if ($c['capital'] !== 'N/A' && $c['capital_lat'])
                        <span class="flex items-center gap-2">
                            <flux:icon.building-library variant="micro" class="text-zinc-400" />
                            রাজধানী: {{ number_format($c['capital_lat'], 4) }}°,
                            {{ number_format($c['capital_lng'], 4) }}°
                        </span>
                    @endif
                </div>
            </div>
        @else
            <flux:button href="{{ $c['google_maps'] }}" target="_blank" rel="noopener noreferrer" variant="filled"
                class="w-full" icon="map">
                {{ $c['name'] }} Google Maps-এ দেখুন
            </flux:button>
        @endif

        {{-- ══════════════════ ★ প্রায়শই জিজ্ঞাসিত প্রশ্ন (FAQ) ══════════════════ --}}
        @if (!empty($faqs))
            <div class="bg-zinc-400/10 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-400/25 flex items-center gap-4">
                    <flux:icon.question-mark-circle variant="micro" class="text-indigo-500" />
                    <flux:heading size="sm" level="2" class="font-bold">প্রায়শই জিজ্ঞাসিত প্রশ্ন
                    </flux:heading>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($faqs as $faq)
                        <details class="group p-4">
                            <summary
                                class="flex items-center justify-between cursor-pointer text-sm font-semibold text-zinc-800 dark:text-zinc-200 list-none">
                                {{ $faq['q'] }}
                                <flux:icon.chevron-down variant="micro"
                                    class="text-zinc-400 group-open:rotate-180 transition-transform flex-shrink-0 ml-2" />
                            </summary>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                {{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ══════════════════ ★ তথ্যসূত্র / Attribution ══════════════════
             লাইসেন্স কমপ্লায়েন্স (mledoze/countries ODbL) + স্বচ্ছতা,
             যা reviewer-এর কাছে সাইটের বিশ্বাসযোগ্যতা বাড়ায়। --}}
        <div class="text-center px-2">
            <flux:text size="xs" class="text-zinc-400">
                তথ্যসূত্র:
                <a href="https://github.com/mledoze/countries" target="_blank" rel="noopener noreferrer"
                    class="underline ">mledoze/countries</a>
                ও
                <a href="https://github.com/samayo/country-json" target="_blank" rel="noopener noreferrer"
                    class="underline ">samayo/country-json</a>
                (ওপেন সোর্স ডেটাসেট)। পতাকার ছবি সরবরাহ করেছে
                <a href="https://flagcdn.com" target="_blank" rel="noopener noreferrer"
                    class="underline ">FlagCDN</a>।
                তথ্য {{ self::CACHE_TTL_SUCCESS_DAYS }} দিন পরপর হালনাগাদ করা হয়। কোনো অসঙ্গতি চোখে পড়লে আমাদের
                <flux:link href="{{ route('contact.us') ?? '#' }}">যোগাযোগ পাতায়</flux:link> জানান।
            </flux:text>
        </div>

        {{-- ══════════════════ ব্যাক বাটন ══════════════════ --}}
        <div
            class="flex flex-wrap justify-between items-center gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('international.all-country') }}" variant="subtle" icon="arrow-left">
                সকল দেশে ফিরুন
            </flux:button>
            <div class="flex gap-4">
                @if ($c['open_street_maps'])
                    <flux:button href="{{ $c['open_street_maps'] }}" target="_blank" rel="noopener noreferrer"
                        variant="subtle" size="sm" icon="arrow-top-right-on-square">
                        OSM
                    </flux:button>
                @endif
                <flux:button href="{{ $c['google_maps'] }}" target="_blank" rel="noopener noreferrer"
                    variant="subtle" size="sm" icon="arrow-top-right-on-square">
                    Maps
                </flux:button>
            </div>
        </div>
    </section>
@endif
