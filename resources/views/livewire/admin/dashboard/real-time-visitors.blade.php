<?php

use Livewire\Volt\Component;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public int $activeVisitors = 0;
    public int $activeSessions = 0;
    public int $currentPageViews = 0;
    public array $visitorsData = [];

    public bool $isLoading = true;

    protected $listeners = [
        'echo:visitors,VisitorTracked' => 'updateStats',
        'refreshStats' => 'loadStats',
    ];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->isLoading = true;

        // Use rememberMany for batch caching
        $cachedData = Cache::remember('live_analytics_dashboard', 5, function () {
            return [
                'active_visitors' => $this->getActiveVisitorsCount(),
                'active_sessions' => $this->getActiveSessionsCount(),
                'current_page_views' => $this->getCurrentPageViewsCount(),
                'visitors_by_country' => $this->getVisitorsByCountry(),
            ];
        });

        $this->activeVisitors = $cachedData['active_visitors'];
        $this->activeSessions = $cachedData['active_sessions'];
        $this->currentPageViews = $cachedData['current_page_views'];
        $this->visitorsData = $cachedData['visitors_by_country'];

        $this->isLoading = false;
    }

    // Add this temporary method to fix the error
    public function pollStats(): void
    {
        $this->loadStats();
    }

    protected function getActiveVisitorsCount(): int
    {
        return Visitor::where('last_seen_at', '>=', now()->subMinutes(5))->count();
    }

    protected function getActiveSessionsCount(): int
    {
        return VisitorSession::whereNull('ended_at')
            ->where('started_at', '>=', now()->subMinutes(30))
            ->count();
    }

    protected function getCurrentPageViewsCount(): int
    {
        // Use raw query for better performance
        return (int) DB::table('page_views')
            ->where('created_at', '>=', now()->subMinute())
            ->count();
    }

    protected function getVisitorsByCountry(): array
    {
        return Visitor::query()
            ->select('country', DB::raw('COUNT(*) as count'))
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function updateStats(): void
    {
        // Invalidate cache and reload
        Cache::forget('live_analytics_dashboard');
        $this->loadStats();
    }
};
?>

<div class="relative">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Live Analytics</h3>
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Live</span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Active Visitors -->
        <div
            class="relative bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800 transition-all duration-300 hover:scale-105 hover:shadow-lg group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600 dark:text-blue-300">Active Visitors</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1 transition-all duration-500">
                        {{ number_format($activeVisitors) }}
                    </p>
                    <p class="text-xs text-blue-500 dark:text-blue-400 mt-1">Last 5 minutes</p>
                </div>
                <div
                    class="p-3 bg-blue-500 rounded-lg shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <flux:icon.users />
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div
            class="relative bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800 transition-all duration-300 hover:scale-105 hover:shadow-lg group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-green-600 dark:text-green-300">Active Sessions</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1 transition-all duration-500">
                        {{ number_format($activeSessions) }}
                    </p>
                    <p class="text-xs text-green-500 dark:text-green-400 mt-1">Last 30 minutes</p>
                </div>
                <div
                    class="p-3 bg-green-500 rounded-lg shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <flux:icon.chart-bar />
                </div>
            </div>
        </div>

        <!-- Page Views/Min -->
        <div
            class="relative bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-xl p-4 border border-purple-100 dark:border-purple-800 transition-all duration-300 hover:scale-105 hover:shadow-lg group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-purple-600 dark:text-purple-300">Page Views/Min</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-100 mt-1 transition-all duration-500">
                        {{ number_format($currentPageViews) }}
                    </p>
                    <p class="text-xs text-purple-500 dark:text-purple-400 mt-1">Last minute</p>
                </div>
                <div
                    class="p-3 bg-purple-500 rounded-lg shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <flux:icon.document-chart-bar />
                </div>
            </div>
        </div>
    </div>
</div>