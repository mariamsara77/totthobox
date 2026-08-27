<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\TranslationService;
use Illuminate\Support\Facades\{Session, App, Log};
use Illuminate\Http\Response;

class TranslateMiddleware
{
    public function handle($request, Closure $next)
    {
        $default = config('translator.default', 'bn');
        $locale = $request->cookie('app_locale') ?? Session::get('app_locale', $default);

        // ১. গ্লোবাল লোকাল সেট করা
        App::setLocale($locale);

        $response = $next($request);

        // ২. লাইভওয়্যার বা AJAX রিকোয়েস্ট হলে সাথে সাথে রিটার্ন করুন (মোস্ট ইম্পর্ট্যান্ট)
        // এটি JSON ডাটা ভাঙা থেকে রক্ষা করবে
        if ($request->hasHeader('X-Livewire') || $request->ajax() || $request->wantsJson()) {
            return $response;
        }

        // ৩. রেসপন্স ভ্যালিডেশন
        if (!$response instanceof Response || $response->getStatusCode() !== 200) {
            return $response;
        }

        // ৪. ডিফল্ট লোকাল হলে প্রসেসিং দরকার নেই
        if ($locale === $default) {
            return $response;
        }

        try {
            $content = $response->getContent();

            // ৫. শুধু HTML কন্টেন্ট হলেই ট্রান্সলেট করুন
            if ($content && $this->isHtmlResponse($response)) {
                $translated = app(TranslationService::class)->translateHtml($content, $locale);
                $response->setContent($translated);
            }
        } catch (\Exception $e) {
            Log::error('Translation Middleware Error: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * চেক করে রেসপন্সটি আসলে HTML কি না
     */
    protected function isHtmlResponse($response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        return str_contains($contentType, 'text/html');
    }
}