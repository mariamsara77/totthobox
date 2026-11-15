<?php

namespace App\Jobs;

use App\Models\WebsitePage;
use App\Services\WebsiteCrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrawlPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * কতবার চেষ্টা করবে
     */
    public int $tries = 3;

    /**
     * ব্যর্থ হলে কত সেকেন্ড পর আবার চেষ্টা করবে
     */
    public array $backoff = [20, 60, 120];

    /**
     * Timeout (সেকেন্ড)
     */
    public int $timeout = 60;

    public function __construct(
        public string $url
    ) {
        // আলাদা কিউতে পাঠানো ভালো (অন্য জবের সাথে মিক্স হবে না)
        $this->onQueue('crawler');
    }

    public function handle(WebsiteCrawlerService $crawler): void
    {
        try {
            $data = $crawler->crawl($this->url);

            // কনটেন্ট না পেলে স্কিপ করুন
            if (empty($data['content']) && empty($data['title'])) {
                Log::warning("Empty content, skipped: {$this->url}");
                return;
            }

            $contentHash = md5($data['content'] ?? '');

            // আগের রেকর্ড আছে কিনা দেখুন
            $existing = WebsitePage::where('url', $this->url)->first();

            // কনটেন্ট একই থাকলে শুধু last_crawled_at আপডেট করুন (অপ্রয়োজনীয় write কমাতে)
            if ($existing && $existing->content_hash === $contentHash) {
                $existing->update([
                    'last_crawled_at' => now(),
                ]);

                Log::info("Unchanged, timestamp updated: {$this->url}");
                return;
            }

            // নতুন বা পরিবর্তিত পেজ সেভ করুন
            WebsitePage::updateOrCreate(
                ['url' => $this->url],
                [
                    'title'           => $data['title'],
                    'content'         => $data['content'],
                    'images'          => $data['images'],
                    'meta'            => $data['meta'],
                    'content_hash'    => $contentHash,
                    'last_crawled_at' => now(),
                    'is_indexed'      => false, // পরে Scout/Meilisearch এ সিঙ্ক করার সময় true হবে
                ]
            );

            Log::info("Successfully crawled & saved: {$this->url}");
        } catch (\Throwable $e) {
            Log::error("CrawlPageJob failed", [
                'url'     => $this->url,
                'message' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // আবার চেষ্টা করার জন্য exception থ্রো করুন
            throw $e;
        }
    }

    /**
     * সব চেষ্টা ব্যর্থ হলে
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("CrawlPageJob permanently failed", [
            'url'     => $this->url,
            'message' => $exception->getMessage(),
        ]);
    }
}