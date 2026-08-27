<?php

namespace App\Services;

use Google\Client;
use Google\Service\Indexing;
use Google\Service\Indexing\UrlNotification;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleIndexingService
{
    protected ?Indexing $indexingService = null;
    protected ?string $serviceAccountEmail = null;

    public function __construct()
    {
        // env() এর বদলে config() ব্যবহার করা নিরাপদ
        $credentialsPath = config('services.google.indexing_credentials');

        if ($credentialsPath && file_exists(base_path($credentialsPath))) {
            $jsonContent = json_decode(file_get_contents(base_path($credentialsPath)), true);
            $this->serviceAccountEmail = $jsonContent['client_email'] ?? null;

            $client = new Client();
            $client->setAuthConfig(base_path($credentialsPath));
            $client->addScope('https://www.googleapis.com/auth/indexing');

            $this->indexingService = new Indexing($client);
        } else {
            Log::warning("Google Indexing: Credentials file missing or path not set.");
        }
    }

    public function indexUrl(string $url): array
    {
        if (!$this->indexingService) {
            throw new Exception("গুগল এপিআই ক্রেডেনশিয়াল বা কনফিগ ফাইল সেটআপ করা নেই।");
        }

        try {
            $urlNotification = new UrlNotification();
            $urlNotification->setUrl($url);
            $urlNotification->setType('URL_UPDATED');

            $response = $this->indexingService->urlNotifications->publish($urlNotification);
            $urlNotificationMetadata = $response->getUrlNotificationMetadata();
            $latestNotify = $urlNotificationMetadata ? $urlNotificationMetadata->getLatestUpdate() : null;

            return [
                'success' => true,
                'url' => $latestNotify?->getUrl() ?? $url,
                'type' => $latestNotify?->getType() ?? 'URL_UPDATED',
                'time' => $latestNotify?->getNotifyTime() ? date('Y-m-d H:i:s', strtotime($latestNotify->getNotifyTime())) : now()->toDateTimeString(),
            ];

        } catch (Exception $e) {
            Log::error("Google Indexing Failed", ['url' => $url, 'error' => $e->getMessage()]);

            if (str_contains($e->getMessage(), 'Failed to verify the URL ownership') || str_contains($e->getMessage(), '403')) {
                throw new Exception(
                    "ওনারশিপ ভেরিফিকেশন ফেইলড! অনুগ্রহ করে আপনার গুগল সার্চ কনসোলে (Google Search Console) গিয়ে এই সার্ভিস ইমেইলটি: **" . ($this->serviceAccountEmail ?? 'Service Account Email') . "** কে 'Owner' পারমিশন দিয়ে যুক্ত করুন।"
                );
            }

            throw new Exception("গুগল এপিআই এরর: " . $e->getMessage());
        }
    }
}