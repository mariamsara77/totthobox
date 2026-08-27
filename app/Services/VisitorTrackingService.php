<?php

namespace App\Services;

use App\Jobs\TrackVisitorJob;
use App\Models\PageView;
use App\Models\Visitor;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class VisitorTrackingService
{
    protected Agent $agent;

    public function __construct()
    {
        $this->agent = new Agent;
    }

    /* -----------------------------------------------------------------
     |  PUBLIC METHODS
     | ----------------------------------------------------------------- */

    public function dispatchTracking(Request $request): void
    {
        $this->agent->setUserAgent($request->userAgent() ?? '');

        if ($this->agent->isRobot()) {
            return;
        }

        $payload = [
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'url' => $request->fullUrl(),
            'route_name' => $request->route()?->getName(),
            'referer' => $request->headers->get('referer'),
            'user_id' => Auth::id(),
            'is_pwa' => $this->resolveIsPwa($request),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'load_time_ms' => defined('LARAVEL_START')
                                ? (int) round((microtime(true) - LARAVEL_START) * 1000)
                                : 0,
            'page_title' => config('app.current_page_title'),
            'timestamp' => now()->toISOString(),
        ];

        TrackVisitorJob::dispatch($payload)->onQueue('tracking');
    }

    public function processTrackingPayload(array $data): void
    {
        try {
            $this->agent->setUserAgent($data['user_agent'] ?? '');

            $visitor = $this->getOrCreateVisitorFromData($data);
            $session = $this->getOrCreateSessionFromData($visitor, $data);

            $this->touchVisitor($visitor, $data['user_id'] ?? null);
            $this->touchSession($session);
            $this->recordPageViewFromData($visitor, $session, $data);

        } catch (\Throwable $e) {
            Log::error('VisitorTrackingService::processTrackingPayload failed', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    public function trackEvent(
        Visitor $visitor,
        string $category,
        string $action,
        ?string $label = null,
        array $payload = []
    ): void {
        try {
            $session = $this->resolveActiveSession($visitor);

            VisitorEvent::create([
                'session_id' => $session?->id,
                'visitor_id' => $visitor->id,
                'event_category' => $category,
                'event_action' => $action,
                'event_label' => $label,
                'payload' => $payload,
                'created_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Event tracking failed', [
                'message' => $e->getMessage(),
                'category' => $category,
                'action' => $action,
            ]);
        }
    }

    public function forceSyncPwaStatus(Request $request): void
    {
        $ip = $request->ip();
        $ua = (string) $request->userAgent();
        $hash = $this->makeHash($ip, $ua);

        $isPwa = $request->boolean('is_pwa')
            || $this->resolveIsPwa($request);

        $visitor = Visitor::where('hash', $hash)->first();

        if ($visitor && $visitor->is_pwa !== $isPwa) {
            $visitor->update(['is_pwa' => $isPwa]);
            $this->bustVisitorCache($hash);
        }

        session(['is_pwa' => $isPwa]);
    }

    public function getOrCreateVisitor(Request $request): Visitor
    {
        $data = [
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'user_id' => Auth::id(),
            'is_pwa' => $this->resolveIsPwa($request),
        ];

        return $this->getOrCreateVisitorFromData($data);
    }

    public function resolveActiveSession(Visitor $visitor): ?VisitorSession
    {
        $cacheKey = "visitor:session:{$visitor->id}";
        $sessionId = Cache::get($cacheKey);

        if ($sessionId) {
            $session = VisitorSession::find($sessionId);
            if ($session && $session->last_active_at?->gt(now()->subMinutes(30))) {
                return $session;
            }
            // Stale cache — clean it up
            Cache::forget($cacheKey);
        }

        return VisitorSession::where('visitor_id', $visitor->id)
            ->where('last_active_at', '>', now()->subMinutes(30))
            ->orderByDesc('last_active_at')
            ->first();
    }

    /* -----------------------------------------------------------------
     |  PROTECTED HELPERS
     | ----------------------------------------------------------------- */

    protected function getOrCreateVisitorFromData(array $data): Visitor
    {
        $ip = $data['ip'];
        $ua = $data['user_agent'] ?? '';
        $hash = $this->makeHash($ip, $ua);
        $cacheKey = "visitor:active:{$hash}";

        // Only use cache if it's a fresh Visitor instance (not stale array)
        $cached = Cache::get($cacheKey);
        if ($cached instanceof Visitor) {
            return $cached;
        }

        $location = Cache::remember(
            "visitor:loc:{$ip}",
            now()->addDay(),
            function () use ($ip) {
                $lookupIp = in_array($ip, ['127.0.0.1', '::1'], true) ? '8.8.8.8' : $ip;

                return Location::get($lookupIp);
            }
        );

        $visitor = Visitor::updateOrCreate(
            ['hash' => $hash],
            [
                'user_id' => $data['user_id'] ?? null,
                'ip_address' => $ip,
                'browser_family' => $this->agent->browser() ?: null,
                'os_family' => $this->agent->platform() ?: null,
                'device_type' => $this->getDeviceType(),
                'country_code' => $location->countryCode ?? null,
                'city_name' => $location->cityName ?? null,
                'is_pwa' => (bool) ($data['is_pwa'] ?? false),
                'is_bot' => false,
                'last_seen_at' => now(),
            ]
        );

        if ($visitor->wasRecentlyCreated || is_null($visitor->first_seen_at)) {
            $visitor->forceFill(['first_seen_at' => now()])->saveQuietly();
        }

        Cache::put($cacheKey, $visitor, now()->addMinutes(10));

        return $visitor;
    }

    protected function getOrCreateSessionFromData(Visitor $visitor, array $data): VisitorSession
    {
        $cacheKey = "visitor:session:{$visitor->id}";
        $sessionId = Cache::get($cacheKey);

        if ($sessionId) {
            $session = VisitorSession::find($sessionId);
            if ($session && $session->last_active_at?->gt(now()->subMinutes(30))) {
                return $session;
            }
        }

        $source = $this->parseTrafficSource($data['referer'] ?? null);

        // UUID explicitly set here to avoid race conditions in boot()
        $session = VisitorSession::create([
            'id' => (string) Str::uuid(),
            'visitor_id' => $visitor->id,
            'origin_type' => $source['type'],
            'origin_source' => $source['source'],
            'entry_url' => Str::limit($data['url'] ?? '', 500),
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'started_at' => now(),
            'last_active_at' => now(),
            'hits_count' => 0,
            'seconds_spent' => 0,
        ]);

        Cache::put($cacheKey, $session->id, now()->addMinutes(35));

        return $session;
    }

    protected function touchVisitor(Visitor $visitor, $userId = null): void
    {
        $visitor->forceFill([
            'last_seen_at' => now(),
            'user_id' => $userId ?? $visitor->user_id,
        ])->saveQuietly();
    }

    protected function touchSession(VisitorSession $session): void
    {
        $now = now();
        $seconds = 0;

        if ($session->last_active_at) {
            $diff = $session->last_active_at->diffInSeconds($now);
            $seconds = (int) min(max($diff, 0), 300); // cap at 5 minutes
        }

        $session->forceFill([
            'last_active_at' => $now,
            'seconds_spent' => $session->seconds_spent + $seconds,
        ])->saveQuietly();
    }

    protected function recordPageViewFromData(Visitor $visitor, VisitorSession $session, array $data): void
    {
        $url = Str::limit($data['url'] ?? '', 500);
        $urlHash = sha1($url);

        // Deduplicate: same URL within 8 seconds in same session
        $recentKey = "pageview:recent:{$session->id}:{$urlHash}";
        if (Cache::has($recentKey)) {
            return;
        }

        $title = $data['page_title']
            ?? Str::headline($data['route_name'] ?? 'Home');

        PageView::create([
            'session_id' => $session->id,
            'visitor_id' => $visitor->id,
            'url' => $url,
            'title' => $title,
            'url_hash' => $urlHash,
            'route_name' => $data['route_name'] ?? null,
            'load_time_ms' => $data['load_time_ms'] ?? 0,
            'created_at' => now(),
        ]);

        // Atomic increment to avoid race condition
        VisitorSession::where('id', $session->id)->increment('hits_count');

        Cache::put($recentKey, true, now()->addSeconds(8));
    }

    protected function parseTrafficSource(?string $referer): array
    {
        if (! $referer) {
            return ['type' => 'direct', 'source' => 'Direct'];
        }

        $host = strtolower(parse_url($referer, PHP_URL_HOST) ?? '');

        $searchEngines = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex'];
        foreach ($searchEngines as $engine) {
            if (str_contains($host, $engine)) {
                return ['type' => 'organic', 'source' => $host];
            }
        }

        $socialDomains = ['facebook', 't.co', 'twitter', 'x.com', 'instagram', 'linkedin', 'tiktok', 'youtube', 'pinterest'];
        foreach ($socialDomains as $social) {
            if (str_contains($host, $social)) {
                return ['type' => 'social', 'source' => $host];
            }
        }

        return ['type' => 'referral', 'source' => $host];
    }

    protected function getDeviceType(): string
    {
        if ($this->agent->isTablet()) {
            return 'tablet';
        }
        if ($this->agent->isMobile()) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Consistent hash generation — always use this method.
     * Never inline hash() calls across the codebase to avoid mismatch.
     */
    public function makeHash(string $ip, string $ua): string
    {
        return hash('sha256', $ip.$ua);
    }

    /**
     * Bust all cache keys associated with a visitor hash.
     */
    public function bustVisitorCache(string $hash): void
    {
        Cache::forget("visitor:active:{$hash}");
        Cache::forget("visitor_v3_{$hash}");
    }

    /**
     * Resolve PWA status from request — single source of truth.
     */
    public function resolveIsPwa(Request $request): bool
    {
        return $request->header('X-App-Mode') === 'standalone'
            || $request->query('utm_source') === 'pwa'
            || $request->boolean('is_pwa');
    }

    // Legacy support
    public function trackRequest(Request $request): void
    {
        $this->dispatchTracking($request);
    }
}