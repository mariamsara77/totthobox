<?php

namespace App\Services;

use App\Models\WebsitePage;
use App\Search\GlobalSearchService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiService
{
    private const CACHE_FAILED_KEY   = 'ai_failed_providers';
    private const CACHE_SUCCESS_KEY  = 'ai_success_counts';
    private const CACHE_LATENCY_KEY  = 'ai_latency_ms';
    private const BLACKLIST_TTL_MIN  = 10;
    private const SUCCESS_TTL_HOURS  = 24;
    private const MAX_HISTORY_MSGS   = 10;
    private const MAX_CONTEXT_ITEMS  = 6;
    private const HTTP_TIMEOUT_SEC   = 45;
    private const MIN_RESPONSE_LEN   = 8;
    private const MAX_TOKENS         = 2048;
    private const MAX_PROMPT_CHARS   = 7000;
    private const GUEST_RATE_LIMIT   = 20;
    private const GUEST_RATE_WINDOW  = 3600;
    private const RESPONSE_CACHE_TTL = 180; // ৩ মিনিট (টেস্টিংয়ের জন্য কম রাখা হয়েছে)

    private array $providers = [
        'gemini' => [
            'name'            => 'Gemini',
            'url'             => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
            'model'           => 'gemini-2.0-flash',
            'fallback_models' => ['gemini-flash-latest', 'gemini-2.0-flash-lite'],
            'key_env'         => 'GEMINI_API_KEY',
            'weight'          => 100,
            'vision'          => true,
            'enabled'         => true,
        ],
        'groq' => [
            'name'            => 'Groq',
            'url'             => 'https://api.groq.com/openai/v1/chat/completions',
            'model'           => 'llama-3.3-70b-versatile',
            'fallback_models' => [
                'llama-3.1-8b-instant',
                'openai/gpt-oss-20b',
                'openai/gpt-oss-120b',
                'meta-llama/llama-4-scout-17b-16e-instruct',
            ],
            'key_env' => 'GROQ_API_KEY',
            'weight'  => 90,
            'vision'  => false,
            'enabled' => true,
        ],
        'mistral' => [
            'name'            => 'Mistral',
            'url'             => 'https://api.mistral.ai/v1/chat/completions',
            'model'           => 'mistral-small-latest',
            'fallback_models' => ['ministral-8b-latest', 'ministral-3b-latest'],
            'key_env'         => 'MISTRAL_API_KEY',
            'weight'          => 70,
            'vision'          => false,
            'enabled'         => true,
        ],
        'cerebras' => [
            'name'            => 'Cerebras',
            'url'             => 'https://api.cerebras.ai/v1/chat/completions',
            'model'           => 'gpt-oss-120b',
            'fallback_models' => ['qwen-3.8-27b'],
            'key_env'         => 'CEREBRAS_API_KEY',
            'weight'          => 50,
            'vision'          => false,
            'enabled'         => false,
        ],
    ];

    public function __construct(
        private readonly GlobalSearchService $searchService
    ) {}

    // =========================================================
    // Main Entry
    // =========================================================
    public function askAi(
        string $prompt,
        array $history = [],
        ?string $imageBase64 = null,
        ?string $imageMime = null,
        ?string $guestIp = null,
    ): string {
        if ($guestIp !== null) {
            $check = $this->checkGuestRateLimit($guestIp);
            if (! $check['allowed']) {
                return $this->getRateLimitResponse($check['retry_after']);
            }
            $this->incrementGuestUsage($guestIp);
        }

        $prompt = $this->sanitizePrompt($prompt);

        if ($prompt === '' && $imageBase64 === null) {
            return 'একটি প্রশ্ন বা ছবি পাঠান।';
        }

        // Cache
        $cacheKey = 'ai_resp_' . sha1($prompt . '|' . json_encode(array_slice($history, -4)) . '|' . ($imageBase64 ? 'img' : 'txt'));
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        Log::info('AiService:request', [
            'len'   => mb_strlen($prompt),
            'hist'  => count($history),
            'img'   => (bool) $imageBase64,
            'guest' => (bool) $guestIp,
        ]);

        // ========== Dynamic Knowledge Base from Database ==========
        $websiteContext = $prompt ? $this->buildWebsiteContext($prompt) : '';
        $finalPrompt = $websiteContext
            ? $websiteContext . "\n\n---\n\n**প্রশ্ন:** " . $prompt
            : $prompt;

        $ordered = $imageBase64
            ? $this->getVisionFirstProviders()
            : $this->getAvailableProviders();

        foreach ($ordered as $key => $provider) {
            $start    = microtime(true);
            $response = $this->callProvider($key, $provider, $finalPrompt, $history, $imageBase64, $imageMime);
            $latency  = (int) round((microtime(true) - $start) * 1000);

            if ($response && $this->isValidResponse($response)) {
                $this->markSuccess($key, $latency);
                Cache::put($cacheKey, $response, self::RESPONSE_CACHE_TTL);

                Log::info("AI OK from {$provider['name']}", [
                    'len' => mb_strlen($response),
                    'ms'  => $latency,
                ]);

                return $response;
            }

            $this->markFailure($key);
            Log::warning("{$provider['name']} failed — next");
        }

        Log::error('All AI providers exhausted');
        return $this->getFallbackResponse();
    }

    // =========================================================
    // Guest Rate Limiting
    // =========================================================
    public function checkGuestRateLimit(string $ip): array
    {
        $key     = 'ai_guest_' . sha1($ip);
        $ttlKey  = 'ai_guest_ttl_' . sha1($ip);
        $current = (int) Cache::get($key, 0);

        if ($current >= self::GUEST_RATE_LIMIT) {
            $expiresAt = (int) Cache::get($ttlKey, 0);
            return [
                'allowed'     => false,
                'retry_after' => max(0, $expiresAt - time()),
                'remaining'   => 0,
            ];
        }

        return [
            'allowed'     => true,
            'retry_after' => 0,
            'remaining'   => self::GUEST_RATE_LIMIT - $current - 1,
        ];
    }

    public function incrementGuestUsage(string $ip): void
    {
        $key    = 'ai_guest_' . sha1($ip);
        $ttlKey = 'ai_guest_ttl_' . sha1($ip);

        if (! Cache::has($key)) {
            Cache::put($key, 0, self::GUEST_RATE_WINDOW);
            Cache::put($ttlKey, time() + self::GUEST_RATE_WINDOW, self::GUEST_RATE_WINDOW);
        }
        Cache::increment($key);
    }

    public function getGuestUsage(string $ip): array
    {
        $used = (int) Cache::get('ai_guest_' . sha1($ip), 0);
        return [
            'used'      => $used,
            'limit'     => self::GUEST_RATE_LIMIT,
            'remaining' => max(0, self::GUEST_RATE_LIMIT - $used),
        ];
    }

    // =========================================================
    // Health Check
    // =========================================================
    public function healthCheck(): array
    {
        $results = [];

        foreach ($this->providers as $key => $provider) {
            if (! ($provider['enabled'] ?? true)) {
                $results[$key] = ['status' => '⏸️', 'message' => 'Disabled', 'latency_ms' => 0];
                continue;
            }

            $apiKey = $this->resolveApiKey($key, $provider);
            if (! $apiKey) {
                $results[$key] = ['status' => '❌', 'message' => 'API key missing', 'latency_ms' => 0];
                continue;
            }

            $start    = microtime(true);
            $response = $this->callProvider($key, $provider, 'Reply with exactly: Health check passed successfully.', []);
            $latency  = (int) round((microtime(true) - $start) * 1000);

            $results[$key] = $response && $this->isValidResponse($response)
                ? ['status' => '✅', 'message' => "OK ({$latency}ms)", 'latency_ms' => $latency, 'sample' => Str::limit($response, 80)]
                : ['status' => '⚠️', 'message' => 'Failed', 'latency_ms' => $latency];
        }

        return $results;
    }

    public function resetProviderStats(): void
    {
        Cache::forget(self::CACHE_FAILED_KEY);
        Cache::forget(self::CACHE_SUCCESS_KEY);
        Cache::forget(self::CACHE_LATENCY_KEY);
    }

    // =========================================================
    // Advanced Dynamic RAG from website_pages table
    // =========================================================
    private function buildWebsiteContext(string $prompt): string
    {
        try {
            $pages = $this->searchWebsitePages($prompt);

            if ($pages->isNotEmpty()) {
                return $this->formatPagesAsContext($pages);
            }

            // Fallback to old GlobalSearchService
            $result = $this->searchService->search($prompt, 12);
            if (! $result->isEmpty) {
                return $this->formatGlobalSearchAsContext($result);
            }

            return '';
        } catch (\Throwable $e) {
            Log::warning('RAG failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function searchWebsitePages(string $prompt)
    {
        $promptLower = mb_strtolower(trim($prompt));
        $keywords    = $this->extractKeywords($prompt);

        // সাইট সম্পর্কিত প্রশ্ন কিনা
        $isAboutSite = Str::contains($promptLower, [
            'totthobox',
            'তথ্যবক্স',
            'তথ্য বক্স',
            'tottho',
            'এই সাইট',
            'এই ওয়েবসাইট',
            'this site',
            'this website',
            'কী',
            'কি',
            'what is',
            'about',
            'kichu',
            'কিছু',
            'content',
            'কনটেন্ট',
            'link',
            'লিংক',
            'image',
            'ইমেজ',
            'ছবি',
            'দাও',
            'দেখাও',
            'send',
            'show',
            'tomar kache',
            'তোমার কাছে'
        ]);

        // ========== সাইট সম্পর্কিত প্রশ্ন হলে ==========
        if ($isAboutSite) {
            // সরাসরি গুরুত্বপূর্ণ পেজগুলো আনো
            $pages = WebsitePage::query()
                ->where(function ($q) {
                    $q->where('url', 'https://totthobox.com/')
                        ->orWhere('url', 'https://totthobox.com')
                        ->orWhere('url', 'like', 'https://totthobox.com/about%')
                        ->orWhere('url', 'like', 'https://totthobox.com/services%')
                        ->orWhere('url', 'like', 'https://totthobox.com/bangla%')
                        ->orWhere('url', 'like', 'https://totthobox.com/bangladesh%')
                        ->orWhere('url', 'like', 'https://totthobox.com/islam%')
                        ->orWhere('title', 'like', '%তথ্যবক্স%')
                        ->orWhere('title', 'like', '%Tottho%')
                        ->orWhere('content', 'like', '%তথ্যবক্স%')
                        ->orWhere('content', 'like', '%Tottho%');
                })
                ->orderByRaw("CASE 
                WHEN url = 'https://totthobox.com/' THEN 1
                WHEN url = 'https://totthobox.com' THEN 1
                WHEN url LIKE '%about%' THEN 2
                WHEN url LIKE '%services%' THEN 3
                ELSE 4 END")
                ->limit(self::MAX_CONTEXT_ITEMS)
                ->get();

            // যদি তবুও কম পায়, তাহলে সাম্প্রতিক পেজ যোগ করো
            if ($pages->count() < 3) {
                $extra = WebsitePage::orderByDesc('id')->limit(5)->get();
                $pages = $pages->merge($extra)->unique('id')->take(self::MAX_CONTEXT_ITEMS);
            }

            return $pages;
        }

        // ========== সাধারণ প্রশ্ন ==========
        $query = WebsitePage::query()->where(function ($q) use ($prompt, $keywords) {
            // Fulltext try
            try {
                $q->whereFullText(['title', 'content'], $prompt);
            } catch (\Throwable $e) {
                // ignore if fulltext not supported
            }

            foreach ($keywords as $word) {
                if (mb_strlen($word) >= 2) {
                    $q->orWhere('title', 'like', "%{$word}%")
                        ->orWhere('content', 'like', "%{$word}%");
                }
            }
        });

        $pages = $query->orderByDesc('last_crawled_at')
            ->limit(self::MAX_CONTEXT_ITEMS)
            ->get();

        // খুব কম পেজ পেলে সাম্প্রতিক কিছু পেজ দাও
        if ($pages->count() < 2) {
            $extra = WebsitePage::orderByDesc('id')->limit(4)->get();
            $pages = $pages->merge($extra)->unique('id')->take(self::MAX_CONTEXT_ITEMS);
        }

        return $pages;
    }

    private function formatPagesAsContext($pages): string
    {
        if ($pages->isEmpty()) {
            return '';
        }

        $lines = [
            '**[তথ্যবক্স ওয়েবসাইটে যাচাইকৃত তথ্য - Database থেকে]**',
            'নিচের তথ্যগুলো সরাসরি website_pages টেবিল থেকে নেওয়া।',
            'শুধুমাত্র এই URL এবং ইমেজ ব্যবহার করো। নিজে কোনো URL বানাবে না।',
            '',
        ];

        foreach ($pages as $index => $page) {
            $num     = $index + 1;
            $title   = trim($page->title ?? 'Untitled');
            $url     = $page->url;
            $content = Str::limit(strip_tags($page->content ?? ''), 1500);

            $lines[] = "### সোর্স {$num}";
            $lines[] = "টাইটেল: {$title}";
            $lines[] = "লিংক: {$url}";
            $lines[] = "কনটেন্ট: {$content}";

            if (! empty($page->images) && is_array($page->images)) {
                $lines[] = "ইমেজসমূহ (সরাসরি ব্যবহার করো):";
                foreach (array_slice($page->images, 0, 5) as $img) {
                    $imgUrl = $img['url'] ?? '';
                    $alt    = $img['alt'] ?? '';
                    if ($imgUrl) {
                        $lines[] = "- {$imgUrl}" . ($alt ? " | alt: {$alt}" : '');
                    }
                }
            }

            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = 'উত্তর দেওয়ার বাধ্যতামূলক নিয়ম:';
        $lines[] = '1. শুধু উপরের তথ্য ব্যবহার করো';
        $lines[] = '2. লিংক দিতে হলে পুরো URL দাও: https://totthobox.com/...';
        $lines[] = '3. ইমেজ থাকলে markdown দিয়ে দেখাও → ![বর্ণনা](ইমেজ_লিংক)';
        $lines[] = '4. তথ্য না থাকলে স্পষ্ট করে বলো যে ডাটাবেসে এই তথ্য নেই';

        return implode("\n", $lines);
    }

    private function formatGlobalSearchAsContext($result): string
    {
        $lines = [
            '**[তথ্যবক্স ওয়েবসাইটে প্রাসঙ্গিক তথ্য]**',
            'শুধুমাত্র নিচের URL ব্যবহার করো। নিজে URL বানাবে না।',
            '',
        ];

        $used = 0;
        foreach ($result->items as $item) {
            if ($used >= self::MAX_CONTEXT_ITEMS) break;

            $title = trim((string) ($item->_search_title ?? ''));
            $url   = trim((string) ($item->_search_url ?? ''));
            $sub   = trim((string) ($item->_search_subtitle ?? ''));

            if ($title === '' || $url === '' || $url === '#' || ! $this->isSafeSiteUrl($url)) {
                continue;
            }

            $lines[] = "- **[{$title}]({$url})**";
            if ($sub !== '') {
                $lines[] = "  " . Str::limit(strip_tags($sub), 120);
            }
            $used++;
        }

        if ($used === 0) return '';

        $lines[] = '';
        $lines[] = '> নিজে URL তৈরি করবে না।';

        return implode("\n", $lines);
    }

    private function extractKeywords(string $question): array
    {
        $stopWords = [
            'কি',
            'কী',
            'কে',
            'কোন',
            'কোনো',
            'এবং',
            'বা',
            'আর',
            'যে',
            'যা',
            'এই',
            'সেই',
            'আমার',
            'আমি',
            'তুমি',
            'আপনি',
            'করে',
            'করা',
            'হয়',
            'হবে',
            'থেকে',
            'জন্য',
            'আছে',
            'দাও',
            'দেখাও',
            'the',
            'is',
            'are',
            'what',
            'how',
            'when',
            'where',
            'which',
            'a',
            'an',
            'of',
            'in',
            'on',
            'to',
            'for',
            'show',
            'send',
            'give'
        ];

        $words = preg_split('/[\s\p{P}]+/u', Str::lower($question), -1, PREG_SPLIT_NO_EMPTY);

        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return mb_strlen($word) >= 2 && ! in_array($word, $stopWords, true);
        });

        return array_values(array_unique(array_slice($keywords, 0, 10)));
    }

    private function isSafeSiteUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) return false;
        $host = parse_url($url, PHP_URL_HOST);
        return in_array($host, ['totthobox.com', 'www.totthobox.com'], true);
    }

    // =========================================================
    // System Prompt
    // =========================================================
    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
তুমি **তথ্যবক্স এআই** — totthobox.com-এর অফিসিয়াল ডিজিটাল সহায়তাকারী।

## ব্যক্তিত্ব
- সরাসরি, স্পষ্ট এবং সহায়ক।
- অপ্রয়োজনীয় ভূমিকা বা বাহবা দিবে না।
- সরাসরি উত্তর দিয়ে শুরু করো।

## তথ্য ও লিংক নিয়ম (অত্যন্ত গুরুত্বপূর্ণ)
- Context-এ `[তথ্যবক্স ওয়েবসাইটে যাচাইকৃত তথ্য - Database থেকে]` থাকলে **শুধু সেই তথ্য ও URL** ব্যবহার করো।
- লিংক ফরম্যাট: `[নাম](https://totthobox.com/...)`
- ইমেজ থাকলে অবশ্যই দেখাও: `![বর্ণনা](ইমেজ_লিংক)`
- নিজে কোনো URL বানাবে না, অনুমান করবে না।
- Context-এ তথ্য না থাকলে স্পষ্ট করে বলো: "এই বিষয়ে আমার ডাটাবেসে এখনো তথ্য নেই।"

## ভাষা
- বাংলা প্রশ্ন → বাংলা উত্তর
- ইংরেজি প্রশ্ন → ইংরেজি উত্তর

## পরিচয়
শুধু "তুমি কে?" জিজ্ঞেস করলে উত্তর দাও:
"আমি তথ্যবক্স এআই — totthobox.com-এর ডিজিটাল সহায়তাকারী।"
PROMPT;
    }

    // =========================================================
    // Provider Calls
    // =========================================================
    private function callProvider(
        string $key,
        array $provider,
        string $prompt,
        array $history,
        ?string $imageBase64 = null,
        ?string $imageMime = null,
    ): ?string {
        if (! ($provider['enabled'] ?? true)) return null;

        $apiKey = $this->resolveApiKey($key, $provider);
        if (! $apiKey) {
            Log::warning("{$provider['name']}: API key missing");
            return null;
        }

        try {
            if ($key === 'gemini') {
                return $this->callGemini($apiKey, $provider, $prompt, $history, $imageBase64, $imageMime);
            }
            return $this->callOpenAICompatible($apiKey, $provider, $prompt, $history, $imageBase64, $imageMime);
        } catch (ConnectionException $e) {
            Log::error("{$provider['name']}: connection error", ['msg' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            Log::error("{$provider['name']}: exception", ['msg' => $e->getMessage()]);
            return null;
        }
    }

    private function callOpenAICompatible(
        string $apiKey,
        array $provider,
        string $prompt,
        array $history,
        ?string $imageBase64 = null,
        ?string $imageMime = null,
    ): ?string {
        $messages = [['role' => 'system', 'content' => $this->getSystemPrompt()]];

        foreach (array_slice($history, -self::MAX_HISTORY_MSGS) as $msg) {
            $role    = ($msg['role'] ?? '') === 'user' ? 'user' : 'assistant';
            $content = mb_substr((string) ($msg['content'] ?? ''), 0, 1800);
            $messages[] = ['role' => $role, 'content' => $content];
        }

        if ($imageBase64 && $imageMime && ($provider['vision'] ?? false)) {
            $messages[] = [
                'role' => 'user',
                'content' => [
                    ['type' => 'image_url', 'image_url' => ['url' => "data:{$imageMime};base64,{$imageBase64}"]],
                    ['type' => 'text', 'text' => $prompt ?: 'এই ছবিটি বর্ণনা করো।'],
                ],
            ];
        } else {
            $messages[] = ['role' => 'user', 'content' => $prompt];
        }

        $modelsToTry = array_values(array_filter(array_unique([
            $provider['model'] ?? null,
            ...($provider['fallback_models'] ?? []),
        ])));

        foreach ($modelsToTry as $model) {
            $response = Http::timeout(self::HTTP_TIMEOUT_SEC)
                ->withToken($apiKey)
                ->acceptJson()
                ->post($provider['url'], [
                    'model'       => $model,
                    'messages'    => $messages,
                    'temperature' => 0.55,
                    'max_tokens'  => self::MAX_TOKENS,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return $content ? trim($content) : null;
            }

            $status = $response->status();
            Log::warning("{$provider['name']}: HTTP {$status} (model: {$model})", [
                'body' => mb_substr($response->body(), 0, 300),
            ]);

            if (in_array($status, [401, 402, 403, 429], true)) return null;
            if (in_array($status, [400, 404], true)) continue;
            return null;
        }

        return null;
    }

    private function callGemini(
        string $apiKey,
        array $provider,
        string $prompt,
        array $history,
        ?string $imageBase64 = null,
        ?string $imageMime = null,
    ): ?string {
        $contents = [];

        foreach (array_slice($history, -self::MAX_HISTORY_MSGS) as $msg) {
            $role    = ($msg['role'] ?? '') === 'user' ? 'user' : 'model';
            $content = mb_substr((string) ($msg['content'] ?? ''), 0, 1800);
            $contents[] = ['role' => $role, 'parts' => [['text' => $content]]];
        }

        $parts = [];
        if ($imageBase64 && $imageMime) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $imageMime,
                    'data'      => $imageBase64,
                ],
            ];
        }
        $parts[] = ['text' => $prompt ?: 'এই ছবিটি বর্ণনা করো।'];
        $contents[] = ['role' => 'user', 'parts' => $parts];

        $models = array_values(array_filter(array_unique([
            $provider['model'] ?? 'gemini-2.0-flash',
            ...($provider['fallback_models'] ?? []),
        ])));

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(self::HTTP_TIMEOUT_SEC)
                ->acceptJson()
                ->post($url, [
                    'system_instruction' => [
                        'parts' => [['text' => $this->getSystemPrompt()]],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature'     => 0.55,
                        'maxOutputTokens' => self::MAX_TOKENS,
                    ],
                ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                return $content ? trim($content) : null;
            }

            $status = $response->status();
            Log::warning("Gemini: HTTP {$status} (model: {$model})", [
                'body' => mb_substr($response->body(), 0, 300),
            ]);

            if (in_array($status, [400, 403, 429], true)) return null;
            if ($status === 404) continue;
        }

        return null;
    }

    // =========================================================
    // Provider Ranking
    // =========================================================
    private function getAvailableProviders(): array
    {
        $enabled = array_filter($this->providers, fn($p) => $p['enabled'] ?? true);
        return $this->sortProviders($enabled);
    }

    private function getVisionFirstProviders(): array
    {
        $enabled = array_filter($this->providers, fn($p) => $p['enabled'] ?? true);
        $vision  = array_filter($enabled, fn($p) => $p['vision'] ?? false);
        $rest    = array_filter($enabled, fn($p) => ! ($p['vision'] ?? false));
        return $this->sortProviders($vision) + $this->sortProviders($rest);
    }

    private function sortProviders(array $providers): array
    {
        $blacklisted   = Cache::get(self::CACHE_FAILED_KEY, []);
        $successCounts = Cache::get(self::CACHE_SUCCESS_KEY, []);
        $latencies     = Cache::get(self::CACHE_LATENCY_KEY, []);

        uksort($providers, function (string $a, string $b) use ($blacklisted, $successCounts, $latencies) {
            $aBlack = in_array($a, $blacklisted, true);
            $bBlack = in_array($b, $blacklisted, true);
            if ($aBlack !== $bBlack) return $aBlack ? 1 : -1;

            $aSuccess = $successCounts[$a] ?? 0;
            $bSuccess = $successCounts[$b] ?? 0;
            if ($aSuccess !== $bSuccess) return $bSuccess <=> $aSuccess;

            $aLat = $latencies[$a] ?? 9999;
            $bLat = $latencies[$b] ?? 9999;
            if (abs($aLat - $bLat) > 150) return $aLat <=> $bLat;

            return ($this->providers[$b]['weight'] ?? 0) <=> ($this->providers[$a]['weight'] ?? 0);
        });

        return $providers;
    }

    private function markSuccess(string $key, int $latencyMs = 0): void
    {
        $success = Cache::get(self::CACHE_SUCCESS_KEY, []);
        $success[$key] = ($success[$key] ?? 0) + 1;
        Cache::put(self::CACHE_SUCCESS_KEY, $success, now()->addHours(self::SUCCESS_TTL_HOURS));

        if ($latencyMs > 0) {
            $lat = Cache::get(self::CACHE_LATENCY_KEY, []);
            $prev = $lat[$key] ?? $latencyMs;
            $lat[$key] = (int) round(($prev * 0.7) + ($latencyMs * 0.3));
            Cache::put(self::CACHE_LATENCY_KEY, $lat, now()->addHours(self::SUCCESS_TTL_HOURS));
        }

        $black = Cache::get(self::CACHE_FAILED_KEY, []);
        if (in_array($key, $black, true)) {
            Cache::put(self::CACHE_FAILED_KEY, array_values(array_diff($black, [$key])), now()->addMinutes(self::BLACKLIST_TTL_MIN));
        }
    }

    private function markFailure(string $key): void
    {
        $black = Cache::get(self::CACHE_FAILED_KEY, []);
        if (! in_array($key, $black, true)) {
            $black[] = $key;
            Cache::put(self::CACHE_FAILED_KEY, $black, now()->addMinutes(self::BLACKLIST_TTL_MIN));
        }
    }

    private function isValidResponse(string $response): bool
    {
        $trimmed = trim($response);
        if (mb_strlen($trimmed) < self::MIN_RESPONSE_LEN) return false;

        $signals = [
            'rate limit',
            'quota exceeded',
            'service unavailable',
            'internal server error',
            'too many requests',
            'please try again later',
            'model does not exist',
            'api key not valid',
            'payment required',
        ];

        $lower = mb_strtolower($trimmed);
        foreach ($signals as $s) {
            if (str_contains($lower, $s)) return false;
        }
        return true;
    }

    private function sanitizePrompt(string $prompt): string
    {
        $prompt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $prompt);
        return mb_substr(trim($prompt), 0, self::MAX_PROMPT_CHARS);
    }

    private function resolveApiKey(string $key, array $provider): ?string
    {
        $apiKey = config("services.ai.{$key}") ?: env($provider['key_env']);
        if (! $apiKey || str_contains((string) $apiKey, 'your_') || strlen(trim($apiKey)) < 10) {
            return null;
        }
        return trim((string) $apiKey);
    }

    private function getFallbackResponse(): string
    {
        return "এই মুহূর্তে সার্ভার থেকে সাড়া পাওয়া যাচ্ছে না। একটু পরে আবার চেষ্টা করুন।\n\nএর মধ্যে দেখতে পারেন: [totthobox.com](https://totthobox.com)";
    }

    private function getRateLimitResponse(int $retryAfter): string
    {
        $minutes = max(1, (int) ceil($retryAfter / 60));
        return "আপনার বিনামূল্যে ব্যবহারের সীমা শেষ হয়েছে। প্রায় **{$minutes} মিনিট** পরে আবার চেষ্টা করুন অথবা [লগইন করুন](https://totthobox.com/login)।";
    }
}