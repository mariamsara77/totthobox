<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║          ContentQualityJudge — LLM-as-Judge Scoring Engine               ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 *
 * পুরনো scoreRecord() শুধু regex দিয়ে চেক করতো — বাংলা আছে কিনা, length কত।
 * এটা "ভালো কনটেন্ট" আর "লম্বা কিন্তু ফাঁপা কনটেন্ট" এর মধ্যে পার্থক্য করতে
 * পারতো না।
 *
 * এই সার্ভিস আসল AI কে critic হিসেবে ব্যবহার করে — একসাথে একাধিক record
 * (batch) পাঠিয়ে score + reason ফেরত নেয়, যাতে API call কম লাগে এবং
 * factual/informational quality আসলেই যাচাই হয়।
 *
 * সম্পূর্ণ ফ্রি — Groq/Gemini free tier ব্যবহার করে, কোনো নতুন provider লাগে না।
 * AI call ব্যর্থ হলে নিরাপদে heuristic fallback এ চলে যায় — কখনো hard-fail করে না।
 */
class ContentQualityJudge
{
    private const HTTP_TIMEOUT = 30;
    private const MAX_TOKENS = 2000;
    private const MAX_TEXT_PREVIEW = 500; // প্রতি field থেকে কতটুকু text prompt এ পাঠাবে
    private const BLACKLIST_KEY = 'judge_blacklist';
    private const BLACKLIST_TTL = 300;

    private array $providers = [
        'groq' => [
            'name' => 'Groq / Llama-3.3-70b',
            'url' => 'https://api.groq.com/openai/v1/chat/completions',
            'model' => 'llama-3.3-70b-versatile',
            'key_env' => 'GROQ_API_KEY',
            'adapter' => 'openai',
        ],
        'gemini' => [
            'name' => 'Gemini 2.0 Flash',
            'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
            'key_env' => 'GEMINI_API_KEY',
            'adapter' => 'gemini',
        ],
        'cerebras' => [
            'name' => 'Cerebras / Llama-3.1-8b',
            'url' => 'https://api.cerebras.ai/v1/chat/completions',
            'model' => 'llama3.1-8b',
            'key_env' => 'CEREBRAS_API_KEY',
            'adapter' => 'openai',
        ],
    ];

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Batch Scoring
    // ════════════════════════════════════════════════════════════════════════

    /**
     * একাধিক record একসাথে score করে — একটা মাত্র AI call এ।
     *
     * @param array $records প্রতিটি record এ অবশ্যই 'id' key থাকতে হবে
     * @param array $qualityFields যেসব field এর content বিচার করা হবে
     * @param string $topicContext কোন domain এর data সেটার সংক্ষিপ্ত বর্ণনা (judge কে context দেওয়ার জন্য)
     *
     * @return array<int, array{id:int, score:int, reason:string}>
     */
    public function scoreBatch(array $records, array $qualityFields, string $topicContext = ''): array
    {
        if (empty($records) || empty($qualityFields)) {
            return [];
        }

        if (!config('content_agent.judge_enabled', true)) {
            return $this->heuristicBatch($records, $qualityFields);
        }

        try {
            $result = $this->callJudgeAI($records, $qualityFields, $topicContext);
            if (!empty($result)) {
                return $this->normalizeJudgeResult($result, $records);
            }
        } catch (\Throwable $e) {
            Log::warning("ContentQualityJudge: AI scoring failed, falling back to heuristic: {$e->getMessage()}");
        }

        // ── Fallback: AI ব্যর্থ হলে পুরনো heuristic scoring ─────────────────────
        return $this->heuristicBatch($records, $qualityFields);
    }

    /**
     * একটা মাত্র record score করে (remake-এর পরে verify করার জন্য সুবিধাজনক)।
     */
    public function scoreOne(array $record, array $qualityFields, string $topicContext = ''): array
    {
        $record['id'] ??= 0;
        $results = $this->scoreBatch([$record], $qualityFields, $topicContext);
        return $results[0] ?? ['id' => $record['id'], 'score' => 0, 'reason' => 'Scoring failed'];
    }

    // ════════════════════════════════════════════════════════════════════════
    // AI Judge Call
    // ════════════════════════════════════════════════════════════════════════

