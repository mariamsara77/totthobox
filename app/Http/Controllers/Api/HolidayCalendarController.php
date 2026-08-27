<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayCalendarController extends Controller
{
    /**
     * নির্দিষ্ট বছরের সকল ছুটির তালিকা প্রদান করবে
     */
    public function index(Request $request)
    {
        // রিকোয়েস্ট থেকে year নিবে, না থাকলে বর্তমান বছর নিবে
        $year = $request->input('year', date('Y'));

        // Active এবং Published ছুটিগুলো ফিল্টার করা
        $holidays = Holiday::where(function ($query) use ($year) {
                // নির্দিষ্ট বছরের ছুটি (যেমন ঈদ, যা প্রতি বছর বদলায়)
                $query->whereYear('date', $year)
                      // অথবা যে ছুটিগুলো প্রতি বছর একই তারিখে হয়
                      ->orWhere('is_annual', true);
            })
            ->get();

        // ডাটা ফরম্যাটিং (Next.js এ সহজে ব্যবহার করার জন্য)
        $formattedHolidays = $holidays->map(function ($holiday) {
            return [
                'id' => $holiday->id,
                'title' => $holiday->title,
                // Next.js এর জন্য মাস এবং দিন (MM-DD) আলাদা করে দিচ্ছি
                'date' => Carbon::parse($holiday->date)->format('m-d'),
                'full_date' => Carbon::parse($holiday->date)->format('Y-m-d'),
                'type' => $holiday->type,
                'is_annual' => $holiday->is_annual,
                // টাইপ অনুযায়ী কালার দিতে পারেন, আপাতত 'amber' রাখছি
                'color' => 'amber', 
            ];
        });

        return response()->json([
            'success' => true,
            'year' => (int) $year,
            'data' => $formattedHolidays
        ]);
    }
}