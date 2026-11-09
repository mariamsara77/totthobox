<?php

namespace App\Console\Commands;

use App\Enums\IndexingStatus;
use App\Jobs\IndexUrlJob;
use App\Models\IndexedUrl;
use App\Services\GoogleIndexingService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class AutoPushSitemapToGoogle extends Command
{
    protected $signature = 'google:auto-index-sitemap
        {--path= : Sitemap XML file path (default: public/sitemap.xml)}
        {--force : Requeue eligible failed, quota-exceeded, and successful URLs}
        {--dry-run : Validate URLs and structured data without writing or dispatching jobs}
        {--max-urls= : Maximum number of sitemap URLs to inspect}';

    protected $description = 'Safely discover eligible URLs from XML sitemaps and queue Google Indexing API notifications';

    private array $allowedHosts = [];

    private array $allowedPathPrefixes = [];

    private string $canonicalHost = '';

    private int $maxUrls = 50000;

    private int $maxSitemapDepth = 5;

    private array $visitedSitemaps = [];

    /** @var array<string, string|null> Canonical URL => sitemap lastmod */
    private array $discoveredUrls = [];

    private array $stats = [
        'rejected_host' => 0,
        'rejected_url' => 0,
        'duplicate_url' => 0,
        'failed_sitemap' => 0,
        'skipped_path' => 0,
        'skipped_schema' => 0,
        'eligible' => 0,
        'queued' => 0,
        'already_queued_or_processed' => 0,
        'queue_errors' => 0,
    ];

    public function handle(GoogleIndexingService $indexingService): int
    {
        try {
            $this->configureAllowlist();

            if ($this->allowedPathPrefixes === []) {
                $message = 'GOOGLE_INDEXING_ALLOWED_PATHS সেট করা নেই। নিরাপত্তার জন্য কোনো sitemap URL Google Indexing API-তে পাঠানো হয়নি।';
                $this->warn($message);
                Log::warning('Google sitemap indexing skipped: no eligible route prefixes configured.');

                return Command::SUCCESS;
            }

            if (! $indexingService->isConfigured()) {
                throw new RuntimeException(
                    'Google Indexing API credentials are missing or invalid. Check GOOGLE_INDEXING_CREDENTIALS_PATH.'
                );
            }

            $path = (string) ($this->option('path') ?: public_path('sitemap.xml'));

            if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
                $path = base_path($path);
            }

            if (! is_file($path) || ! is_readable($path)) {
                throw new RuntimeException("Sitemap file is missing or unreadable: {$path}");
            }

            $requestedLimit = $this->option('max-urls');
            $configuredLimit = (int) config('services.google.indexing_max_sitemap_urls', 50000);
            $this->maxUrls = max(1, min(
                $requestedLimit !== null ? (int) $requestedLimit : $configuredLimit,
                100000
            ));

            $this->maxSitemapDepth = max(
                1,
                (int) config('services.google.indexing_max_sitemap_depth', 5)
            );

            $this->info('Reading sitemap and validating public URLs...');

            $rootContent = $this->readLocalSitemap($path);
            $this->visitedSitemaps[$path] = true;
            $this->parseSitemapContent($rootContent, 0, $path);

            $discoveredCount = count($this->discoveredUrls);

            $this->line("Unique allowed-host URLs discovered: {$discoveredCount}");

            if ($discoveredCount === 0) {
                $this->warn('কোনো অনুমোদিত public URL পাওয়া যায়নি। Host allowlist এবং sitemap source পরীক্ষা করুন।');
                $this->writeSummary();

                return $this->stats['failed_sitemap'] > 0 ? Command::FAILURE : Command::SUCCESS;
            }

            $this->info('Checking configured route prefixes and Google-supported structured data...');

            $progress = $this->output->createProgressBar($discoveredCount);
            $progress->start();

            foreach ($this->discoveredUrls as $url => $lastmod) {
                if (! $this->matchesAllowedPath($url)) {
                    $this->stats['skipped_path']++;
                    $progress->advance();

                    continue;
                }

                if (! $this->hasSupportedStructuredData($url)) {
                    $this->stats['skipped_schema']++;
                    $progress->advance();

                    continue;
                }

                $this->stats['eligible']++;

                if ($this->option('dry-run')) {
                    $progress->advance();

                    continue;
                }

                try {
                    $result = $this->queueUrl($url, $lastmod);

                    if ($result === 'queued') {
                        $this->stats['queued']++;
                    } else {
                        $this->stats['already_queued_or_processed']++;
                    }
                } catch (Throwable $e) {
                    $this->stats['queue_errors']++;

                    Log::error('Google sitemap indexing: failed to queue URL.', [
                        'url' => $url,
                        'error' => $e->getMessage(),
                    ]);

                    $this->warn("\nQueue failed for {$url}: {$e->getMessage()}");
                }

                $progress->advance();
            }

            $progress->finish();
            $this->newLine(2);

            if ($this->option('dry-run')) {
                $this->info('Dry run completed. No database records or queue jobs were changed.');
            }

            $this->writeSummary();

            Log::info('Google sitemap indexing command completed.', [
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'stats' => $this->stats,
            ]);

            return ($this->stats['queue_errors'] > 0 || $this->stats['failed_sitemap'] > 0)
                ? Command::FAILURE
                : Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Google sitemap indexing stopped: '.$e->getMessage());

            Log::error('Google sitemap indexing command failed.', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return Command::FAILURE;
        }
    }

    private function configureAllowlist(): void
    {
        $hosts = $this->asStringList(
            config('services.google.indexing_allowed_hosts', [
                'totthobox.com',
                'www.totthobox.com',
            ])
        );

        $this->allowedHosts = array_values(array_unique(array_map(
            static fn (string $host): string => strtolower(rtrim($host, '.')),
            $hosts
        )));

        $this->canonicalHost = strtolower(rtrim(
            (string) config('services.google.indexing_canonical_host', 'totthobox.com'),
            '.'
        ));

        if ($this->canonicalHost === '' || ! in_array($this->canonicalHost, $this->allowedHosts, true)) {
            throw new RuntimeException(
                'GOOGLE_INDEXING_CANONICAL_HOST must also be present in GOOGLE_INDEXING_ALLOWED_HOSTS.'
            );
        }

        $this->allowedPathPrefixes = array_values(array_filter(
            array_map(function (string $prefix): string {
                $prefix = trim($prefix);

                if ($prefix === '') {
                    return '';
                }

                return str_starts_with($prefix, '/') ? $prefix : '/'.$prefix;
            }, $this->asStringList(config('services.google.indexing_allowed_paths', []))),
            static fn (string $prefix): bool => $prefix !== ''
        ));
    }

    /** @return array<int, string> */
    private function asStringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn ($item): string => trim((string) $item), $value),
            static fn (string $item): bool => $item !== ''
        ));
    }

    private function readLocalSitemap(string $path): string
    {
        $size = filesize($path);
        $maxBytes = max(1024, (int) config('services.google.indexing_max_sitemap_bytes', 20971520));

        if ($size !== false && $size > $maxBytes) {
            throw new RuntimeException('Local sitemap exceeds the configured maximum file size.');
        }

        $content = file_get_contents($path);

        if ($content === false || $content === '') {
            throw new RuntimeException('Unable to read the local sitemap or the file is empty.');
        }

        return $this->decodeGzipIfRequired($content);
    }

    private function fetchRemoteSitemap(string $url): string
    {
        $response = Http::connectTimeout(5)
            ->timeout(20)
            ->withOptions(['allow_redirects' => false])
            ->accept('application/xml, text/xml, application/gzip, */*')
            ->withUserAgent('Totthobox-SitemapIndexer/1.0')
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Sitemap request returned HTTP {$response->status()}.");
        }

        $content = $response->body();
        $maxBytes = max(1024, (int) config('services.google.indexing_max_sitemap_bytes', 20971520));

        if (strlen($content) > $maxBytes) {
            throw new RuntimeException('Remote sitemap exceeds the configured maximum file size.');
        }

        if ($content === '') {
            throw new RuntimeException('Remote sitemap response was empty.');
        }

        return $this->decodeGzipIfRequired($content);
    }

    private function decodeGzipIfRequired(string $content): string
    {
        if (! str_starts_with($content, "\x1f\x8b")) {
            return $content;
        }

        $decoded = @gzdecode($content);

        if ($decoded === false) {
            throw new RuntimeException('Unable to decompress the gzip sitemap.');
        }

        return $decoded;
    }

    private function parseSitemapContent(string $content, int $depth, string $source): void
    {
        if ($depth > $this->maxSitemapDepth) {
            throw new RuntimeException("Maximum sitemap nesting depth exceeded at {$source}.");
        }

        if (strlen($content) > max(1024, (int) config('services.google.indexing_max_sitemap_bytes', 20971520))) {
            throw new RuntimeException("Sitemap exceeds the configured size limit: {$source}");
        }

        // External entity declarations are not needed in a sitemap and are rejected.
        if (stripos($content, '<!DOCTYPE') !== false || stripos($content, '<!ENTITY') !== false) {
            throw new RuntimeException("DOCTYPE/ENTITY declarations are not allowed in sitemap XML: {$source}");
        }

        $previousLibxmlState = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string(
                $content,
                SimpleXMLElement::class,
                LIBXML_NONET | LIBXML_NOBLANKS
            );

            if ($xml === false) {
                $errors = libxml_get_errors();
                $message = $errors !== []
                    ? trim($errors[0]->message)
                    : 'Unknown XML parsing error';

                throw new RuntimeException("Invalid sitemap XML at {$source}: {$message}");
            }

            $rootName = strtolower($xml->getName());

            if ($rootName === 'sitemapindex') {
                $sitemapNodes = $xml->xpath(
                    '/*[local-name()="sitemapindex"]/*[local-name()="sitemap"]'
                ) ?: [];

                foreach ($sitemapNodes as $sitemapNode) {
                    if (count($this->discoveredUrls) >= $this->maxUrls) {
                        break;
                    }

                    $locNodes = $sitemapNode->xpath('./*[local-name()="loc"]') ?: [];
                    $rawChildUrl = isset($locNodes[0]) ? trim((string) $locNodes[0]) : '';

                    if ($rawChildUrl === '') {
                        $this->stats['rejected_url']++;

                        continue;
                    }

                    $childUrl = $this->normalisePublicUrl($rawChildUrl);

                    if ($childUrl === null) {
                        continue;
                    }

                    if (isset($this->visitedSitemaps[$childUrl])) {
                        continue;
                    }

                    $this->visitedSitemaps[$childUrl] = true;

                    try {
                        $childContent = $this->fetchRemoteSitemap($childUrl);
                        $this->parseSitemapContent($childContent, $depth + 1, $childUrl);
                    } catch (Throwable $e) {
                        $this->stats['failed_sitemap']++;

                        Log::warning('Google sitemap indexing: child sitemap could not be processed.', [
                            'sitemap' => $childUrl,
                            'error' => $e->getMessage(),
                        ]);

                        $this->warn("\nSkipped child sitemap {$childUrl}: {$e->getMessage()}");
                    }
                }

                return;
            }

            if ($rootName !== 'urlset') {
                throw new RuntimeException("Unsupported sitemap root element '{$rootName}' at {$source}.");
            }

            $urlNodes = $xml->xpath(
                '/*[local-name()="urlset"]/*[local-name()="url"]'
            ) ?: [];

            foreach ($urlNodes as $urlNode) {
                if (count($this->discoveredUrls) >= $this->maxUrls) {
                    break;
                }

                $locNodes = $urlNode->xpath('./*[local-name()="loc"]') ?: [];
                $lastmodNodes = $urlNode->xpath('./*[local-name()="lastmod"]') ?: [];

                $rawUrl = isset($locNodes[0]) ? trim((string) $locNodes[0]) : '';
                $lastmod = isset($lastmodNodes[0]) ? trim((string) $lastmodNodes[0]) : null;

                if ($rawUrl === '') {
                    $this->stats['rejected_url']++;

                    continue;
                }

                $url = $this->normalisePublicUrl($rawUrl);

                if ($url === null) {
                    continue;
                }

                if (array_key_exists($url, $this->discoveredUrls)) {
                    $this->stats['duplicate_url']++;

                    // Keep the newest valid lastmod seen for the canonical URL.
                    if ($lastmod !== null && $lastmod !== '') {
                        $existingLastmod = $this->discoveredUrls[$url];

                        if ($existingLastmod === null || $this->isDateNewer($lastmod, $existingLastmod)) {
                            $this->discoveredUrls[$url] = $lastmod;
                        }
                    }

                    continue;
                }

                $this->discoveredUrls[$url] = ($lastmod !== null && $lastmod !== '')
                    ? $lastmod
                    : null;
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlState);
        }
    }

    private function normalisePublicUrl(string $rawUrl): ?string
    {
        if (! filter_var($rawUrl, FILTER_VALIDATE_URL)) {
            $this->stats['rejected_url']++;

            return null;
        }

        $parts = parse_url($rawUrl);

        if (! is_array($parts)) {
            $this->stats['rejected_url']++;

            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));

        if (
            $scheme !== 'https'
            || $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)
        ) {
            $this->stats['rejected_url']++;

            return null;
        }

        if (! in_array($host, $this->allowedHosts, true)) {
            $this->stats['rejected_host']++;

            return null;
        }

        // Sitemap URLs should be canonical page URLs, not tracking/query variants.
        if (! empty($parts['query']) || ! empty($parts['fragment'])) {
            $this->stats['rejected_url']++;

            return null;
        }

        $path = (string) ($parts['path'] ?? '/');

        if ($path === '') {
            $path = '/';
        }

        return 'https://'.$this->canonicalHost.$path;
    }

    private function matchesAllowedPath(string $url): bool
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '/');

        foreach ($this->allowedPathPrefixes as $prefix) {
            $base = rtrim($prefix, '/');

            if ($base === '') {
                return true;
            }

            if ($path === $base || str_starts_with($path, $base.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Google Indexing API is restricted to JobPosting pages and livestream pages
     * containing BroadcastEvent embedded in a VideoObject.
     */
    private function hasSupportedStructuredData(string $url): bool
    {
        try {
            $response = Http::connectTimeout(5)
                ->timeout(12)
                ->withOptions(['allow_redirects' => false])
                ->accept('text/html')
                ->withUserAgent('Totthobox-GoogleIndexingEligibilityCheck/1.0')
                ->get($url);

            if (! $response->successful()) {
                Log::notice('Google indexing eligibility check skipped a URL with a non-success HTTP response.', [
                    'url' => $url,
                    'http_status' => $response->status(),
                ]);

                return false;
            }

            $html = $response->body();

            // Avoid processing unexpectedly large HTML responses in the scheduler.
            if ($html === '' || strlen($html) > 8388608) {
                return false;
            }

            preg_match_all(
                '~<script\b([^>]*)>(.*?)</script\s*>~is',
                $html,
                $scripts,
                PREG_SET_ORDER
            );

            foreach ($scripts as $script) {
                if (stripos($script[1], 'application/ld+json') === false) {
                    continue;
                }

                $json = trim($script[2]);

                if ($json === '') {
                    continue;
                }

                $data = json_decode($json, true);

                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
                    continue;
                }

                if (
                    $this->containsSchemaType($data, 'JobPosting')
                    || $this->containsVideoObjectWithBroadcastEvent($data)
                ) {
                    return true;
                }
            }
        } catch (Throwable $e) {
            Log::warning('Google indexing eligibility check failed.', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function containsSchemaType(mixed $node, string $expectedType): bool
    {
        if (! is_array($node)) {
            return false;
        }

        if ($this->nodeHasSchemaType($node, $expectedType)) {
            return true;
        }

        foreach ($node as $child) {
            if ($this->containsSchemaType($child, $expectedType)) {
                return true;
            }
        }

        return false;
    }

    private function containsVideoObjectWithBroadcastEvent(mixed $node): bool
    {
        if (! is_array($node)) {
            return false;
        }

        if (
            $this->nodeHasSchemaType($node, 'VideoObject')
            && $this->containsSchemaType($node, 'BroadcastEvent')
        ) {
            return true;
        }

        foreach ($node as $child) {
            if ($this->containsVideoObjectWithBroadcastEvent($child)) {
                return true;
            }
        }

        return false;
    }

    private function nodeHasSchemaType(array $node, string $expectedType): bool
    {
        $types = $node['@type'] ?? [];

        foreach ((array) $types as $type) {
            if (! is_string($type)) {
                continue;
            }

            $shortType = preg_replace('~^.*[/#:]+~', '', trim($type));

            if (is_string($shortType) && strcasecmp($shortType, $expectedType) === 0) {
                return true;
            }
        }

        return false;
    }

    private function queueUrl(string $url, ?string $lastmod): string
    {
        $now = now();

        // Atomic insert: the existing unique index is the final duplicate guard.
        // insertOrIgnore also prevents a race between overlapping scheduler/manual runs.
        DB::table('indexed_urls')->insertOrIgnore([
            'url' => $url,
            'source' => 'sitemap',
            'status' => IndexingStatus::Pending->value,
            'is_indexed' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Include soft-deleted rows: they still occupy the unique URL index.
        $record = IndexedUrl::withTrashed()
            ->where('url', $url)
            ->first();

        if (! $record) {
            throw new RuntimeException("Could not load indexed_urls record for {$url} after insert-or-ignore.");
        }

        if ($record->trashed()) {
            $record->restore();
            $record->refresh();
        }

        $allowedStatuses = [IndexingStatus::Pending->value];

        if ($this->option('force')) {
            $allowedStatuses = [
                IndexingStatus::Pending->value,
                IndexingStatus::Failed->value,
                IndexingStatus::QuotaExceeded->value,
                IndexingStatus::Success->value,
            ];
        } elseif (
            $record->status === IndexingStatus::Success
            && $this->isSitemapLastmodNewerThanLastPush($record, $lastmod)
        ) {
            $allowedStatuses[] = IndexingStatus::Success->value;
        }

        // Atomic claim: only one command process can change a claimable row to queued.
        // A row already queued is intentionally never dispatched again by this command.
        $previousStatus = $record->status instanceof IndexingStatus
            ? $record->status->value
            : (string) $record->status;

        $previousIsIndexed = (bool) $record->is_indexed;
        $previousError = $record->error_message;

        $claimed = DB::table('indexed_urls')
            ->where('id', $record->getKey())
            ->whereNull('deleted_at')
            ->whereIn('status', $allowedStatuses)
            ->update([
                'status' => IndexingStatus::Queued->value,
                'is_indexed' => false,
                'error_message' => null,
                'updated_at' => $now,
            ]);

        if ($claimed !== 1) {
            return 'skipped';
        }

        try {
            IndexUrlJob::dispatch((int) $record->getKey());
        } catch (Throwable $e) {
            // Do not strand a record in "queued" if the queue backend rejects dispatch.
            DB::table('indexed_urls')
                ->where('id', $record->getKey())
                ->where('status', IndexingStatus::Queued->value)
                ->update([
                    'status' => $previousStatus,
                    'is_indexed' => $previousIsIndexed,
                    'error_message' => $previousError,
                    'updated_at' => now(),
                ]);

            throw $e;
        }

        return 'queued';
    }

    private function isSitemapLastmodNewerThanLastPush(IndexedUrl $record, ?string $lastmod): bool
    {
        if ($lastmod === null || $lastmod === '' || $record->last_pushed_at === null) {
            return false;
        }

        try {
            return CarbonImmutable::parse($lastmod)
                ->greaterThan(CarbonImmutable::parse($record->last_pushed_at));
        } catch (Throwable) {
            return false;
        }
    }

    private function isDateNewer(string $candidate, string $current): bool
    {
        try {
            return CarbonImmutable::parse($candidate)
                ->greaterThan(CarbonImmutable::parse($current));
        } catch (Throwable) {
            return false;
        }
    }

    private function writeSummary(): void
    {
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Rejected non-canonical/invalid URLs', $this->stats['rejected_url']],
                ['Rejected non-public hosts', $this->stats['rejected_host']],
                ['Duplicate sitemap URLs', $this->stats['duplicate_url']],
                ['Child sitemap failures', $this->stats['failed_sitemap']],
                ['Skipped route prefixes', $this->stats['skipped_path']],
                ['Skipped unsupported/missing structured data', $this->stats['skipped_schema']],
                ['Google API eligible URLs', $this->stats['eligible']],
                ['Queued jobs', $this->stats['queued']],
                ['Already queued/processed URLs', $this->stats['already_queued_or_processed']],
                ['Queue/database errors', $this->stats['queue_errors']],
            ]
        );
    }
}
