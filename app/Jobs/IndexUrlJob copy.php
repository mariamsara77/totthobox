<?php

namespace App\Jobs;

use App\Enums\IndexingStatus;
use App\Exceptions\GoogleIndexingException;
use App\Exceptions\GoogleIndexingQuotaExceededException;
use App\Models\IndexedUrl;
use App\Services\GoogleIndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $indexedUrlId,
    ) {}

    /**
     * ট্রানজিয়েন্ট এরর হলে exponential backoff দিয়ে রিট্রাই হবে (২ মিনিট, ১০ মিনিট, ৩০ মিনিট)
     */
    public function backoff(): array
    {
        return [120, 600, 1800];
    }

    public function handle(GoogleIndexingService $indexingService): void
    {
        $record = IndexedUrl::find($this->indexedUrlId);

        if (! $record) {
            return;
        }

        try {
            $indexingService->indexUrl($record->url);

            $record->update([
                'status' => IndexingStatus::Success,
                'is_indexed' => true,
                'last_pushed_at' => now(),
                'last_crawled' => now(),
                'error_message' => null,
            ]);
        } catch (GoogleIndexingQuotaExceededException $e) {
            $record->update([
                'status' => IndexingStatus::QuotaExceeded,
                'error_message' => $e->getMessage(),
            ]);

            // আজকের কোটা শেষ, তাই আগামীকাল সকালে আবার চেষ্টা করার জন্য রিলিজ করা হচ্ছে
            $this->release(now()->addDay()->startOfDay()->diffInSeconds(now()));
        } catch (GoogleIndexingException $e) {
            $record->update([
                'status' => IndexingStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            if ($e->isRetryable && $this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 1800);

                return;
            }

            $this->fail($e);
        }
    }
}
