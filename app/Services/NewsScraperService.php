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
     * Prevents RSS + HTML scrape of the same URL being counted twice
     * and avoids redundant getMetaImage() HTTP calls.
     */
    protected array $seenUrls = [];

    protected int $metaImageLookups = 0;

    protected int $maxMetaImageLookupsPerRun = 60;

    protected int $maxResponseBytes = 4194304;

    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64; rv:125.0) Gecko/20100101 Firefox/125.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/124.0.0.0 Safari/537.36',
    ];

    /**
     * Junk URL fragments — "/category/" intentionally excluded because
     * several sources (Somoy, Prothom Alo, etc.) use it as a valid path.
     */
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

    // ─────────────────────────────────────────────────────────────────────────
    // SOURCE DEFINITIONS
    // ─────────────────────────────────────────────────────────────────────────

    protected array $sources = [

        // ── 1. Prothom Alo (বাংলা) ───────────────────────────────────────────
        // RSS verified working.
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
        // RSS verified working.
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
        // RSS verified working.
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
        // rss.xml confirmed 404 — RSS removed. Relies on channel scraping only.
        // NOTE: channel/selector accuracy not verified live; re-check periodically.
        [
            'key' => 'kalerkantho',
            'name' => 'Kaler Kantho',
            'language' => 'bn',
            'rss' => [], // no working feed found
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
        // /feed confirmed 404, /rss blocked by bot-detection (no evidence it works).
        // RSS removed. Channels verified valid against current site nav.
        [
            'key' => 'samakal',
            'name' => 'Samakal',
            'language' => 'bn',
            'rss' => [], // no confirmed working feed
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
        // ALL RSS candidates (rss.xml, feed, feed/rss, feed/rss.xml) confirmed
        // 404 / access denied. RSS fully removed — scraping only.
        [
            'key' => 'jugantor',
            'name' => 'Jugantor',
            'language' => 'bn',
            'rss' => [], // no working feed — site has dropped RSS entirely
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
        // /feed and /rss.xml both confirmed 404. RSS removed.
        [
            'key' => 'ittefaq',
            'name' => 'Daily Ittefaq',
            'language' => 'bn',
            'rss' => [], // no working feed found
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
        // /feed and /rss.xml both confirmed 404 — RSS removed.
        // Old `category.php?cat=X` URLs now redirect to `/category/<bangla-slug>`.
        // Updated to the real current URLs (confirmed live) instead of relying
        // on the redirect.
        [
            'key' => 'manabzamin',
            'name' => 'Manabzamin',
            'language' => 'bn',
            'rss' => [], // no working feed found
            'channels' => [
                'https://www.mzamin.com/category/বিশ্বজমিন',      // World (was cat=5)
                'https://www.mzamin.com/category/অর্থ-বাণিজ্য',    // Economy (was cat=1)
                'https://www.mzamin.com/category/বাংলারজমিন',      // National (was cat=2)
                'https://www.mzamin.com/category/খেলা',            // Sports (was cat=3)
            ],
            'selectors' => [
                // Site now renders as headline cards under h2/h4 with figure/img
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
        // /feed no longer serves RSS (returns homepage HTML — site rebuilt on
        // Next.js). RSS removed. Channels rewritten to match the current
        // /category/* structure; old 'trade-market' slug doesn't exist anymore.
        [
            'key' => 'financial_express',
            'name' => 'The Financial Express',
            'language' => 'en',
            'rss' => [], // site no longer serves RSS at /feed
            'channels' => [
                'https://thefinancialexpress.com.bd/category/latest',
                'https://thefinancialexpress.com.bd/category/economy',
                'https://thefinancialexpress.com.bd/category/trade', // was 'trade-market' — wrong slug
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
        // rss.xml could not be confirmed — request was blocked by bot-detection
        // (not a 404, so it may still work from a normal server IP). Left in
        // place but flagged; test directly from your production server.
        [
            'key' => 'new_age',
            'name' => 'New Age',
            'language' => 'en',
            'rss' => [
                'https://www.newagebd.net/rss.xml', // UNVERIFIED — bot-blocked during check, re-test from your server
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
        // /feed and /rss/national both confirmed 404. RSS removed.
        [
            'key' => 'somoy_news',
            'name' => 'Somoy News',
            'language' => 'bn',
            'rss' => [], // no working feed found
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
        $this->seenUrls = []; // Reset dedup map for this run
        $this->metaImageLookups = 0;

        foreach ($this->sources as $source) {
            $this->processSource($source);
        }
    }

    public function scrapeByKey(string $key): void
    {
        $this->seenUrls = [];
        $this->metaImageLookups = 0;

        $source = collect($this->sources)->firstWhere('key', $key);

        if (! $source) {
            Log::warning("[NewsScraperService] Unknown source key: {$key}");

            return;
        }

        $this->processSource($source);
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

        // ক্যাশ ক্লিয়ার করার লজিক এখানে
        if ($persisted > 0) {
            Cache::forget('news_sidebar_counts_v1');
            Cache::forget('news_sidebar_grouped_v4');
            Cache::forget('news_sidebar_sources_v1');
            Cache::forget('news_api_sources_v1');

            Log::info("[{$source['key']}] News sidebar caches cleared.");
        }
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
                break; // First working selector set wins
            }
        }

        // Generic <hN> link fallback
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
                    // ── Title ─────────────────────────────────────────────
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

                    // ── Link ──────────────────────────────────────────────
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

                    // ── Image (6-layer with lazy-load attrs) ──────────────
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

                        // Take first URL from srcset
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
                    // One bad node never kills the whole pass
                }
            });

            return $count;

        } catch (\Throwable $e) {
            Log::warning("[{$source['key']}] extractFromHtml error: ".$e->getMessage());

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
            Log::warning("[{$source['key']}] Generic extract failed: ".$e->getMessage());

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

        // Avoid duplicating the same article when RSS and HTML both find it.
        if (isset($this->seenUrls[$link])) {
            return false;
        }

        if ($this->isJunkUrl($link)) {
            return false;
        }

        $sourceHash = hash('sha256', $link);
        $image = $data['image'] ?? null;

        // RSS feeds often provide relative image URLs. Normalize them before
        // saving so the API does not mistake them for local storage paths.
        if (is_string($image) && trim($image) !== '') {
            $image = trim($image);
            $scheme = strtolower((string) parse_url($image, PHP_URL_SCHEME));

            if ($scheme !== '' && ! in_array($scheme, ['http', 'https'], true)) {
                $image = null;
            } else {
                $image = $this->makeAbsolute($image, $link);
                if ($this->isPlaceholderImage($image)) {
                    $image = null;
                }
            }
        } else {
            $image = null;
        }

        $existing = NewsHeading::query()
            ->where('source_hash', $sourceHash)
            ->first();

        if (! $existing) {
            $existing = NewsHeading::query()
                ->where('source_link', $link)
                ->first();
        }

        if ($existing) {
            // Backfill an image only when the existing row lacks a usable one.
            // Do not overwrite editorial metadata or the original publication time.
            $currentImage = trim((string) $existing->image_url);
            $missingImage = $currentImage === '' || $this->isPlaceholderImage($currentImage);
            $hasLocalImage = trim((string) $existing->local_image_path) !== '';

            if ($missingImage && ! $hasLocalImage) {
                // RSS may not expose a thumbnail. Try a bounded Open Graph lookup
                // for older records too, without replacing their publication date.
                if ($image === null && $this->metaImageLookups < $this->maxMetaImageLookupsPerRun) {
                    $image = $this->getMetaImage($link);
                    $this->metaImageLookups++;
                }

                if ($image !== null) {
                    $existing->forceFill(['image_url' => $image])->save();
                }
            }

            $this->seenUrls[$link] = true;

            return false;
        }

        // Open Graph fallback is bounded and only used for genuinely new stories.
        if ($image === null && $this->metaImageLookups < $this->maxMetaImageLookupsPerRun) {
            $image = $this->getMetaImage($link);
            $this->metaImageLookups++;
        }

        $slug = Str::slug(Str::limit($data['title'], 100));
        if (empty($slug)) {
            $slug = 'news-'.md5($data['title']);
        }

        $baseSlug = $slug;
        $attempt = 0;

        while (
            NewsHeading::where('slug', $slug)
                ->where('source_link', '!=', $link)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.(++$attempt);
            if ($attempt > 20) {
                $slug = $baseSlug.'-'.substr(md5($link), 0, 8);
                break;
            }
        }

        $record = NewsHeading::firstOrCreate(
            ['source_link' => $link],
            [
                'title' => $data['title'],
                'slug' => $slug,
                'source_hash' => $sourceHash,
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
    // HTTP — Retry with exponential backoff + rotating User-Agent
    // ─────────────────────────────────────────────────────────────────────────

    protected function fetchWithRetry(string $url, int $timeout = 20): ?string
    {
        $attempts = 0;

        while ($attempts < $this->maxRetries) {
            try {
                $response = Http::timeout($timeout)
                    ->withHeaders([
                        'User-Agent' => 'TotthoboxNewsAggregator/1.0 (+https://totthobox.com/news)',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'bn-BD,bn;q=0.9,en-US;q=0.8,en;q=0.7',
                    ])
                    ->get($url);

                if ($response->successful()) {
                    $contentLength = (int) ($response->header('Content-Length') ?? 0);
                    if ($contentLength > $this->maxResponseBytes || strlen($response->body()) > $this->maxResponseBytes) {
                        Log::warning("[NewsScraperService] Response too large — skip {$url}");
                        return null;
                    }

                    return $response->body();
                }

                // These status codes won't improve on retry
                if (in_array($response->status(), [403, 404, 429], true)) {
                    Log::warning("[NewsScraperService] HTTP {$response->status()} — skip {$url}");

                    return null;
                }

            } catch (\Throwable $e) {
                Log::warning("[NewsScraperService] Attempt {$attempts} failed for {$url}: ".$e->getMessage());
            }

            $attempts++;
            // Exponential backoff: 2s, 4s, 6s
            sleep($this->retryDelay * $attempts);
        }

        Log::error("[NewsScraperService] All retries exhausted for {$url}");

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMAGE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    protected function extractRssImage(\SimpleXMLElement $item): ?string
    {
        // 1. media:content
        $media = $item->children('media', true);
        if (isset($media->content)) {
            $url = (string) ($media->content->attributes()['url'] ?? '');
            if ($url) {
                return $url;
            }
        }

        // 2. media:thumbnail
        if (isset($media->thumbnail)) {
            $url = (string) ($media->thumbnail->attributes()['url'] ?? '');
            if ($url) {
                return $url;
            }
        }

        // 3. enclosure
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

        // 4. <img> inside <description>
        $desc = (string) $item->description;
        if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $desc, $m)) {
            return $m[1];
        }

        // 5. content:encoded
        $content = $item->children('content', true);
        if (isset($content->encoded)) {
            $encoded = (string) $content->encoded;
            if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $encoded, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /**
     * Fetch og:image / twitter:image from the article's <head> only.
     * Reads only the first 8 KB of the returned document head to keep parsing lightweight.
     * Used for new stories and as a bounded fallback for older rows missing images.
     */
    protected function getMetaImage(string $url): ?string
    {
        try {
            $html = $this->fetchWithRetry($url, timeout: 10);
            if (! $html) {
                return null;
            }

            // Only parse the first 8 KB — the <head> is always there
            $head = substr($html, 0, 8000);
            $crawler = new Crawler($head);

            foreach ([
                'meta[property="og:image"]',
                'meta[name="og:image"]',
                'meta[name="twitter:image"]',
                'meta[property="twitter:image"]',
                'meta[itemprop="image"]',
                'link[rel="image_src"]',
            ] as $selector) {
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

    /**
     * Strip UTM/tracking params and trailing slashes so that
     * "article?utm_source=rss" and "article" don't create two DB rows.
     */
    protected function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (! $parsed) {
            return rtrim($url, '/');
        }

        $query = [];
        if (! empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            // Strip common tracking parameters
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

        $normalized = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '');

        if (! empty($parsed['path'])) {
            $normalized .= rtrim($parsed['path'], '/');
        }

        if (! empty($query)) {
            $normalized .= '?'.http_build_query($query);
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
            return 'https:'.$url;
        }
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        $parsed = parse_url($baseUrl);
        $root = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '');

        if (str_starts_with($url, '?')) {
            return $root.($parsed['path'] ?? '/').$url;
        }

        if (str_starts_with($url, '/')) {
            return $root.$url;
        }

        $basePath = $parsed['path'] ?? '/';
        $directory = rtrim(str_replace('\\', '/', dirname($basePath)), '/');
        $combined = ($directory ? $directory.'/' : '/').ltrim($url, '/');

        $segments = [];
        foreach (explode('/', $combined) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        return $root.'/'.implode('/', $segments);
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
