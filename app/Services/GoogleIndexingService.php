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
use Illuminate\Support\Facades\RateLimiter;

class GoogleIndexingService
{
    /**
     * এই নামেই app/Providers/GoogleIndexingServiceProvider-এ RateLimiter::for() ডিফাইন করা আছে।
     * Job middleware আর এই সার্ভিস — দুই জায়গাতেই একই key ব্যবহার হচ্ছে, যাতে কোটার হিসাব
     * সবসময় এক জায়গা থেকেই (single source of truth) আসে, আলাদা Cache counter লাগে না।
     */
    public const RATE_LIMITER_NAME = 'google-indexing';

    public const RATE_LIMITER_BY_KEY = 'google-indexing-api';

    protected ?Indexing $indexingService = null;

    protected ?SearchConsole $searchConsoleService = null;

    protected ?string $serviceAccountEmail = null;

    protected int $dailyQuota;

    public function __construct()
    {
        // GoogleIndexingServiceProvider-এর RateLimiter definition-এর সাথে এই বাফার মিলিয়ে রাখা
        // জরুরি, নাহলে remainingQuota() ভুল সংখ্যা দেখাবে।
        $this->dailyQuota = max(1, (int) config('services.google.indexing_daily_quota', 200) - 10);

        $credentialsPath = config('services.google.indexing_credentials');

        if (! $credentialsPath || ! file_exists(base_path($credentialsPath))) {
            Log::warning('GoogleIndexingService: credentials ফাইল পাওয়া যায়নি।', [
                'path' => $credentialsPath,
            ]);

            return;
        }

        try {
            $jsonContent = json_decode(file_get_contents(base_path($credentialsPath)), true, 512, JSON_THROW_ON_ERROR);
            $this->serviceAccountEmail = $jsonContent['client_email'] ?? null;

            $client = new Client;
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
     * আজকের বাকি কোটা কত — এখন RateLimiter থেকে সরাসরি পড়া হচ্ছে, যেটা IndexUrlJob-এর
     * RateLimited middleware প্রতিটা প্রসেসড অ্যাটেম্পটে hit করে। তাই এখানে আলাদা করে
     * Cache::increment() রাখার দরকার নেই — race condition এর সুযোগও নেই।
     */
    public function remainingQuota(): int
    {
        $key = self::RATE_LIMITER_NAME.':'.self::RATE_LIMITER_BY_KEY;

        return max(0, RateLimiter::remaining($key, $this->dailyQuota));
    }

    public function isIndexed(string $url, string $siteUrl): bool
    {
        if (! $this->searchConsoleService) {
            return false;
        }

        $cacheKey = 'google-indexing:is-indexed:'.md5($siteUrl.'|'.$url);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($url, $siteUrl) {
            try {
                $request = new InspectUrlIndexRequest;
                $request->setInspectionUrl($url);
                $request->setSiteUrl($siteUrl);

                $response = $this->searchConsoleService->urlInspection_index->inspect($request);
                $result = $response->getInspectionResult();

                if (! $result) {
                    return false;
                }

                $verdict = $result->getIndexStatusResult()->getVerdict();

                return in_array($verdict, ['PASS', 'NEUTRAL'], true);
            } catch (GoogleServiceException $e) {
                Log::warning('GoogleIndexingService: isIndexed চেক ব্যর্থ।', [
                    'url' => $url,
                    'code' => $e->getCode(),
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        });
    }

    /**
     * একটি URL গুগলে ইনডেক্সিং-এর জন্য পুশ করে।
     *
     * নোট: এই মেথড শুধুমাত্র IndexUrlJob-এর ভেতর থেকে কল হওয়া উচিত, যাতে জবের
     * RateLimited + WithoutOverlapping middleware প্রকৃত থ্রটলিং handle করতে পারে।
     * নিচের quota-চেক শুধু ডিফেন্সিভ সেফটি-নেট (কেউ যদি সরাসরি সার্ভিস কল করে বসে)।
     *
     * @throws GoogleIndexingQuotaExceededException
     * @throws GoogleIndexingException
     */
    public function indexUrl(string $url, string $type = 'URL_UPDATED'): void
    {
        if (! $this->indexingService) {
            throw new GoogleIndexingException(
                'গুগল এপিআই ক্রেডেনশিয়াল সেটআপ করা নেই।',
                isRetryable: false
            );
        }

        if ($this->remainingQuota() <= 0) {
            throw new GoogleIndexingQuotaExceededException;
        }

        try {
            $notification = new UrlNotification;
            $notification->setUrl($url);
            $notification->setType($type);

            $this->indexingService->urlNotifications->publish($notification);
        } catch (GoogleServiceException $e) {
            $code = (int) $e->getCode();
            $message = $e->getMessage();

            // 429 বা explicit quota message
            if ($code === 429 || str_contains(strtolower($message), 'quota')) {
                throw new GoogleIndexingQuotaExceededException;
            }

            // Ownership / permission সমস্যা সাধারণত permanent
            $isPermanent = in_array($code, [400, 401, 403, 404], true)
                || str_contains(strtolower($message), 'permission')
                || str_contains(strtolower($message), 'ownership')
                || str_contains(strtolower($message), 'not found');

            Log::error('GoogleIndexingService: ইনডেক্সিং ব্যর্থ', [
                'url' => $url,
                'code' => $code,
                'error' => $message,
            ]);

            throw new GoogleIndexingException(
                message: "গুগল এপিআই এরর ({$code}): {$message}",
                isRetryable: ! $isPermanent && $code >= 500,
                previous: $e
            );
        } catch (\Throwable $e) {
            Log::error('GoogleIndexingService: অপ্রত্যাশিত এরর', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new GoogleIndexingException(
                message: 'অপ্রত্যাশিত এরর: '.$e->getMessage(),
                isRetryable: true,
                previous: $e
            );
        }
    }
}
