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
use RuntimeException;
use Throwable;

class GoogleIndexingService
{
    public const RATE_LIMITER_NAME = 'google-indexing';

    public const RATE_LIMITER_BY_KEY = 'google-indexing-api';

    protected ?Indexing $indexingService = null;

    protected ?SearchConsole $searchConsoleService = null;

    protected ?string $serviceAccountEmail = null;

    protected int $dailyQuota;

    public function __construct()
    {
        // Keep a safety buffer below the configured Google daily quota.
        $this->dailyQuota = max(
            1,
            (int) config('services.google.indexing_daily_quota', 200) - 10
        );

        $this->loadGoogleCredentials();
    }

    /**
     * Load and initialize Google API credentials.
     */
    private function loadGoogleCredentials(): void
    {
        $credentialsPath =
            config('services.google.indexing_credentials')
            ?: config('services.google.indexing_key');

        if (! $credentialsPath) {
            Log::warning(
                'Google Indexing: credentials path is not configured.'
            );

            return;
        }

        $credentialsPath = (string) $credentialsPath;

        // Support both absolute and Laravel-relative paths.
        if (! str_starts_with($credentialsPath, DIRECTORY_SEPARATOR)) {
            $credentialsPath = base_path($credentialsPath);
        }

        if (
            ! is_file($credentialsPath) ||
            ! is_readable($credentialsPath)
        ) {
            Log::warning(
                'Google Indexing: credentials file not found.',
                [
                    'path' => $credentialsPath,
                ]
            );

            return;
        }

        try {
            $credentialsContent = file_get_contents(
                $credentialsPath
            );

            if ($credentialsContent === false) {
                throw new RuntimeException(
                    'Unable to read Google credentials.'
                );
            }

            $credentials = json_decode(
                $credentialsContent,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (
                ! is_array($credentials) ||
                empty($credentials['client_email']) ||
                empty($credentials['private_key']) ||
                ($credentials['type'] ?? null) !== 'service_account'
            ) {
                throw new RuntimeException(
                    'Invalid Google service account credentials.'
                );
            }

            $this->serviceAccountEmail =
                $credentials['client_email'];

            $client = new Client();

            $client->setAuthConfig($credentialsPath);

            $client->setHttpClient(
                new \GuzzleHttp\Client([
                    'timeout' => 20,
                    'connect_timeout' => 10,
                ])
            );

            $client->addScope([
                Indexing::INDEXING,
                SearchConsole::WEBMASTERS_READONLY,
            ]);

            $this->indexingService = new Indexing($client);

            $this->searchConsoleService = new SearchConsole($client);
        } catch (Throwable $e) {
            $this->indexingService = null;
            $this->searchConsoleService = null;

            Log::error(
                'Google Indexing: credential initialization failed.',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Check whether Google API is configured.
     */
    public function isConfigured(): bool
    {
        return $this->indexingService !== null;
    }

    /**
     * Return configured service account email.
     */
    public function getServiceAccountEmail(): ?string
    {
        return $this->serviceAccountEmail;
    }

    /**
     * Return remaining daily indexing quota.
     *
     * Uses the same rate limiter key as the existing
     * IndexUrlJob middleware.
     */
    public function remainingQuota(): int
    {
        $key = self::RATE_LIMITER_NAME . ':' .
            self::RATE_LIMITER_BY_KEY;

        return max(
            0,
            RateLimiter::remaining(
                $key,
                $this->dailyQuota
            )
        );
    }

    /**
     * Check Google Search Console indexing status.
     */
    public function isIndexed(
        string $url,
        string $siteUrl
    ): bool {
        if (! $this->searchConsoleService) {
            return false;
        }

        $cacheKey = 'google-indexing:is-indexed:' .
            md5($siteUrl . '|' . $url);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use ($url, $siteUrl) {
                try {
                    $request = new InspectUrlIndexRequest();

                    $request->setInspectionUrl($url);

                    $request->setSiteUrl($siteUrl);

                    $response = $this->searchConsoleService
                        ->urlInspection_index
                        ->inspect($request);

                    $result = $response->getInspectionResult();

                    if (! $result) {
                        return false;
                    }

                    $statusResult = $result->getIndexStatusResult();

                    if (! $statusResult) {
                        return false;
                    }

                    $verdict = $statusResult->getVerdict();

                    return in_array(
                        $verdict,
                        ['PASS', 'NEUTRAL'],
                        true
                    );
                } catch (GoogleServiceException $e) {
                    Log::warning(
                        'Google Search Console inspection failed.',
                        [
                            'url' => $url,
                            'site_url' => $siteUrl,
                            'code' => $e->getCode(),
                            'error' => $e->getMessage(),
                        ]
                    );

                    return false;
                } catch (Throwable $e) {
                    Log::error(
                        'Google indexing status check failed.',
                        [
                            'url' => $url,
                            'error' => $e->getMessage(),
                        ]
                    );

                    return false;
                }
            }
        );
    }

    /**
     * Submit a URL to Google Indexing API.
     *
     * Supported notification types:
     * URL_UPDATED
     * URL_DELETED
     *
     * Normally this method should be called through IndexUrlJob,
     * where the existing queue middleware controls API rate limits.
     *
     * @throws GoogleIndexingException
     * @throws GoogleIndexingQuotaExceededException
     */
    public function indexUrl(
        string $url,
        string $type = 'URL_UPDATED'
    ): void {
        if (! $this->indexingService) {
            throw new GoogleIndexingException(
                'Google Indexing API credentials are not configured.',
                isRetryable: false
            );
        }

        // Validate supported notification types.
        if (! in_array(
            $type,
            ['URL_UPDATED', 'URL_DELETED'],
            true
        )) {
            throw new GoogleIndexingException(
                'Unsupported Google Indexing notification type.',
                isRetryable: false
            );
        }

        // Validate URL format.
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new GoogleIndexingException(
                'Invalid URL supplied to Google Indexing API.',
                isRetryable: false
            );
        }

        $parts = parse_url($url);

        if (
            ! is_array($parts) ||
            strtolower($parts['scheme'] ?? '') !== 'https' ||
            empty($parts['host']) ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            (isset($parts['port']) && (int) $parts['port'] !== 443)
        ) {
            throw new GoogleIndexingException(
                'Only valid public HTTPS URLs are allowed.',
                isRetryable: false
            );
        }

        // Only allow explicitly configured public domains.
        $allowedHosts = config(
            'services.google.indexing_allowed_hosts',
            []
        );

        if (is_string($allowedHosts)) {
            $allowedHosts = explode(',', $allowedHosts);
        }

        $allowedHosts = array_map(
            fn($host) => strtolower(
                rtrim(trim((string) $host), '.')
            ),
            is_array($allowedHosts) ? $allowedHosts : []
        );

        $host = strtolower(rtrim($parts['host'], '.'));

        if (
            ! empty($allowedHosts) &&
            ! in_array($host, $allowedHosts, true)
        ) {
            throw new GoogleIndexingException(
                'URL domain is not in the configured Google indexing allowlist.',
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

            $this->indexingService
                ->urlNotifications
                ->publish($notification);

            Log::info(
                'Google Indexing API notification submitted.',
                [
                    'url' => $url,
                    'type' => $type,
                ]
            );
        } catch (GoogleServiceException $e) {
            $code = (int) $e->getCode();

            $message = $e->getMessage();

            $lowerMessage = strtolower($message);

            // Handle Google quota errors.
            if (
                $code === 429 ||
                str_contains($lowerMessage, 'quota') ||
                str_contains($lowerMessage, 'rate limit')
            ) {
                Log::warning(
                    'Google Indexing API quota exceeded.',
                    [
                        'url' => $url,
                        'code' => $code,
                        'error' => $message,
                    ]
                );

                throw new GoogleIndexingQuotaExceededException();
            }

            // Authentication, ownership and permission errors.
            $isPermanent =
                in_array($code, [400, 401, 403, 404], true) ||
                str_contains($lowerMessage, 'permission') ||
                str_contains($lowerMessage, 'ownership') ||
                str_contains($lowerMessage, 'not authorized');

            $isRetryable = ! $isPermanent && (
                $code >= 500 ||
                $code === 0
            );

            Log::error(
                'Google Indexing API request failed.',
                [
                    'url' => $url,
                    'code' => $code,
                    'retryable' => $isRetryable,
                    'error' => $message,
                ]
            );

            throw new GoogleIndexingException(
                message: "Google API error ({$code}): {$message}",
                isRetryable: $isRetryable,
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Google Indexing API unexpected error.',
                [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]
            );

            throw new GoogleIndexingException(
                message: 'Unexpected Google API error: ' .
                    $e->getMessage(),
                isRetryable: true,
                previous: $e
            );
        }
    }
}