<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║        AutonomousContentAgent v1 — Zero-Instruction AI Agent             ║
 * ║  Scans all models → detects gaps → generates → scores → auto-improves   ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 *
 * কোনো instruction দেওয়া লাগবে না।
 * Agent নিজেই:
 *   1. সব registered model স্ক্যান করে
 *   2. data quality check করে (Bengali text আছে কিনা, empty fields কতটা)
 *   3. AI-কে জিজ্ঞেস করে "কী generate করবো?"
 *   4. ContentGenerationService দিয়ে data তৈরি করে
 *   5. প্রতিটা record score করে (0-100)
 *   6. score কম হলে RemakeExisting দিয়ে ঠিক করে
 *
 * ব্যবহার:
 *   php artisan content:auto-improve
 *   অথবা scheduler দিয়ে প্রতিদিন রাত ২টায় চালানো যায়
 */
class AutonomousContentAgent
{
    // ── Constants ────────────────────────────────────────────────────────────

    /** প্রতি model-এ সর্বোচ্চ কতটা record generate করবে এক রানে */
    private const MAX_GENERATE_PER_MODEL = 15;

    /** quality score এর নিচে হলে remake করবে */
    private const QUALITY_THRESHOLD = 60;

    /** কতটা existing record score করবে একসাথে */
    private const SCORE_BATCH_SIZE = 20;

    /** agent activity log cache key */
    private const AGENT_LOG_KEY = 'autonomous_agent_last_run';

    // ── Model Registry ────────────────────────────────────────────────────────
    // এখানে তোমার সব model যোগ করো।
    // 'display_name' → AI বুঝবে এটা কী ধরনের data
    // 'topic'        → AI এই topic নিয়ে content বানাবে
    // 'min_rows'     → এর কম row থাকলে generate করবে
    // 'quality_fields' → এই fields গুলো Bengali কিনা check করবে

    private array $modelRegistry = [
        \App\Models\TourismBd::class => [
            'display_name' => 'Bangladesh Tourism Spots',
            'topic' => 'বাংলাদেশের বিখ্যাত পর্যটন স্থান, ইতিহাস, কীভাবে যাবেন, কী দেখবেন',
            'min_rows' => 20,
            'quality_fields' => ['name', 'description', 'title'],
        ],
        \App\Models\HistoryBd::class => [
            'display_name' => 'Bangladesh History',
            'topic' => 'বাংলাদেশের ঐতিহাসিক ঘটনা, মুক্তিযুদ্ধ, সংস্কৃতি ও ঐতিহ্য',
            'min_rows' => 20,
            'quality_fields' => ['name', 'description'],
        ],
        \App\Models\Person::class => [
            'display_name' => 'Notable Persons of Bangladesh',
            'topic' => 'বাংলাদেশের বিখ্যাত ব্যক্তিত্ব — কবি, রাজনীতিবিদ, বিজ্ঞানী, শিল্পী',
            'min_rows' => 15,
            'quality_fields' => ['name', 'bio'],
        ],
        \App\Models\BasicIslam::class => [
            'display_name' => 'Basic Islam Topics',
            'topic' => 'ইসলামের মৌলিক বিষয়, নামাজ, রোজা, হজ, যাকাত, আকিদা',
            'min_rows' => 20,
            'quality_fields' => ['name', 'description'],
        ],
        \App\Models\BasicHealth::class => [
            'display_name' => 'Basic Health Information',
            'topic' => 'স্বাস্থ্য বিষয়ক তথ্য, রোগ, চিকিৎসা, স্বাস্থ্যকর জীবনযাপন',
            'min_rows' => 15,
            'quality_fields' => ['name', 'description'],
        ],
        \App\Models\Food::class => [
            'display_name' => 'Bangladeshi Food',
            'topic' => 'বাংলাদেশের ঐতিহ্যবাহী খাবার, রেসিপি, পুষ্টিগুণ',
            'min_rows' => 20,
            'quality_fields' => ['name', 'description'],
        ],
        \App\Models\IntroBd::class => [
            'display_name' => 'Introduction to Bangladesh',
            'topic' => 'বাংলাদেশ পরিচিতি — ভূগোল, জনসংখ্যা, অর্থনীতি, সংস্কৃতি',
            'min_rows' => 10,
            'quality_fields' => ['name', 'description'],
        ],
        \App\Models\ExcelTutorial::class => [
            'display_name' => 'Excel Tutorials in Bengali',
            'topic' => 'Microsoft Excel টিউটোরিয়াল — সূত্র, ফাংশন, চার্ট, ডেটা বিশ্লেষণ বাংলায়',
            'min_rows' => 15,
            'quality_fields' => ['name', 'description'],
        ],
    ];

