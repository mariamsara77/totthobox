<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Services\VisitorTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function __construct(
        protected VisitorTrackingService $trackingService
    ) {}

    /**
     * Sync PWA install status from the frontend.
     */
    public function syncPwaStatus(Request $request): JsonResponse
    {
        $request->validate([
            'is_pwa' => 'required|boolean',
        ]);

        try {
            $this->trackingService->forceSyncPwaStatus($request);

            return response()->json([
                'status' => 'success',
                'is_pwa' => $request->boolean('is_pwa'),
            ]);
        } catch (\Throwable $e) {
            Log::error('PWA Sync Error', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to sync status',
            ], 500);
        }
    }

    /**
     * Track client-side events (page view, click, hardware, custom).
     */
    public function trackEvent(Request $request): JsonResponse
    {
        try {
            /** @var Visitor|null $visitor */
            $visitor = $request->attributes->get('current_visitor')
                ?? $this->trackingService->getOrCreateVisitor($request);

            if (! $visitor) {
                return response()->json(['status' => 'ignored'], 200);
            }

            $category = $request->input('category', 'interaction');
            $action   = $request->input('action', 'click');
            $payload  = $request->input('payload', []);
            $label    = $payload['label'] ?? $request->input('label');

            // System event → device specs আপডেট
            if ($category === 'system') {
                $this->updateVisitorSpecs($request, $visitor, $payload);
            }

            // Page View → পুরো Visitor + Session + PageView পাইপলাইন
            if ($category === 'page' && $action === 'view') {
                $this->handlePageView($request, $visitor, $payload);
            }

            // সব ইভেন্ট সেভ
            $this->trackingService->trackEvent($visitor, $category, $action, $label, $payload);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            Log::error('Tracking Controller Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            // সবসময় 200 দাও যাতে ক্লায়েন্ট রিট্রাই না করে
            return response()->json(['status' => 'error'], 200);
        }
    }

    /* -----------------------------------------------------------------
     |  PRIVATE HELPERS
     | ----------------------------------------------------------------- */

    /**
     * Frontend Page View হ্যান্ডেল করে (Middleware-এর সমতুল্য)
     */
    private function handlePageView(Request $request, Visitor $visitor, array $payload): void
    {
        $url  = $payload['url'] ?? $request->fullUrl();
        $path = $payload['path'] ?? (parse_url($url, PHP_URL_PATH) ?: '/');

        $data = [
            'ip'           => $request->ip(),
            'user_agent'   => (string) $request->userAgent(),
            'url'          => $url,
            'route_name'   => $this->guessRouteName($path, $payload['route_name'] ?? null),
            'referer'      => $payload['referrer'] ?? $request->headers->get('referer'),
            'user_id'      => Auth::id(),
            'is_pwa'       => $this->trackingService->resolveIsPwa($request) || !empty($payload['is_pwa']),
            'utm_source'   => $payload['utm_source'] ?? $request->query('utm_source'),
            'utm_medium'   => $payload['utm_medium'] ?? $request->query('utm_medium'),
            'utm_campaign' => $payload['utm_campaign'] ?? $request->query('utm_campaign'),
            'load_time_ms' => $payload['load_time_ms'] ?? 0,
            'page_title'   => $payload['title'] ?? null,
            'timestamp'    => now()->toISOString(),
        ];

        // সরাসরি প্রসেস (ফাস্ট). চাইলে Job ব্যবহার করতে পারো।
        $this->trackingService->processTrackingPayload($data);

        // Queue ব্যবহার করতে চাইলে উপরের লাইন কমেন্ট করে এটা চালাও:
        // \App\Jobs\TrackVisitorJob::dispatch($data)->onQueue('tracking');
    }

    /**
     * Path থেকে route_name বানায়
     */
    private function guessRouteName(?string $path, ?string $frontendName = null): ?string
    {
        if ($frontendName) {
            return $frontendName;
        }

        if (!$path || $path === '/') {
            return 'home';
        }

        // তোমার সাইট অনুযায়ী ম্যাপিং বাড়াও
        $map = [
            '/products'   => 'products.index',
            '/cart'       => 'cart',
            '/checkout'   => 'checkout',
            '/account'    => 'account',
            '/blog'       => 'blog.index',
            '/contact'    => 'contact',
            '/about'      => 'about',
        ];

        if (isset($map[$path])) {
            return $map[$path];
        }

        // Wildcard স্টাইল
        if (str_starts_with($path, '/products/')) {
            return 'products.show';
        }
        if (str_starts_with($path, '/blog/')) {
            return 'blog.show';
        }

        return str_replace('/', '.', trim($path, '/')) ?: 'home';
    }

    /**
     * System event থেকে device/timezone আপডেট
     */
    private function updateVisitorSpecs(Request $request, Visitor $visitor, array $data): void
    {
        $update = [];

        if (!empty($data['timezone'])) {
            $update['timezone'] = $data['timezone'];
        }

        if (!empty($data['screen_res'])) {
            $res     = $data['screen_res'];
            $current = $visitor->device_model ?? '';

            if (!str_contains($current, $res)) {
                $update['device_model'] = trim($current . ' | ' . $res, ' | ');
            }
        }

        if (empty($update)) {
            return;
        }

        $update['last_seen_at'] = now();
        $visitor->update($update);

        $hash = $this->trackingService->makeHash(
            $request->ip(),
            (string) $request->userAgent()
        );

        $this->trackingService->bustVisitorCache($hash);
    }
}