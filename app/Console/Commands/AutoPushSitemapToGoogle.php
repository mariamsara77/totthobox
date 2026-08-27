<?php

namespace App\Console\Commands;

use App\Enums\IndexingStatus;
use App\Jobs\IndexUrlJob;
use App\Models\IndexedUrl;
use Illuminate\Console\Command;
use SimpleXMLElement;

class AutoPushSitemapToGoogle extends Command
{
    protected $signature = 'google:auto-index-sitemap
        {--path= : sitemap.xml এর কাস্টম পাথ (ডিফল্ট: public/sitemap.xml)}
        {--fresh : status=success থাকা URL গুলোও আবার পুশ করার জন্য কিউ করবে}';

    protected $description = 'সাইটম্যাপ পার্স করে নতুন/পরিবর্তিত URL গুলো Google Indexing API-তে কিউ করে পাঠায়';

    public function handle(): int
    {
        $path = $this->option('path') ?: public_path('sitemap.xml');

        if (!file_exists($path)) {
            $this->error("সাইটম্যাপ ফাইল পাওয়া যায়নি: {$path}");

            return Command::FAILURE;
        }

        $urls = $this->extractUrls($path);

        if (empty($urls)) {
            $this->warn('সাইটম্যাপে কোনো URL পাওয়া যায়নি।');

            return Command::SUCCESS;
        }

        $this->info(sprintf('সাইটম্যাপে %d টি URL পাওয়া গেছে। প্রসেসিং শুরু হচ্ছে...', count($urls)));

        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        $queued = 0;
        $delay = 0;

        foreach ($urls as $url) {
            $record = IndexedUrl::firstOrNew(['url' => $url]);
            $record->source = $record->exists ? $record->source : 'sitemap';

            $alreadyDone = $record->exists
                && $record->status === IndexingStatus::Success
                && !$this->option('fresh');

            if ($alreadyDone) {
                $bar->advance();

                continue;
            }

            $record->status = IndexingStatus::Queued;
            $record->save();

            // Google-এর প্রতি-সেকেন্ড রেট লিমিট এড়াতে জবগুলো স্ট্যাগার করে ডিসপ্যাচ করা হচ্ছে
            IndexUrlJob::dispatch($record->id)->delay(now()->addSeconds($delay));
            $delay += 2;
            $queued++;

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info("{$queued} টি URL কিউ করা হয়েছে। ব্যাকগ্রাউন্ড কিউ ওয়ার্কারে প্রসেস হবে (php artisan queue:work)।");

        return Command::SUCCESS;
    }

    /**
     * সাধারণ sitemap.xml এবং sitemap index (নেস্টেড sitemap) — উভয় ফরম্যাট সাপোর্ট করে।
     *
     * @return array<int, string>
     */
    protected function extractUrls(string $path): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($path);

        if ($xml === false) {
            $this->error('সাইটম্যাপ পার্স করতে ব্যর্থ, ফাইলটি valid XML কিনা যাচাই করুন।');

            return [];
        }

        // এটা একটা sitemap index ফাইল (একাধিক sitemap-এর লিংক ধারণ করে)
        if ($xml->getName() === 'sitemapindex') {
            $urls = [];

            foreach ($xml->sitemap as $sitemapTag) {
                $childPath = (string) $sitemapTag->loc;
                $childXml = @simplexml_load_string(@file_get_contents($childPath));

                if ($childXml !== false) {
                    foreach ($childXml->url as $urlTag) {
                        $urls[] = (string) $urlTag->loc;
                    }
                }
            }

            return array_values(array_unique($urls));
        }

        $urls = [];
        foreach ($xml->url as $urlTag) {
            $urls[] = (string) $urlTag->loc;
        }

        return array_values(array_unique($urls));
    }
}