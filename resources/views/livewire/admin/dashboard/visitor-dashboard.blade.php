<?php

use Livewire\Volt\Component;
use App\Models\{Visitor, PageView, VisitorSession, VisitorEvent};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use App\Exports\VisitorExport;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithPagination;
    public $timeRange = 'week'; // ডিফল্ট ৭ দিন রাখা হয়েছে
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
        // নতুন কলাম 'last_seen_at' অনুযায়ী সর্টিং
        return $query->orderBy('last_seen_at', 'desc')->paginate(10);
    }

    protected function getBaseQuery()
    {
        $query = Visitor::query();
        $query = $this->applyTimeRange($query);

        if ($this->deviceFilter) {
            // নতুন কলাম: device_type
            $query->where('device_type', $this->deviceFilter);
        }

        return $query;
    }

    protected function applyTimeRange($query)
    {
        // নতুন কলাম 'last_seen_at' ফিল্টার করার জন্য বেশি একুরেট
        $column = 'last_seen_at';

        return match ($this->timeRange) {
            'today' => $query->whereDate($column, today()),
            'yesterday' => $query->whereDate($column, today()->subDay()),
            'week' => $query->whereBetween($column, [today()->startOfWeek(), today()->endOfWeek()]),
            'month' => $query->whereBetween($column, [today()->startOfMonth(), today()->endOfMonth()]),
            'year' => $query->whereBetween($column, [today()->startOfYear(), today()->endOfYear()]),
            '7days' => $query->whereBetween($column, [today()->subDays(7), today()]),
            default => $query,
        };
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
        // নতুন কলাম: seconds_spent
        return VisitorSession::whereIn('visitor_id', (clone $query)->select('id'))
            ->whereNotNull('seconds_spent')
            ->avg('seconds_spent');
    }

    protected function getTopPages($query, $limit = 5)
    {
        return PageView::whereIn('visitor_id', (clone $query)->select('id'))
            ->select('url', DB::raw('count(*) as views'))
            ->groupBy('url')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    protected function getDevices($query)
    {
        // নতুন কলাম: device_type
        return (clone $query)
            ->whereNotNull('device_type')
            ->select('device_type as device', DB::raw('count(*) as visitors'))
            ->groupBy('device_type')
            ->orderByDesc('visitors')
            ->get();
    }

    protected function getChangePercentage($current, $previous)
    {
        if ($previous == 0)
            return $current > 0 ? 100 : 0;
        return (($current - $previous) / $previous) * 100;
    }

    protected function getPreviousPeriodData()
    {
        $query = Visitor::query();
        $col = 'last_seen_at';

        // বর্তমান সময়সীমা অনুযায়ী গত সময়সীমা নির্ধারণ
        [$start, $end] = match ($this->timeRange) {
            'today' => [today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'yesterday' => [today()->subDays(2)->startOfDay(), today()->subDays(2)->endOfDay()],
            '7days' => [now()->subDays(13)->startOfDay(), now()->subDays(7)->endOfDay()],
            'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => [now()->subDays(59)->startOfDay(), now()->subDays(30)->endOfDay()],
        };

        $query->whereBetween($col, [$start, $end]);

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


    public function exportChartData()
    {
        // ১. আপনার বর্তমান ফিল্টার করা বেস কোয়েরিটি নিন
        $query = $this->getBaseQuery();

        // ২. এক্সেল ফাইল ডাউনলোড শুরু করুন
        return Excel::download(new VisitorExport($query), 'visitors_report_' . now()->format('Y-m-d') . '.xlsx');
    }
}; ?>

<div class="space-y-8">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Visitor Dashboard</flux:heading>
        <div class="flex gap-2">
            <flux:button wire:click="exportChartData" icon="arrow-down-tray" variant="subtle">Export CSV</flux:button>
        </div>
    </div>

    <flux:card class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:select label="Time Range" wire:model.live="timeRange">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="7days">Last 7 Days</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
        </flux:select>

        <flux:select label="Chart Data" wire:model.live="chartType">
            <option value="visitors">Visitors</option>
            <option value="pageviews">Page Views</option>
            <option value="sessions">Sessions</option>
        </flux:select>

        <flux:select label="Device" wire:model.live="deviceFilter">
            <option value="">All Devices</option>
            @foreach ($devices as $device)
                <option value="{{ $device->device }}">{{ ucfirst($device->device) }} ({{ $device->visitors }})</option>
            @endforeach
        </flux:select>
    </flux:card>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card>
            <div class="flex justify-between items-start">
                <div>
                    <flux:subheading>Unique Visitors</flux:subheading>
                    <flux:heading size="xl">{{ number_format($visitorsCount) }}</flux:heading>
                </div>
                <flux:icon.users class="text-blue-500" />
            </div>
            <div class="mt-4 flex items-center">
                <flux:badge color="{{ $visitorsChange >= 0 ? 'green' : 'red' }}">
                    {{ $visitorsChange >= 0 ? '+' : '' }}{{ round($visitorsChange, 1) }}%
                </flux:badge>
                <flux:text size="xs" class="ml-2">vs last period</flux:text>
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
            <div class="mt-4 flex items-center">
                <flux:badge color="{{ $pageViewsChange >= 0 ? 'green' : 'red' }}">
                    {{ $pageViewsChange >= 0 ? '+' : '' }}{{ round($pageViewsChange, 1) }}%
                </flux:badge>
                <flux:text size="xs" class="ml-2">vs last period</flux:text>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-start">
                <div>
                    <flux:subheading>Total Sessions</flux:subheading>
                    <flux:heading size="xl">{{ number_format($sessionsCount) }}</flux:heading>
                </div>
                <flux:icon.chart-bar class="text-amber-500" />
            </div>
            <div class="mt-4 flex items-center">
                <flux:badge color="{{ $sessionsChange >= 0 ? 'green' : 'red' }}">
                    {{ $sessionsChange >= 0 ? '+' : '' }}{{ round($sessionsChange, 1) }}%
                </flux:badge>
                <flux:text size="xs" class="ml-2">vs last period</flux:text>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-start">
                <div>
                    <flux:subheading>Avg. Duration</flux:subheading>
                    <flux:heading size="xl">
                        {{ $avgSessionDuration ? gmdate('i\m s\s', (int) $avgSessionDuration) : '0s' }}
                    </flux:heading>
                </div>
                <flux:icon.clock class="text-emerald-500" />
            </div>
            <div class="mt-4 flex items-center">
                <flux:badge color="{{ $avgSessionChange >= 0 ? 'green' : 'red' }}">
                    {{ $avgSessionChange >= 0 ? '+' : '' }}{{ round($avgSessionChange, 1) }}%
                </flux:badge>
                <flux:text size="xs" class="ml-2">vs last period</flux:text>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card>
            <flux:heading level="3" class="mb-4">Top Visited Pages</flux:heading>
            <div class="overflow-x-auto">
                <flux:navlist>
                    @foreach ($topPages as $page)
                        <flux:navlist.item class="flex items-center gap-4 py-2" badge="{{ number_format($page->views) }}">
                            <flux:text weight="medium" class="truncate" title="{{ $page->url }}">
                                {{ $page->url }}
                            </flux:text>
                        </flux:navlist.item>
                    @endforeach
                </flux:navlist>
            </div>
        </flux:card>

        <flux:card>
            <flux:heading level="3" class="mb-4">Device Usage</flux:heading>
            <div class="space-y-4">
                @foreach ($devices as $device)
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <flux:text>{{ ucfirst($device->device) }}</flux:text>
                            <flux:text variant="bold">
                                {{ round(($device->visitors / max($visitorsCount, 1)) * 100, 1) }}%
                            </flux:text>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-700 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full"
                                style="width: {{ ($device->visitors / max($visitorsCount, 1)) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </div>

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Visitor Profile</flux:table.column>
                <flux:table.column>Software</flux:table.column>
                <flux:table.column>Device</flux:table.column>
                <flux:table.column>Location</flux:table.column>
                <flux:table.column>Platform</flux:table.column>
                <flux:table.column>Activity</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($visitors as $visitor)
                                                                                <flux:table.row :key="$visitor->id">
                                                                                    <flux:table.cell>
                                                                                        <div class="flex items-center gap-2">
                                                                                            <div class="flex flex-col">
                                                                                                <span class="font-bold text-zinc-800 dark:text-white">ID: {{ $visitor->id }}</span>
                                                                                                <span class="text-[10px] font-mono text-zinc-400">
                                                                                                    {{ Str::limit($visitor->hash, 8, '') }}
                                                                                                </span>
                                                                                            </div>
                                                                                            <div>
                                                                                                @if($visitor->user)
                                                                                                    <div class="flex items-center gap-2">
                                                                                                        <flux:avatar size="xs" badge badge:size="xs"
                                                                                                            badge:color="{{ $visitor->user->isOnline() ? 'green' : 'zinc' }}"
                                                                                                            src="{{ $visitor->user->avatar_url }}"  name="{{ $visitor->user->name }}"/>
                                                                                                        <div>
                                                                                                            <flux:heading size="sm" class="font-medium">{{ $visitor->user->name }}
                                                                                                            </flux:heading>
                                                                                                            <flux:text size="xs" class="text-zinc-500">{{ $visitor->user->email }}
                                                                                                            </flux:text>

                                                                                                        </div>
                                                                                                    </div>
                                                                                                @else
                                                                                                    <div class="flex items-center gap-2 text-zinc-400 italic text-sm">
                                                                                                        <flux:avatar size="xs" badge badge:size="xs" badge:color="{{ $visitor->isOnline() ? 'green' : 'zinc' }}"
                                                                                                            src="{{ $visitor->avatar_url ?: '' }}" name="Guest Visitor" color="red"/>
                                                                                                        <span>Guest Visitor</span>
                                                                                                    </div>
                                                                                                @endif
                                                                                            </div>

                                                                                        </div>
                                                                                    </flux:table.cell>

                                                                                    <flux:table.cell>
                                                                                        <div class="items-center gap-2">
                                                                                            <flux:text size="sm">{{ ucfirst($visitor->device_type) }}</flux:text>
                                                                                            <flux:badge size="sm" variant="solid" color="{{ match ($visitor->browser_family) {
                        'Chrome' => 'orange',
                        'Firefox' => 'red',
                        'Safari' => 'indigo',
                        'Edge' => 'cyan',
                        'Opera' => 'red',
                        default => 'zinc'
                    } }}" icon="{{ $visitor->browser_icon }}">
                                                                                                {{ $visitor->browser_family }}
                                                                                            </flux:badge>
                                                                                        </div>
                                                                                    </flux:table.cell>

                                                                                    <flux:table.cell>
                                                                                        <div class="flex flex-col gap-1">
                                                                                            @php
                    $os = strtolower($visitor->os_family);
                                                                                            @endphp

                                                                                            <div class="flex items-center gap-2">
                                                                                                {{-- OS অনুযায়ী আইকন এবং লেবেল --}}
                                                                                                @if(str_contains($os, 'android'))
                                                                                                    <flux:icon.device-phone-mobile class="size-4 text-green-600" />
                                                                                                    <span class="text-sm font-medium">Android</span>
                                                                                                @elseif(str_contains($os, 'iphone') || str_contains($os, 'ios') || str_contains($os, 'mac'))
                                                                                                    <flux:icon.device-phone-mobile class="size-4 text-zinc-400" />
                                                                                                    <span class="text-sm font-medium">iPhone/Mac</span>
                                                                                                @elseif(str_contains($os, 'windows'))
                                                                                                    <flux:icon.computer-desktop class="size-4 text-blue-600" />
                                                                                                    <span class="text-sm font-medium">Windows</span>
                                                                                                @elseif(str_contains($os, 'linux'))
                                                                                                    <flux:icon.computer-desktop class="size-4 text-orange-600" />
                                                                                                    <span class="text-sm font-medium">Linux</span>
                                                                                                @else
                                                                                                    <flux:icon.question-mark-circle class="size-4 text-zinc-400" />
                                                                                                    <span
                                                                                                        class="text-sm font-medium">{{ $visitor->os_family ?? 'Unknown' }}</span>
                                                                                                @endif

                                                                                                {{-- যদি PWA হয় তবে পাশে একটি ছোট ব্যাজ --}}
                                                                                                @if($visitor->is_pwa)
                                                                                                    <flux:badge size="sm">
                                                                                                        PWA
                                                                                                    </flux:badge>
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                                    </flux:table.cell>

                                                                                    <flux:table.cell>
                                                                                        <div class="flex flex-col">
                                                                                            <span class="text-sm">{{ $visitor->city_name ?? 'Unknown' }}</span>
                                                                                            <span class="text-xs text-zinc-400">{{ $visitor->country_code ?? '??' }}</span>
                                                                                        </div>
                                                                                    </flux:table.cell>

                                                                                    <flux:table.cell>
                                                                                        <flux:badge size="sm" color="{{ $visitor->is_pwa ? 'indigo' : 'zinc' }}" variant="pill">
                                                                                            {{ $visitor->is_pwa ? 'App' : 'Browser' }}
                                                                                        </flux:badge>
                                                                                    </flux:table.cell>

                                                                                    <flux:table.cell class="whitespace-nowrap text-xs text-zinc-500">
                                                                                        {{ $visitor->last_seen_at->diffForHumans() }}
                                                                                    </flux:table.cell>

                                                                                    <flux:table.cell>
                                                                                        <flux:button variant="ghost" size="sm" icon="eye"
                                                                                            href="{{ route('admin.dashboard.visitor.details', $visitor->id) }}" />
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