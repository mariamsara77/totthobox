<?php

namespace App\Jobs;

use App\Enums\IndexingStatus;
use App\Models\IndexedUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * NOTE: এই ফাইলটার আগের ভার্সন শেয়ার করা হয়নি (শুধু reference ছিল), তাই এটা নতুন করে
 * লেখা হয়েছে — বড় CSV-ও মেমোরিতে পুরোটা না নিয়ে স্ট্রিম করে পড়ে, এবং জব গুলোকে
 * ছড়িয়ে dispatch করে যাতে RateLimited middleware-এ একসাথে হাজারটা job গিয়ে জ্যাম না বাঁধায়।
 */
class ImportUrlsFromCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    protected int $batchSize = 200;

    public function __construct(
        public readonly string $storedPath,
    ) {}

    public function handle(): void
    {
        if (! Storage::exists($this->storedPath)) {
            Log::warning('ImportUrlsFromCsvJob: ফাইল খুঁজে পাওয়া যায়নি।', ['path' => $this->storedPath]);

            return;
        }

        $imported = 0;
        $skipped = 0;
        $dispatchIndex = 0;

        try {
            $file = new \SplFileObject(Storage::path($this->storedPath), 'r');
            $file->setFlags(
                \SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE
            );

            $batch = [];

            foreach ($file as $row) {
                if (! is_array($row) || ! isset($row[0])) {
                    continue;
                }

                $url = trim((string) $row[0]);

                if ($url === '' || strtolower($url) === 'url') {
                    continue; // খালি লাইন বা হেডার রো
                }

                if (! filter_var($url, FILTER_VALIDATE_URL)) {
                    $skipped++;

                    continue;
                }

                $batch[] = $url;

                if (count($batch) >= $this->batchSize) {
                    $imported += $this->processBatch($batch, $dispatchIndex);
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                $imported += $this->processBatch($batch, $dispatchIndex);
            }
        } finally {
            Storage::delete($this->storedPath);
        }

        Log::info('ImportUrlsFromCsvJob: ইমপোর্ট শেষ হয়েছে।', [
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }

    /**
     * @param  array<int, string>  $urls
     */
    protected function processBatch(array $urls, int &$dispatchIndex): int
    {
        $now = now();

        $rows = array_map(fn (string $url) => [
            'url' => $url,
            'status' => IndexingStatus::Pending->value,
            'source' => 'csv',
            'created_at' => $now,
            'updated_at' => $now,
        ], $urls);

        // 'url' কলামে unique index থাকা দরকার — আগে থেকে থাকা URL গুলো ছুঁয়ে দেখা হবে না,
        // ফলে ম্যানুয়ালি ইনডেক্স করা বা আগে সফল হওয়া রেকর্ড ভুলবশত রিসেট হবে না।
        IndexedUrl::upsert($rows, uniqueBy: ['url'], update: ['updated_at']);

        $records = IndexedUrl::whereIn('url', $urls)
            ->where('status', IndexingStatus::Pending)
            ->get(['id']);

        foreach ($records as $record) {
            $record->update(['status' => IndexingStatus::Queued]);

            // প্রতিটা জব কয়েক সেকেন্ড করে ছড়িয়ে dispatch হচ্ছে, যাতে হাজার হাজার job
            // একসাথে RateLimited middleware-এ গিয়ে সব একবারে release/retry না করতে থাকে।
            $delaySeconds = intdiv($dispatchIndex, 5); // প্রতি ৫টা জবে ১ সেকেন্ড করে বাড়বে
            IndexUrlJob::dispatch($record->id)->delay(now()->addSeconds($delaySeconds));

            $dispatchIndex++;
        }

        return $records->count();
    }
}
