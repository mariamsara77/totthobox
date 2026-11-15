<?php

namespace App\Console\Commands;

use App\Services\GoogleIndexingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class AutoPushSitemapToGoogle extends Command
{
    protected $signature = 'google:auto-index-sitemap
        {--dry-run : Validate URLs without sending API requests}
        {--max-urls=50000 : Maximum URLs to inspect}
        {--path= : Optional local sitemap XML path}';

    protected $description = 'Safely process eligible sitemap URLs using Google Indexing API';

    private array $allowedHosts = [];

    private array $visitedSitemaps = [];

    private array $urls = [];

    private int $maxUrls = 50000;

    private int $successCount = 0;

    private int $failedCount = 0;

    private int $skippedCount = 0;

    private int $invalidCount = 0;

    public function handle(GoogleIndexingService $indexingService): int
    {
        $this->info('Starting Google sitemap indexing...');

        try {
            if (! $indexingService->isConfigured()) {
                throw new RuntimeException(
                    'Google Indexing API is not configured. Check your credentials.'
                );
            }

            $this->configureHosts();

            $this->maxUrls = max(
                1,
                min((int) $this->option('max-urls'), 100000)
            );

            $path = $this->option('path')
                ? (string) $this->option('path')
                : public_path('sitemap.xml');

            if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
                $path = base_path($path);
            }

            if (! is_file($path) || ! is_readable($path)) {
                throw new RuntimeException(
                    "Sitemap file not found or unreadable: {$path}"
                );
            }

            $content = file_get_contents($path);

            if ($content === false || $content === '') {
                throw new RuntimeException('Sitemap file is empty.');
            }

            $this->visitedSitemaps[$path] = true;

            $this->parseSitemap($content, 0);

            $total = count($this->urls);

            $this->info("Total unique valid URLs discovered: {$total}");

            if ($total === 0) {
                $this->warn('No valid sitemap URLs found.');

                return Command::SUCCESS;
            }

            $this->newLine();

            foreach ($this->urls as $url) {
                if ($indexingService->remainingQuota() <= 0) {
                    $this->warn(
                        'Google daily indexing quota reached. Stopping safely.'
                    );

                    break;
                }

                $this->line("Checking: {$url}");

                try {
                    if (! $this->isEligibleForGoogleIndexing($url)) {
                        $this->skippedCount++;

                        $this->comment('Skipped: unsupported structured data.');

                        continue;
                    }

                    if ($this->option('dry-run')) {
                        $this->successCount++;

                        $this->info('[DRY RUN] Eligible URL.');

                        continue;
                    }

                    // Publish only Google-supported page types.
                    $indexingService->indexUrl($url, 'URL_UPDATED');

                    /*
                     * Keep the direct command's successful API calls visible
                     * to the same quota counter used by the service.
                     */
                    RateLimiter::hit(
                        GoogleIndexingService::RATE_LIMITER_NAME . ':' .
                            GoogleIndexingService::RATE_LIMITER_BY_KEY,
                        86400
                    );

                    $this->successCount++;

                    $this->info('Successfully submitted.');

                    // Conservative pacing between API requests.
                    sleep(1);
                } catch (Throwable $e) {
                    $this->failedCount++;

                    $this->warn(
                        "Failed: {$url} - {$e->getMessage()}"
                    );

                    Log::error('Google sitemap indexing URL failed.', [
                        'url' => $url,
                        'error' => $e->getMessage(),
                    ]);

                    // Continue with the remaining URLs.
                    continue;
                }
            }

            $this->showSummary();

            Log::info('Google sitemap indexing completed.', [
                'total_urls' => $total,
                'success' => $this->successCount,
                'failed' => $this->failedCount,
                'skipped' => $this->skippedCount,
                'invalid' => $this->invalidCount,
                'dry_run' => (bool) $this->option('dry-run'),
            ]);

            return $this->failedCount > 0
                ? Command::FAILURE
                : Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            Log::error('Google sitemap indexing command failed.', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Configure trusted public domains.
     */
    private function configureHosts(): void
    {
        $hosts = config('services.google.indexing_allowed_hosts', []);

        if (is_string($hosts)) {
            $hosts = explode(',', $hosts);
        }

        if (! is_array($hosts)) {
            $hosts = [];
        }

        if (empty($hosts)) {
            $appHost = parse_url(
                (string) config('app.url'),
                PHP_URL_HOST
            );

            if ($appHost) {
                $hosts = [$appHost];
            }
        }

        $this->allowedHosts = array_values(array_unique(array_filter(
            array_map(
                fn($host) => strtolower(rtrim(trim((string) $host), '.')),
                $hosts
            )
        )));

        if (empty($this->allowedHosts)) {
            throw new RuntimeException(
                'No trusted sitemap hosts configured.'
            );
        }
    }

    /**
     * Parse sitemap XML, including nested sitemap indexes.
     */
    private function parseSitemap(
        string $content,
        int $depth
    ): void {
        if ($depth > 5) {
            throw new RuntimeException(
                'Maximum sitemap nesting depth exceeded.'
            );
        }

        if (strlen($content) > 20 * 1024 * 1024) {
            throw new RuntimeException('Sitemap exceeds the 20 MB limit.');
        }

        if (
            stripos($content, '<!DOCTYPE') !== false ||
            stripos($content, '<!ENTITY') !== false
        ) {
            throw new RuntimeException(
                'DOCTYPE and ENTITY declarations are not allowed.'
            );
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string(
                $content,
                SimpleXMLElement::class,
                LIBXML_NONET | LIBXML_NOBLANKS
            );

            if ($xml === false) {
                throw new RuntimeException(
                    'Invalid sitemap XML.'
                );
            }

            $root = strtolower($xml->getName());

            if ($root === 'sitemapindex') {
                $children = $xml->xpath(
                    '/*[local-name()="sitemapindex"]/*[local-name()="sitemap"]'
                ) ?: [];

                foreach ($children as $child) {
                    if (count($this->urls) >= $this->maxUrls) {
                        break;
                    }

                    $loc = $child->xpath(
                        './*[local-name()="loc"]'
                    ) ?: [];

                    $url = isset($loc[0])
                        ? trim((string) $loc[0])
                        : '';

                    $url = $this->validateUrl($url);

                    if (! $url || isset($this->visitedSitemaps[$url])) {
                        continue;
                    }

                    $this->visitedSitemaps[$url] = true;

                    try {
                        $response = Http::connectTimeout(5)
                            ->timeout(20)
                            ->withOptions([
                                'allow_redirects' => false,
                            ])
                            ->accept('application/xml, text/xml')
                            ->get($url);

                        if (! $response->successful()) {
                            throw new RuntimeException(
                                'Child sitemap HTTP error: ' .
                                    $response->status()
                            );
                        }

                        $this->parseSitemap(
                            $response->body(),
                            $depth + 1
                        );
                    } catch (Throwable $e) {
                        Log::warning(
                            'Unable to process child sitemap.',
                            [
                                'sitemap' => $url,
                                'error' => $e->getMessage(),
                            ]
                        );

                        $this->warn(
                            "Child sitemap skipped: {$url}"
                        );
                    }
                }

                return;
            }

            if ($root !== 'urlset') {
                throw new RuntimeException(
                    "Unsupported sitemap type: {$root}"
                );
            }

            $nodes = $xml->xpath(
                '/*[local-name()="urlset"]/*[local-name()="url"]'
            ) ?: [];

            foreach ($nodes as $node) {
                if (count($this->urls) >= $this->maxUrls) {
                    break;
                }

                $loc = $node->xpath(
                    './*[local-name()="loc"]'
                ) ?: [];

                $rawUrl = isset($loc[0])
                    ? trim((string) $loc[0])
                    : '';

                $url = $this->validateUrl($rawUrl);

                if (! $url) {
                    $this->invalidCount++;

                    continue;
                }

                if (! in_array($url, $this->urls, true)) {
                    $this->urls[] = $url;
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * Validate HTTPS URLs and trusted hosts.
     */
    private function validateUrl(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return null;
        }

        $scheme = strtolower($parts['scheme'] ?? '');

        $host = strtolower(
            rtrim($parts['host'] ?? '', '.')
        );

        if (
            $scheme !== 'https' ||
            ! in_array($host, $this->allowedHosts, true) ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            (isset($parts['port']) && (int) $parts['port'] !== 443) ||
            isset($parts['query']) ||
            isset($parts['fragment'])
        ) {
            return null;
        }

        return 'https://' . $host .
            ($parts['path'] ?? '/');
    }

    /**
     * Google Indexing API eligibility:
     *
     * 1. JobPosting
     * 2. VideoObject containing BroadcastEvent
     */
    private function isEligibleForGoogleIndexing(
        string $url
    ): bool {
        $response = Http::connectTimeout(5)
            ->timeout(15)
            ->withOptions([
                'allow_redirects' => false,
            ])
            ->accept('text/html')
            ->get($url);

        if (! $response->successful()) {
            Log::notice(
                'Google indexing eligibility HTTP check failed.',
                [
                    'url' => $url,
                    'status' => $response->status(),
                ]
            );

            return false;
        }

        $html = $response->body();

        if (
            $html === '' ||
            strlen($html) > 8 * 1024 * 1024
        ) {
            return false;
        }

        preg_match_all(
            '~<script\b([^>]*)>(.*?)</script\s*>~is',
            $html,
            $scripts,
            PREG_SET_ORDER
        );

        foreach ($scripts as $script) {
            if (! preg_match(
                '~\btype\s*=\s*(["\'])application/ld\+json\1~i',
                $script[1]
            )) {
                continue;
            }

            $data = json_decode(
                trim($script[2]),
                true
            );

            if (
                json_last_error() !== JSON_ERROR_NONE ||
                ! is_array($data)
            ) {
                continue;
            }

            if (
                $this->containsType($data, 'JobPosting') ||
                $this->containsBroadcastVideo($data)
            ) {
                return true;
            }
        }

        return false;
    }

    private function containsType(
        mixed $data,
        string $type
    ): bool {
        if (! is_array($data)) {
            return false;
        }

        $types = (array) ($data['@type'] ?? []);

        foreach ($types as $item) {
            if (! is_string($item)) {
                continue;
            }

            $shortType = basename(
                str_replace('#', '/', $item)
            );

            if (strcasecmp($shortType, $type) === 0) {
                return true;
            }
        }

        foreach ($data as $value) {
            if ($this->containsType($value, $type)) {
                return true;
            }
        }

        return false;
    }

    private function containsBroadcastVideo(
        mixed $data
    ): bool {
        if (! is_array($data)) {
            return false;
        }

        $types = (array) ($data['@type'] ?? []);

        $isVideo = false;

        foreach ($types as $type) {
            if (
                is_string($type) &&
                strcasecmp(
                    basename(str_replace('#', '/', $type)),
                    'VideoObject'
                ) === 0
            ) {
                $isVideo = true;
                break;
            }
        }

        if (
            $isVideo &&
            $this->containsType($data, 'BroadcastEvent')
        ) {
            return true;
        }

        foreach ($data as $value) {
            if ($this->containsBroadcastVideo($value)) {
                return true;
            }
        }

        return false;
    }

    private function showSummary(): void
    {
        $this->newLine();

        $this->info('Google Indexing Summary');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Successfully submitted / dry-run eligible', $this->successCount],
                ['Failed', $this->failedCount],
                ['Skipped unsupported pages', $this->skippedCount],
                ['Invalid URLs', $this->invalidCount],
            ]
        );

        $this->info('Sitemap processing completed.');
    }
}