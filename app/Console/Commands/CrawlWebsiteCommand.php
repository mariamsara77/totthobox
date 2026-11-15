<?php

namespace App\Console\Commands;

use App\Jobs\CrawlPageJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrawlWebsiteCommand extends Command
{
    protected $signature = 'website:crawl 
                            {--force : Force re-crawl all pages even if recently crawled}
                            {--url= : Crawl only a specific URL}';

    protected $description = 'Crawl totthobox.com from sitemap.xml and store page data for AI knowledge base';

    public function handle(): int
    {
        $specificUrl = $this->option('url');

        // শুধু একটা URL ক্রল করতে চাইলে
        if ($specificUrl) {
            $this->info("Dispatching single URL: {$specificUrl}");
            CrawlPageJob::dispatch($specificUrl);
            $this->info('Job dispatched successfully.');
            return self::SUCCESS;
        }

        $this->info('Fetching sitemap...');

        $sitemapUrl = 'https://totthobox.com/sitemap.xml';

        try {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'TotthoBoxBot/1.0'])
                ->get($sitemapUrl);

            if (!$response->successful()) {
                $this->error("Failed to fetch sitemap. HTTP Status: {$response->status()}");
                return self::FAILURE;
            }

            $xml = simplexml_load_string($response->body());

            if ($xml === false) {
                $this->error('Invalid sitemap XML');
                return self::FAILURE;
            }

            $urls = $this->extractUrls($xml);

            $urls = array_unique($urls);
            $total = count($urls);

            $this->info("Total unique URLs found: {$total}");

            if ($total === 0) {
                $this->warn('No URLs found in sitemap.');
                return self::SUCCESS;
            }

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $dispatched = 0;

            foreach ($urls as $url) {
                // অপ্রয়োজনীয় URL বাদ দিতে চাইলে এখানে ফিল্টার করতে পারেন
                // উদাহরণ: if (Str::contains($url, '/tag/')) continue;

                CrawlPageJob::dispatch($url);
                $dispatched++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("Successfully dispatched {$dispatched} jobs to [crawler] queue.");
            $this->info('Run queue worker: php artisan queue:work redis --queue=crawler --tries=3 --timeout=60');

            Log::info('Website crawl command completed', [
                'total_urls' => $total,
                'dispatched' => $dispatched,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('CrawlWebsiteCommand failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Sitemap থেকে সব URL বের করে
     * Sitemap Index এবং সাধারণ Sitemap দুটোই সাপোর্ট করে
     */
    private function extractUrls(\SimpleXMLElement $xml): array
    {
        $urls = [];

        // Sitemap Index কিনা চেক (অনেক সাইটে একাধিক sitemap থাকে)
        if (isset($xml->sitemap)) {
            $this->info('Sitemap Index detected. Fetching sub-sitemaps...');

            foreach ($xml->sitemap as $sitemap) {
                $subSitemapUrl = (string) $sitemap->loc;

                try {
                    $subResponse = Http::timeout(20)->get($subSitemapUrl);

                    if ($subResponse->successful()) {
                        $subXml = simplexml_load_string($subResponse->body());

                        if ($subXml && isset($subXml->url)) {
                            foreach ($subXml->url as $urlNode) {
                                $urls[] = (string) $urlNode->loc;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $this->warn("Failed to fetch sub-sitemap: {$subSitemapUrl}");
                    Log::warning('Sub-sitemap fetch failed', ['url' => $subSitemapUrl]);
                }
            }
        }
        // সাধারণ Sitemap
        elseif (isset($xml->url)) {
            foreach ($xml->url as $urlNode) {
                $urls[] = (string) $urlNode->loc;
            }
        }

        return $urls;
    }
}