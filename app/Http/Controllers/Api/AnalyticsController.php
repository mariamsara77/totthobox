<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    /**
     * Public user count stats for About page.
     * GET /api/analytics/user-count
     */
    public function index(): JsonResponse
    {
        $data = Cache::remember('about_us_public_dashboard_data', 3600, function () {
            $realUsers = Visitor::realUsers();

            $actualTotal  = (clone $realUsers)->count();
            $actualToday  = (clone $realUsers)->where('last_seen_at', '>=', now()->startOfDay())->count();
            $actualOnline = (clone $realUsers)->online()->count();
            $actualPwa    = (clone $realUsers)->where('has_installed_pwa', true)->count();

            return [
                'total'  => $this->formatKilo($actualTotal + 100000),
                'today'  => $this->formatKilo($actualToday + 10000),
                'online' => $this->formatKilo($actualOnline + 10000),
                'pwa'    => $this->formatKilo($actualPwa + 10000),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    private function formatKilo(int $number): string
    {
        if ($number >= 1_000_000) {
            return round($number / 1_000_000, 2) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 2) . 'k';
        }
        return (string) $number;
    }
}