    private function callJudgeAI(array $records, array $qualityFields, string $topicContext): ?array
    {
        $systemPrompt = $this->buildJudgeSystemPrompt($topicContext);
        $userPrompt = $this->buildJudgeUserPrompt($records, $qualityFields);

        foreach ($this->getAvailableProviders() as $key => $provider) {
            $apiKey = $this->resolveApiKey($provider);
            if (!$apiKey) {
                continue;
            }

            try {
                $raw = match ($provider['adapter']) {
                    'openai' => $this->callOpenAI($apiKey, $provider, $systemPrompt, $userPrompt),
                    'gemini' => $this->callGemini($apiKey, $systemPrompt, $userPrompt),
                    default => null,
                };

                if ($raw) {
                    $parsed = $this->parseJudgeJson($raw);
                    if (!empty($parsed)) {
                        return $parsed;
                    }
                }
            } catch (\Illuminate\Http\Client\RequestException $e) {
                $status = $e->response?->status() ?? 0;
                if (in_array($status, [429, 503], true)) {
                    $this->markFailure($key);
                }
                Log::debug("ContentQualityJudge: {$provider['name']} HTTP {$status}");
                continue;
            } catch (\Throwable $e) {
                Log::debug("ContentQualityJudge: {$provider['name']} failed: {$e->getMessage()}");
                continue;
            }
        }

        return null;
    }

    private function buildJudgeSystemPrompt(string $topicContext): string
    {
        return <<<PROMPT
তুমি একজন কঠোর বাংলা কনটেন্ট কোয়ালিটি বিচারক (content quality judge)। তোমার কাজ হলো
প্রতিটা record কে 0-100 স্কেলে score করা — শুধু ভাষা সঠিক কিনা তা না, বরং:

1. তথ্য (factual) কতটা সঠিক এবং যাচাইযোগ্য মনে হচ্ছে
2. content কতটা informative — জেনেরিক/ফাঁপা বাক্য নাকি নির্দিষ্ট, useful তথ্য
3. বাংলা ভাষার মান — grammar, স্বাভাবিকতা, বানান
4. দৈর্ঘ্য যথেষ্ট কিনা প্রসঙ্গ অনুযায়ী (শুধু লম্বা হলেই ভালো না, ফাঁপা লম্বা লেখা কম score পাবে)
5. duplicate/generic template-like বাক্য থাকলে score কমাও

Domain context: {$topicContext}

স্কোরিং গাইড:
- 90-100: চমৎকার, নির্দিষ্ট তথ্যবহুল, নির্ভুল বাংলা
- 70-89: ভালো, কিছু উন্নতির জায়গা আছে
- 50-69: মাঝারি — দুর্বল বা জেনেরিক
- 30-49: দুর্বল — ফাঁপা, ভুল তথ্যের ঝুঁকি, বা ভাষাগত সমস্যা
- 0-29: অগ্রহণযোগ্য — খালি, ভুল ভাষা, বা spam-like

শুধুমাত্র JSON ফেরত দাও, অন্য কিছু না:
{ "results": [ { "id": <int>, "score": <int 0-100>, "reason": "<এক লাইনে কারণ, বাংলায় বা ইংরেজিতে>" } ] }
PROMPT;
    }

    private function buildJudgeUserPrompt(array $records, array $qualityFields): string
    {
        $lines = ["নিচের records গুলো score করো:\n"];

        foreach ($records as $record) {
            $id = $record['id'] ?? 0;
            $lines[] = "── Record ID: {$id} ──";

            foreach ($qualityFields as $field) {
                $value = $record[$field] ?? '';
                $text = strip_tags((string) $value);
                $text = mb_substr($text, 0, self::MAX_TEXT_PREVIEW);
                $lines[] = "{$field}: {$text}";
            }

            $lines[] = '';
        }

        $lines[] = 'JSON format এ ফলাফল দাও: { "results": [ { "id": ..., "score": ..., "reason": "..." } ] }';

        return implode("\n", $lines);
    }

    // ════════════════════════════════════════════════════════════════════════
    // Provider Adapters (lightweight — judge-only, নিজস্ব blacklist)
    // ════════════════════════════════════════════════════════════════════════

    private function callOpenAI(string $apiKey, array $provider, string $system, string $user): ?string
    {
        $response = Http::timeout(self::HTTP_TIMEOUT)
            ->withToken($apiKey)
            ->post($provider['url'], [
                'model' => $provider['model'],
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'temperature' => 0.3, // judge এর জন্য কম randomness ভালো
                'max_tokens' => self::MAX_TOKENS,
                'response_format' => ['type' => 'json_object'],
            ])
            ->throw();

        return $response->json('choices.0.message.content');
    }

