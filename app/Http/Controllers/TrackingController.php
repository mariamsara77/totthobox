<?php

namespace App\Http\Controllers;

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

    public function trackEvent(Request $request)
    {
        try {
            // ১. ভিজিটর আইডেন্টিফাই করা (Middleware থেকে আসলে ভালো, না থাকলে সার্ভিস থেকে তৈরি হবে)
            $visitor = $request->attributes->get('current_visitor')
                ?? $this->trackingService->getOrCreateVisitor([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

            if (!$visitor) {
                return response()->json(['status' => 'ignored'], 200);
            }

            // ২. অফলাইন বা ব্যাচ ডাটা হ্যান্ডেল করা
            $events = $request->input('data') ?? [$request->all()];
            $syncedCount = 0;

            foreach ($events as $event) {
                $type = $event['event_type'] ?? $event['type'] ?? 'interaction';
                $name = $event['event_name'] ?? $event['key'] ?? 'unknown_action';
                $data = $event['event_data'] ?? $event['value'] ?? [];

                // ৩. যদি এটি Page View হয়, তবে হার্ডওয়্যার স্পেকস আপডেট করা
                if ($type === 'pageview') {
                    $this->updateVisitorSpecs($visitor, $data);
                }

                // ৪. ইভেন্ট ডাটাবেসে সেভ করা (VisitorEvent টেবিল)
                $this->trackingService->trackEvent(
                    $visitor,
                    $type,
                    $name,
                    $data
                );

                $syncedCount++;
            }

            return response()->json([
                'status' => 'success',
                'synced' => $syncedCount
            ]);

        } catch (\Exception $e) {
            Log::error("Tracking Controller Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ভিজিটরের হার্ডওয়্যার এবং সিস্টেম ইনফো আপডেট করার মেথড
     */
    protected function updateVisitorSpecs($visitor, $data)
    {
        // জাভাস্ক্রিপ্ট থেকে আসা ডাটা এবং ডাটাবেস কলাম ম্যাপিং
        $updateData = [];

        if (isset($data['screen_resolution'])) {
            $updateData['screen_resolution'] = $data['screen_resolution'];
        }

        if (isset($data['ram']) && $data['ram'] !== 'unknown') {
            $updateData['ram_gb'] = (float) $data['ram'];
        }

        if (isset($data['cpu_cores']) && $data['cpu_cores'] !== 'unknown') {
            $updateData['cpu_cores'] = (int) $data['cpu_cores'];
        }

        if (isset($data['network'])) {
            $updateData['network_type'] = $data['network'];
        }

        if (isset($data['timezone'])) {
            $updateData['timezone'] = $data['timezone'];
        }

        if (!empty($updateData)) {
            $updateData['last_seen_at'] = now();
            $visitor->update($updateData);
        }
    }
}