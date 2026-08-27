<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║        WikipediaGroundingService — 100% Free Fact-Grounding Layer        ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 *
 * উদ্দেশ্য: AI কে "নিজের মাথা থেকে" বানানোর বদলে একটা reference summary
 * দিয়ে দাও, যেন hallucination কমে এবং factual accuracy বাড়ে।
 *
 * কোনো API key লাগে না — bn.wikipedia.org এর পাবলিক REST API ব্যবহার করে।
 * বাংলা পেজ না পেলে ইংরেজি Wikipedia তে fallback করে।
 *
 * ব্যবহার:
 *   $grounding->fetchSummaries(['কক্সবাজার সমুদ্র সৈকত', 'সুন্দরবন']);
 *   → ['কক্সবাজার সমুদ্র সৈকত' => 'সংক্ষিপ্ত বিবরণ...', ...]
 */
class WikipediaGroundingService
{
    private const CACHE_TTL = 86400 * 7; // ১ সপ্তাহ cache — একই topic বারবার fetch করবে না
    private const HTTP_TIMEOUT = 8;
    private const MAX_SUMMARY_CHARS = 600; // prompt token বাঁচাতে সংক্ষিপ্ত রাখা

    /**
     * একাধিক topic এর জন্য summary fetch করে। ব্যর্থ হলে সেই topic স্কিপ হয়ে যায়,
     * পুরো process থামে না (grounding সবসময় "best effort" — না পেলে AI নিজের
     * জ্ঞান দিয়েই লিখবে, কিন্তু পেলে অনেক বেশি নির্ভুল হবে)।
     *
     * @param string[] $topics
     * @return array<string,string> topic => summary text
     */
    public function fetchSummaries(array $topics, int $limitPerCall = 6): array
    {
        $results = [];
        $topics = array_slice($topics, 0, $limitPerCall);

        foreach ($topics as $topic) {
            $summary = $this->fetchSingleSummary($topic);
            if ($summary) {
                $results[$topic] = $summary;
            }
        }

        return $results;
    }

    public function fetchSingleSummary(string $topic): ?string
    {
        $cacheKey = 'wiki_grounding_' . md5(mb_strtolower(trim($topic)));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($topic) {
            // ── ধাপ ১: বাংলা Wikipedia সরাসরি চেষ্টা ─────────────────────────
            $summary = $this->tryDirectSummary('bn', $topic);
            if ($summary) {
                return $summary;
            }

            // ── ধাপ ২: বাংলা Wikipedia তে search করে সঠিক title বের করা ───────
            $matchedTitle = $this->searchTitle('bn', $topic);
            if ($matchedTitle) {
                $summary = $this->tryDirectSummary('bn', $matchedTitle);
                if ($summary) {
                    return $summary;
                }
            }

            // ── ধাপ ৩: ইংরেজি Wikipedia fallback (তথ্য না পাওয়ার চেয়ে ভালো) ──
            $enTitle = $this->searchTitle('en', $topic) ?? $topic;
            $summary = $this->tryDirectSummary('en', $enTitle);

            return $summary; // null হতে পারে — caller handle করবে
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    // Internal
    // ════════════════════════════════════════════════════════════════════════

    private function tryDirectSummary(string $lang, string $title): ?string
    {
        try {
            $url = "https://{$lang}.wikipedia.org/api/rest_v1/page/summary/" . rawurlencode($title);
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['User-Agent' => 'TotthoboxContentAgent/1.0'])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $extract = $response->json('extract');
            if (!$extract || $response->json('type') === 'disambiguation') {
                return null;
            }

            return mb_substr(trim($extract), 0, self::MAX_SUMMARY_CHARS);

        } catch (\Throwable $e) {
            Log::debug("WikipediaGrounding: direct fetch failed [{$lang}] {$title}: {$e->getMessage()}");
            return null;
        }
    }

    private function searchTitle(string $lang, string $query): ?string
    {
        try {
            $url = "https://{$lang}.wikipedia.org/w/api.php";
            $response = Http::timeout(self::HTTP_TIMEOUT)->get($url, [
                'action' => 'opensearch',
                'search' => $query,
                'limit' => 1,
                'namespace' => 0,
                'format' => 'json',
            ]);

            if (!$response->successful()) {
                return null;
            }

            // opensearch format: [query, [titles], [descriptions], [urls]]
            $titles = $response->json('1') ?? [];
            return $titles[0] ?? null;

        } catch (\Throwable $e) {
            Log::debug("WikipediaGrounding: search failed [{$lang}] {$query}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Fetched summaries গুলোকে একটা single prompt-ready ব্লক এ format করে।
     * এটা ContentGenerationService এর instruction এ সরাসরি জুড়ে দেওয়া যায় —
     * কোনো service সিগনেচার পরিবর্তন করা লাগে না।
     */
    public function formatAsPromptContext(array $summaries): string
    {
        if (empty($summaries)) {
            return '';
        }

        $lines = ["\n\n── REFERENCE FACTS (Wikipedia থেকে, সঠিকতা নিশ্চিত করতে এগুলোর ভিত্তিতে লেখো) ──"];

        foreach ($summaries as $topic => $summary) {
            $lines[] = "▸ {$topic}: {$summary}";
        }

        $lines[] = "── এই তথ্যগুলো বাংলায় নিজের ভাষায় rewrite/expand করো, verbatim কপি না করে। যে topic এর জন্য reference নেই, সেটা তোমার সাধারণ জ্ঞান দিয়ে লেখো কিন্তু স্পষ্টভাবে যাচাইযোগ্য তথ্য দাও। ──";

        return implode("\n", $lines);
    }
}