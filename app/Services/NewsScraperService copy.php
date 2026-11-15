<?php

namespace App\Services;

use App\Models\NewsHeading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class NewsScraperService
{
    protected int $maxRetries = 3;

    protected int $retryDelay = 2;

    /**
     * URLs already persisted in this scrape run.
     */
    protected array $seenUrls = [];

    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64; rv:125.0) Gecko/20100101 Firefox/125.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/124.0.0.0 Safari/537.36',
    ];

    private const JUNK_URL_FRAGMENTS = [
        '/page/',
        '/tag/',
        '/author/',
        '/search/',
        '/login',
        '/register',
        '/subscribe',
        '/contact',
        '/about',
        '/privacy',
        '/terms',
        'javascript:',
        'mailto:',
        '#',
    ];

    protected array $sources = [

        // ── 1. Prothom Alo (বাংলা) ───────────────────────────────────────────
        [
            'key' => 'prothom_alo',
            'name' => 'Prothom Alo',
            'language' => 'bn',
            'rss' => [
                'https://www.prothomalo.com/feed',
                'https://www.prothomalo.com/stories.rss',
            ],
            'channels' => [
                'https://www.prothomalo.com/bangladesh',
                'https://www.prothomalo.com/politics',
                'https://www.prothomalo.com/economy',
                'https://www.prothomalo.com/international',
            ],
            'selectors' => [
                ['item' => 'article', 'title' => 'h2,h3', 'link' => 'a', 'image' => 'img,picture source'],
                ['item' => '.story-card', 'title' => '.title', 'link' => 'a', 'image' => 'img'],
                ['item' => '.custom-card', 'title' => 'h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '[class*="card"]', 'title' => 'h2,h3,h4', 'link' => 'a', 'image' => 'img'],
            ],
            'category_map' => [
                'bangladesh' => 'National',
                'politics' => 'Politics',
                'economy' => 'Economy',
                'international' => 'International',
            ],
        ],

        // ── 2. The Daily Star (English) ───────────────────────────────────────
        [
            'key' => 'daily_star',
            'name' => 'The Daily Star',
            'language' => 'en',
            'rss' => [
                'https://www.thedailystar.net/rss.xml',
            ],
            'channels' => [
                'https://www.thedailystar.net/top-news',
                'https://www.thedailystar.net/news/bangladesh',
                'https://www.thedailystar.net/business',
                'https://www.thedailystar.net/sports',
            ],
            'selectors' => [
                ['item' => '.card-content', 'title' => 'h3,h4', 'link' => 'a', 'image' => 'img'],
                ['item' => 'article', 'title' => 'h2,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '.views-row', 'title' => '.field-content', 'link' => 'a', 'image' => 'img'],
                ['item' => '[class*="card"]', 'title' => 'h2,h3,h4', 'link' => 'a', 'image' => 'img'],
            ],
            'category_map' => [
                'top-news' => 'Top News',
                'bangladesh' => 'National',
                'business' => 'Business',
                'sports' => 'Sports',
            ],
        ],

        // ── 3. bdnews24 (English) ───────────────────────────────────────────────
        [
            'key' => 'bdnews24',
            'name' => 'bdnews24',
            'language' => 'en',
            'rss' => [
                'https://bdnews24.com/stories.rss',
            ],
            'channels' => [
                'https://bdnews24.com/bangladesh',
                'https://bdnews24.com/world',
                'https://bdnews24.com/economy',
                'https://bdnews24.com/sports',
            ],
            'selectors' => [
                ['item' => '.news-item', 'title' => 'h3,h2', 'link' => 'a', 'image' => 'img'],
                ['item' => 'article', 'title' => 'h2,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '.container-items', 'title' => 'h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '[class*="article"]', 'title' => 'h2,h3,h4', 'link' => 'a', 'image' => 'img'],
            ],
            'category_map' => [
                'bangladesh' => 'National',
                'world' => 'International',
                'economy' => 'Economy',
                'sports' => 'Sports',
            ],
        ],

        // ── 4. Kaler Kantho (বাংলা) ─────────────────────────────────────────────
        [
            'key' => 'kalerkantho',
            'name' => 'Kaler Kantho',
            'language' => 'bn',
            'rss' => [],
            'channels' => [
                'https://www.kalerkantho.com/online/national',
                'https://www.kalerkantho.com/special/recent',
                'https://www.kalerkantho.com/online/world',
                'https://www.kalerkantho.com/online/sport',
                'https://www.kalerkantho.com/online/entertainment',
                'https://www.kalerkantho.com/online/business',
            ],
            'selectors' => [
                [
                    'item' => '.col-sm-6.col-md-4, .col-lg-3, .col-xs-12.col-sm-6',
                    'title' => 'h2, h3, h4, h5, .title',
                    'link' => 'a',
                    'image' => 'img',
                ],
                [
                    'item' => 'article, .box, .news-item',
                    'title' => 'h2, h3, a',
                    'link' => 'a',
                    'image' => 'img',
                ],
            ],
            'category_map' => [
                'national' => 'National',
                'recent' => 'Latest',
                'world' => 'International',
                'sport' => 'Sports',
                'entertainment' => 'Entertainment',
                'business' => 'Business',
            ],
        ],

        // ── 5. Samakal (বাংলা) ────────────────────────────────────────────────
        [
            'key' => 'samakal',
            'name' => 'Samakal',
            'language' => 'bn',
            'rss' => [],
            'channels' => [
                'https://samakal.com/whole-country',
                'https://samakal.com/politics',
                'https://samakal.com/economics',
                'https://samakal.com/sports',
            ],
            'selectors' => [
                ['item' => '.news-title-list', 'title' => 'h2,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => 'article', 'title' => 'h2,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '.card', 'title' => 'h3,h4', 'link' => 'a', 'image' => 'img'],
                ['item' => '[class*="news"]', 'title' => 'h2,h3,h4', 'link' => 'a', 'image' => 'img'],
            ],
            'category_map' => [
                'whole-country' => 'National',
                'politics' => 'Politics',
                'economics' => 'Economy',
                'sports' => 'Sports',
            ],
        ],

        // ── 6. Jugantor (বাংলা) ───────────────────────────────────────────────
        [
            'key' => 'jugantor',
            'name' => 'Jugantor',
            'language' => 'bn',
            'rss' => [],
            'channels' => [
                'https://www.jugantor.com/national',
                'https://www.jugantor.com/politics',
                'https://www.jugantor.com/economics',
                'https://www.jugantor.com/country',
            ],
            'selectors' => [
                [
                    'item' => '.jeg_post, .jeg_block_module_3, .jeg_block_module_6',
                    'title' => '.jeg_post_title, h3',
                    'link' => 'a',
                    'image' => 'img.wp-post-image, img[data-src], img[src]',
                ],
                [
                    'item' => '.post-item, .news-item-inner',
                    'title' => 'h2, h3, .post-title',
                    'link' => 'a',
                    'image' => 'img',
                ],
                [
                    'item' => '.news-list-item',
                    'title' => '.title, h3',
                    'link' => 'a',
                    'image' => 'img',
                ],
                [
                    'item' => 'article, [class*="news"]',
                    'title' => 'h2, h3, h4',
                    'link' => 'a',
                    'image' => 'img',
                ],
            ],
            'category_map' => [
                'all-news' => 'Latest',
                'national' => 'National',
                'politics' => 'Politics',
                'economics' => 'Economy',
                'country' => 'Country',
            ],
        ],

        // ── 7. Daily Ittefaq (বাংলা) ──────────────────────────────────────────
        [
            'key' => 'ittefaq',
            'name' => 'Daily Ittefaq',
            'language' => 'bn',
            'rss' => [],
            'channels' => [
                'https://www.ittefaq.com.bd/latest-news',
            ],
            'selectors' => [
                [
                    'item' => '.each_news, .d_news_list, .post_list, article',
                    'title' => 'h2, h3, .title',
                    'link' => 'a',
                    'image' => 'img',
                ],
                [
                    'item' => '.news_item',
                    'title' => 'h3, h4',
                    'link' => 'a',
                    'image' => 'img',
                ],
            ],
            'category_map' => [
                'latest-news' => 'Latest',
                'national' => 'National',
                'politics' => 'Politics',
                'economy' => 'Economy',
            ],
        ],

        // ── 8. Manabzamin (বাংলা) ─────────────────────────────────────────────
        [
            'key' => 'manabzamin',
            'name' => 'Manabzamin',
            'language' => 'bn',
            'rss' => [],
            'channels' => [
                'https://www.mzamin.com/category/বিশ্বজমিন',
                'https://www.mzamin.com/category/অর্থ-বাণিজ্য',
                'https://www.mzamin.com/category/বাংলারজমিন',
                'https://www.mzamin.com/category/খেলা',
            ],
            'selectors' => [
                ['item' => 'article, .news-item, [class*="card"]', 'title' => 'h2,h3,h4', 'link' => 'a', 'image' => 'img'],
                ['item' => 'li', 'title' => 'h3,a', 'link' => 'a', 'image' => 'img'],
            ],
            'category_map' => [
                'বিশ্বজমিন' => 'International',
                'অর্থ-বাণিজ্য' => 'Economy',
                'বাংলারজমিন' => 'National',
                'খেলা' => 'Sports',
            ],
        ],

        // ── 9. The Financial Express (English) ────────────────────────────────
        [
            'key' => 'financial_express',
            'name' => 'The Financial Express',
            'language' => 'en',
            'rss' => [],
            'channels' => [
                'https://thefinancialexpress.com.bd/category/latest',
                'https://thefinancialexpress.com.bd/category/economy',
                'https://thefinancialexpress.com.bd/category/trade',
                'https://thefinancialexpress.com.bd/category/national',
            ],
            'selectors' => [
                ['item' => '.single-news', 'title' => 'h4,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => 'article', 'title' => 'h2,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '.news-item', 'title' => 'h3,h4', 'link' => 'a', 'image' => 'img'],
                ['item' => '[class*="card"]', 'title' => 'h2,h3,h4', 'link' => 'a', 'image' => 'img'],
            ],
            'category_map' => [
                'latest' => 'Latest',
                'economy' => 'Economy',
                'trade' => 'Business',
                'national' => 'National',
            ],
        ],

        // ── 10. New Age (English) ──────────────────────────────────────────────
        [
            'key' => 'new_age',
            'name' => 'New Age',
            'language' => 'en',
            'rss' => [
                'https://www.newagebd.net/rss.xml',
            ],
            'channels' => [
                'https://www.newagebd.net/latest',
                'https://www.newagebd.net/national',
                'https://www.newagebd.net/business',
                'https://www.newagebd.net/sports',
            ],
            'selectors' => [
                ['item' => '.news-item', 'title' => 'h3,h2', 'link' => 'a', 'image' => 'img'],
                ['item' => 'article', 'title' => 'h2,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '.latest-news-item', 'title' => 'h4,h3', 'link' => 'a', 'image' => 'img'],
                ['item' => '[class*="news"]', 'title' => 'h2,h3,h4', 'link' => 'a', 'image' => 'img'],
            ],
            'category_map' => [
                'latest' => 'Latest',
                'national' => 'National',
                'business' => 'Business',
                'sports' => 'Sports',
            ],
        ],

        // ── 11. Somoy News (বাংলা) ─────────────────────────────────────────────
        [
            'key' => 'somoy_news',
            'name' => 'Somoy News',
            'language' => 'bn',
            'rss' => [],
            'channels' => [
                'https://www.somoynews.tv/pages/all-news',
                'https://www.somoynews.tv/category/bangladesh',
                'https://www.somoynews.tv/category/politics',
                'https://www.somoynews.tv/category/international',
                'https://www.somoynews.tv/category/economy',
            ],
            'selectors' => [
                [
                    'item' => '.col-md-4.col-sm-6, .card',
                    'title' => 'h5, h2',
                    'link' => 'a',
                    'image' => 'img',
                ],
                [
                    'item' => '.news-list-item',
                    'title' => 'h3, .title',
                    'link' => 'a',
                    'image' => 'img',
                ],
                [
                    'item' => '[class*="story-card"]',
                    'title' => 'h2, h3',
                    'link' => 'a',
                    'image' => 'img',
                ],
                [
                    'item' => 'article',
                    'title' => 'h1, h2, h3',
                    'link' => 'a',
                    'image' => 'img',
                ],
            ],
            'category_map' => [
                'all-news' => 'Latest',
                'bangladesh' => 'National',
                'politics' => 'Politics',
                'international' => 'International',
                'economy' => 'Economy',
            ],
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────────────────

    public function scrapeAll(): void
    {
        $this->seenUrls = [];

        foreach ($this->sources as $source) {
            $this->processSource($source);
        }

        // সব সোর্স শেষ হলে একবারই ক্যাশ রিফ্রেশ
        $this->refreshNewsCaches();
    }

    public function scrapeByKey(string $key): void
    {
        $this->seenUrls = [];

        $source = collect($this->sources)->firstWhere('key', $key);

        if (! $source) {
            Log::warning("[NewsScraperService] Unknown source key: {$key}");

            return;
        }

        $this->processSource($source);

        // সিঙ্গেল সোর্স স্ক্র্যাপের পরও ক্যাশ আপডেট
        $this->refreshNewsCaches();
    }

    /**
     * Refresh Redis caches used by Sidebar + News API.
     * Heavy COUNT runs only here — never on HTTP request path.
     */
    protected function refreshNewsCaches(): void
    {
        try {
            $counts = NewsHeading::query()
                ->selectRaw('source_key, COUNT(*) as total')
                ->groupBy('source_key')
                ->pluck('total', 'source_key')
                ->toArray();

            Cache::put('news_source_counts_v1', $counts, now()->addHours(6));

            Cache::forget('sidebar_news_sources_v5');
            Cache::forget('news_api_sources_v5');

            // News index page caches (Redis)
            try {
                $store = Cache::getStore();
                if (method_exists($store, 'getRedis')) {
                    $redis = $store->getRedis();
                    $keys = $redis->keys('*news_index_v1:*');
                    if (! empty($keys)) {
                        $redis->del($keys);
                    }
                }
            } catch (\Throwable $e) {
                // ignore — 30 min TTL will expire old index cache
            }

            Log::info('[NewsScraperService] News caches refreshed', [
                'sources' => count($counts),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[NewsScraperService] Cache refresh failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CORE PIPELINE
    // ─────────────────────────────────────────────────────────────────────────

    protected function processSource(array $source): void
    {
        $persisted = 0;

        // Layer 1 — RSS
        foreach ($source['rss'] as $rssUrl) {
            $count = $this->scrapeRss($source, $rssUrl);
            $persisted += $count;
            if ($count > 0) {
                Log::info("[{$source['key']}] RSS OK via {$rssUrl}: {$count} items");
                break;
            }
        }

        // Layer 2 — HTML
        foreach ($source['channels'] as $channelUrl) {
            $persisted += $this->scrapeHtml($source, $channelUrl);
        }

        Log::info("[{$source['key']}] Total persisted this run: {$persisted}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RSS SCRAPER
    // ─────────────────────────────────────────────────────────────────────────

    protected function scrapeRss(array $source, string $url): int
    {
        $response = $this->fetchWithRetry($url);
        if (! $response) {
            return 0;
        }

        libxml_use_internal_errors(true);
        $rss = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);

        if (! $rss || ! isset($rss->channel->item)) {
            return 0;
        }

        $count = 0;

        foreach ($rss->channel->item as $item) {
            $title = $this->cleanText((string) $item->title);
            $link = trim((string) $item->link);

            if (empty($title) || empty($link)) {
                continue;
            }

            $persisted = $this->persistNews($source, [
                'title' => $title,
                'link' => $link,
                'image' => $this->extractRssImage($item),
                'date' => $this->parseDate((string) $item->pubDate),
                'category' => $this->detectCategory((string) ($item->category ?? ''), $source, $link),
            ]);

            if ($persisted) {
                $count++;
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTML SCRAPER
    // ─────────────────────────────────────────────────────────────────────────

    protected function scrapeHtml(array $source, string $url): int
    {
        $html = $this->fetchWithRetry($url);
        if (! $html) {
            return 0;
        }

        $category = $this->detectCategory('', $source, $url);
        $count = 0;

        foreach ($source['selectors'] as $selectorSet) {
            $found = $this->extractFromHtml($html, $source, $url, $category, $selectorSet);
            if ($found > 0) {
                $count += $found;
                break;
            }
        }

        if ($count === 0) {
            $count += $this->genericExtract($html, $source, $url, $category);
        }

        return $count;
    }

    protected function extractFromHtml(
        string $html,
        array $source,
        string $baseUrl,
        string $category,
        array $s,
    ): int {
        try {
            $crawler = new Crawler($html);
            $items = $crawler->filter($s['item']);

            if (! $items->count()) {
                return 0;
            }

            $count = 0;

            $items->slice(0, 25)->each(function (Crawler $node) use ($source, $baseUrl, $category, $s, &$count) {
                try {
                    $titleNode = null;
                    foreach (explode(',', $s['title']) as $sel) {
                        $sel = trim($sel);
                        if ($node->filter($sel)->count()) {
                            $titleNode = $node->filter($sel)->first();
                            break;
                        }
                    }
                    if (! $titleNode) {
                        return;
                    }

                    $title = $this->cleanText($titleNode->text());
                    if (empty($title) || mb_strlen($title) < 10) {
                        return;
                    }

                    $link = null;
                    try {
                        $link = $node->filter('a')->first()->attr('href');
                    } catch (\Throwable) {
                    }

                    if (! $link) {
                        return;
                    }

                    $link = $this->makeAbsolute($link, $baseUrl);
                    if (! filter_var($link, FILTER_VALIDATE_URL)) {
                        return;
                    }

                    $image = null;
                    foreach (explode(',', $s['image']) as $imgSel) {
                        $imgSel = trim($imgSel);
                        $imgNode = $node->filter($imgSel);
                        if (! $imgNode->count()) {
                            continue;
                        }

                        $image = $imgNode->attr('data-main-img')
                            ?? $imgNode->attr('data-src')
                            ?? $imgNode->attr('data-original')
                            ?? $imgNode->attr('data-lazy-src')
                            ?? $imgNode->attr('data-lazy')
                            ?? $imgNode->attr('srcset')
                            ?? $imgNode->attr('src');

                        if ($image && str_contains($image, ' ')) {
                            $image = explode(' ', trim($image))[0];
                        }

                        if ($image) {
                            $image = $this->makeAbsolute($image, $baseUrl);
                            if ($this->isPlaceholderImage($image)) {
                                $image = null;
                                continue;
                            }
                            break;
                        }
                    }

                    if (
                        $this->persistNews($source, [
                            'title' => $title,
                            'link' => $link,
                            'image' => $image,
                            'date' => now(),
                            'category' => $category,
                        ])
                    ) {
                        $count++;
                    }
                } catch (\Throwable) {
                    // skip bad node
                }
            });

            return $count;
        } catch (\Throwable $e) {
            Log::warning("[{$source['key']}] extractFromHtml error: " . $e->getMessage());

            return 0;
        }
    }

    protected function genericExtract(
        string $html,
        array $source,
        string $baseUrl,
        string $category,
    ): int {
        try {
            $crawler = new Crawler($html);
            $count = 0;

            $crawler->filter('h1 a, h2 a, h3 a, h4 a, article a')
                ->each(function (Crawler $node) use ($source, $baseUrl, $category, &$count) {
                    $title = $this->cleanText($node->text());
                    $link = $this->makeAbsolute($node->attr('href'), $baseUrl);

                    if (mb_strlen($title) < 15 || ! filter_var($link, FILTER_VALIDATE_URL)) {
                        return;
                    }

                    if (
                        $this->persistNews($source, [
                            'title' => $title,
                            'link' => $link,
                            'image' => null,
                            'date' => now(),
                            'category' => $category,
                        ])
                    ) {
                        $count++;
                    }
                });

            if ($count > 0) {
                Log::info("[{$source['key']}] Generic extract: {$count} items from {$baseUrl}");
            }

            return $count;
        } catch (\Throwable $e) {
            Log::warning("[{$source['key']}] Generic extract failed: " . $e->getMessage());

            return 0;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PERSIST
    // ─────────────────────────────────────────────────────────────────────────

    protected function persistNews(array $source, array $data): bool
    {
        if (empty($data['title']) || empty($data['link'])) {
            return false;
        }

        $link = $this->normalizeUrl($data['link']);

        if (isset($this->seenUrls[$link])) {
            return false;
        }

        if ($this->isJunkUrl($link)) {
            return false;
        }

        $exists = NewsHeading::where('source_link', $link)->exists();
        if ($exists) {
            $this->seenUrls[$link] = true;

            return false;
        }

        $image = $data['image'] ?? null;

        $slug = Str::slug(Str::limit($data['title'], 100));
        if (empty($slug)) {
            $slug = 'news-' . md5($data['title']);
        }

        $baseSlug = $slug;
        $attempt = 0;
        while (
            NewsHeading::where('slug', $slug)
            ->where('source_link', '!=', $link)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . (++$attempt);
            if ($attempt > 20) {
                $slug = $baseSlug . '-' . substr(md5($link), 0, 8);
                break;
            }
        }

        $record = NewsHeading::firstOrCreate(
            ['source_link' => $link],
            [
                'title' => $data['title'],
                'slug' => $slug,
                'source_name' => $source['name'],
                'source_key' => $source['key'],
                'category' => $data['category'] ?? 'National',
                'image_url' => $image,
                'language' => $source['language'],
                'published_at' => $data['date'] ?? now(),
            ]
        );

        $this->seenUrls[$link] = true;

        return $record->wasRecentlyCreated;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP
    // ─────────────────────────────────────────────────────────────────────────


    protected function fetchWithRetry(string $url, int $timeout = 20): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            Log::warning('[NewsScraperService] Invalid URL', ['url' => $url]);
            return null;
        }

        // A domain returning 403 is temporarily skipped.
        $blockedKey = 'news_scraper:blocked_host:' . $host;

        if (Cache::has($blockedKey)) {
            Log::info('[NewsScraperService] Host cooldown active', [
                'host' => $host,
                'url' => $url,
            ]);

            return null;
        }

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::connectTimeout(8)
                    ->timeout($timeout)
                    ->withHeaders([
                        'User-Agent' => $this->userAgents[0],
                        'Accept' => 'application/rss+xml, application/atom+xml, '
                            . 'application/xml, text/html;q=0.9, */*;q=0.8',
                        'Accept-Language' => 'bn-BD,bn;q=0.9,en;q=0.7',
                    ])
                    ->get($url);

                $status = $response->status();

                if ($response->successful()) {
                    return $response->body();
                }

                if ($status === 403) {
                    Cache::put($blockedKey, true, now()->addMinutes(30));

                    Log::warning('[NewsScraperService] HTTP 403; host cooldown enabled', [
                        'host' => $host,
                        'url' => $url,
                    ]);

                    return null;
                }

                // These errors are not normally fixed by retrying.
                if (in_array($status, [400, 401, 404, 410], true)) {
                    Log::warning('[NewsScraperService] Non-retryable HTTP response', [
                        'status' => $status,
                        'url' => $url,
                    ]);

                    return null;
                }

                // Retry rate limits and temporary server failures.
                if ($status !== 429 && $status < 500) {
                    Log::warning('[NewsScraperService] HTTP request rejected', [
                        'status' => $status,
                        'url' => $url,
                    ]);

                    return null;
                }

                Log::warning('[NewsScraperService] Temporary HTTP failure', [
                    'status' => $status,
                    'attempt' => $attempt,
                    'url' => $url,
                ]);

                if ($attempt < $this->maxRetries) {
                    $retryAfter = (int) $response->header('Retry-After', 0);
                    $delay = $retryAfter > 0
                        ? min($retryAfter, 30)
                        : $this->retryDelay * $attempt;

                    sleep($delay);
                }
            } catch (\Throwable $e) {
                Log::warning('[NewsScraperService] HTTP request failed', [
                    'attempt' => $attempt,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay * $attempt);
                }
            }
        }

        Log::error('[NewsScraperService] Retries exhausted', ['url' => $url]);

        return null;
    }


    // ─────────────────────────────────────────────────────────────────────────
    // IMAGE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    protected function extractRssImage(\SimpleXMLElement $item): ?string
    {
        $media = $item->children('media', true);
        if (isset($media->content)) {
            $url = (string) ($media->content->attributes()['url'] ?? '');
            if ($url) {
                return $url;
            }
        }

        if (isset($media->thumbnail)) {
            $url = (string) ($media->thumbnail->attributes()['url'] ?? '');
            if ($url) {
                return $url;
            }
        }

        if (isset($item->enclosure)) {
            $attr = $item->enclosure->attributes();
            $type = (string) ($attr['type'] ?? '');
            if (str_starts_with($type, 'image/') || empty($type)) {
                $url = (string) ($attr['url'] ?? '');
                if ($url) {
                    return $url;
                }
            }
        }

        $desc = (string) $item->description;
        if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $desc, $m)) {
            return $m[1];
        }

        $content = $item->children('content', true);
        if (isset($content->encoded)) {
            $encoded = (string) $content->encoded;
            if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $encoded, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    protected function getMetaImage(string $url): ?string
    {
        try {
            $html = $this->fetchWithRetry($url, timeout: 10);
            if (! $html) {
                return null;
            }

            $head = substr($html, 0, 8000);
            $crawler = new Crawler($head);

            foreach (
                [
                    'meta[property="og:image"]',
                    'meta[name="og:image"]',
                    'meta[name="twitter:image"]',
                    'meta[property="twitter:image"]',
                    'meta[itemprop="image"]',
                    'link[rel="image_src"]',
                ] as $selector
            ) {
                try {
                    $node = $crawler->filter($selector);
                    if ($node->count()) {
                        $value = $node->attr('content') ?? $node->attr('href');
                        if ($value && ! $this->isPlaceholderImage($value)) {
                            return $this->makeAbsolute($value, $url);
                        }
                    }
                } catch (\Throwable) {
                }
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function isPlaceholderImage(?string $url): bool
    {
        if (! $url) {
            return true;
        }

        $lower = strtolower($url);

        return str_contains($lower, 'placeholder')
            || str_contains($lower, 'blank.gif')
            || str_contains($lower, 'spacer.gif')
            || str_contains($lower, 'pixel.gif')
            || str_contains($lower, '1x1')
            || str_ends_with($lower, '.svg')
            || strlen($url) < 15;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UTILITIES
    // ─────────────────────────────────────────────────────────────────────────

    protected function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (! $parsed) {
            return rtrim($url, '/');
        }

        $query = [];
        if (! empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $tracking = [
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'fbclid',
                'gclid',
                'ref',
                'referrer',
            ];
            foreach ($tracking as $key) {
                unset($params[$key]);
            }
            $query = $params;
        }

        $normalized = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        if (! empty($parsed['path'])) {
            $normalized .= rtrim($parsed['path'], '/');
        }

        if (! empty($query)) {
            $normalized .= '?' . http_build_query($query);
        }

        return $normalized;
    }

    protected function makeAbsolute(?string $url, string $baseUrl): ?string
    {
        if (! $url || trim($url) === '' || $url === '#') {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        $parsed = parse_url($baseUrl);
        $root = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        return $root . '/' . ltrim($url, '/');
    }

    protected function detectCategory(string $rssCat, array $source, string $url): string
    {
        if (! empty($rssCat)) {
            $lower = strtolower(trim($rssCat));
            foreach ($source['category_map'] as $key => $label) {
                if (str_contains($lower, strtolower($key))) {
                    return $label;
                }
            }
        }

        foreach ($source['category_map'] as $key => $label) {
            if (str_contains($url, $key)) {
                return $label;
            }
        }

        return 'National';
    }

    protected function cleanText(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

        return trim($text);
    }

    protected function isJunkUrl(string $url): bool
    {
        foreach (self::JUNK_URL_FRAGMENTS as $fragment) {
            if (str_contains($url, $fragment)) {
                return true;
            }
        }

        return false;
    }

    protected function parseDate(string $dateString): ?Carbon
    {
        if (empty(trim($dateString))) {
            return null;
        }

        try {
            return Carbon::parse($dateString);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function randomUserAgent(): string
    {
        return $this->userAgents[array_rand($this->userAgents)];
    }
}