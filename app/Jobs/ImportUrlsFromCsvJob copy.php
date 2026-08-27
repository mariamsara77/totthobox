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

class ImportUrlsFromCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(
        public readonly string $storedPath, // Storage::disk('local') এ রাখা টেম্প ফাইলের পাথ
        public readonly ?int $notifyUserId = null,
    ) {
    }

    public function handle(): void
    {
        $fullPath = Storage::path($this->storedPath);

        if (!file_exists($fullPath)) {
            Log::error('ImportUrlsFromCsvJob: ফাইল পাওয়া যায়নি।', ['path' => $this->storedPath]);

            return;
        }

        $handle = fopen($fullPath, 'r');
        $header = fgetcsv($handle); // প্রথম লাইন (header) স্কিপ

        $imported = 0;
        $skipped = 0;
        $dispatchDelaySeconds = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $url = trim($row[0] ?? '');

            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                $skipped++;
                continue;
            }

            $record = IndexedUrl::updateOrCreate(
                ['url' => $url],
                [
                    'source' => 'csv',
                    'status' => IndexingStatus::Queued,
                ]
            );

            // Google Indexing API-তে সেকেন্ডে অনেকগুলো রিকোয়েস্ট না পাঠিয়ে সামান্য স্ট্যাগার করা হচ্ছে
            IndexUrlJob::dispatch($record->id)->delay(now()->addSeconds($dispatchDelaySeconds));
            $dispatchDelaySeconds += 2;

            $imported++;
        }

        fclose($handle);
        Storage::delete($this->storedPath);

        Log::info('ImportUrlsFromCsvJob সম্পন্ন হয়েছে।', [
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }
}