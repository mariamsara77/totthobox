<?php

namespace App\Services;

use App\Jobs\AutoImproveModelJob;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║        AutonomousContentAgent v2 — Topic-Aware, Grounded, Judged         ║
 * ║   Scans all models → detects coverage gaps → grounds with Wikipedia →   ║
 * ║   generates → judges quality with AI → auto-remakes weak records         ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 *
 * v1 থেকে যা বদলেছে:
 *   1. Model registry এখন config/content_agent.php থেকে আসে — command আর
 *      agent দুটোই একই source ব্যবহার করে, sync করা লাগে না।
 *   2. Instruction আর "blind" না — seed_topics এর সাথে existing data diff
 *      করে ঠিক কোন topic missing সেটা বের করে নির্দিষ্টভাবে জিজ্ঞেস করে।
 *   3. Factual domain এর জন্য Wikipedia থেকে grounding fact টেনে এনে
 *      instruction এ জুড়ে দেয় — hallucination কমায়।
 *   4. Quality scoring এখন regex এর বদলে ContentQualityJudge (LLM-as-judge)
 *      ব্যবহার করে — batch এ, তাই AI call সংখ্যা বাড়ে না বরং কমে।
 *   5. Remake করার পরে re-score করে verify করে যে আসলেই উন্নতি হয়েছে কিনা।
 *   6. auto_generate_disabled ফ্ল্যাগ থাকা model (ফোন নম্বর, মন্ত্রী তালিকা,
 *      কুরআন টেক্সট ইত্যাদি sensitive/time-sensitive ডেটা) স্বয়ংক্রিয়ভাবে skip করে।
 *   7. dispatchFullCycleAsJobs() — Bus::batch দিয়ে প্রতিটা model কে আলাদা
 *      queue job হিসেবে parallel এ চালানো যায় (scheduler এর জন্য উপযুক্ত)।
 *
 * ব্যবহার:
 *   php artisan content:auto-improve                  → sync, সব model
 *   php artisan content:auto-improve --queue           → async, Bus::batch
 *   php artisan content:auto-improve --model=TourismBd → শুধু একটা model
 *   php artisan content:auto-improve --dry-run         → শুধু stats
 */
class AutonomousContentAgent
{
    private const AGENT_LOG_KEY = 'autonomous_agent_last_run';

    private array $modelRegistry;
    private int $qualityThreshold;
    private int $scoreBatchSize;
    private int $interModelDelay;

