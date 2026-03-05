<?php

namespace App\Http\Middleware;

use App\Services\VisitorTrackingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Visitor;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    protected $trackingService;

    public function __construct(VisitorTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $visitorHash = hash('sha256', $request->ip() . $request->userAgent());

        // ১. রিকোয়েস্ট থেকে PWA স্ট্যাটাস ডিটেক্ট করা
        $isPwaRequest = $request->header('X-App-Mode') === 'standalone' ||
            $request->query('utm_source') === 'pwa';

        // ২. ভিজিটর ক্যাশ থেকে নিন
        $visitor = Cache::remember("visitor_v3_{$visitorHash}", now()->addMinutes(15), function () use ($visitorHash) {
            return Visitor::where('hash', $visitorHash)->first();
        });

        if ($visitor) {
            // যদি ডাটাবেসের সাথে বর্তমান রিকোয়েস্টের মিল না থাকে (যেমন আনইনস্টল বা ব্রাউজার মোড)
            if ($visitor->is_pwa !== $isPwaRequest && !$this->shouldSkipTracking($request)) {
                $visitor->update(['is_pwa' => $isPwaRequest]);
                Cache::forget("visitor_v3_{$visitorHash}");
            }

            view()->share('currentVisitor', $visitor);
            $request->attributes->set('current_visitor', $visitor);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // ২০০ OK এবং ট্র্যাকিং স্কিপ না করলেই কেবল ট্র্যাক করো
        if ($response->getStatusCode() === 200 && !$this->shouldSkipTracking($request)) {
            $this->trackingService->trackRequest($request);
        }
    }

    protected function shouldSkipTracking(Request $request): bool
    {
        // PWA সিঙ্ক রুট হলে স্কিপ করবেন না
        if ($request->is('api/tracking/sync-pwa')) {
            return false;
        }

        if ($request->is('lang/*') || $request->header('X-Livewire') || $request->is('livewire/*')) {
            return true;
        }

        // অন্যান্য API রুট স্কিপ করুন
        if ($request->is('api/*')) {
            return true;
        }

        if ($request->is('admin/*', 'horizon/*', 'telescope/*', 'nova/*')) {
            return true;
        }

        return !$request->isMethod('GET') || !$request->acceptsHtml();
    }
}