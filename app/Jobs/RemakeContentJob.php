<?php

namespace App\Jobs;

use App\Services\ContentGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║          RemakeContentJob v3 — Bengali Rewrite Worker                    ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
class RemakeContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public readonly string $jobId,
        public readonly string $modelClass,
        public readonly array $ids = [],
        public readonly int $batchSize = 5,
    ) {
        $this->onQueue('ai-generation');
    }

    public function handle(ContentGenerationService $service): void
    {
        $this->updateProgress('running', 'শুরু হচ্ছে...');

        try {
            $model = new $this->modelClass;
            $result = $service->remakeExisting(
                model: $model,
                ids: $this->ids,
                batchSize: $this->batchSize,
            );

            $this->updateProgress('done', "সম্পন্ন — {$result['updated']} টি record আপডেট হয়েছে।", $result);
            Log::info("RemakeContentJob [{$this->jobId}]: done", $result);

        } catch (\Throwable $e) {
            $this->updateProgress('failed', $e->getMessage());
            Log::error("RemakeContentJob [{$this->jobId}]: failed", ['error' => $e->getMessage()]);
            $this->fail($e);
        }
    }

    private function updateProgress(string $status, string $stage, array $extras = []): void
    {
        Cache::put("job_progress_{$this->jobId}", array_merge([
            'status' => $status,
            'stage' => $stage,
            'inserted' => $extras['updated'] ?? 0,
            'skipped' => $extras['skipped'] ?? 0,
            'images' => 0,
            'provider' => '',
            'started_at' => time(),
            'finished_at' => in_array($status, ['done', 'failed']) ? time() : null,
            'error' => $status === 'failed' ? $stage : null,
        ], $extras), 3600);
    }

    public static function dispatchWithTracking(
        string $modelClass,
        array $ids = [],
        int $batchSize = 5,
    ): string {
        $jobId = (string) \Illuminate\Support\Str::uuid();

        Cache::put("job_progress_{$jobId}", [
            'status' => 'pending',
            'stage' => 'Queue-এ আছে...',
            'inserted' => 0,
            'skipped' => 0,
            'images' => 0,
            'provider' => '',
            'started_at' => time(),
            'finished_at' => null,
            'error' => null,
        ], 3600);

        static::dispatch(
            jobId: $jobId,
            modelClass: $modelClass,
            ids: $ids,
            batchSize: $batchSize,
        );

        return $jobId;
    }
}