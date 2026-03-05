<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Session;

class TranslateMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // ১. লোকেল চেক
        $locale = Session::get('app_locale', $request->cookie('app_locale', config('translator.default')));

        if (
            $locale === config('translator.default') ||
            !method_exists($response, 'getContent') ||
            $response->getStatusCode() !== 200 // শুধুমাত্র সাকসেসফুল রেসপন্স ট্রান্সলেট করুন
        ) {
            return $response;
        }

        // ২. লাইভওয়্যার রিকোয়েস্ট চেক (Livewire রিকোয়েস্টে পুরো HTML থাকে না, তাই স্কিপ করা ভালো)
        if ($request->header('X-Livewire')) {
            return $response;
        }

        $content = $response->getContent();

        // ৩. শুধুমাত্র HTML রেসপন্স হলে কাজ করুন
        if (str_contains($response->headers->get('Content-Type'), 'text/html')) {

            // এসইও এবং স্ক্রিপ্ট ট্যাগগুলোকে ট্রান্সলেশন থেকে বাদ দেওয়া জরুরি
            // আপনার TranslationService-এ এমন মেকানিজম থাকা উচিত যা <script> এবং <meta> ট্যাগ স্কিপ করে
            $translator = app(TranslationService::class);
            $translated = $translator->translateHtml($content, $locale);

            $response->setContent($translated);
        }

        return $response;
    }
}