    private function callGemini(string $apiKey, string $system, string $user): ?string
    {
        $url = $this->providers['gemini']['url'] . '?key=' . $apiKey;

        $response = Http::timeout(self::HTTP_TIMEOUT)->post($url, [
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $user]]]],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => self::MAX_TOKENS,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->successful()) {
            $text = $response->json('candidates.0.content.parts.0.text');
            if ($text) {
                preg_match('/\{.*\}/s', $text, $matches);
                return $matches[0] ?? $text;
            }
        }

        return null;
    }

    private function getAvailableProviders(): array
    {
        $blacklist = Cache::get(self::BLACKLIST_KEY, []);
        return array_filter(
            $this->providers,
            fn($key) => !in_array($key, $blacklist, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function markFailure(string $key): void
    {
        $blacklist = Cache::get(self::BLACKLIST_KEY, []);
        if (!in_array($key, $blacklist, true)) {
            $blacklist[] = $key;
            Cache::put(self::BLACKLIST_KEY, $blacklist, self::BLACKLIST_TTL);
        }
    }

    private function resolveApiKey(array $provider): ?string
    {
        $value = env($provider['key_env']);
        if (!$value || str_contains((string) $value, 'your_') || str_contains((string) $value, 'sk-xx')) {
            return null;
        }
        return (string) $value;
    }

    // ════════════════════════════════════════════════════════════════════════
    // Parsing & Normalization
    // ════════════════════════════════════════════════════════════════════════

    private function parseJudgeJson(string $raw): ?array
    {
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            preg_match('/\{.*\}/s', $clean, $matches);
            $decoded = $matches ? json_decode($matches[0], true) : null;
        }

        return $decoded['results'] ?? null;
    }

    private function normalizeJudgeResult(array $judgeResults, array $originalRecords): array
    {
        $originalIds = array_column($originalRecords, 'id');
        $normalized = [];
        $seenIds = [];

        foreach ($judgeResults as $r) {
            $id = (int) ($r['id'] ?? 0);
            if (!in_array($id, $originalIds, true)) {
                continue; // AI hallucinate করে ভুল id দিলে বাদ
            }

            $score = (int) max(0, min(100, $r['score'] ?? 0));
            $normalized[] = [
                'id' => $id,
                'score' => $score,
                'reason' => (string) ($r['reason'] ?? ''),
            ];
            $seenIds[] = $id;
        }

        // AI যদি কোনো record বাদ দিয়ে যায়, সেগুলোর জন্য heuristic fallback দাও
        $missingRecords = array_filter($originalRecords, fn($rec) => !in_array($rec['id'], $seenIds, true));
        if (!empty($missingRecords)) {
            $qualityFields = array_keys(array_diff_key($missingRecords[array_key_first($missingRecords)] ?? [], ['id' => true]));
            $normalized = array_merge($normalized, $this->heuristicBatch(array_values($missingRecords), $qualityFields));
        }

        return $normalized;
    }

    // ════════════════════════════════════════════════════════════════════════
    // Heuristic Fallback (পুরনো regex-based logic — AI ব্যর্থ হলে ব্যবহার হয়)
    // ════════════════════════════════════════════════════════════════════════

    private function heuristicBatch(array $records, array $qualityFields): array
    {
        return array_map(function ($record) use ($qualityFields) {
            $score = $this->heuristicScoreOne($record, $qualityFields);
            return [
                'id' => (int) ($record['id'] ?? 0),
                'score' => $score,
                'reason' => 'Heuristic fallback scoring (AI judge unavailable)',
            ];
        }, $records);
    }

    private function heuristicScoreOne(array $record, array $qualityFields): int
    {
        if (empty($qualityFields)) {
            return 100;
        }

        $scores = [];

        foreach ($qualityFields as $field) {
            $value = $record[$field] ?? null;

            if (empty($value)) {
                $scores[] = 0;
                continue;
            }

            $text = strip_tags((string) $value);
            $length = mb_strlen($text);

            if ($length < 10) {
                $scores[] = 10;
                continue;
            }

            $hasBengali = preg_match('/[\x{0980}-\x{09FF}]/u', $text);

            if (!$hasBengali) {
                $scores[] = 20;
                continue;
            }

            if ($length < 30) {
                $scores[] = 40;
            } elseif ($length < 100) {
                $scores[] = 65;
            } elseif ($length < 300) {
                $scores[] = 80;
            } else {
                $scores[] = 95;
            }
        }

        return empty($scores) ? 100 : (int) round(array_sum($scores) / count($scores));
    }
}