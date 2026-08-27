<?php

namespace App\Console\Commands;

use App\Jobs\CheckAndSyncStreamJob;
use App\Models\LiveChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncAllChannels extends Command
{
    protected $signature = 'channels:sync-all
                        {--type=all : all|worldcup|bangladesh|football}
                        {--force : Skip existing health-check, only fetch new channels}
                        {--health-only : Only health-check existing DB channels}
                        {--dry-run : Preview without saving}
                        {--chunk=50 : Jobs per batch}';

    protected $description = 'Sync Live Channels — Bangladesh TV + FIFA World Cup 2026 + Global Football';

    // ===================================================================
    //  PRIORITY BANGLADESH CHANNELS
    //  These are hardcoded known-good channels that are manually curated.
    //  They get synced first and marked as featured.
    // ===================================================================
    protected array $priorityBangladeshChannels = [
        // T Sports — FIFA WC2026 rights in Bangladesh
        [
            'title' => 'T Sports HD',
            'stream_url' => '', // filled from M3U
            'keyword' => 't sports',
            'broadcaster' => 'T Sports',
            'is_featured' => true,
        ],
        // Toffee TV (T Sports OTT)
        [
            'title' => 'Toffee TV',
            'keyword' => 'toffee',
            'broadcaster' => 'Toffee / Banglalink',
            'is_featured' => true,
        ],
        // Somoy TV
        [
            'title' => 'Somoy TV',
            'keyword' => 'somoy tv',
            'broadcaster' => 'Somoy Media',
            'is_featured' => true,
        ],
        // BTV National
        [
            'title' => 'BTV National',
            'keyword' => 'btv national',
            'broadcaster' => 'Bangladesh Television',
            'is_featured' => true,
        ],
        // BTV World
        [
            'title' => 'BTV World',
            'keyword' => 'btv world',
            'broadcaster' => 'Bangladesh Television',
            'is_featured' => false,
        ],
        // Jamuna TV
        [
            'title' => 'Jamuna TV',
            'keyword' => 'jamuna tv',
            'broadcaster' => 'Jamuna Future Park Media',
            'is_featured' => true,
        ],
        // Channel i
        [
            'title' => 'Channel i',
            'keyword' => 'channel i',
            'broadcaster' => 'Channel i',
            'is_featured' => false,
        ],
        // NTV
        [
            'title' => 'NTV Bangladesh',
            'keyword' => 'ntv',
            'broadcaster' => 'NTV',
            'is_featured' => false,
        ],
    ];

    // ===================================================================
    //  M3U SOURCES
    // ===================================================================
    protected array $sources = [

        'bangladesh' => [
            ['url' => 'https://iptv-org.github.io/iptv/countries/bd.m3u', 'label' => 'IPTV-Org Bangladesh'],
            ['url' => 'https://raw.githubusercontent.com/byte-capsule/Toffee-Channels-Link-Headers/main/toffee_OTT_Live_channels.m3u', 'label' => 'Toffee OTT Bangladesh'],
            ['url' => 'https://raw.githubusercontent.com/Dhruv859/BD-TV/main/live.m3u', 'label' => 'BD-TV GitHub'],
            ['url' => 'https://raw.githubusercontent.com/IPTV-BD/BDTV/main/Bdtv.m3u', 'label' => 'BDTV GitHub'],
        ],

        'football' => [
            ['url' => 'https://iptv-org.github.io/iptv/categories/sports.m3u', 'label' => 'IPTV-Org Sports (Global)'],
            ['url' => 'https://raw.githubusercontent.com/FunctionError/PiratesTV/main/combined_playlist.m3u', 'label' => 'PiratesTV Sports'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/gb.m3u', 'label' => 'UK Sports'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/us.m3u', 'label' => 'US Sports'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/ae.m3u', 'label' => 'beIN Sports (UAE)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/sa.m3u', 'label' => 'SSC Sports (Saudi)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/qa.m3u', 'label' => 'Al Kass (Qatar)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/in.m3u', 'label' => 'India Sports'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/de.m3u', 'label' => 'Germany Sports'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/fr.m3u', 'label' => 'France Sports'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/es.m3u', 'label' => 'Spain Sports'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/br.m3u', 'label' => 'Brazil Sports'],
        ],

        'worldcup' => [
            // Global sports M3Us
            ['url' => 'https://iptv-org.github.io/iptv/categories/sports.m3u', 'label' => 'Global Sports → WC Filter'],
            ['url' => 'https://raw.githubusercontent.com/FunctionError/PiratesTV/main/combined_playlist.m3u', 'label' => 'PiratesTV → WC Filter'],
            // Official broadcaster countries
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/us.m3u', 'label' => 'USA — Fox/Telemundo/TNT (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/mx.m3u', 'label' => 'Mexico — Azteca/TUDN (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/ca.m3u', 'label' => 'Canada — TSN/CTV (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/gb.m3u', 'label' => 'UK — BBC/ITV (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/ae.m3u', 'label' => 'beIN Sports UAE (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/de.m3u', 'label' => 'Germany — ARD/ZDF (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/in.m3u', 'label' => 'India — JioCinema/Sports18 (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/bd.m3u', 'label' => 'Bangladesh — T Sports/Toffee (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/fr.m3u', 'label' => 'France — TF1 (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/it.m3u', 'label' => 'Italy — RAI Sport (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/es.m3u', 'label' => 'Spain — RTVE (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/pt.m3u', 'label' => 'Portugal — RTP (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/nl.m3u', 'label' => 'Netherlands — NOS (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/pl.m3u', 'label' => 'Poland — TVP (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/ar.m3u', 'label' => 'Argentina — TyC/TV Pública (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/br.m3u', 'label' => 'Brazil — Globo/SporTV (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/jp.m3u', 'label' => 'Japan — NHK/Fuji (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/kr.m3u', 'label' => 'South Korea — KBS/MBC/SBS (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/pk.m3u', 'label' => 'Pakistan — PTV Sports (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/tr.m3u', 'label' => 'Turkey — TRT Spor (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/cn.m3u', 'label' => 'China — CCTV-5 (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/au.m3u', 'label' => 'Australia — SBS (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/za.m3u', 'label' => 'South Africa — SuperSport (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/ng.m3u', 'label' => 'Nigeria — NTA/SuperSport (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/eg.m3u', 'label' => 'Egypt — beIN MENA (WC2026)'],
            ['url' => 'https://raw.githubusercontent.com/iptv-org/iptv/master/streams/ru.m3u', 'label' => 'Russia — Match TV (WC2026)'],
        ],
    ];

    // ===================================================================
    //  KEYWORDS
    // ===================================================================
    protected array $bangladeshChannels = [
        'btv',
        'bangladesh television',
        'channel i',
        'channel-i',
        'ntv',
        'n tv',
        'rtv',
        'r tv',
        'atv',
        'atn bangla',
        'atn news',
        'atn music',
        'somoy',
        'somoy tv',
        'somoy news',
        'jamuna',
        'jamuna tv',
        'ekattor',
        'deepto',
        'dbc',
        'dbc news',
        'boishakhi',
        'maasranga',
        'banglavision',
        'bangla vision',
        'independent tv',
        'news24',
        'channel 24',
        'sa tv',
        'satv',
        'channel 9',
        'my tv',
        'mytv',
        'mohona',
        'desh tv',
        'bijoy tv',
        'duronto',
        'ekushey',
        'gazi tv',
        'ananda tv',
        'bangla tv',
        'star jalsha',
        'zee bangla',
        'sun bangla',
        't sports',
        'tsports',
        'toffee',
        'btv world',
        'btv national',
        'btv news',
        'sangsad tv',
        'ruposhi bangla',
        'ekhon tv',
        'nagorik tv',
        'rang tv',
        ' bd',
        '(bd)',
        '.bd',
        'bangladesh',
    ];

    protected array $footballKeywords = [
        'champions league',
        'ucl',
        'europa league',
        'premier league',
        'epl',
        'la liga',
        'serie a',
        'bundesliga',
        'ligue 1',
        'uefa',
        'copa america',
        'afcon',
        'afc',
        'fa cup',
        'carabao cup',
        'mls',
        'j league',
        'k league',
        'bein sport',
        'sky sports',
        'bt sport',
        'tnt sports',
        'espn',
        'fox sports',
        'fs1',
        'fs2',
        'sport tv',
        'eurosport',
        'dazn',
        'canal+ sport',
        'eleven sport',
        'ssc sport',
        'al kass',
        'sony sports',
        'star sports',
        'supersport',
        'sportklub',
        'arena sport',
        'nova sport',
        'match tv',
        'football channel',
        'live football',
        'live soccer',
        'tsn',
        'sportsnet',
        'bbc sport',
        'itv sport',
        'tf1 sport',
        'rai sport',
        'azteca deportes',
        'tudn',
        'telemundo deportes',
        'nbc sports',
        'cbs sports',
        'globo esporte',
        'sports18',
        'ptv sports',
        'tyc sports',
        'cctv-5',
        'nhk sport',
        'trt spor',
        'nos sport',
        'tvp sport',
    ];

    protected array $worldCupKeywords = [
        // Direct WC references
        'world cup',
        'worldcup',
        'fifa 2026',
        'wc2026',
        'wc 2026',
        'copa mundial',
        'coupe du monde',
        'weltmeisterschaft',
        'coppa del mondo',
        'copa do mundo',
        '世界杯',
        'fifa',
        // Bangladesh WC2026 rights
        't sports',
        'tsports',
        'toffee',
        // USA official
        'fox sports',
        'foxsports',
        'fs1',
        'fs2',
        'telemundo',
        'tnt sports',
        'univision',
        // Canada
        'tsn',
        'ctv',
        'rds',
        'sportsnet',
        // Mexico
        'azteca',
        'tudn',
        'televisa',
        // UK
        'bbc sport',
        'bbc one',
        'bbc two',
        'itv sport',
        'itvx',
        // Germany
        'ard',
        'zdf',
        'das erste',
        // France
        'tf1',
        'rmc sport',
        // Spain
        'rtve',
        'la 1',
        'cuatro',
        // Italy
        'rai 1',
        'rai sport',
        'rai uno',
        // Portugal
        'rtp',
        'sic',
        // Netherlands
        'nos sport',
        'npo sport',
        // Poland
        'tvp sport',
        'tvp1',
        // Turkey
        'trt spor',
        'trt 1',
        // India
        'jiocinema',
        'sports18',
        'sports 18',
        'star sports',
        'sony sports',
        // Pakistan
        'ptv sports',
        'ary sports',
        // MENA
        'bein sports',
        'al kass',
        'ssc sport',
        'nile sport',
        // Brazil
        'tv globo',
        'globo esporte',
        'sportv',
        'band sport',
        // Argentina
        'tv publica',
        'tyc sports',
        'directv sports',
        // Japan
        'nhk sport',
        'fuji tv',
        // South Korea
        'kbs sport',
        'mbc sport',
        'sbs sport',
        // Russia
        'match tv',
        'pervyi kanal',
        // China
        'cctv-5',
        'cctv5',
        // Australia
        'sbs',
        'optus sport',
        // Africa
        'supersport',
        'sabc sport',
        'gtv sport',
    ];

    // ===================================================================
    //  HANDLE
    // ===================================================================
    public function handle(): void
    {
        $type = $this->option('type');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $healthOnly = $this->option('health-only');
        $chunk = max(1, (int) $this->option('chunk'));

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║   Totthobox Channel Sync — v4.0                  ║');
        $this->info('║   Bangladesh TV + FIFA World Cup 2026 Edition    ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        if ($dryRun)
            $this->warn('  [DRY RUN] — No changes will be saved.');
        if ($healthOnly)
            $this->warn('  [HEALTH ONLY] — Skipping M3U fetch.');
        $this->info('');

        // ── Health-only mode ──────────────────────────────────────────
        if ($healthOnly) {
            $this->healthCheckExisting($dryRun, $chunk);
            $this->info('✅ Health check queued!');
            return;
        }

        // ── Step 1: Health-check existing (unless --force) ────────────
        if (!$force) {
            $this->healthCheckExisting($dryRun, $chunk);
        } else {
            $this->warn('[FORCE] Skipping existing health check.');
        }

        // ── Step 2: Fetch new channels ────────────────────────────────
        if (in_array($type, ['all', 'worldcup', 'fifa', 'fifa2026'])) {
            $this->info('');
            $this->info('━━━ 🏆 FIFA World Cup 2026 ━━━');
            $this->fetchFromSources('worldcup', $dryRun, $chunk);
        }

        if (in_array($type, ['all', 'bangladesh'])) {
            $this->info('');
            $this->info('━━━ 🇧🇩 Bangladesh TV ━━━');
            $this->fetchFromSources('bangladesh', $dryRun, $chunk);
        }

        if (in_array($type, ['all', 'football'])) {
            $this->info('');
            $this->info('━━━ ⚽ Football / Sports ━━━');
            $this->fetchFromSources('football', $dryRun, $chunk);
        }

        $this->info('');
        $this->info('✅ Sync queued! Run: php artisan queue:work');
    }

    // ===================================================================
    //  Health-check existing DB channels
    // ===================================================================
    private function healthCheckExisting(bool $dryRun, int $chunk): void
    {
        $this->info('[1] Health-checking existing channels...');
        $channels = LiveChannel::query()->get(['id', 'title', 'stream_url', 'category']);

        if ($channels->isEmpty()) {
            $this->line('    → DB is empty.');
            return;
        }

        if (!$dryRun) {
            foreach ($channels->chunk($chunk) as $batch) {
                foreach ($batch as $ch) {
                    CheckAndSyncStreamJob::dispatch(
                        $ch->title,
                        $ch->stream_url,
                        true,  // isExisting
                        $ch->category,
                    )->onQueue('default');
                }
            }
        }

        $this->line("    → {$channels->count()} channels queued.");
    }

    // ===================================================================
    //  Fetch new channels from M3U sources
    // ===================================================================
    private function fetchFromSources(string $category, bool $dryRun, int $chunk): void
    {
        $sources = $this->sources[$category] ?? [];

        $knownUrls = Cache::remember(
            'sync:known_urls_' . $category,
            now()->addMinutes(10),
            fn() => LiveChannel::query()->pluck('stream_url')->flip()->all()
        );

        $seenThisRun = [];
        $total = 0;

        foreach ($sources as $source) {
            $this->line('');
            $this->line("  📡 {$source['label']}");

            [$count, $seenThisRun] = $this->processM3uSource(
                $source['url'],
                $source['label'],
                $category,
                $dryRun,
                $knownUrls,
                $seenThisRun,
                $chunk
            );

            $total += $count;
            $this->line("     → {$count} queued.");

            if (!$dryRun)
                usleep(300_000);
        }

        $this->info("  Total: {$total} channels queued.");
    }

    private function processM3uSource(
        string $url,
        string $sourceLabel,
        string $category,
        bool $dryRun,
        array $knownUrls,
        array $seenThisRun,
        int $chunk
    ): array {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; TotthoboxSync/4.0)', 'Accept' => '*/*'])
                ->get($url);

            if (!$response->successful()) {
                $this->warn("     ⚠ HTTP {$response->status()} — skipped.");
                return [0, $seenThisRun];
            }
        } catch (\Exception $e) {
            $this->warn("     ⚠ {$e->getMessage()}");
            return [0, $seenThisRun];
        }

        $lines = explode("\n", $response->body());
        $currentTitle = null;
        $currentMeta = [];
        $dispatched = 0;
        $batch = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === '#EXTM3U')
                continue;

            if (str_starts_with($line, '#EXTINF')) {
                $currentTitle = $this->parseTitle($line);
                $currentMeta = $this->parseMeta($line);
                continue;
            }

            if (str_starts_with($line, '#'))
                continue;

            if ($currentTitle !== null && (str_starts_with($line, 'http://') || str_starts_with($line, 'https://'))) {
                $streamUrl = $line;

                if (!isset($seenThisRun[$streamUrl]) && !isset($knownUrls[$streamUrl])) {
                    $shouldInclude = match ($category) {
                        'worldcup' => $this->isWorldCupChannel($currentTitle, $currentMeta),
                        'bangladesh' => $this->isBangladeshChannel($currentTitle, $currentMeta),
                        'football' => $this->isFootballChannel($currentTitle, $currentMeta),
                        default => false,
                    };

                    if ($shouldInclude) {
                        $this->line("     ✓ {$currentTitle}");
                        $seenThisRun[$streamUrl] = true;
                        $batch[] = [
                            'title' => $currentTitle,
                            'url' => $streamUrl,
                            'category' => $category,
                            'meta' => $currentMeta,
                            'sourceLabel' => $sourceLabel,
                        ];
                        $dispatched++;

                        if (count($batch) >= $chunk) {
                            if (!$dryRun)
                                $this->dispatchBatch($batch);
                            $batch = [];
                        }
                    }
                }

                $currentTitle = null;
                $currentMeta = [];
            }
        }

        if (!$dryRun && !empty($batch)) {
            $this->dispatchBatch($batch);
        }

        return [$dispatched, $seenThisRun];
    }

    private function dispatchBatch(array $batch): void
    {
        foreach ($batch as $item) {
            CheckAndSyncStreamJob::dispatch(
                $item['title'],
                $item['url'],
                false,  // isExisting
                $item['category'],
                $item['meta']['country'] ?? '',
                $item['meta']['language'] ?? '',
                '',     // broadcaster — detected from title
                $item['sourceLabel'],
                $item['meta'],
            )->onQueue('default');
        }
    }

    // ===================================================================
    //  M3U Parser
    // ===================================================================
    private function parseTitle(string $line): ?string
    {
        if (str_contains($line, ',')) {
            $t = trim(Str::afterLast($line, ','));
            return $t !== '' ? $t : null;
        }
        return null;
    }

    private function parseMeta(string $line): array
    {
        $meta = [];
        if (preg_match('/tvg-country=["\']([^"\']+)["\']/', $line, $m))
            $meta['country'] = strtoupper(trim($m[1]));
        if (preg_match('/group-title=["\']([^"\']+)["\']/', $line, $m))
            $meta['group'] = strtolower(trim($m[1]));
        if (preg_match('/tvg-language=["\']([^"\']+)["\']/', $line, $m))
            $meta['language'] = strtolower(trim($m[1]));
        if (preg_match('/tvg-name=["\']([^"\']+)["\']/', $line, $m))
            $meta['tvg_name'] = trim($m[1]);
        if (preg_match('/tvg-logo=["\']([^"\']+)["\']/', $line, $m))
            $meta['logo'] = trim($m[1]);
        return $meta;
    }

    // ===================================================================
    //  FILTERS
    // ===================================================================
    private function isWorldCupChannel(string $title, array $meta): bool
    {
        $lower = Str::lower($title);
        $tvgName = Str::lower($meta['tvg_name'] ?? '');
        $group = $meta['group'] ?? '';

        foreach ($this->worldCupKeywords as $kw) {
            if (str_contains($lower, $kw) || ($tvgName && str_contains($tvgName, $kw)))
                return true;
        }

        if (str_contains($group, 'world cup') || str_contains($group, 'worldcup') || str_contains($group, 'fifa')) {
            return true;
        }

        return false;
    }

    private function isBangladeshChannel(string $title, array $meta): bool
    {
        if (($meta['country'] ?? '') === 'BD')
            return true;
        if (str_contains($meta['group'] ?? '', 'bangladesh'))
            return true;
        if (in_array($meta['language'] ?? '', ['ben', 'bengali', 'bangla'], true))
            return true;

        $lower = Str::lower($title);
        foreach ($this->bangladeshChannels as $kw) {
            if (str_contains($lower, Str::lower($kw)))
                return true;
        }
        return false;
    }

    private function isFootballChannel(string $title, array $meta): bool
    {
        $group = $meta['group'] ?? '';
        if (str_contains($group, 'sport') || str_contains($group, 'football') || str_contains($group, 'soccer')) {
            return true;
        }

        $lower = Str::lower($title);
        foreach ($this->footballKeywords as $kw) {
            if (str_contains($lower, Str::lower($kw)))
                return true;
        }
        return false;
    }
}