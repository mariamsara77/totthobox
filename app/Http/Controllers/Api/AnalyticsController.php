<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Cache::remember('api_user_analytics_data_v2', 30, function () {
            $actualCount = Visitor::where('is_bot', false)->count();

            return [
                'total_users' => $this->formatKilo($actualCount + 100000),
                'raw_count' => $actualCount + 100000,
                'status' => 'success',
            ];
        });

        return response()->json($data);
    }

    private function formatKilo(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 2) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 2) . 'k';
        }
        return (string) $number;
    }
}