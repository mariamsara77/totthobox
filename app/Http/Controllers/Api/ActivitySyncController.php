<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\UserActivity; // আপনার যদি নির্দিষ্ট মডেল থাকে

class ActivitySyncController extends Controller
{
    public function sync(Request $request)
    {
        // ১. ডাটা ভ্যালিডেশন
        $validated = $request->validate([
            'data' => 'required|array',
            'data.*.type' => 'required|string',
            'data.*.key' => 'required|string',
            'data.*.value' => 'nullable',
            'data.*.timestamp' => 'required',
        ]);

        try {
            $activities = $validated['data'];

            foreach ($activities as $item) {
                // ২. ডাটাবেসে সেভ করার লজিক 
                // আপনি চাইলে আলাদা টেবিলে রাখতে পারেন অথবা লগ ফাইল হিসেবে রাখতে পারেন
                Log::info('Offline Activity Received:', $item);

                /* // উদাহরণ: যদি ডাটাবেসে সেভ করতে চান
                UserActivity::create([
                    'user_id'   => auth()->id() ?? null, // লগইন করা থাকলে আইডি পাবে
                    'type'      => $item['type'],
                    'key'       => $item['key'],
                    'value'     => $item['value'],
                    'meta'      => json_encode($item),
                    'created_at'=> date('Y-m-d H:i:s', $item['timestamp'] / 1000),
                ]);
                */
            }

            return response()->json(['status' => 'success', 'message' => 'Data Synced!'], 200);

        } catch (\Exception $e) {
            Log::error('Sync Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Sync Failed'], 500);
        }
    }
}