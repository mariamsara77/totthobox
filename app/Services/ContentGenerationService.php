<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║          ContentGenerationService v3 — Relation-Aware AI Engine         ║
 * ║  Auto-detects FK relations, fetches valid parent IDs, multi-provider    ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 *
 * NEW IN v3:
 *  • Full Eloquent relation introspection (BelongsTo, HasMany, BelongsToMany)
 *  • Auto-injects valid foreign key IDs from related tables into AI prompt
 *  • BelongsToMany pivot rows auto-created post-insert
 *  • Relation-aware deduplication
 *  • Schema column type hints (enum values, max length, nullable)
 *
 * Provider Priority:
 *   1. Groq      — llama-3.3-70b-versatile  (free, fastest)
 *   2. Gemini    — gemini-2.0-flash          (free, vision-capable)
 *   3. Cerebras  — llama3.1-8b               (ultra-low latency)
 *   4. Mistral   — mistral-small-latest      (reliable EU)
 *   5. HuggingFace — zephyr-7b              (last resort)
 */
class ContentGenerationService
{
    // ── Constants ────────────────────────────────────────────────────────────

    private const BLACKLIST_KEY = 'cgs_blacklist';

    private const SUCCESS_KEY = 'cgs_success_counts';

    private const BLACKLIST_TTL = 300;

    private const SUCCESS_TTL = 86400;

    private const HTTP_TIMEOUT = 45;

    private const MAX_TOKENS = 3000;

    private const MAX_RETRIES = 2;

    // ── Provider Registry ────────────────────────────────────────────────────