    public function __construct(
        private readonly ContentGenerationService $generator,
    ) {
    }

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Main Entry — Full Autonomous Run
    // ════════════════════════════════════════════════════════════════════════

    /**
     * সম্পূর্ণ autonomous run।
     * Console command বা scheduler থেকে call করো।
     *
     * @return array{
     *   models_processed: int,
     *   total_generated: int,
     *   total_remade: int,
     *   total_skipped: int,
     *   duration_seconds: float,
     *   report: array,
     * }
     */
    public function runFullCycle(callable $log = null): array
    {
        $startedAt = microtime(true);
        $log ??= fn($msg) => null;

        $log('🤖 AutonomousContentAgent starting full cycle...');

        $report = [];
        $totalGenerated = 0;
        $totalRemade = 0;
        $totalSkipped = 0;

        foreach ($this->modelRegistry as $modelClass => $config) {
            if (!class_exists($modelClass)) {
                $log("⚠ Model class not found: {$modelClass} — skipping");
                continue;
            }

            $log("\n📋 Processing: {$config['display_name']}");

            try {
                $result = $this->processModel($modelClass, $config, $log);

                $report[$modelClass] = $result;
                $totalGenerated += $result['generated'];
                $totalRemade += $result['remade'];
                $totalSkipped += $result['skipped'];

                $log("✅ {$config['display_name']}: +{$result['generated']} generated, {$result['remade']} remade");

            } catch (\Throwable $e) {
                $log("❌ {$config['display_name']}: FAILED — {$e->getMessage()}");
                Log::error("AutonomousContentAgent: model failed", [
                    'model' => $modelClass,
                    'error' => $e->getMessage(),
                ]);
                $report[$modelClass] = ['error' => $e->getMessage()];
            }

            // Provider rate limit এড়াতে একটু wait করো
            sleep(2);
        }

        $duration = round(microtime(true) - $startedAt, 2);

        // Last run সংরক্ষণ করো
        Cache::put(self::AGENT_LOG_KEY, [
            'ran_at' => now()->toDateTimeString(),
            'duration_seconds' => $duration,
            'total_generated' => $totalGenerated,
            'total_remade' => $totalRemade,
            'models' => count($this->modelRegistry),
        ], 86400 * 7);

        $log("\n🎉 Full cycle complete in {$duration}s");
        $log("   Generated: {$totalGenerated} | Remade: {$totalRemade} | Skipped: {$totalSkipped}");

        return [
            'models_processed' => count($report),
            'total_generated' => $totalGenerated,
            'total_remade' => $totalRemade,
            'total_skipped' => $totalSkipped,
            'duration_seconds' => $duration,
            'report' => $report,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // PRIVATE: Process One Model
    // ════════════════════════════════════════════════════════════════════════

    private function processModel(string $modelClass, array $config, callable $log): array
    {
        /** @var Model $model */
        $model = new $modelClass();
        $currentCount = $modelClass::count();

        $log("   Current rows: {$currentCount} | Min required: {$config['min_rows']}");

        $generated = 0;
        $remade = 0;
        $skipped = 0;

        // ── A. Data gap check → generate if needed ────────────────────────────
        $needsGeneration = $currentCount < $config['min_rows'];

        if ($needsGeneration) {
            $toGenerate = min(
                $config['min_rows'] - $currentCount + 5, // একটু extra
                self::MAX_GENERATE_PER_MODEL
            );

            $log("   📝 Need {$toGenerate} more records — asking AI for instruction...");

            // AI-কে দিয়ে instruction বানাও
            $instruction = $this->generateInstruction($config, $toGenerate, $log);

            $log("   💡 Instruction: {$instruction}");

            // ContentGenerationService দিয়ে generate করো
            try {
                $result = $this->generator->generate(
                    model: $model,
                    instruction: $instruction,
                    count: $toGenerate,
                    options: [
                        'realistic' => true,
                        'localized' => true,
                        'with_images' => false, // speed এর জন্য off রাখো
                    ],
                );

                // Records insert করো
                foreach ($result['records'] as $record) {
                    $pivotData = $record['__pivot__'] ?? [];
                    unset($record['__pivot__']);

                    try {
                        $saved = $modelClass::create($record);
                        $generated++;

                        // BelongsToMany sync
                        foreach ($pivotData as $relName => $relIds) {
                            if (method_exists($saved, $relName) && !empty($relIds)) {
                                $saved->{$relName}()->sync($relIds);
                            }
                        }
                    } catch (\Throwable $e) {
                        $skipped++;
                        Log::warning("AutonomousContentAgent: record insert failed", [
                            'model' => $modelClass,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $log("   ✅ Inserted {$generated} records via {$result['provider_used']}");
                $skipped += $result['skipped_dupes'];

            } catch (\Throwable $e) {
                $log("   ❌ Generation failed: {$e->getMessage()}");
                Log::error("AutonomousContentAgent: generation failed", [
                    'model' => $modelClass,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $log("   ✓ Sufficient rows exist — skipping generation");
            $skipped++;
        }

        // ── B. Quality audit → remake weak records ────────────────────────────
        $weakIds = $this->findWeakRecords($modelClass, $config, $log);

        if (!empty($weakIds)) {
            $log("   🔧 Found " . count($weakIds) . " weak records — remaking...");

            try {
                $remakeResult = $this->generator->remakeExisting(
                    model: $model,
                    ids: $weakIds,
                    batchSize: 3,
                );

                $remade = $remakeResult['updated'];
                $log("   ✅ Remade {$remade} records");

            } catch (\Throwable $e) {
                $log("   ❌ Remake failed: {$e->getMessage()}");
            }
        } else {
            $log("   ✓ All records quality OK");
        }

        return [
            'generated' => $generated,
            'remade' => $remade,
            'skipped' => $skipped,
            'initial_count' => $currentCount,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // AI-Generated Instruction (Agent এর নিজস্ব "চিন্তা")
    // ════════════════════════════════════════════════════════════════════════

    /**
     * AI-কে দিয়ে নিজেই একটা instruction বানায়।
     * তুমি কিছু বলো না — AI নিজেই বোঝে কী generate করতে হবে।
     */
    private function generateInstruction(array $config, int $count, callable $log): string
    {
        // Fallback instruction (যদি AI call fail করে)
        $fallback = "Generate {$count} realistic and detailed records about: {$config['topic']}. "
            . "All text must be in Bengali. Include rich descriptions.";

        try {
            $providers = [
                ['key' => 'GROQ_API_KEY', 'url' => 'https://api.groq.com/openai/v1/chat/completions', 'model' => 'llama-3.3-70b-versatile'],
                ['key' => 'GEMINI_API_KEY', 'url' => null, 'model' => 'gemini'],
            ];

            foreach ($providers as $provider) {
                $apiKey = env($provider['key']);
                if (!$apiKey)
                    continue;

                $instruction = $this->callAIForInstruction($apiKey, $provider, $config, $count);
                if ($instruction) {
                    return $instruction;
                }
            }
        } catch (\Throwable $e) {
            $log("   ⚠ AI instruction generation failed, using fallback: {$e->getMessage()}");
        }

        return $fallback;
    }

    private function callAIForInstruction(string $apiKey, array $provider, array $config, int $count): ?string
    {
        $systemPrompt = <<<PROMPT
তুমি একজন data generation expert। তোমার কাজ হলো একটা সংক্ষিপ্ত কিন্তু কার্যকর instruction লেখা যা দিয়ে AI ভালো বাংলা content generate করতে পারবে।

Rules:
- শুধু instruction text দাও, কোনো JSON বা code না
- বাংলা ও ইংরেজি মিশিয়ে instruction লিখতে পারো
- Specific হও — generic কথা এড়াও
- Maximum 2 sentences
PROMPT;

        $userPrompt = "Model: {$config['display_name']}\nTopic: {$config['topic']}\nCount: {$count}\n\nএই model এর জন্য একটা instruction লেখো।";

        if ($provider['model'] === 'gemini') {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
            $response = Http::timeout(15)->post($url, [
                'contents' => [['role' => 'user', 'parts' => [['text' => $systemPrompt . "\n\n" . $userPrompt]]]],
                'generationConfig' => ['maxOutputTokens' => 200],
            ]);

            if ($response->successful()) {
                return trim($response->json('candidates.0.content.parts.0.text') ?? '');
            }
            return null;
        }

        // OpenAI-compatible (Groq, Cerebras, Mistral)
        $response = Http::timeout(15)
            ->withToken($apiKey)
            ->post($provider['url'], [
                'model' => $provider['model'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 200,
                'temperature' => 0.8,
            ]);

        if ($response->successful()) {
            return trim($response->json('choices.0.message.content') ?? '');
        }

        return null;
    }

    // ════════════════════════════════════════════════════════════════════════
    // Quality Audit — কোন records দুর্বল?
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Database থেকে কিছু record নিয়ে quality check করে।
     * দুর্বল records এর ID list return করে।
     *
     * দুর্বল মানে:
     *  - Bengali text নেই (Latin script মাত্র)
     *  - খুব ছোট content (< 20 characters)
     *  - NULL fields যেগুলো fillable
     */
    private function findWeakRecords(string $modelClass, array $config, callable $log): array
    {
        $model = new $modelClass();
        $table = $model->getTable();
        $availableColumns = Schema::getColumnListing($table);

        $selects = array_intersect(
            array_merge(['id', 'slug'], $config['quality_fields']),
            $availableColumns
        );

        $records = $modelClass::query()
            ->select($selects)
            ->latest()
            ->limit(self::SCORE_BATCH_SIZE)
            ->get();

        if ($records->isEmpty()) {
            return [];
        }

        $weakIds = [];
        foreach ($records as $record) {
            // getAttributes() ব্যবহার করলে মডেলে থাকা Accessor/Appends রান হবে না, ফলে এরর আসবে না
            $score = $this->scoreRecord($record->getAttributes(), $config['quality_fields']);

            if ($score < self::QUALITY_THRESHOLD) {
                $weakIds[] = $record->id;
            }
        }

        return array_slice($weakIds, 0, 10);
    }

    /**
     * একটা record-এর quality score করো (0–100)।
     */
    private function scoreRecord(array $record, array $qualityFields): int
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

            // Bengali character check
            $hasBengali = preg_match('/[\x{0980}-\x{09FF}]/u', $text);

            if (!$hasBengali) {
                // Bengali নেই — score কম
                $scores[] = 20;
                continue;
            }

            // Content length score
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

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Single Model Run (UI থেকে call করার জন্য)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * শুধু একটা model process করো।
     * Blade component এর "Auto-Run" button থেকে ব্যবহার করা যাবে।
     */
    public function runForModel(string $modelClass, callable $log = null): array
    {
        $log ??= fn($msg) => null;

        if (!isset($this->modelRegistry[$modelClass])) {
            throw new \InvalidArgumentException("Model {$modelClass} is not registered in AutonomousContentAgent.");
        }

        $config = $this->modelRegistry[$modelClass];
        return $this->processModel($modelClass, $config, $log);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Status & Stats
    // ════════════════════════════════════════════════════════════════════════

    /**
     * শেষ কখন run হয়েছিল এবং কী হয়েছিল।
     */
    public function getLastRunInfo(): ?array
    {
        return Cache::get(self::AGENT_LOG_KEY);
    }

    /**
     * সব registered model এর current stats।
     */
    public function getModelStats(): array
    {
        $stats = [];

        foreach ($this->modelRegistry as $modelClass => $config) {
            if (!class_exists($modelClass)) {
                continue;
            }

            $model = new $modelClass();
            $table = $model->getTable();
            $count = $modelClass::count();

            // কলাম লিস্ট চেক করা (কমন কলামগুলো সহ)
            $availableColumns = Schema::getColumnListing($table);

            // সিলেকশনের জন্য কলাম ফিল্টার (ID, slug এবং quality_fields)
            $selects = array_intersect(
                array_merge(['id', 'slug', 'title', 'name'], $config['quality_fields']),
                $availableColumns
            );

            // Sample quality check (first 5 records)
            $sample = $modelClass::query()
                ->select($selects)
                ->limit(5)
                ->get();

            $avgScore = 100;
            if ($sample->isNotEmpty()) {
                // toArray() এর বদলে getRawAttributes() ব্যবহার করা হয়েছে Accessor error এড়াতে
                $scores = $sample->map(fn($r) => $this->scoreRecord($r->getAttributes(), $config['quality_fields']));
                $avgScore = (int) round($scores->avg());
            }

            $stats[] = [
                'model' => $modelClass,
                'display_name' => $config['display_name'],
                'table' => $table,
                'count' => $count,
                'min_rows' => $config['min_rows'],
                'needs_generation' => $count < $config['min_rows'],
                'avg_quality_score' => $avgScore,
                'quality_ok' => $avgScore >= self::QUALITY_THRESHOLD,
            ];
        }

        return $stats;
    }

    /**
     * Registered models এর list।
     */
    public function getRegisteredModels(): array
    {
        return array_keys($this->modelRegistry);
    }
}