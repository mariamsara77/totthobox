<?php

namespace App\Services;

use App\Models\{Visitor, VisitorSession, PageView};
use Illuminate\Support\Facades\{Log, DB, Auth, Cache};
use Stevebauman\Location\Facades\Location;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VisitorTrackingService
{
    protected Agent $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

  public function forceSyncPwaStatus($request)
{
    $ip = $request->ip();
    $ua = $request->userAgent();
    $hash = hash('sha256', $ip . $ua);
    
    // বর্তমান রিকোয়েস্টটি PWA কি না (Header বা Query থেকে)
    $isPwaNow = $request->header('X-App-Mode') === 'standalone' || 
                 $request->query('utm_source') === 'pwa';

    // ডাটাবেস থেকে ভিজিটর নিন
    $visitor = Visitor::where('hash', $hash)->first();

    if ($visitor && $visitor->is_pwa !== $isPwaNow) {
        $visitor->update(['is_pwa' => $isPwaNow]);
        
        // সব ক্যাশ ক্লিয়ার করুন যাতে লাইভ আপডেট দেখা যায়
        Cache::forget("v_active_{$hash}");
        Cache::forget("visitor_v3_{$hash}");
    }

    session(['is_pwa' => $isPwaNow]);
}

    public function trackRequest($request)
    {
        // 1. Instant Bot Kill
        if ($this->agent->isRobot()) return;

        try {
            // No DB::transaction here to avoid row locking across tables
            $visitor = $this->getOrCreateVisitor($request);
            $session = $this->getOrCreateSession($visitor, $request);
            $this->recordPageView($visitor, $session, $request);
        } catch (\Exception $e) {
            Log::error("Tracking Error: " . $e->getMessage());
        }
    }

    // ১. Visitor এবং Session হ্যান্ডলিং ঠিক করা
    public function getOrCreateVisitor($request): Visitor
    {
        $ip = $request->ip();
        $ua = $request->userAgent();
        $hash = hash('sha256', $ip . $ua);
        $cacheKey = "v_active_{$hash}";

        // ক্যাশ থেকে ভিজিটর নিন
        $visitor = Cache::get($cacheKey);

        if (!$visitor) {
            // লোকেশন ক্যাশ করা (আপনি অলরেডি করছেন, ভালো প্র্যাকটিস)
            $location = Cache::remember(
                "v_loc_{$ip}",
                86400,
                fn() =>
                Location::get(in_array($ip, ['127.0.0.1', '::1']) ? '8.8.8.8' : $ip)
            );

            // লারাভেল ১২ স্ট্যান্ডার্ড: সরাসরি ডাটাবেসে হিট না করে আগে চেক বা অপ্টিমাইজড আপসার্ট
            $visitor = Visitor::updateOrCreate(
                ['hash' => $hash],
                [
                    'user_id' => Auth::id(),
                    'ip_address' => $ip,
                    'browser_family' => $this->agent->browser(),
                    'os_family' => $this->agent->platform(),
                    'device_type' => $this->getDeviceType(),
                    'country_code' => $location->countryCode ?? null,
                    'last_seen_at' => now(),
                ]
            );
            Cache::put($cacheKey, $visitor, 300);
        }

        return $visitor;
    }

    protected function getOrCreateSession(Visitor $visitor, $request): VisitorSession
    {
        $cacheKey = "v_sess_id_{$visitor->id}";
        $sessionId = Cache::get($cacheKey);

        // যদি ক্যাশে সেশন আইডি থাকে, তবে ডাটাবেস থেকে শুধু ওই সেশনটি নিন
        if ($sessionId) {
            $session = VisitorSession::find($sessionId);
            if ($session && $session->last_active_at > now()->subMinutes(30)) {
                return $session;
            }
        }

        // সেশন না থাকলে বা ৩০ মিনিট পার হলে নতুন তৈরি করুন
        $referer = $request->headers->get('referer');
        $source = $this->parseTrafficSource($referer);

        $session = VisitorSession::create([
            'id' => (string) Str::uuid(),
            'visitor_id' => $visitor->id,
            'origin_type' => $source['type'],
            'origin_source' => $source['source'],
            'entry_url' => Str::limit($request->fullUrl(), 500),
            'started_at' => now(),
            'last_active_at' => now(),
        ]);

        Cache::put($cacheKey, $session->id, 1800);
        return $session;
    }

    protected function recordPageView($visitor, $session, $request)
    {

        $dynamicTitle = config('app.current_page_title')
            ?? Str::headline($request->route()?->getName() ?? 'Home');
        PageView::create([
            'session_id'   => $session->id,
            'visitor_id'   => $visitor->id,
            'url'          => Str::limit($request->fullUrl(), 500),
            'title' => $dynamicTitle,
            'url_hash'     => sha1($request->fullUrl()),
            'route_name'   => $request->route()?->getName(),
            'load_time_ms' => defined('LARAVEL_START') ? round((microtime(true) - LARAVEL_START) * 1000) : 0,
            'created_at'   => now(),
        ]);

        // Use increment without retrieving the model
        VisitorSession::where('id', $session->id)->increment('hits_count');
    }

    protected function parseTrafficSource($referer): array
    {
        if (!$referer)
            return ['type' => 'direct', 'source' => 'Direct'];

        $host = strtolower(parse_url($referer, PHP_URL_HOST));

        if (str_contains($host, 'google') || str_contains($host, 'bing'))
            return ['type' => 'organic', 'source' => $host];

        if (str_contains($host, 'facebook') || str_contains($host, 't.co') || str_contains($host, 'instagram'))
            return ['type' => 'social', 'source' => $host];

        return ['type' => 'referral', 'source' => $host];
    }

    protected function getDeviceType(): string
    {
        if ($this->agent->isTablet())
            return 'tablet';
        if ($this->agent->isMobile())
            return 'mobile';
        return 'desktop';
    }

    public function trackEvent($visitor, $category, $action, $label = null, $payload = [])
    {
        try {
            $cacheKey = "v_sess_obj_{$visitor->id}";
            $session = Cache::get($cacheKey);

            // সেশন না থাকলে নতুন তৈরি করুন
            if (!$session) {
                $session = VisitorSession::where('visitor_id', $visitor->id)
                    ->where('last_active_at', '>', now()->subMinutes(30))
                    ->orderByDesc('last_active_at')
                    ->first();

                if ($session) {
                    Cache::put($cacheKey, $session, 1800);
                }
            }

            // ইভেন্ট তৈরি করুন - session_id nullable হওয়ায় এখন error হবে না
            \App\Models\VisitorEvent::create([
                'session_id' => $session->id ?? null, // ✅ nullable
                'event_category' => $category,
                'event_action' => $action,
                'event_label' => $label,
                'payload' => $payload,
                'created_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Error লগ করুন কিন্তু application crash করবেন না
            Log::error('Event tracking failed', [
                'error' => $e->getMessage(),
                'category' => $category,
                'action' => $action
            ]);
        }
    }
}