    private array $providers = [
        'groq' => [
            'name' => 'Groq / Llama-3.3-70b',
            'url' => 'https://api.groq.com/openai/v1/chat/completions',
            'model' => 'llama-3.3-70b-versatile',
            'key_env' => 'GROQ_API_KEY',
            'weight' => 100,
            'adapter' => 'openai',
        ],
        'gemini' => [
            'name' => 'Gemini 2.0 Flash',
            'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
            'key_env' => 'GEMINI_API_KEY',
            'weight' => 90,
            'adapter' => 'gemini',
        ],
        'cerebras' => [
            'name' => 'Cerebras / Llama-3.1-8b',
            'url' => 'https://api.cerebras.ai/v1/chat/completions',
            'model' => 'llama3.1-8b',
            'key_env' => 'CEREBRAS_API_KEY',
            'weight' => 80,
            'adapter' => 'openai',
        ],
        'mistral' => [
            'name' => 'Mistral Small',
            'url' => 'https://api.mistral.ai/v1/chat/completions',
            'model' => 'mistral-small-latest',
            'key_env' => 'MISTRAL_API_KEY',
            'weight' => 70,
            'adapter' => 'openai',
        ],
        'huggingface' => [
            'name' => 'HuggingFace / Zephyr-7b',
            'url' => 'https://api-inference.huggingface.co/models/HuggingFaceH4/zephyr-7b-beta',
            'model' => null,
            'key_env' => 'HUGGINGFACE_API_KEY',
            'weight' => 50,
            'adapter' => 'huggingface',
        ],
    ];

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC: Main Entry Point
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Generate records for a model, with full relation awareness.
     *
     * @return array{
     *   records: array,
     *   provider_used: string,
     *   skipped_dupes: int,
     *   images_attached: int,
     *   duration_ms: int,
     *   relation_context: array,
     * }
     */
    public function generate(
        Model $model,
        string $instruction,
        int $count = 10,
        array $options = [],
        ?string $causerType = null,
        ?int $causerId = null,
    ): array {
        $startedAt = microtime(true);

        $options = array_merge([
            'realistic' => true,
            'localized' => true,
            'with_images' => true,
        ], $options);

        // ── 1. Deep schema + relation introspection ──────────────────────────
        $schemaContext = $this->buildSchemaContext($model);
        $relationContext = $this->resolveRelations($model);   // ★ NEW
        $existingContext = $this->buildExistingDataContext($model);

        // ── 2. Build prompts ─────────────────────────────────────────────────
        $systemPrompt = $this->buildSystemPrompt($schemaContext, $relationContext, $existingContext, $options);
        $userPrompt = $this->buildUserPrompt($instruction, $count, $model, $relationContext);

        // ── 3. Dispatch to AI providers ──────────────────────────────────────
        [$rawJson, $usedProvider] = $this->dispatchToProviders($systemPrompt, $userPrompt);

        // ── 4. Parse + validate ──────────────────────────────────────────────
        $records = $this->parseAndValidate($rawJson, $model, $relationContext);

        // ── 5. Deduplicate ───────────────────────────────────────────────────
        [$records, $skippedDupes] = $this->deduplicate($records, $model);

        // ── 6. Attach images ─────────────────────────────────────────────────
        $imagesAttached = 0;
        if ($options['with_images']) {
            $imagesAttached = $this->attachImages($records, $model, $instruction);
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        // ── 7. Activity log ──────────────────────────────────────────────────
        $this->logActivity(
            model: $model,
            provider: $usedProvider,
            instruction: $instruction,
            count: count($records),
            skipped: $skippedDupes,
            durationMs: $durationMs,
            causerType: $causerType,
            causerId: $causerId,
        );

        return [
            'records' => $records,
            'provider_used' => $usedProvider,
            'skipped_dupes' => $skippedDupes,
            'images_attached' => $imagesAttached,
            'duration_ms' => $durationMs,
            'relation_context' => $relationContext,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // ★ NEW: Relation Introspection Engine
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Deeply introspects a model's Eloquent relations and DB columns to
     * produce a rich context map used for prompt engineering + FK injection.
     *
     * Returns structure:
     * [
     *   'belongs_to' => [
     *     'category_id' => [
     *       'fk'          => 'category_id',
     *       'relation'    => 'category',
     *       'related'     => App\Models\Category::class,
     *       'table'       => 'categories',
     *       'valid_ids'   => [1, 3, 5, 8],          // live DB values
     *       'sample_data' => [['id'=>1,'name'=>'...'], ...]
     *     ],
     *   ],
     *   'has_many'    => [...],
     *   'belongs_to_many' => [...],
     *   'fk_columns'  => ['category_id', 'user_id'],  // all detected FKs
     * ]
     */
    public function resolveRelations(Model $model): array
    {
        $result = [
            'belongs_to' => [],
            'has_many' => [],
            'belongs_to_many' => [],
            'fk_columns' => [],
        ];

        // ── A. Introspect declared Eloquent relation methods ─────────────────
        $reflection = new \ReflectionClass($model);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Skip inherited Laravel base methods, static methods, and methods with params
            if (
                $method->getDeclaringClass()->getName() === Model::class ||
                $method->isStatic() ||
                $method->getNumberOfParameters() > 0
            ) {
                continue;
            }

            try {
                $returnType = $method->getReturnType();
                if (! $returnType) {
                    continue;
                }

                $typeName = $returnType instanceof \ReflectionNamedType
                    ? $returnType->getName()
                    : null;

                if (! $typeName || ! class_exists($typeName)) {
                    continue;
                }

                // Check if return type is an Eloquent relation
                if (! is_subclass_of($typeName, Relation::class)) {
                    continue;
                }

                /** @var Relation $relation */
                $relation = $model->{$method->getName()}();
                $relatedModel = $relation->getRelated();
                $relatedClass = get_class($relatedModel);
                $relatedTable = $relatedModel->getTable();

                if ($relation instanceof BelongsTo) {
                    $fk = $relation->getForeignKeyName();
                    $sampleData = $this->fetchRelatedSamples($relatedModel);
                    $validIds = collect($sampleData)->pluck('id')->toArray();

                    $result['belongs_to'][$fk] = [
                        'fk' => $fk,
                        'relation' => $method->getName(),
                        'related' => $relatedClass,
                        'table' => $relatedTable,
                        'valid_ids' => $validIds,
                        'sample_data' => $sampleData,
                    ];

                    $result['fk_columns'][] = $fk;

                } elseif ($relation instanceof HasMany) {
                    $result['has_many'][$method->getName()] = [
                        'relation' => $method->getName(),
                        'related' => $relatedClass,
                        'table' => $relatedTable,
                        'fk' => $relation->getForeignKeyName(),
                    ];

                } elseif ($relation instanceof BelongsToMany) {
                    $pivot = $relation->getTable();
                    $result['belongs_to_many'][$method->getName()] = [
                        'relation' => $method->getName(),
                        'related' => $relatedClass,
                        'table' => $relatedTable,
                        'pivot_table' => $pivot,
                        'fk' => $relation->getForeignPivotKeyName(),
                        'related_fk' => $relation->getRelatedPivotKeyName(),
                        'valid_ids' => $relatedModel::query()->pluck('id')->toArray(),
                    ];
                }

            } catch (\Throwable) {
                // Silently skip problematic methods
                continue;
            }
        }

        // ── B. Supplement with column-name heuristics (FK columns without declared relations) ──
        $columns = Schema::getColumnListing($model->getTable());

        foreach ($columns as $col) {
            if (
                Str::endsWith($col, '_id') &&
                ! in_array($col, $result['fk_columns'], true) &&
                $col !== 'id'
            ) {
                // Guess the related table from column name
                $guessedTable = Str::plural(Str::beforeLast($col, '_id'));

                if (Schema::hasTable($guessedTable)) {
                    $guessedModelClass = $this->guessModelClass($guessedTable);
                    $validIds = \DB::table($guessedTable)->pluck('id')->toArray();

                    $sampleData = \DB::table($guessedTable)
                        ->select(array_filter(['id', 'name', 'title', 'slug'], fn ($c) => Schema::hasColumn($guessedTable, $c)))
                        ->limit(10)
                        ->get()
                        ->map(fn ($r) => (array) $r)
                        ->toArray();

                    $result['belongs_to'][$col] = [
                        'fk' => $col,
                        'relation' => Str::camel(Str::beforeLast($col, '_id')),
                        'related' => $guessedModelClass,
                        'table' => $guessedTable,
                        'valid_ids' => $validIds,
                        'sample_data' => $sampleData,
                        'heuristic' => true,
                    ];

                    $result['fk_columns'][] = $col;
                }
            }
        }

        $result['fk_columns'] = array_unique($result['fk_columns']);

        return $result;
    }

    /**
     * Fetch representative sample rows from a related model table.
     */
    private function fetchRelatedSamples(Model $relatedModel): array
    {
        $table = $relatedModel->getTable();
        $columns = array_filter(
            ['id', 'name', 'title', 'slug', 'label'],
            fn ($c) => Schema::hasColumn($table, $c)
        );

        if (empty($columns)) {
            $columns = ['id'];
        }

        return $relatedModel::query()
            ->select(array_values($columns))
            ->orderBy('id')
            ->limit(30)
            ->get()
            ->map(fn ($r) => $r->toArray())
            ->toArray();
    }

    /**
     * Attempt to resolve a model class from a table name.
     */
    private function guessModelClass(string $table): string
    {
        $modelName = Str::studly(Str::singular($table));
        $candidates = [
            "App\\Models\\{$modelName}",
            "App\\Models\\{$modelName}",
        ];

        foreach ($candidates as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return "App\\Models\\{$modelName}"; // Return best guess even if class doesn't exist
    }

    // ════════════════════════════════════════════════════════════════════════
    // Provider Dispatch
    // ════════════════════════════════════════════════════════════════════════

    private function dispatchToProviders(string $systemPrompt, string $userPrompt): array
    {
        foreach ($this->getRankedProviders() as $key => $provider) {
            $apiKey = $this->resolveApiKey($key, $provider);

            if (! $apiKey) {
                Log::debug("CGS: {$provider['name']} — no API key, skipping");

                continue;
            }

            for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
                try {
                    $raw = match ($provider['adapter']) {
                        'openai' => $this->callOpenAI($apiKey, $provider, $systemPrompt, $userPrompt),
                        'gemini' => $this->callGemini($apiKey, $systemPrompt, $userPrompt),
                        'huggingface' => $this->callHuggingFace($apiKey, $provider, $systemPrompt, $userPrompt),
                        default => null,
                    };

                    if ($raw && $this->looksLikeJson($raw)) {
                        $this->markSuccess($key);
                        Log::info("CGS: success via {$provider['name']} (attempt {$attempt})");

                        return [$raw, $key];
                    }

                } catch (RequestException $e) {
                    $status = $e->response?->status() ?? 0;

                    if (in_array($status, [429, 503], true)) {
                        Log::warning("{$provider['name']}: rate-limited (HTTP {$status}), blacklisting");
                        $this->markFailure($key);
                        break;
                    }

                    Log::warning("{$provider['name']}: HTTP {$status} on attempt {$attempt}");
                } catch (\Throwable $e) {
                    Log::error("{$provider['name']}: {$e->getMessage()} on attempt {$attempt}");
                }

                if ($attempt < self::MAX_RETRIES) {
                    usleep(500_000 * $attempt);
                }
            }

            $this->markFailure($key);
        }

        throw new \RuntimeException('CGS: all AI providers exhausted.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // AI Adapters
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
                'temperature' => 0.7,
                'max_tokens' => self::MAX_TOKENS,
                'response_format' => ['type' => 'json_object'],
            ])
            ->throw();

        return $response->json('choices.0.message.content');
    }

    private function callGemini(string $apiKey, string $system, string $user): ?string
    {
        $url = $this->providers['gemini']['url'].'?key='.$apiKey;

        $response = Http::timeout(self::HTTP_TIMEOUT)
            ->post($url, [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => [['role' => 'user', 'parts' => [['text' => $user]]]],
                'generationConfig' => [
                    'temperature' => 0.7,
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

        Log::warning('Gemini: HTTP '.$response->status(), ['body' => mb_substr($response->body(), 0, 400)]);

        return null;
    }

    private function callHuggingFace(string $apiKey, array $provider, string $system, string $user): ?string
    {
        $prompt = "<|system|>\n{$system}\n<|user|>\n{$user}\n<|assistant|>";

        $response = Http::timeout(self::HTTP_TIMEOUT)
            ->withToken($apiKey)
            ->post($provider['url'], [
                'inputs' => $prompt,
                'parameters' => [
                    'max_new_tokens' => self::MAX_TOKENS,
                    'temperature' => 0.7,
                    'return_full_text' => false,
                ],
            ])
            ->throw();

        return $response->json('0.generated_text');
    }

    // ════════════════════════════════════════════════════════════════════════
    // ★ Prompt Engineering (Relation-Aware)
    // ════════════════════════════════════════════════════════════════════════

    private function buildSystemPrompt(
        string $schemaContext,
        array $relationContext,
        string $existingContext,
        array $options,
    ): string {
        $localeNote = $options['localized'] ? 'Use Bangladesh locale, context, and examples.' : 'Use neutral locale.';
        $realisticNote = $options['realistic'] ? 'All data must be factually plausible and internally consistent.' : 'Creative data is acceptable.';

        // Build relation constraint block
        $relationBlock = $this->buildRelationSystemBlock($relationContext);

        return <<<PROMPT
You are an expert data synthesizer for a Laravel application. Your ONLY job is to generate valid, structured JSON data matching the provided schema.

⚠️ CRITICAL: ALL TEXT CONTENT MUST BE IN BENGALI (বাংলা) SCRIPT. This is non-negotiable.

STRICT RULES:
1. Respond with ONLY a JSON object: { "data": [ {...}, {...} ] }
2. No markdown, no code fences, no explanations — raw JSON only.
3. Every field must match its column type exactly.
4. Slugs: English kebab-case only.
5. Dates: YYYY-MM-DD format.
6. {$localeNote}
7. {$realisticNote}
8. Never repeat existing names or slugs.

LANGUAGE RULES:
- name, title, bio, description, details, address, label → always in Bengali (বাংলা)
- slug, url, email, phone, numeric fields → English/standard format
- Use rich, context-appropriate Bengali content

{$relationBlock}

DATABASE SCHEMA:
{$schemaContext}

EXISTING DATA (do not duplicate):
{$existingContext}
PROMPT;
    }

    /**
     * Build the relation constraint section of the system prompt.
     */
    private function buildRelationSystemBlock(array $relationContext): string
    {
        if (empty($relationContext['belongs_to'])) {
            return '';
        }

        $lines = [
            '╔════ FOREIGN KEY CONSTRAINTS (MANDATORY) ════╗',
            'The following FK fields MUST use ONLY the valid IDs listed below.',
            'Using any other ID will cause a database constraint error.',
            '',
        ];

        foreach ($relationContext['belongs_to'] as $fk => $rel) {
            if (empty($rel['valid_ids'])) {
                $lines[] = "• {$fk}: [TABLE IS EMPTY — skip this field or use null if nullable]";

                continue;
            }

            $idList = implode(', ', array_slice($rel['valid_ids'], 0, 20));
            $lines[] = "• {$fk} → must be one of: [{$idList}]";

            // Show sample names for context
            if (! empty($rel['sample_data'])) {
                $samples = collect($rel['sample_data'])
                    ->take(5)
                    ->map(fn ($r) => "  id={$r['id']}: ".($r['name'] ?? $r['title'] ?? $r['label'] ?? '—'))
                    ->join("\n");
                $lines[] = $samples;
            }

            $lines[] = '';
        }

        if (! empty($relationContext['belongs_to_many'])) {
            $lines[] = 'MANY-TO-MANY RELATIONS (include as array field):';
            foreach ($relationContext['belongs_to_many'] as $name => $rel) {
                $idList = implode(', ', array_slice($rel['valid_ids'], 0, 15));
                $lines[] = "• {$name}_ids (array) → valid related IDs: [{$idList}]";
            }
            $lines[] = '';
        }

        $lines[] = '╚══════════════════════════════════════════════╝';

        return implode("\n", $lines);
    }

    private function buildUserPrompt(string $instruction, int $count, Model $model, array $relationContext): string
    {
        $modelName = class_basename($model);
        $fillable = implode(', ', $model->getFillable());

        // Build FK enforcement section
        $fkEnforcement = '';
        if (! empty($relationContext['belongs_to'])) {
            $fkEnforcement = "\nFOREIGN KEY ENFORCEMENT:\n";
            foreach ($relationContext['belongs_to'] as $fk => $rel) {
                if (! empty($rel['valid_ids'])) {
                    $ids = implode(', ', array_slice($rel['valid_ids'], 0, 10));
                    $fkEnforcement .= "- {$fk}: choose randomly from [{$ids}]\n";
                } else {
                    $fkEnforcement .= "- {$fk}: table is empty, use null if nullable\n";
                }
            }
        }

        // BelongsToMany array fields
        $m2mNote = '';
        if (! empty($relationContext['belongs_to_many'])) {
            $m2mNote = "\nMANY-TO-MANY (include these as JSON arrays — they will be synced after insert):\n";
            foreach ($relationContext['belongs_to_many'] as $name => $rel) {
                $ids = implode(', ', array_slice($rel['valid_ids'], 0, 8));
                $m2mNote .= "- {$name}_ids: array of 1-3 IDs from [{$ids}]\n";
            }
        }

        return <<<PROMPT
Generate exactly {$count} records for the `{$modelName}` model.

Instruction: {$instruction}

Required fillable fields: {$fillable}
{$fkEnforcement}{$m2mNote}
BENGALI LANGUAGE RULES (NON-NEGOTIABLE):
- name, title, bio, description → বাংলায় লিখবে
- bio/description → HTML paragraph tags সহ: <p>...</p>
- slug → name-এর বাংলা উচ্চারণ থেকে English kebab-case
- date fields → YYYY-MM-DD

EXAMPLE OUTPUT FORMAT:
{
  "data": [
    {
      "name": "রবীন্দ্রনাথ ঠাকুর",
      "slug": "rabindranath-thakur",
      "bio": "<p>রবীন্দ্রনাথ ঠাকুর বিশ্বখ্যাত বাঙালি কবি।</p>",
      "category_id": 3,
      "tags_ids": [1, 4]
    }
  ]
}

Return format: { "data": [ { ...all fillable fields... } ] }
PROMPT;
    }

    // ════════════════════════════════════════════════════════════════════════
    // Schema Context (Enhanced with type hints)
    // ════════════════════════════════════════════════════════════════════════

    private function buildSchemaContext(Model $model): string
    {
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $fillable = $model->getFillable();
        $hidden = $model->getHidden();

        $lines = ["Table: `{$table}`", 'Columns:'];

        foreach ($columns as $col) {
            $type = Schema::getColumnType($table, $col);
            $isFillable = in_array($col, $fillable) ? '[FILLABLE]' : '[readonly]';
            $isHidden = in_array($col, $hidden) ? '[HIDDEN]' : '';

            // Add nullability hint
            try {
                $doctrineCol = \DB::getDoctrineColumn($table, $col);
                $nullable = ! $doctrineCol->getNotnull() ? '[nullable]' : '';
            } catch (\Throwable) {
                $nullable = '';
            }

            $lines[] = "  - {$col} ({$type}) {$isFillable} {$isHidden} {$nullable}";
        }

        return implode("\n", $lines);
    }

    private function buildExistingDataContext(Model $model): string
    {
        $table = $model->getTable();
        $columns = array_filter(
            ['id', 'name', 'title', 'slug'],
            fn ($c) => Schema::hasColumn($table, $c)
        );

        if (empty($columns)) {
            return 'No existing records context available.';
        }

        $existing = $model::query()
            ->select(array_values($columns))
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($r) => array_filter($r->toArray()))
            ->toArray();

        if (empty($existing)) {
            return 'No existing records — fresh dataset.';
        }

        return json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ★ Parse & Validate (Relation-Aware)
    // ════════════════════════════════════════════════════════════════════════

    private function parseAndValidate(string $raw, Model $model, array $relationContext = []): array
    {
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            preg_match('/\{.*\}/s', $clean, $matches);
            $decoded = $matches ? json_decode($matches[0], true) : null;
        }

        $records = $decoded['data'] ?? (is_array($decoded) ? $decoded : []);

        if (empty($records)) {
            Log::warning('CGS: empty/unparseable JSON', ['raw' => mb_substr($raw, 0, 500)]);

            return [];
        }

        $fillable = array_flip($model->getFillable());
        $fkColumns = $relationContext['fk_columns'] ?? [];
        $belongsTo = $relationContext['belongs_to'] ?? [];
        $belongsToMany = $relationContext['belongs_to_many'] ?? [];

        return array_filter(array_map(function (array $record) use ($fillable, $model, $fkColumns, $belongsTo, $belongsToMany) {

            // ── Extract M2M pivot data before filtering ──────────────────────
            $pivotData = [];
            foreach ($belongsToMany as $name => $rel) {
                $key = "{$name}_ids";
                if (isset($record[$key]) && is_array($record[$key])) {
                    $pivotData[$name] = array_intersect(
                        array_map('intval', $record[$key]),
                        $rel['valid_ids']
                    );
                }
                unset($record[$key]); // Remove from main record before insert
            }

            // ── Filter to fillable keys ───────────────────────────────────────
            $filtered = array_intersect_key($record, $fillable);

            // ── Validate + coerce FK values ───────────────────────────────────
            foreach ($fkColumns as $fk) {
                if (! isset($filtered[$fk])) {
                    continue;
                }

                $validIds = $belongsTo[$fk]['valid_ids'] ?? [];

                if (empty($validIds)) {
                    // Related table empty — set null or remove
                    $filtered[$fk] = null;

                    continue;
                }

                $val = (int) $filtered[$fk];

                if (! in_array($val, $validIds, true)) {
                    // Invalid ID — pick a random valid one
                    $filtered[$fk] = $validIds[array_rand($validIds)];
                    Log::debug("CGS: corrected invalid FK {$fk}={$val} → {$filtered[$fk]}");
                }
            }

            // ── Auto-generate slug ────────────────────────────────────────────
            if (in_array('slug', $model->getFillable()) && empty($filtered['slug'])) {
                $source = $filtered['name'] ?? $filtered['title'] ?? Str::random(8);
                $filtered['slug'] = Str::slug($source).'-'.Str::random(4);
            }

            // ── Attach pivot data as metadata (picked up by Job after insert) ─
            if (! empty($pivotData)) {
                $filtered['__pivot__'] = $pivotData;
            }

            return $filtered;

        }, $records));
    }

    // ════════════════════════════════════════════════════════════════════════
    // De-duplication
    // ════════════════════════════════════════════════════════════════════════

    private function deduplicate(array $records, Model $model): array
    {
        if (empty($records)) {
            return [[], 0];
        }

        $fillable = $model->getFillable();
        $hasSlug = in_array('slug', $fillable);
        $skipped = 0;

        $existingSlugs = $hasSlug
            ? $model::query()->pluck('slug')->flip()->toArray()
            : [];

        $existingHashes = Cache::remember(
            'cgs_hashes_'.$model->getTable(),
            60,
            fn () => $this->buildExistingHashes($model)
        );

        $clean = [];

        foreach ($records as $record) {
            if ($hasSlug && isset($record['slug']) && isset($existingSlugs[$record['slug']])) {
                $skipped++;

                continue;
            }

            $hash = $this->contentHash($record);
            if (isset($existingHashes[$hash])) {
                $skipped++;

                continue;
            }

            if ($hasSlug && isset($record['slug'])) {
                $existingSlugs[$record['slug']] = true;
            }
            $existingHashes[$hash] = true;
            $clean[] = $record;
        }

        Cache::forget('cgs_hashes_'.$model->getTable());

        return [$clean, $skipped];
    }

    private function buildExistingHashes(Model $model): array
    {
        return $model::query()
            ->select($model->getFillable())
            ->limit(500)
            ->get()
            ->mapWithKeys(fn ($row) => [$this->contentHash($row->toArray()) => true])
            ->toArray();
    }

    private function contentHash(array $record): string
    {
        $excluded = ['id', 'created_at', 'updated_at', 'slug', '__pivot__'];
        $data = array_diff_key($record, array_flip($excluded));
        ksort($data);

        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    // ════════════════════════════════════════════════════════════════════════
    // Image Handling
    // ════════════════════════════════════════════════════════════════════════

    public function attachImagesToSavedModels(Model $modelInstance, string $keyword): int
    {
        if (! method_exists($modelInstance, 'addMediaFromUrl')) {
            return 0;
        }

        $imageUrl = $this->fetchImageUrl($keyword);
        if (! $imageUrl) {
            return 0;
        }

        try {
            $modelInstance
                ->addMediaFromUrl($imageUrl)
                ->usingName(Str::slug($keyword))
                ->usingFileName(Str::slug($keyword).'-'.time().'.jpg')
                ->toMediaCollection('images');

            return 1;
        } catch (\Throwable $e) {
            Log::warning('CGS: image attachment failed', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    public function fetchImageUrl(string $keyword, int $width = 800, int $height = 600): ?string
    {
        $pollinationsUrl = $this->tryPollinations($keyword, $width, $height);
        if ($pollinationsUrl) {
            return $pollinationsUrl;
        }

        $unsplashUrl = $this->tryUnsplash($keyword);
        if ($unsplashUrl) {
            return $unsplashUrl;
        }

        $seed = abs(crc32($keyword)) % 1000;

        return "https://picsum.photos/seed/{$seed}/{$width}/{$height}";
    }

    private function tryPollinations(string $keyword, int $width, int $height): ?string
    {
        try {
            $prompt = urlencode("high quality photo of {$keyword}, professional, realistic");
            $url = "https://image.pollinations.ai/prompt/{$prompt}?width={$width}&height={$height}&nologo=true&enhance=true";
            $response = Http::timeout(15)->head($url);

            return $response->successful() ? $url : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function tryUnsplash(string $keyword): ?string
    {
        $accessKey = config('services.unsplash.access_key') ?: env('UNSPLASH_ACCESS_KEY');
        if (! $accessKey) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get('https://api.unsplash.com/photos/random', [
                'query' => $keyword,
                'orientation' => 'landscape',
                'client_id' => $accessKey,
            ]);

            return $response->successful() ? $response->json('urls.regular') : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function attachImages(array &$records, Model $model, string $keyword): int
    {
        $count = 0;
        $imageUrl = $this->fetchImageUrl($keyword);

        if (! $imageUrl) {
            return 0;
        }

        foreach ($records as &$record) {
            if (in_array('image_url', $model->getFillable())) {
                $record['image_url'] = $imageUrl;
                $count++;
            }
        }
        unset($record);

        return $count;
    }

    // ════════════════════════════════════════════════════════════════════════
    // Provider Priority & Blacklist
    // ════════════════════════════════════════════════════════════════════════

    private function getRankedProviders(): array
    {
        $blacklist = Cache::get(self::BLACKLIST_KEY, []);
        $success = Cache::get(self::SUCCESS_KEY, []);
        $providers = $this->providers;

        uksort($providers, function (string $a, string $b) use ($blacklist, $success) {
            $aBlack = in_array($a, $blacklist, true);
            $bBlack = in_array($b, $blacklist, true);

            if ($aBlack !== $bBlack) {
                return $aBlack ? 1 : -1;
            }

            $successDiff = ($success[$b] ?? 0) <=> ($success[$a] ?? 0);
            if ($successDiff !== 0) {
                return $successDiff;
            }

            return ($this->providers[$b]['weight'] ?? 0) <=> ($this->providers[$a]['weight'] ?? 0);
        });

        return $providers;
    }

    private function markSuccess(string $key): void
    {
        $success = Cache::get(self::SUCCESS_KEY, []);
        $success[$key] = ($success[$key] ?? 0) + 1;
        Cache::put(self::SUCCESS_KEY, $success, self::SUCCESS_TTL);

        $blacklist = array_values(array_diff(Cache::get(self::BLACKLIST_KEY, []), [$key]));
        Cache::put(self::BLACKLIST_KEY, $blacklist, self::BLACKLIST_TTL);
    }

    private function markFailure(string $key): void
    {
        $blacklist = Cache::get(self::BLACKLIST_KEY, []);
        if (! in_array($key, $blacklist, true)) {
            $blacklist[] = $key;
            Cache::put(self::BLACKLIST_KEY, $blacklist, self::BLACKLIST_TTL);
        }
    }

    private function resolveApiKey(string $key, array $provider): ?string
    {
        $value = config("services.ai.{$key}") ?: env($provider['key_env'] ?? '');

        if (! $value || str_contains((string) $value, 'your_') || str_contains((string) $value, 'sk-xx')) {
            return null;
        }

        return (string) $value;
    }

    // ════════════════════════════════════════════════════════════════════════
    // Remake Existing Records
    // ════════════════════════════════════════════════════════════════════════

    public function remakeExisting(Model $model, array $ids = [], int $batchSize = 5): array
    {
        $query = $model::query();
        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $records = $query->get();
        if ($records->isEmpty()) {
            return ['updated' => 0, 'failed' => 0, 'skipped' => 0];
        }

        $fillable = $model->getFillable();
        $table = $model->getTable();
        $updated = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($records->chunk($batchSize) as $batch) {
            $systemPrompt = $this->buildRemakeSystemPrompt($table, $fillable);
            $userPrompt = $this->buildRemakeUserPrompt($batch->toArray(), $fillable);

            try {
                [$rawJson] = $this->dispatchToProviders($systemPrompt, $userPrompt);
                $remadeRecords = $this->parseRemakeResponse($rawJson);

                foreach ($remadeRecords as $remadeData) {
                    $id = $remadeData['id'] ?? null;
                    if (! $id) {
                        $skipped++;

                        continue;
                    }

                    $instance = $model::find($id);
                    if (! $instance) {
                        $skipped++;

                        continue;
                    }

                    $updateData = array_intersect_key($remadeData, array_flip($fillable));

                    if (isset($updateData['slug']) && $updateData['slug'] !== $instance->slug) {
                        if ($model::where('slug', $updateData['slug'])->where('id', '!=', $id)->exists()) {
                            $updateData['slug'] .= '-'.$id;
                        }
                    }

                    $instance->update($updateData);
                    $updated++;
                }
            } catch (\Throwable $e) {
                Log::error('CGS: remake batch failed', ['error' => $e->getMessage()]);
                $failed += $batch->count();
            }
        }

        return ['updated' => $updated, 'failed' => $failed, 'skipped' => $skipped];
    }

    private function buildRemakeSystemPrompt(string $table, array $fillable): string
    {
        $fields = implode(', ', $fillable);

        return <<<PROMPT
তুমি একজন বাংলাদেশের বিশেষজ্ঞ কন্টেন্ট লেখক।
টেবিল: `{$table}` | Fields: {$fields}

নিয়ম:
1. শুধু JSON দাও: { "data": [ {...} ] }
2. প্রতিটি record-এ মূল `id` রাখো
3. সব text content বাংলায় লিখবে
4. slug → English kebab-case
5. bio/description → <p> tags সহ বিস্তারিত বাংলায়
6. তথ্য যোগ করো — সংক্ষিপ্ত না হয়ে বিস্তারিত লিখবে
PROMPT;
    }

    private function buildRemakeUserPrompt(array $records, array $fillable): string
    {
        $simplified = array_map(
            fn ($r) => array_intersect_key($r, array_flip(array_merge(['id'], $fillable))),
            $records
        );

        return 'এই records গুলো বাংলায় rewrite করো: '.
            json_encode($simplified, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).
            "\n\n{ \"data\": [...] } format-এ দাও";
    }

    private function parseRemakeResponse(string $raw): array
    {
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);
        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            preg_match('/\{.*\}/s', $clean, $matches);
            $decoded = $matches ? json_decode($matches[0], true) : null;
        }

        return $decoded['data'] ?? [];
    }

    // ════════════════════════════════════════════════════════════════════════
    // Helpers
    // ════════════════════════════════════════════════════════════════════════

    private function looksLikeJson(string $raw): bool
    {
        $t = trim($raw);

        return str_starts_with($t, '{') || str_starts_with($t, '[');
    }

    private function logActivity(
        Model $model,
        string $provider,
        string $instruction,
        int $count,
        int $skipped,
        int $durationMs,
        ?string $causerType,
        ?int $causerId,
    ): void {
        try {
            activity('content-generation')
                ->performedOn($model)
                ->withProperties([
                    'provider' => $this->providers[$provider]['name'] ?? $provider,
                    'instruction' => mb_substr($instruction, 0, 200),
                    'generated' => $count,
                    'skipped' => $skipped,
                    'duration_ms' => $durationMs,
                    'table' => $model->getTable(),
                ])
                ->log("Generated {$count} records for {$model->getTable()} via {$provider}");
        } catch (\Throwable $e) {
            Log::warning('CGS: activitylog failed', ['error' => $e->getMessage()]);
        }
    }

    public function resetBlacklist(): void
    {
        Cache::forget(self::BLACKLIST_KEY);
        Cache::forget(self::SUCCESS_KEY);
    }

    public function healthCheck(): array
    {
        $results = [];
        $testPrompt = 'Reply with exactly: {"data":[{"test":true}]}';

        foreach ($this->providers as $key => $provider) {
            $apiKey = $this->resolveApiKey($key, $provider);

            if (! $apiKey) {
                $results[$key] = ['status' => 'no_key', 'latency_ms' => 0, 'provider' => $provider['name']];

                continue;
            }

            $start = microtime(true);
            try {
                $raw = match ($provider['adapter']) {
                    'openai' => $this->callOpenAI($apiKey, $provider, 'You generate JSON.', $testPrompt),
                    'gemini' => $this->callGemini($apiKey, 'You generate JSON.', $testPrompt),
                    'huggingface' => $this->callHuggingFace($apiKey, $provider, 'You generate JSON.', $testPrompt),
                    default => null,
                };

                $results[$key] = [
                    'status' => ($raw && $this->looksLikeJson($raw)) ? 'ok' : 'bad_response',
                    'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                    'provider' => $provider['name'],
                ];
            } catch (\Throwable $e) {
                $results[$key] = [
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                    'error' => $e->getMessage(),
                    'provider' => $provider['name'],
                ];
            }
        }

        return $results;
    }

    /**
     * Public accessor for provider names (used by UI).
     */
    public function getProviderName(string $key): string
    {
        return $this->providers[$key]['name'] ?? $key;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }
}
