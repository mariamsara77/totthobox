<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpdateLastActive
{
    private const UPDATE_INTERVAL_MINUTES = 5;

    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            try {
                $user = Auth::user();
                $cacheKey = 'user-active-' . $user->id;

                // ডাটাবেসে যাওয়ার আগে ক্যাশ চেক করা হচ্ছে
                if (!Cache::has($cacheKey)) {

                    // ডাটাবেস আপডেট
                    $user->update(['last_active_at' => now()]);

                    // ৫ মিনিটের জন্য ক্যাশে মার্ক করে রাখা (যাতে এই ৫ মিনিট ডাটাবেসে আর টাচ না করে)
                    Cache::put($cacheKey, true, now()->addMinutes(self::UPDATE_INTERVAL_MINUTES));

                    if (config('app.debug')) {
                        Log::info('User activity updated (Throttled)', ['user_id' => $user->id]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to update user activity', [
                    'user_id' => Auth::id() ?? 'Unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
}