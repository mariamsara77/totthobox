<?php

namespace App\Jobs;

use App\Services\ContentGenerationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateContentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // টাইমআউট বাড়িয়ে ৯০০ সেকেন্ড (১৫ মিনিট) করা হলো, যেন ইমেজ ডাউনলোডে জব কিল না হয়
    public int $timeout = 900;
    public int $tries = 1;
    public int $maxExceptions = 1;

    private const PROGRESS_TTL = 3600;

    public function __construct(
        public readonly string $jobId,
        public readonly string $modelClass,
        public readonly string $instruction,
        public readonly int $count = 10,
        public readonly array $options = [],
        public readonly ?string $causerType = null,
        public readonly ?int $causerId = null,
    ) {
        $this->onQueue('ai-generation');
    }

    public function handle(ContentGenerationService $service): void
    {
        if ($this->batch()?->cancelled()) {
            $this->updateProgress('failed', stage: 'Batch cancelled');
            return;
        }

        $this->updateProgress('running', stage: 'AI engine initializing...');

        Log::info("GenerateContentJob [{$this->jobId}]: starting", [
            'model' => $this->modelClass,
            'count' => $this->count,
        ]);

        try {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $this->modelClass;

            // ── 1. Generate via AI ─────────────────────────────────────────────
            $this->updateProgress('running', stage: 'Calling AI provider (relation-aware)...');

            $result = $service->generate(
                model: $model,
                instruction: $this->instruction,
                count: $this->count,
                options: $this->options,
                causerType: $this->causerType,
                causerId: $this->causerId,
            );

            $records = $result['records'];
            $relationContext = $result['relation_context'];

            if (empty($records)) {
                $this->updateProgress('done', stage: 'Complete — no new records generated', extras: [
                    'inserted' => 0,
                    'skipped' => $result['skipped_dupes'] ?? 0,
                    'images' => 0,
                    'provider' => $result['provider_used'] ?? 'Unknown',
                ]);
                return;
            }

            // ── 2. Persist records one-by-one ──────────────────────────────────
            $this->updateProgress('running', stage: 'Persisting to database...');

            $inserted = 0;
            $imageCount = 0;
            $withImages = $this->options['with_images'] ?? true;
            $errors = [];

            foreach ($records as $index => $record) {
                $pivotData = $record['__pivot__'] ?? [];
                unset($record['__pivot__']);

                try {
                    /** @var \Illuminate\Database\Eloquent\Model $saved */
                    $saved = $this->modelClass::create($record);
                    $inserted++;

                    // ── 3. Sync BelongsToMany relations ──────────────────────────
                    if (!empty($pivotData)) {
                        foreach ($pivotData as $relationName => $relatedIds) {
                            if (method_exists($saved, $relationName) && !empty($relatedIds)) {
                                try {
                                    $saved->{$relationName}()->sync($relatedIds);
                                } catch (\Throwable $e) {
                                    Log::warning("GenerateContentJob: pivot sync failed for {$relationName}", [
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                        }
                    }

                    // ── 4. Attach Spatie media image ──────────────────────────────
                    if ($withImages && method_exists($saved, 'addMediaFromUrl')) {
                        $keyword = $record['name'] ?? $record['title'] ?? $this->instruction;

                        $this->updateProgress('running', stage: "Fetching image: {$keyword}...", extras: [
                            'inserted' => $inserted,
                            'skipped' => $result['skipped_dupes'] ?? 0,
                            'images' => $imageCount,
                            'provider' => $result['provider_used'] ?? 'Unknown',
                        ]);

                        $imageCount += $service->attachImagesToSavedModels($saved, $keyword);
                    }

                    // Live progress update
                    $this->updateProgress('running', stage: "Inserted {$inserted} / {$this->count}...", extras: [
                        'inserted' => $inserted,
                        'skipped' => $result['skipped_dupes'] ?? 0,
                        'images' => $imageCount,
                        'provider' => $result['provider_used'] ?? 'Unknown',
                    ]);

                } catch (\Throwable $e) {
                    $errors[] = [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'keys' => array_keys($record),
                    ];
                }
            }

            // ── 5. Final progress ──────────────────────────────────────────────
            $this->updateProgress('done', stage: "✅ Complete — {$inserted} records added.", extras: [
                'inserted' => $inserted,
                'skipped' => $result['skipped_dupes'] ?? 0,
                'images' => $imageCount,
                'provider' => $result['provider_used'] ?? 'Unknown',
                'duration_ms' => $result['duration_ms'] ?? 0,
                'errors' => $errors,
                'relations_detected' => count($relationContext['belongs_to'] ?? []),
            ]);

        } catch (\Throwable $e) {
            Log::error("GenerateContentJob [{$this->jobId}]: fatal", ['error' => $e->getMessage()]);
            $this->updateProgress('failed', stage: 'Fatal error', error: $e->getMessage());
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->updateProgress('failed', stage: 'Job failed', error: $exception->getMessage());
    }

    private function updateProgress(string $status, ?string $error = null, string $stage = '', array $extras = []): void
    {
        $existing = Cache::get($this->progressKey(), []);

        $payload = array_merge([
            'status' => $status,
            'stage' => $stage,
            'inserted' => $existing['inserted'] ?? 0,
            'skipped' => $existing['skipped'] ?? 0,
            'images' => $existing['images'] ?? 0,
            'provider' => $existing['provider'] ?? '',
            'relations_detected' => $existing['relations_detected'] ?? 0,
            'errors' => $existing['errors'] ?? [],
            'error' => $error,
            'started_at' => $existing['started_at'] ?? time(),
            'finished_at' => in_array($status, ['done', 'failed']) ? time() : null,
        ], $extras);

        Cache::put($this->progressKey(), $payload, self::PROGRESS_TTL);
    }

    private function progressKey(): string
    {
        return "job_progress_{$this->jobId}";
    }

    public static function dispatchWithTracking(string $modelClass, string $instruction, int $count = 10, array $options = [], ?string $causerType = null, ?int $causerId = null): string
    {
        $jobId = (string) \Illuminate\Support\Str::uuid();

        Cache::put("job_progress_{$jobId}", [
            'status' => 'pending',
            'stage' => 'Queued — waiting for worker...',
            'inserted' => 0,
            'skipped' => 0,
            'images' => 0,
            'provider' => '',
            'relations_detected' => 0,
            'errors' => [],
            'error' => null,
            'started_at' => time(),
            'finished_at' => null,
        ], 3600);

        static::dispatch(
            jobId: $jobId,
            modelClass: $modelClass,
            instruction: $instruction,
            count: $count,
            options: $options,
            causerType: $causerType,
            causerId: $causerId,
        );

        return $jobId;
    }

    public static function getProgress(string $jobId): ?array
    {
        return Cache::get("job_progress_{$jobId}");
    }
}