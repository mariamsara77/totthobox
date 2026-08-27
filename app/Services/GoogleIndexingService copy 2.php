<?php

namespace App\Services;

use App\Exceptions\GoogleIndexingException;
use App\Exceptions\GoogleIndexingQuotaExceededException;
use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Indexing;
use Google\Service\Indexing\UrlNotification;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\InspectUrlIndexRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleIndexingService
{
    protected ?Indexing $indexingService = null;
    protected ?SearchConsole $searchConsoleService = null;
    protected ?string $serviceAccountEmail = null;

    /** Google-এর Indexing API-র ডিফল্ট দৈনিক কোটা প্রতি প্রজেক্টে ২০০ রিকোয়েস্ট */
    protected int $dailyQuota;

    public function __construct()
    {
        $this->dailyQuota = (int) config('services.google.indexing_daily_quota', 200);

        $credentialsPath = config('services.google.indexing_credentials');

        if (!$credentialsPath || !file_exists(base_path($credentialsPath))) {
            Log::warning('GoogleIndexingService: credentials ফাইল পাওয়া যায়নি।', [
                'path' => $credentialsPath,
            ]);

            return;
        }

        try {
            $jsonContent = json_decode(file_get_contents(base_path($credentialsPath)), true, 512, JSON_THROW_ON_ERROR);
            $this->serviceAccountEmail = $jsonContent['client_email'] ?? null;

            $client = new Client();
            $client->setAuthConfig(base_path($credentialsPath));
            $client->setHttpClient(new \GuzzleHttp\Client(['timeout' => 15]));
            $client->addScope([
                Indexing::INDEXING,
                SearchConsole::WEBMASTERS_READONLY,
            ]);

            $this->indexingService = new Indexing($client);
            $this->searchConsoleService = new SearchConsole($client);
        } catch (\Throwable $e) {
            Log::error('GoogleIndexingService: credentials লোড করতে ব্যর্থ।', ['error' => $e->getMessage()]);
        }
    }

    public function isConfigured(): bool
    {
        return $this->indexingService !== null;
    }

    /**
     * আজকের বাকি কোটা কত, সেটা জানায়।
     */
    public function remainingQuota(): int
    {
        $used = (int) Cache::get($this->quotaCacheKey(), 0);

        return max(0, $this->dailyQuota - $used);
    }

    public function isIndexed(string $url, string $siteUrl): bool
    {
        if (!$this->searchConsoleService) {
            return false;
        }

        try {
            $request = new InspectUrlIndexRequest();
            $request->setInspectionUrl($url);
            $request->setSiteUrl($siteUrl);

            $response = $this->searchConsoleService->urlInspection_index->inspect($request);
            $result = $response->getInspectionResult();

            if (!$result) {
                return false;
            }

            $verdict = $result->getIndexStatusResult()->getVerdict();

            return in_array($verdict, ['PASS', 'NEUTRAL'], true);
        } catch (GoogleServiceException $e) {
            // 429 রেট-লিমিটে আগের মতো সাইলেন্টলি true না ধরে, স্পষ্টভাবে লগ করে false ফেরত দিচ্ছি
            Log::warning('GoogleIndexingService: isIndexed চেক ব্যর্থ।', [
                'url' => $url,
                'code' => $e->getCode(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * একটি URL গুগলে ইনডেক্সিং-এর জন্য পুশ করে।
     *
     * @throws GoogleIndexingQuotaExceededException
     * @throws GoogleIndexingException
     */
    public function indexUrl(string $url, string $type = 'URL_UPDATED'): void
    {
        if (!$this->indexingService) {
            throw new GoogleIndexingException(
                'গুগল এপিআই ক্রেডেনশিয়াল সেটআপ করা নেই।',
                isRetryable: false
            );
        }

        if ($this->remainingQuota() <= 0) {
            throw new GoogleIndexingQuotaExceededException();
        }

        try {
            $notification = new UrlNotification();
            $notification->setUrl($url);
            $notification->setType($type);

            $this->indexingService->urlNotifications->publish($notification);

            $this->incrementQuotaUsage();
        } catch (GoogleServiceException $e) {
            $code = $e->getCode();

            // 429 = কোটা শেষ, 5xx = সাময়িক সমস্যা (retryable), 4xx বাকিগুলো সাধারণত permanent
            if ($code === 429) {
                throw new GoogleIndexingQuotaExceededException();
            }

            Log::error('GoogleIndexingService: ইনডেক্সিং ব্যর্থ।', [
                'url' => $url,
                'code' => $code,
                'error' => $e->getMessage(),
            ]);

            throw new GoogleIndexingException(
                message: "গুগল এপিআই এরর ({$code}): " . $e->getMessage(),
                isRetryable: $code >= 500,
                previous: $e
            );
        } catch (\Throwable $e) {
            Log::error('GoogleIndexingService: অপ্রত্যাশিত এরর।', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new GoogleIndexingException(
                message: 'অপ্রত্যাশিত এরর: ' . $e->getMessage(),
                isRetryable: true,
                previous: $e
            );
        }
    }

    protected function incrementQuotaUsage(): void
    {
        $key = $this->quotaCacheKey();

        if (!Cache::has($key)) {
            Cache::put($key, 0, now()->endOfDay());
        }

        Cache::increment($key);
    }

    protected function quotaCacheKey(): string
    {
        return 'google-indexing:quota:' . now()->toDateString();
    }
}