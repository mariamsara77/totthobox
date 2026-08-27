<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleIndexingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class AutoPushSitemapToGoogle extends Command
{
    // টার্মিনালে রান করার কমান্ড নেম
    protected $signature = 'google:auto-index-sitemap';
    protected $description = 'Parse sitemap and automatically push updated URLs to Google Indexing API';

    public function handle(GoogleIndexingService $indexingService): int
{
    $this->info('Starting full sitemap indexing process...');
    
    // পাবলিক ডিরেক্টরি থেকে সরাসরি sitemap.xml রিড করা
    $sitemapPath = public_path('sitemap.xml'); 

    if (!file_exists($sitemapPath)) {
        $this->error('Sitemap file not found at: ' . $sitemapPath);
        Log::error('Google Auto-Indexing: sitemap.xml file missing.');
        return Command::FAILURE;
    }

    try {
        $sitemapContent = file_get_contents($sitemapPath);
        $sitemap = new SimpleXMLElement($sitemapContent);
        $pushedCount = 0;

        $this->info('Parsing all URLs from sitemap...');

        // কোনো কন্ডিশন ছাড়া সাইটম্যাপের প্রতিটি ইউআরএল লুপ করে গুগলে পুশ করা হচ্ছে
        foreach ($sitemap->url as $urlTag) {
            $url = (string) $urlTag->loc;

            $this->info("Pushing to Google: {$url}");
            
            try {
                // আপনার এক্সিস্টিং সার্ভিস দিয়ে গুগলে পুশ
                $indexingService->indexUrl($url);
                $pushedCount++;
                
                // গুগল এপিআই ওভারলোড এড়াতে প্রতি পুশের মাঝে ১ সেকেন্ড গ্যাপ
                sleep(1); 
            } catch (\Exception $e) {
                $this->warn("Failed to push {$url}: " . $e->getMessage());
                // কোনো একটা লিংকে এরর আসলেও লুপ যেন ভেঙে না যায়, তাই কন্টিনিউ করা হলো
                continue; 
            }
        }

        $this->info("Successfully pushed all {$pushedCount} URLs to Google Indexing API.");
        Log::info("Google Full Auto-Indexing Complete: Pushed {$pushedCount} URLs.");
        
        return Command::SUCCESS;

    } catch (\Exception $e) {
        $this->error('Error during full indexing: ' . $e->getMessage());
        Log::error('Google Full Auto-Indexing Error: ' . $e->getMessage());
        return Command::FAILURE;
    }
}
}