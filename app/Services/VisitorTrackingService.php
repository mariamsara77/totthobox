<?php

namespace App\Services;

use App\Models\{Visitor, VisitorSession, PageView, VisitorEvent};
use Illuminate\Support\Facades\{DB, Cache, Log};
use Stevebauman\Location\Facades\Location;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class VisitorTrackingService
{
    protected $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    public function trackRequest($request)
    {
        if ($this->agent->isRobot()) {
            return null;
        }

        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'full_url' => $request->fullUrl(),
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'referer' => $request->headers->get('referer'),
            'session_id' => session()->getId(),
            'start_time' => defined('LARAVEL_START') ? LARAVEL_START : microtime(true),
        ];

        $visitor = $this->getOrCreateVisitor($data);
        $session = $this->getCurrentSession($visitor, $data);

        $this->trackPageView($visitor, $session, $data);

        return $visitor;
    }

    protected function trackPageView($visitor, $session, $data)
    {
        try {
            $loadTime = (microtime(true) - $data['start_time']) * 1000;

            PageView::create([
                'visitor_id' => $visitor->id,
                'session_id' => $session->id,
                'url' => $data['full_url'],
                'route_name' => $data['route_name'],
                'method' => $data['method'],
                'status_code' => http_response_code() ?: 200,
                'load_time' => round($loadTime, 2),
                'is_ajax' => request()->ajax(),
                'is_secure' => request()->secure(),
            ]);

            $visitor->update(['last_seen_at' => now()]);
        } catch (\Exception $e) {
            Log::error("Visitor Tracking PageView Error: " . $e->getMessage());
        }
    }

    protected function getCurrentSession($visitor, $data = [])
    {
        // ১. আগে চেক করি কোনো একটিভ সেশন আছে কি না
        $session = VisitorSession::where('visitor_id', $visitor->id)
            ->whereNull('ended_at')
            ->where('started_at', '>=', now()->subMinutes(30))
            ->latest()
            ->first();

        if (!$session) {
            // ২. সেশন হাশ তৈরি (এটি ইউনিক হতে হবে আজকের দিন বা নির্দিষ্ট উইন্ডোর জন্য)
            $sessionHash = sha1($visitor->hash . ($data['session_id'] ?? Str::random(10)) . date('Y-m-d H'));

            try {
                // ৩. firstOrCreate সঠিক ভাবে ব্যবহার: প্রথম অ্যারে দিয়ে সার্চ করবে, না পেলে দ্বিতীয় অ্যারে সহ ইনসার্ট করবে
                $session = VisitorSession::firstOrCreate(
                    ['session_hash' => $sessionHash],
                    [
                        'id' => (string) Str::uuid(),
                        'visitor_id' => $visitor->id,
                        'started_at' => now(),
                    ]
                );
            } catch (QueryException $e) {
                // ৪. যদি Race Condition এর কারণে ইনসার্ট ফেইল করে, তবে দ্রুত বিদ্যমান সেশনটি খুঁজে নেবে
                if ($e->getCode() === '23000') {
                    $session = VisitorSession::where('session_hash', $sessionHash)->first();
                } else {
                    Log::error("Session Creation Error: " . $e->getMessage());
                }
            }
        }

        return $session;
    }

    public function trackEvent($visitor, $type, $name, $data = [], $timestamp = null)
    {
        try {
            $session = $this->getCurrentSession($visitor);

            return VisitorEvent::create([
                'visitor_id' => $visitor->id,
                'session_id' => $session?->id,
                'event_type' => $type,
                'event_name' => $name,
                'event_data' => $data,
                'created_at' => $timestamp ? \Carbon\Carbon::createFromTimestampMs($timestamp) : now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Visitor Tracking Event Error: " . $e->getMessage());
            return null;
        }
    }

    public function getOrCreateVisitor($data)
    {
        $hash = sha1($data['ip'] . $data['user_agent']);

        return Visitor::firstOrCreate(
            ['hash' => $hash],
            [
                'ip_address' => $data['ip'],
                'user_agent' => $data['user_agent'],
                'browser' => $this->agent->browser(),
                'os' => $this->agent->platform(),
                'device' => $this->getDeviceType(),
                'last_seen_at' => now(),
                'first_seen_at' => now(),
            ]
        );
    }

    protected function getDeviceType()
    {
        if ($this->agent->isTablet())
            return 'tablet';
        if ($this->agent->isMobile())
            return 'mobile';
        return 'desktop';
    }
}