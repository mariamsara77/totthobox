<?php

namespace App\Http\Middleware;

use App\Services\VisitorTrackingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Visitor;

class TrackVisitors
{
    protected $trackingService;

    public function __construct(VisitorTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function handle(Request $request, Closure $next)
    {
        // ১. ট্র্যাকিং বাদ দেওয়ার কন্ডিশন চেক
        if ($this->shouldSkipTracking($request)) {
            return $next($request);
        }

        // ২. ট্র্যাকিং জব কিউতে পাঠানো (Background processing)
        $this->trackingService->trackRequest($request);

        // ৩. ভিজিটর আইডেন্টিফাই করা
        $visitorHash = sha1($request->ip() . $request->userAgent());

        // ৪. ক্যাশ থেকে ডাটা নেওয়া বা ডাটাবেস চেক করা
        $visitor = $this->getVisitor($visitorHash);

        if ($visitor) {
            view()->share('currentVisitor', $visitor);
        }

        return $next($request);
    }

    /**
     * ভিজিটর ডাটা খুঁজে বের করা এবং ক্যাশ করা
     */
    protected function getVisitor(string $visitorHash)
    {
        $cacheKey = "visitor_view_{$visitorHash}";

        // প্রথমে ক্যাশে আছে কি না চেক করি
        $visitor = Cache::get($cacheKey);

        if (!$visitor) {
            // IDE এরর এড়াতে ৩টি আর্গুমেন্ট ব্যবহার করা হয়েছে
            $visitor = Visitor::where('hash', '=', $visitorHash)->first();

            // যদি ডাটাবেসে পাওয়া যায়, তবেই ১০ মিনিটের জন্য ক্যাশ করি
            if ($visitor) {
                Cache::put($cacheKey, $visitor, 600);
            }
        }

        return $visitor;
    }

    /**
     * কোন কোন রিকোয়েস্ট ট্র্যাক করা হবে না
     */
    protected function shouldSkipTracking(Request $request): bool
    {
        return $request->is('api/*', 'admin/*', 'livewire/*', 'horizon/*', 'telescope/*') ||
            $request->ajax() ||
            !$request->acceptsHtml();
    }
}