    public function __construct(
        private readonly ContentGenerationService $generator,
        private readonly ContentQualityJudge $judge,
        private readonly WikipediaGroundingService $grounding,
    ) {
        $this->modelRegistry = config('content_agent.models', []);
        $this->qualityThreshold = config('content_agent.quality_threshold', 65);
        $this->scoreBatchSize = config('content_agent.score_batch_size', 20);
        $this->interModelDelay = config('content_agent.inter_model_delay', 2);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Main Entry — Full Autonomous Run (Synchronous)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * সম্পূর্ণ autonomous run — sequential, sync। Console command থেকে বা
     * ছোট registry এর জন্য scheduler থেকে call করো। বড় registry এর জন্য
     * dispatchFullCycleAsJobs() ব্যবহার করা ভালো (parallel + non-blocking)।
     */
    public function runFullCycle(callable $log = null): array
    {
        $startedAt = microtime(true);
        $log ??= fn($msg) => null;

        $log('🤖 AutonomousContentAgent v2 starting full cycle...');

        $report = [];
        $totalGenerated = 0;
        $totalRemade = 0;
        $totalSkipped = 0;

        foreach ($this->modelRegistry as $name => $config) {
            if (!empty($config['auto_generate_disabled'])) {
                $log("⏭ {$config['display_name']} — auto-generation disabled (sensitive/manual data), skipping");
                continue;
            }

            if (!class_exists($config['class'])) {
                $log("⚠ Model class not found: {$config['class']} — skipping");
                continue;
            }

            $log("\n📋 Processing: {$config['display_name']}");

            try {
                $result = $this->processModel($config, $log);

                $report[$name] = $result;
                $totalGenerated += $result['generated'];
                $totalRemade += $result['remade'];
                $totalSkipped += $result['skipped'];

                $log("✅ {$config['display_name']}: +{$result['generated']} generated, {$result['remade']} remade, avg quality {$result['avg_quality_after']}%");

            } catch (\Throwable $e) {
                $log("❌ {$config['display_name']}: FAILED — {$e->getMessage()}");
                Log::error('AutonomousContentAgent: model failed', [
                    'model' => $config['class'],
                    'error' => $e->getMessage(),
                ]);
                $report[$name] = ['error' => $e->getMessage()];
            }

            if ($this->interModelDelay > 0) {
                sleep($this->interModelDelay);
            }
        }

        $duration = round(microtime(true) - $startedAt, 2);

        Cache::put(self::AGENT_LOG_KEY, [
            'ran_at' => now()->toDateTimeString(),
            'duration_seconds' => $duration,
            'total_generated' => $totalGenerated,
            'total_remade' => $totalRemade,
            'models' => count($this->modelRegistry),
            'mode' => 'sync',
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
    // PUBLIC: Full Cycle — Queue-Based Parallel Dispatch
    // ════════════════════════════════════════════════════════════════════════

    /**
     * প্রতিটা model এর জন্য আলাদা queue job dispatch করে, Bus::batch দিয়ে।
     * এটা non-blocking — command/scheduler সাথে সাথে ফিরে আসে, আসল কাজ
     * queue worker গুলো parallel এ করে। বড় registry (৩০+ model) এর জন্য
     * এটাই সঠিক পদ্ধতি — sync loop এ timeout/crash ঝুঁকি অনেক কম।
     *
     * প্রয়োজন: `php artisan queue:work --queue=ai-generation` চালু থাকতে হবে।
     */
    public function dispatchFullCycleAsJobs(?callable $onFinish = null): Batch
    {
        $jobs = [];

        foreach ($this->modelRegistry as $name => $config) {
            if (!empty($config['auto_generate_disabled']) || !class_exists($config['class'])) {
                continue;
            }
            $jobs[] = new AutoImproveModelJob($name);
        }

        $batch = Bus::batch($jobs)
            ->name('autonomous-content-agent-' . now()->format('Y-m-d-H-i'))
            ->onQueue('ai-generation')
            ->allowFailures()
            ->then(function (Batch $batch) use ($onFinish) {
                Cache::put(self::AGENT_LOG_KEY, [
                    'ran_at' => now()->toDateTimeString(),
                    'duration_seconds' => null,
                    'total_generated' => null,
                    'total_remade' => null,
                    'models' => $batch->totalJobs,
                    'mode' => 'queue',
                    'batch_id' => $batch->id,
                ], 86400 * 7);

                if ($onFinish) {
                    $onFinish($batch);
                }
            })
            ->dispatch();

        return $batch;
    }

    // ════════════════════════════════════════════════════════════════════════
    // PRIVATE: Process One Model (core logic)
    // ════════════════════════════════════════════════════════════════════════

    private function processModel(array $config, callable $log): array
    {
        $modelClass = $config['class'];
        /** @var Model $model */
        $model = new $modelClass();
        $currentCount = $modelClass::count();

        $log("   Current rows: {$currentCount} | Min required: {$config['min_rows']}");

        $generated = 0;
        $remade = 0;
        $skipped = 0;

        // ── A. Coverage-gap-aware generation ───────────────────────────────────
        $needsGeneration = $currentCount < $config['min_rows'];

        if ($needsGeneration && $config['max_generate'] > 0) {
            $toGenerate = min(
                $config['min_rows'] - $currentCount + 5,
                $config['max_generate']
            );

            $log("   📝 Need ~{$toGenerate} more records...");

            [$instruction, $chosenTopics] = $this->buildInstruction($modelClass, $config, $toGenerate, $log);

            $log('   💡 Instruction: ' . mb_substr($instruction, 0, 160) . '...');

            try {
                $result = $this->generator->generate(
                    model: $model,
                    instruction: $instruction,
                    count: $toGenerate,
                    options: [
                        'realistic' => true,
                        'localized' => true,
                        'with_images' => false,
                    ],
                );

                foreach ($result['records'] as $record) {
                    $pivotData = $record['__pivot__'] ?? [];
                    unset($record['__pivot__']);

                    try {
                        $saved = $modelClass::create($record);
                        $generated++;

                        foreach ($pivotData as $relName => $relIds) {
                            if (method_exists($saved, $relName) && !empty($relIds)) {
                                $saved->{$relName}()->sync($relIds);
                            }
                        }
                    } catch (\Throwable $e) {
                        $skipped++;
                        Log::warning('AutonomousContentAgent: record insert failed', [
                            'model' => $modelClass,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $log("   ✅ Inserted {$generated} records via {$result['provider_used']}");
                $skipped += $result['skipped_dupes'];

            } catch (\Throwable $e) {
                $log("   ❌ Generation failed: {$e->getMessage()}");
                Log::error('AutonomousContentAgent: generation failed', [
                    'model' => $modelClass,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (!$needsGeneration) {
            $log('   ✓ Sufficient rows exist — skipping generation');
        } else {
            $log('   ⏭ max_generate=0 — generation disabled for this model');
        }

        // ── B. Quality audit → LLM-judge scoring ───────────────────────────────
        $qualityFields = $this->resolveQualityFields($modelClass, $config['quality_fields']);
        $auditResult = $this->auditQuality($modelClass, $config, $qualityFields, $log);
        $weakIds = $auditResult['weak_ids'];
        $avgQualityBefore = $auditResult['avg_score'];

        // ── C. Remake weak records + re-verify ──────────────────────────────────
        $avgQualityAfter = $avgQualityBefore;

        if (!empty($weakIds)) {
            $log('   🔧 Found ' . count($weakIds) . ' weak records — remaking...');

            try {
                $remakeResult = $this->generator->remakeExisting(
                    model: $model,
                    ids: $weakIds,
                    batchSize: 3,
                );

                $remade = $remakeResult['updated'];
                $log("   ✅ Remade {$remade} records");

                // Re-score শুধু remade IDs গুলো — verify করার জন্য যে আসলেই উন্নতি হয়েছে
                if ($remade > 0) {
                    $reVerify = $this->auditQuality($modelClass, $config, $qualityFields, $log, $weakIds);
                    $avgQualityAfter = $reVerify['avg_score'];
                    $log("   📊 Quality after remake: {$avgQualityBefore}% → {$avgQualityAfter}%");
                }

            } catch (\Throwable $e) {
                $log("   ❌ Remake failed: {$e->getMessage()}");
            }
        } else {
            $log('   ✓ All sampled records quality OK');
        }

        return [
            'generated' => $generated,
            'remade' => $remade,
            'skipped' => $skipped,
            'initial_count' => $currentCount,
            'avg_quality_before' => $avgQualityBefore,
            'avg_quality_after' => $avgQualityAfter,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // Coverage-Gap-Aware Instruction Building
    // ════════════════════════════════════════════════════════════════════════

    /**
     * seed_topics দেওয়া থাকলে existing name/slug/title এর সাথে diff করে
     * missing topics বের করে এবং সেগুলো নির্দিষ্টভাবে জিজ্ঞেস করে।
     * seed_topics খালি হলে পুরনো generic AI-instruction পদ্ধতিতে fallback করে।
     * Factual/grounding-enabled model হলে Wikipedia থেকে fact টেনে instruction এ যোগ করে।
     *
     * @return array{0: string, 1: string[]} [instruction, chosenTopics]
     */
    private function buildInstruction(string $modelClass, array $config, int $count, callable $log): array
    {
        $seedTopics = $config['seed_topics'] ?? [];
        $chosenTopics = [];

        if (!empty($seedTopics)) {
            $existing = $this->getExistingTextValues($modelClass);
            $missing = array_values(array_filter($seedTopics, function ($topic) use ($existing) {
                foreach ($existing as $text) {
                    if (Str::contains($text, $topic) || Str::contains($topic, $text)) {
                        return false; // ইতিমধ্যে cover হয়ে গেছে
                    }
                }
                return true;
            }));

            if (!empty($missing)) {
                $chosenTopics = array_slice($missing, 0, $count);
                $log('   🎯 Coverage gap found: ' . implode(', ', array_slice($chosenTopics, 0, 5)) . (count($chosenTopics) > 5 ? '...' : ''));
            } else {
                $log('   ℹ️  Seed topics সব cover হয়ে গেছে — সাধারণ generation এ যাচ্ছে');
            }
        }

        // ── Grounding: factual topic হলে Wikipedia থেকে fact টেনে আনো ──────────
        $groundingBlock = '';
        if (!empty($config['grounding']) && !empty($chosenTopics)) {
            $log('   🔍 Fetching Wikipedia grounding facts...');
            $summaries = $this->grounding->fetchSummaries($chosenTopics, limitPerCall: 6);
            if (!empty($summaries)) {
                $groundingBlock = $this->grounding->formatAsPromptContext($summaries);
                $log('   📚 Grounded ' . count($summaries) . '/' . count($chosenTopics) . ' topics with Wikipedia facts');
            } else {
                $log('   ⚠ কোনো Wikipedia summary পাওয়া যায়নি — AI নিজের জ্ঞান দিয়ে লিখবে');
            }
        }

        if (!empty($chosenTopics)) {
            $topicList = implode(', ', $chosenTopics);
            $instruction = "নিম্নলিখিত নির্দিষ্ট বিষয়গুলো নিয়ে {$count}টি রেকর্ড তৈরি করো (প্রতিটা বিষয় থেকে একটা করে): {$topicList}। "
                . "প্রতিটার জন্য বিস্তারিত, নির্ভুল, সমৃদ্ধ বাংলা বিবরণ লিখো। Domain: {$config['topic']}."
                . $groundingBlock;

            return [$instruction, $chosenTopics];
        }

        // ── Fallback: পুরনো পদ্ধতি — AI কে দিয়ে নিজে instruction বানানো ──────────
        $instruction = $this->generateGenericInstruction($config, $count, $log) . $groundingBlock;

        return [$instruction, $chosenTopics];
    }

    /**
     * seed_topics না থাকা model এর জন্য AI কে দিয়ে instruction বানায় (v1 আচরণ)।
     */
    private function generateGenericInstruction(array $config, int $count, callable $log): string
    {
        $fallback = "Generate {$count} realistic and detailed records about: {$config['topic']}. "
            . 'All text must be in Bengali. Include rich, specific, non-generic descriptions.';

        try {
            $providers = [
                ['key' => 'GROQ_API_KEY', 'url' => 'https://api.groq.com/openai/v1/chat/completions', 'model' => 'llama-3.3-70b-versatile'],
                ['key' => 'GEMINI_API_KEY', 'url' => null, 'model' => 'gemini'],
            ];

            foreach ($providers as $provider) {
                $apiKey = env($provider['key']);
                if (!$apiKey) {
                    continue;
                }

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
            $response = \Illuminate\Support\Facades\Http::timeout(15)->post($url, [
                'contents' => [['role' => 'user', 'parts' => [['text' => $systemPrompt . "\n\n" . $userPrompt]]]],
                'generationConfig' => ['maxOutputTokens' => 200],
            ]);

            if ($response->successful()) {
                return trim($response->json('candidates.0.content.parts.0.text') ?? '');
            }
            return null;
        }

        $response = \Illuminate\Support\Facades\Http::timeout(15)
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

    /**
     * বর্তমানে যা আছে তার name/title/slug একসাথে fetch করে — coverage-gap diff এর জন্য।
     */
    private function getExistingTextValues(string $modelClass): array
    {
        $model = new $modelClass();
        $table = $model->getTable();
        $columns = array_intersect(['name', 'title', 'slug'], Schema::getColumnListing($table));

        if (empty($columns)) {
            return [];
        }

        return $modelClass::query()
            ->select(array_values($columns))
            ->get()
            ->flatMap(fn($row) => array_filter($row->only($columns)))
            ->map(fn($v) => (string) $v)
            ->values()
            ->toArray();
    }

    // ════════════════════════════════════════════════════════════════════════
    // Quality Audit (LLM-Judge based)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @param array|null $onlyIds দিলে শুধু এই ID গুলো audit করবে (remake-পরবর্তী verify এর জন্য)
     * @return array{weak_ids: array, avg_score: int}
     */
    private function auditQuality(string $modelClass, array $config, array $qualityFields, callable $log, ?array $onlyIds = null): array
    {
        if (empty($qualityFields)) {
            return ['weak_ids' => [], 'avg_score' => 100];
        }

        $model = new $modelClass();
        $table = $model->getTable();
        $availableColumns = Schema::getColumnListing($table);

        $selects = array_intersect(array_merge(['id', 'slug'], $qualityFields), $availableColumns);

        $query = $modelClass::query()->select($selects);

        if ($onlyIds) {
            $query->whereIn('id', $onlyIds);
        } else {
            $query->latest()->limit($this->scoreBatchSize);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            return ['weak_ids' => [], 'avg_score' => 100];
        }

        $recordArrays = $records->map(fn($r) => array_merge(['id' => $r->id], $r->getAttributes()))->toArray();

        $scored = $this->judge->scoreBatch($recordArrays, $qualityFields, $config['topic']);

        if (empty($scored)) {
            return ['weak_ids' => [], 'avg_score' => 100];
        }

        $avgScore = (int) round(array_sum(array_column($scored, 'score')) / count($scored));

        $weakIds = array_values(array_map(
            fn($r) => $r['id'],
            array_filter($scored, fn($r) => $r['score'] < $this->qualityThreshold)
        ));

        if (!$onlyIds && !empty($weakIds)) {
            $reasons = array_filter($scored, fn($r) => $r['score'] < $this->qualityThreshold);
            foreach (array_slice($reasons, 0, 3) as $r) {
                $log("      ⚠ ID {$r['id']} scored {$r['score']}%: {$r['reason']}");
            }
        }

        return ['weak_ids' => array_slice($weakIds, 0, 10), 'avg_score' => $avgScore];
    }

    private function resolveQualityFields(string $modelClass, array $configuredFields): array
    {
        if (empty($configuredFields)) {
            return [];
        }

        $model = new $modelClass();
        $availableColumns = Schema::getColumnListing($model->getTable());

        return array_values(array_intersect($configuredFields, $availableColumns));
    }

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Single Model Run (UI / Job থেকে call করার জন্য)
    // ════════════════════════════════════════════════════════════════════════

    public function runForModel(string $modelName, callable $log = null): array
    {
        $log ??= fn($msg) => null;

        if (!isset($this->modelRegistry[$modelName])) {
            throw new \InvalidArgumentException("Model '{$modelName}' is not registered in config/content_agent.php.");
        }

        $config = $this->modelRegistry[$modelName];

        if (!empty($config['auto_generate_disabled'])) {
            $log("⏭ {$config['display_name']} — auto-generation disabled, skipping");
            return [
                'generated' => 0,
                'remade' => 0,
                'skipped' => 0,
                'initial_count' => $config['class']::count(),
                'avg_quality_before' => null,
                'avg_quality_after' => null,
                'disabled' => true,
            ];
        }

        return $this->processModel($config, $log);
    }

    /**
     * runForModel() ব্যাকওয়ার্ড-কম্প্যাটিবিলিটি — model class দিয়েও call করা যায়।
     */
    public function runForModelClass(string $modelClass, callable $log = null): array
    {
        $name = collect($this->modelRegistry)->search(fn($c) => $c['class'] === $modelClass);
        if ($name === false) {
            throw new \InvalidArgumentException("Model class {$modelClass} is not registered.");
        }
        return $this->runForModel($name, $log);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Status & Stats
    // ════════════════════════════════════════════════════════════════════════

    public function getLastRunInfo(): ?array
    {
        return Cache::get(self::AGENT_LOG_KEY);
    }

    public function getModelStats(): array
    {
        $stats = [];

        foreach ($this->modelRegistry as $name => $config) {
            if (!class_exists($config['class'])) {
                continue;
            }

            $modelClass = $config['class'];
            $model = new $modelClass();
            $table = $model->getTable();
            $count = $modelClass::count();

            $qualityFields = $this->resolveQualityFields($modelClass, $config['quality_fields']);
            $availableColumns = Schema::getColumnListing($table);

            $selects = array_intersect(array_merge(['id', 'slug', 'title', 'name'], $qualityFields), $availableColumns);

            $avgScore = 100;
            if (!empty($qualityFields)) {
                $sample = $modelClass::query()->select($selects)->limit(5)->get();
                if ($sample->isNotEmpty()) {
                    $recordArrays = $sample->map(fn($r) => array_merge(['id' => $r->id], $r->getAttributes()))->toArray();
                    $scored = $this->judge->scoreBatch($recordArrays, $qualityFields, $config['topic']);
                    if (!empty($scored)) {
                        $avgScore = (int) round(array_sum(array_column($scored, 'score')) / count($scored));
                    }
                }
            }

            $stats[] = [
                'model' => $name,
                'class' => $modelClass,
                'display_name' => $config['display_name'],
                'table' => $table,
                'count' => $count,
                'min_rows' => $config['min_rows'],
                'needs_generation' => $count < $config['min_rows'],
                'avg_quality_score' => $avgScore,
                'quality_ok' => $avgScore >= $this->qualityThreshold,
                'auto_generate_disabled' => !empty($config['auto_generate_disabled']),
                'grounding_enabled' => !empty($config['grounding']),
                'has_seed_topics' => !empty($config['seed_topics']),
            ];
        }

        return $stats;
    }

    public function getRegisteredModels(): array
    {
        return array_keys($this->modelRegistry);
    }

    public function getModelConfig(string $modelName): ?array
    {
        return $this->modelRegistry[$modelName] ?? null;
    }
}