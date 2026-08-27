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

    // সর্বোচ্চ ৩ বার রিট্রাই করবে (অনন্তকাল লুপ হওয়া বন্ধ করতে)
    public int $tries = 3;

    public function __construct(
        public readonly int $indexedUrlId,
    ) {}

    /**
     * ট্রানজিয়েন্ট এরর হলে exponential backoff দিয়ে রিট্রাই হবে (২ মিনিট, ১০ মিনিট)
     */
    public function backoff(): array
    {
        return [120, 600];
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

            // release() এর বদলে নতুন জব delay দিয়ে dispatch করুন
            self::dispatch($this->indexedUrlId)->delay(
                now()->addDay()->startOfDay()
            );

            return; // বর্তমান জবটি শেষ হবে, attempt কাউন্ট আর বাড়বে না
        } catch (GoogleIndexingException $e) {
            $record->update([
                'status' => IndexingStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            // যদি রিট্রাই করার মতো এরর হয় এবং এখনও চেষ্টা করার সুযোগ থাকে
            if ($e->isRetryable && $this->attempts() < $this->tries) {
                $delay = $this->backoff()[$this->attempts() - 1] ?? 600;
                $this->release($delay);

                return;
            }

            // অন্যথায় জবটি ফেইল হিসেবে মার্ক হবে এবং কিউ থেকে বিদায় নেবে
            $this->fail($e);
        }
    }
}
