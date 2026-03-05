<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\VisitorEvent; // আমি VisitorEvent মডেলটি ধরছি, আপনি আপনার মডেল নাম দিন

class ActivitySyncController extends Controller
{
    public function sync(Request $request)
    {
        // ১. ভ্যালিডেশন: 'activities' কী ব্যবহার করা হয়েছে যা নতুন JS থেকে আসবে
        $validated = $request->validate([
            'activities' => 'required|array',
            'activities.*.type' => 'required|string',
            'activities.*.key' => 'required|string',
            'activities.*.value' => 'nullable',
            'activities.*.timestamp' => 'required',
            'activities.*.id' => 'nullable'
        ]);

        try {
            $activities = $validated['activities'];
            $visitorId = $request->attributes->get('current_visitor')?->id;

            foreach ($activities as $item) {
                // ২. মিলি-সেকেন্ড টাইমস্ট্যাম্পকে মানুষের পড়ার যোগ্য ফরমেটে রূপান্তর
                // JS থেকে আসা ১০ বা ১৩ ডিজিটের টাইমস্ট্যাম্প হ্যান্ডেল করা
                $timestamp = (int) ($item['timestamp'] / 1000);
                $formattedDate = date('Y-m-d H:i:s', $timestamp);

                // ৩. ডাটাবেসে সেভ করা (VisitorEvent মডেলে)
                // updateOrCreate ব্যবহার করা হয়েছে যাতে একই অফলাইন ডাটা বারবার না আসে
                \App\Models\VisitorEvent::updateOrCreate(
                    [
                        'event_action' => $item['key'],
                        'created_at' => $formattedDate
                    ],
                    [
                        'event_category' => $item['type'],
                        'event_label' => is_array($item['value']) ? json_encode($item['value']) : $item['value'],
                        'payload' => $item, // পুরো ডাটা পেলোড হিসেবে থাকল
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Synced successfully',
                'count' => count($activities)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Sync Failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Sync failed'], 500);
        }
    }
}