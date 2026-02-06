<?php

use Livewire\Volt\Component;
use App\Models\Visitor;
use App\Models\PageView;
use App\Models\VisitorSession;
use Carbon\Carbon;
use App\Models\VisitorEvent;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    public $timeRange = 'year';
    public $chartType = 'visitors';
    public $deviceFilter;

    protected $queryString = [
        'timeRange' => ['except' => 'today'],
        'chartType' => ['except' => 'visitors'],
        'deviceFilter' => ['except' => ''],
    ];

    protected function getVisitorsList()
    {
        $query = $this->getBaseQuery();
        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    protected function getBaseQuery()
    {
        $query = Visitor::query();
        $query = $this->applyTimeRange($query);

        if ($this->deviceFilter) {
            $query->where('device', $this->deviceFilter);
        }

        return $query;
    }

    protected function applyTimeRange($query)
    {
        switch ($this->timeRange) {
            case 'today':
                return $query->whereDate('created_at', today());
            case 'yesterday':
                return $query->whereDate('created_at', today()->subDay());
            case 'week':
                return $query->whereBetween('created_at', [today()->startOfWeek(), today()->endOfWeek()]);
            case 'month':
                return $query->whereBetween('created_at', [today()->startOfMonth(), today()->endOfMonth()]);
            case 'year':
                return $query->whereBetween('created_at', [today()->startOfYear(), today()->endOfYear()]);
            default:
                return $query->whereBetween('created_at', [today()->subDays(7), today()]);
        }
    }

    protected function getVisitorsCount($query)
    {
        return (clone $query)->count();
    }

    protected function getPageViewsCount($query)
    {
        return PageView::whereIn('visitor_id', (clone $query)->select('id'))->count();
    }

    protected function getSessionsCount($query)
    {
        return VisitorSession::whereIn('visitor_id', (clone $query)->select('id'))->count();
    }

    protected function getAvgSessionDuration($query)
    {
        return VisitorSession::whereIn('visitor_id', (clone $query)->select('id'))->whereNotNull('duration')->avg('duration');
    }

    protected function getTopPages($query, $limit = 5)
    {
        return PageView::whereIn('visitor_id', (clone $query)->select('id'))->select('url', DB::raw('count(*) as views'))->groupBy('url')->orderByDesc('views')->limit($limit)->get();
    }

    protected function getTopReferrers($query, $limit = 5)
    {
        return (clone $query)->whereNotNull('referrer_domain')->select('referrer_domain', DB::raw('count(*) as visits'))->groupBy('referrer_domain')->orderByDesc('visits')->limit($limit)->get();
    }

    protected function getDevices($query, $limit = 5)
    {
        return (clone $query)->whereNotNull('device')->select('device', DB::raw('count(*) as visitors'))->groupBy('device')->orderByDesc('visitors')->limit($limit)->get();
    }

    protected function getBrowsers($query, $limit = 5)
    {
        return (clone $query)->whereNotNull('browser')->select('browser', DB::raw('count(*) as visitors'))->groupBy('browser')->orderByDesc('visitors')->limit($limit)->get();
    }

    protected function getChangePercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    protected function getPreviousPeriodData()
    {
        $query = Visitor::query();

        switch ($this->timeRange) {
            case 'today':
                $query->whereDate('created_at', today()->subDay());
                break;
            case 'yesterday':
                $query->whereDate('created_at', today()->subDays(2));
                break;
            case 'week':
                $query->whereBetween('created_at', [today()->subWeek()->startOfWeek(), today()->subWeek()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('created_at', [today()->subMonth()->startOfMonth(), today()->subMonth()->endOfMonth()]);
                break;
            case 'year':
                $query->whereBetween('created_at', [today()->subYear()->startOfYear(), today()->subYear()->endOfYear()]);
                break;
            default:
                // 7days
                $query->whereBetween('created_at', [today()->subDays(14), today()->subDays(7)]);
        }

        return [
            'visitorsCount' => $this->getVisitorsCount($query),
            'pageViewsCount' => $this->getPageViewsCount($query),
            'sessionsCount' => $this->getSessionsCount($query),
            'avgSessionDuration' => $this->getAvgSessionDuration($query),
        ];
    }

    public function with(): array
    {
        $query = $this->getBaseQuery();
        // আগের পিরিয়ডের ডাটার জন্য ক্লোন ব্যবহার করা ভালো
        $prevData = $this->getPreviousPeriodData();

        $currentVisitors = $this->getVisitorsCount($query);
        $currentPageViews = $this->getPageViewsCount($query);
        $currentSessions = $this->getSessionsCount($query);
        $currentAvgSession = $this->getAvgSessionDuration($query);

        return [
            'visitors' => $this->getVisitorsList(),
            'devices' => $this->getDevices($query),
            'topPages' => $this->getTopPages($query),
            'visitorsCount' => $currentVisitors,
            'pageViewsCount' => $currentPageViews,
            'sessionsCount' => $currentSessions,
            'avgSessionDuration' => $currentAvgSession,
            'visitorsChange' => $this->getChangePercentage($currentVisitors, $prevData['visitorsCount']),
            'pageViewsChange' => $this->getChangePercentage($currentPageViews, $prevData['pageViewsCount']),
            'sessionsChange' => $this->getChangePercentage($currentSessions, $prevData['sessionsCount']),
            'avgSessionChange' => $this->getChangePercentage($currentAvgSession, $prevData['avgSessionDuration']),
        ];
    }
}; ?>

<div class="space-y-8">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Visitor Summary</flux:heading>
        <div class="flex gap-2">
            <flux:button wire:click="exportChartData" icon="arrow-down-tray">Export Data</flux:button>
        </div>
    </div>

    <flux:card class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:select label="Time Range" wire:model.live="timeRange">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
            <option value="7days">Last 7 Days</option>
        </flux:select>

        <flux:select label="Chart Data" wire:model.live="chartType">
            <option value="visitors">Visitors</option>
            <option value="pageviews">Page Views</option>
            <option value="sessions">Sessions</option>
        </flux:select>



        <flux:select label="Device" wire:model.live="deviceFilter">
            <option value="">All Devices</option>
            @foreach ($devices as $device)
                <option value="{{ $device->device }}">{{ $device->device }} ({{ $device->visitors }})</option>
            @endforeach
        </flux:select>
    </flux:card>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card>
            <div class="flex justify-between items-start">
                <div>
                    <flux:subheading>Visitors</flux:subheading>
                    <flux:heading size="xl">{{ number_format($visitorsCount) }}</flux:heading>
                </div>
                <flux:icon.users class="text-blue-500" />
            </div>
            <div class="mt-4 flex items-center">
                <flux:badge color="{{ $visitorsChange >= 0 ? 'green' : 'red' }}">
                    {{ $visitorsChange >= 0 ? '+' : '' }}{{ round($visitorsChange, 1) }}%
                </flux:badge>
                <flux:text size="sm" class="ml-2">vs last period</flux:text>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-start">
                <div>
                    <flux:subheading>Page Views</flux:subheading>
                    <flux:heading size="xl">{{ number_format($pageViewsCount) }}</flux:heading>
                </div>
                <flux:icon.document-text class="text-purple-500" />
            </div>
            <div class="mt-4">
                <flux:badge color="{{ $pageViewsChange >= 0 ? 'green' : 'red' }}">
                    {{ $pageViewsChange >= 0 ? '+' : '' }}{{ round($pageViewsChange, 1) }}%
                </flux:badge>
                <flux:text size="sm" class="ml-2">vs last period</flux:text>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-start">
                <div>
                    <flux:subheading>Sessions</flux:subheading>
                    <flux:heading size="xl">{{ number_format($sessionsCount) }}</flux:heading>
                </div>
                <flux:icon.chart-bar class="text-amber-500" />
            </div>
            <div class="mt-4">
                <flux:badge color="{{ $sessionsChange >= 0 ? 'green' : 'red' }}">
                    {{ $sessionsChange >= 0 ? '+' : '' }}{{ round($sessionsChange, 1) }}%
                </flux:badge>
                <flux:text size="sm" class="ml-2">vs last period</flux:text>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-start">
                <div>
                    <flux:subheading>Avg. Session</flux:subheading>
                    <flux:heading size="xl">
                        {{ $avgSessionDuration ? gmdate('i\m s\s', $avgSessionDuration) : 'N/A' }}
                    </flux:heading>
                </div>
                <flux:icon.clock class="text-emerald-500" />
            </div>
            <div class="mt-4">
                <flux:badge color="{{ $avgSessionChange >= 0 ? 'green' : 'red' }}">
                    {{ $avgSessionChange >= 0 ? '+' : '' }}{{ round($avgSessionChange, 1) }}%
                </flux:badge>
                <flux:text size="sm" class="ml-2">vs last period</flux:text>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card>
            <flux:heading level="3" class="mb-4">Top Pages</flux:heading>
            <flux:navlist>
                @foreach ($topPages as $page)
                    <flux:navlist.item class="flex items-center gap-4 py-2 px-1" badge="{{ number_format($page->views) }}"
                        badge:color="zinc">
                        <div class="min-w-0 flex-1">
                            <flux:text weight="medium" class="truncate block" title="{{ $page->url }}">
                                {{ $page->url }}
                            </flux:text>
                        </div>

                        {{-- <div class="flex-shrink-0">
                            <flux:badge variant="pill" class="whitespace-nowrap">

                            </flux:badge>
                        </div> --}}
                    </flux:navlist.item>
                @endforeach
            </flux:navlist>
        </flux:card>

        <flux:card>
            <flux:heading level="3" class="mb-4">Usage Stats</flux:heading>
            <div class="space-y-6">
                <div>
                    <flux:subheading class="mb-2">Devices</flux:subheading>
                    <div class="space-y-3">
                        @foreach ($devices as $device)
                            <div class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <flux:text>{{ $device->device }}</flux:text>
                                    <flux:text variant="bold">
                                        {{ round(($device->visitors / $visitorsCount) * 100, 1) }}%
                                    </flux:text>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-blue-500 h-1.5 rounded-full"
                                        style="width: {{ ($device->visitors / $visitorsCount) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Visitor</flux:table.column>
                <flux:table.column>Device Info</flux:table.column>
                <flux:table.column>Hardware</flux:table.column>
                <flux:table.column>Location</flux:table.column>
                <flux:table.column>Network</flux:table.column>
                <flux:table.column>Seen</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($visitors as $visitor)
                    <flux:table.row :key="$visitor->id">

                        <flux:table.cell tooltip="{{ $visitor->hash }}">

                            <div class="flex flex-col">
                                <span class="font-bold text-zinc-800 dark:text-white">ID: {{ $visitor->id }}</span>
                                <flux:tooltip content="Hash: {{ $visitor->hash }}">
                                    <span class="text-[10px] font-mono text-zinc-500">
                                        {{ Str::limit($visitor->hash, 10) }}
                                    </span>
                                </flux:tooltip>
                            </div>

                        </flux:table.cell>


                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:text size="sm">{{ $visitor->device ?? 'Desktop' }}</flux:text>
                                <flux:badge size="sm" variant="subtle" color="zinc">{{ $visitor->browser }}</flux:badge>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="text-xs space-y-1">
                                <div class="flex items-center gap-1">
                                    <flux:icon.cpu-chip class="size-3 text-zinc-400" />
                                    <span>{{ $visitor->cpu_cores ?? '?' }} Cores</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:icon.circle-stack class="size-3 text-zinc-400" />
                                    <span>{{ number_format($visitor->ram_gb, 1) }} GB RAM</span>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <span class="text-sm">{{ $visitor->city ?? 'Unknown' }}</span>
                                <span class="text-zinc-400">({{ $visitor->country ?? '??' }})</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $visitor->network_type === 'wifi' ? 'green' : 'blue' }}"
                                variant="pill">
                                {{ strtoupper($visitor->network_type ?? 'N/A') }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap text-xs text-zinc-500">
                            {{ $visitor->updated_at->diffForHumans() }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:button variant="ghost" size="sm" icon="eye"
                                href="{{ route('admin.dashboard.visitor.details', $visitor->id) }}">
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $visitors->links() }}
        </div>
    </flux:card>
</div>