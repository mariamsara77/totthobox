<?php

namespace App\Jobs;

use App\Enums\IndexingStatus;
use App\Exceptions\GoogleIndexingException;
use App\Exceptions\GoogleIndexingQuotaExceededException;
use App\Models\IndexedUrl;
use App\Services\GoogleIndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class IndexUrlJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Transient error-এর জন্য যথেষ্ট tries। Quota-এর জন্য আর tries পোড়াবে না। */
    public int $tries = 5;

    public int $maxExceptions = 3;

    public int $timeout = 45;

    /** একই URL ১ ঘণ্টা পর্যন্ত ডুপ্লিকেট dispatch হবে না */
    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $indexedUrlId,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->indexedUrlId;
    }

    public function middleware(): array
    {
        return [
            new RateLimited(GoogleIndexingService::RATE_LIMITER_NAME),
            (new WithoutOverlapping((string) $this->indexedUrlId))->expireAfter(180),
        ];
    }

    /** Transient error-এর জন্য exponential + jitter */
    public function backoff(): array
    {
        return [90, 300, 900, 1800, 3600];
    }

    public function handle(GoogleIndexingService $indexingService): void
    {
        $record = IndexedUrl::find($this->indexedUrlId);

        // Record মুছে গেলে বা soft-deleted হলে চুপচাপ শেষ
        if (! $record) {
            return;
        }

        // ইতিমধ্যে Success হলে আর কোটা নষ্ট করব না (re-push চাইলে Livewire থেকে আলাদা করে করুন)
        if ($record->status === IndexingStatus::Success && $record->is_indexed) {
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

            Log::info('IndexUrlJob: সফলভাবে ইনডেক্স হয়েছে', [
                'indexed_url_id' => $this->indexedUrlId,
                'url' => $record->url,
            ]);
        } catch (GoogleIndexingQuotaExceededException $e) {
            // ★ গুরুত্বপূর্ণ পরিবর্তন:
            // আর release() করছি না → tries পোড়াবে না।
            // Status = QuotaExceeded রেখে Job সফলভাবে শেষ করছি।
            // Daily scheduler পরের দিন আবার dispatch করবে।
            $record->update([
                'status' => IndexingStatus::QuotaExceeded,
                'error_message' => $e->getMessage(),
            ]);

            Log::warning('IndexUrlJob: কোটা শেষ, পরের দিন অটো-রিট্রাই হবে', [
                'indexed_url_id' => $this->indexedUrlId,
                'url' => $record->url,
            ]);

            // Job successfully completed (no release, no fail)
            return;
        } catch (GoogleIndexingException $e) {
            $record->update([
                'status' => IndexingStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            // শুধুমাত্র retryable error হলে backoff দিয়ে আবার চেষ্টা
            if ($e->isRetryable && $this->attempts() < $this->tries) {
                $delay = $this->backoff()[$this->attempts() - 1] ?? 3600;

                Log::warning('IndexUrlJob: রিট্রায়েবল এরর, পরে আবার চেষ্টা হবে', [
                    'indexed_url_id' => $this->indexedUrlId,
                    'attempt' => $this->attempts(),
                    'delay' => $delay,
                    'error' => $e->getMessage(),
                ]);

                $this->release($delay);

                return;
            }

            // Permanent failure
            $this->fail($e);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $record = IndexedUrl::find($this->indexedUrlId);

        // ইতিমধ্যে Success বা QuotaExceeded থাকলে আর ওভাররাইট করব না
        if ($record && in_array($record->status, [
            IndexingStatus::Success,
            IndexingStatus::QuotaExceeded,
        ], true)) {
            return;
        }

        $record?->update([
            'status' => IndexingStatus::Failed,
            'error_message' => $exception?->getMessage() ?? 'অজানা এরর, জব চূড়ান্তভাবে ফেইল হয়েছে।',
        ]);

        Log::error('IndexUrlJob: চূড়ান্তভাবে ফেইল হয়েছে', [
            'indexed_url_id' => $this->indexedUrlId,
            'exception' => $exception ? get_class($exception) : null,
            'error' => $exception?->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
