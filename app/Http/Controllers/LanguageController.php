<?php

namespace App\Http\Controllers;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        $supported = ['bn', 'en', 'ar', 'hi'];

        if (!in_array($locale, $supported)) {
            return redirect()->back();
        }

        // ✅ বাংলা সিলেক্ট = সেশন ক্লিয়ার (ডিফল্ট মোড)
        if ($locale === 'bn') {
            session()->forget('app_locale'); // locale এর বদলে app_locale
        } else {
            session(['app_locale' => $locale]); // locale এর বদলে app_locale
        }

        // 🔥 লাইভওয়্যার রিকোয়েস্ট - JSON রেসপন্স
        if (request()->ajax() || request()->header('X-Livewire')) {
            return response()->json([
                'success' => true,
                'locale' => $locale,
                'is_default' => $locale === 'bn'
            ]);
        }

        return redirect()->back();
    }
}