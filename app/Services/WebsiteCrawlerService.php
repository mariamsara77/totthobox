<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class WebsiteCrawlerService
{
    /**
     * একটি পেজ ক্রল করে ক্লিন ডেটা রিটার্ন করে
     */
    public function crawl(string $url): array
    {
        $response = Http::timeout(25)
            ->withHeaders([
                'User-Agent' => 'TotthoBoxBot/1.0 (+https://totthobox.com)',
                'Accept'     => 'text/html,application/xhtml+xml',
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("HTTP {$response->status()} while fetching: {$url}");
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        // ---------- Title ----------
        $title = $crawler->filter('title')->count()
            ? $this->cleanText($crawler->filter('title')->text())
            : null;

        // ---------- Meta Description ----------
        $description = null;
        if ($crawler->filter('meta[name="description"]')->count()) {
            $description = $crawler->filter('meta[name="description"]')->attr('content');
        }

        // ---------- Main Content ----------
        // আপনার সাইটের structure অনুযায়ী selector গুলো adjust করতে পারেন
        $contentSelectors = [
            'article',
            'main',
            '.entry-content',
            '.post-content',
            '.content',
            '#content',
            '.page-content',
            'body',
        ];

        $content = null;
        foreach ($contentSelectors as $selector) {
            if ($crawler->filter($selector)->count()) {
                $content = $this->cleanText($crawler->filter($selector)->first()->text());
                break;
            }
        }

        // ---------- Images ----------
        $images = [];
        $crawler->filter('img')->each(function (Crawler $node) use (&$images, $url) {
            $src = $node->attr('src') ?: $node->attr('data-src') ?: $node->attr('data-lazy-src');

            if (!$src) {
                return;
            }

            $src = $this->makeAbsoluteUrl($src, $url);

            // অপ্রয়োজনীয় ইমেজ বাদ
            $skipKeywords = ['logo', 'icon', 'avatar', 'emoji', 'spinner', 'loading', 'placeholder'];
            foreach ($skipKeywords as $keyword) {
                if (Str::contains(Str::lower($src), $keyword)) {
                    return;
                }
            }

            $alt = $node->attr('alt') ?? '';

            $images[] = [
                'url' => $src,
                'alt' => $this->cleanText($alt),
            ];
        });

        // ডুপ্লিকেট ইমেজ সরা노
        $images = collect($images)
            ->unique('url')
            ->values()
            ->take(15) // বেশি ইমেজ রাখার দরকার নেই
            ->toArray();

        return [
            'title'   => $title,
            'content' => $content,
            'images'  => $images,
            'meta'    => [
                'description' => $description ? $this->cleanText($description) : null,
            ],
        ];
    }

    /**
     * টেক্সট ক্লিন করা
     */
    private function cleanText(?string $text): string
    {
        if (!$text) {
            return '';
        }

        // অতিরিক্ত স্পেস ও লাইন ব্রেক কমানো
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * Relative URL কে Absolute URL-এ রূপান্তর
     */
    private function makeAbsoluteUrl(string $src, string $baseUrl): string
    {
        // ইতিমধ্যে absolute হলে
        if (Str::startsWith($src, ['http://', 'https://', '//'])) {
            if (Str::startsWith($src, '//')) {
                return 'https:' . $src;
            }
            return $src;
        }

        $base = parse_url($baseUrl);
        $origin = ($base['scheme'] ?? 'https') . '://' . ($base['host'] ?? '');

        if (Str::startsWith($src, '/')) {
            return $origin . $src;
        }

        // relative path
        $path = isset($base['path']) ? dirname($base['path']) : '';
        return $origin . rtrim($path, '/') . '/' . ltrim($src, '/');
    }
}