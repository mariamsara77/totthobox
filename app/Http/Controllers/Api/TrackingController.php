<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Services\VisitorTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    protected $trackingService;

    public function __construct(VisitorTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * PWA স্ট্যাটাস সিঙ্ক করার জন্য (JS থেকে কল হবে)
     */
// VisitorController.php
    public function syncPwaStatus(Request $request)
    {
        // ১. ভ্যালিডেশন (নিশ্চিত হওয়া যে ডাটা ঠিক আছে)
        $validated = $request->validate([
            'is_pwa' => 'required|boolean'
        ]);

        try {
            // ২. আপনার সার্ভিস কল করা
            $this->trackingService->forceSyncPwaStatus($request);

            // ৩. সেশনে সেভ করে রাখা যাতে ব্লেড ফাইলে চেক করা সহজ হয়
            session(['is_pwa' => $request->is_pwa]);

            return response()->json([
                'status' => 'success',
                'is_pwa' => $request->is_pwa // ডাইনামিক স্ট্যাটাস পাঠানো
            ]);

        } catch (\Exception $e) {
            Log::error("PWA Sync Error: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to sync status'
            ], 500); // সার্ভার এরর বুঝাতে ৫০০ দেওয়া ভালো
        }
    }
    /**
     * বাটন ক্লিক, ফরম সাবমিট বা সিস্টেম ইনফো ট্র্যাক করার জন্য
     */
    public function trackEvent(Request $request)
    {
        try {
            // ১. ভিজিটর আইডেন্টিফাই (মিডলওয়্যার থেকে অথবা নতুন তৈরি)
            $visitor = $request->attributes->get('current_visitor')
                ?? $this->trackingService->getOrCreateVisitor($request);

            if (!$visitor) {
                return response()->json(['status' => 'ignored'], 200);
            }

            // ২. ডেটা রিসিভ
            $category = $request->input('category', 'interaction');
            $action = $request->input('action', 'click');
            $payload = $request->input('payload', []);
            $label = $payload['label'] ?? null;

            // ৩. সিস্টেম ইনফো আপডেট (Timezone, Screen Resolution ইত্যাদি)
            if ($category === 'system') {
                $this->updateVisitorSpecs($visitor, $payload);
            }

            // ৪. ইভেন্ট সেভ করা (Service এর মাধ্যমে)
            $this->trackingService->trackEvent($visitor, $category, $action, $label, $payload);

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error("Tracking Controller Error: " . $e->getMessage());
            return response()->json(['status' => 'error'], 200);
        }
    }

    protected function updateVisitorSpecs($visitor, $data)
    {
        $updateData = [];

        if (!empty($data['timezone'])) {
            $updateData['timezone'] = $data['timezone'];
        }

        if (!empty($data['screen_res'])) {
            // রেজোলিউশনকে মডেলের সাথে অ্যাপেন্ড করা
            $res = $data['screen_res'];
            if (!str_contains($visitor->device_model ?? '', $res)) {
                $updateData['device_model'] = trim(($visitor->device_model ?? '') . ' | ' . $res, ' | ');
            }
        }

        if (!empty($updateData)) {
            $updateData['last_seen_at'] = now();
            $visitor->update($updateData);

            // ক্যাশ আপডেট করা যাতে রিপোর্টে সাথে সাথে দেখা যায়
            $cacheKey = "v_active_" . hash('sha256', request()->ip() . request()->userAgent());
            \Illuminate\Support\Facades\Cache::put($cacheKey, $visitor->fresh(), 300);
        }
    }
}