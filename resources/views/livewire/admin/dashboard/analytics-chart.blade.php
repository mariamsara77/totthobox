<?php
use Livewire\Volt\Component;
use App\Models\Visitor;

new class extends Component {
    public string $timeRange = '7days';
    public array $chartData = ['labels' => [], 'values' => []];

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedTimeRange(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $startDate = match ($this->timeRange) {
            'today' => now()->startOfDay(),
            '7days' => now()->subDays(6)->startOfDay(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->subDays(6)->startOfDay(),
        };

        $results = Visitor::query()
            ->where('is_bot', false)
            ->where('first_seen_at', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(first_seen_at, '%d %b') as label")
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("MIN(first_seen_at) as sort_date")
            ->groupBy('label')
            ->orderBy('sort_date', 'ASC')
            ->get();

        $this->chartData = [
            'labels' => $results->pluck('label')->toArray(),
            'values' => $results->pluck('total')->map(fn($item) => (int) $item)->toArray(),
        ];

        $this->dispatch('update-chart', chartData: $this->chartData);
    }
}; ?>

<div class="space-y-6 antialiased">
    {{-- Top Header with Stats Summary --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-pink-500 rounded-lg shadow-lg shadow-pink-500/20">
                    <flux:icon.chart-bar class="w-5 h-5 text-white" variant="mini" />
                </div>
                <flux:heading size="xl" class="font-black tracking-tight text-zinc-800 dark:text-zinc-100">Audience
                    Metrics</flux:heading>
            </div>
            <flux:subheading class="ml-9">Tracking unique user engagement & traffic patterns</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <div wire:loading.flex class="px-2 items-center gap-2 text-pink-500">
                <flux:icon.arrow-path class="w-4 h-4 animate-spin" />
                <span class="text-[10px] font-bold tracking-widest">Syncing</span>
            </div>

            <flux:select wire:model.live="timeRange" variant="listbox"
                class="border-none! shadow-none! bg-transparent min-w-[140px]!">
                <flux:select.option value="today">Today</flux:select.option>
                <flux:select.option value="7days">Last 7 Days</flux:select.option>
                <flux:select.option value="month">This Month</flux:select.option>
                <flux:select.option value="year">This Year</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Main Analytics Card --}}
    <flux:card class="">
        {{-- Card Header Decor --}}
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-pink-500"></span>
                </span>
                <span class="text-xs font-bold tracking-widest text-zinc-500">Real-time Traffic</span>
            </div>
            <div class="flex gap-1.5">
                <div class="h-1.5 w-6 rounded-full bg-pink-500/20"></div>
                <div class="h-1.5 w-1.5 rounded-full bg-pink-500"></div>
            </div>
        </div>

        {{-- Chart Container --}}
        <div class="p-4 md:p-6">
            <div wire:ignore id="main-visitor-chart" class="w-full min-h-[380px]">
            </div>
        </div>
    </flux:card>
</div>

@script
<script>
    let chart;
    const brandColor = '#f705eb';

    const renderChart = (labels, values) => {
        const el = document.querySelector("#main-visitor-chart");
        if (!el || typeof ApexCharts === 'undefined') return;

        const isDark = document.documentElement.classList.contains('dark');

        const options = {
            series: [{
                name: 'Unique Visitors',
                data: values
            }],
            chart: {
                type: 'area',
                height: 380,
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeout', speed: 1000 },
                background: 'transparent',
                dropShadow: {
                    enabled: true,
                    top: 10,
                    left: 0,
                    blur: 4,
                    color: brandColor,
                    opacity: 0.1
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.02,
                    stops: [0, 100],
                    colorStops: isDark ? [
                        { offset: 0, color: brandColor, opacity: 0.2 },
                        { offset: 100, color: brandColor, opacity: 0 }
                    ] : [
                        { offset: 0, color: brandColor, opacity: 0.15 },
                        { offset: 100, color: brandColor, opacity: 0 }
                    ]
                }
            },
            stroke: { curve: 'smooth', width: 2, colors: [brandColor], lineCap: 'round' },
            grid: {
                show: true,
                borderColor: isDark ? '#27272a' : '#f4f4f5',
                strokeDashArray: 6,
                position: 'back',
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 10, right: 10, bottom: 0, left: 10 }
            },
            markers: {
                size: 0,
                colors: [brandColor],
                strokeColors: isDark ? '#18181b' : '#fff',
                strokeWidth: 3,
                hover: { size: 6, strokeWidth: 2 }
            },
            xaxis: {
                categories: labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#71717a', fontSize: '12px', fontWeight: 500 },
                    offsetY: 5
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#71717a', fontSize: '12px', fontWeight: 500 },
                    formatter: (val) => val.toLocaleString()
                }
            },
            dataLabels: { enabled: false },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                // Default style disable kore nite hobe jate custom UI thikmoto bose
                style: { fontSize: '12px' },
                onDatasetHover: { highlightDataSeries: true },

                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    const value = series[seriesIndex][dataPointIndex];
                    const label = w.globals.labels[dataPointIndex];
                    const isDark = document.documentElement.classList.contains('dark');

                    return `
            <div class="relative overflow-hidden">
                <div class="min-w-[140px] px-4 py-3 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md 
                            border border-zinc-200/50 dark:border-zinc-800/50 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.1)] 
                            dark:shadow-[0_10px_40px_-10px_rgba(0,0,0,0.5)] rounded antialiased">
                    
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] uppercase tracking-[0.1em] font-bold text-zinc-400 dark:text-zinc-500">
                            Traffic Snap
                        </span>
                        <span class="text-[9px] px-1.5 py-0.5 bg-indigo-500/10 text-indigo-500 rounded font-bold">
                            ${label}
                        </span>
                    </div>

                    <div class="flex items-end gap-1.5">
                        <div class="text-xl font-black tracking-tight text-zinc-800 dark:text-zinc-100 leading-none">
                            ${value.toLocaleString()}
                        </div>
                        <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mb-[1px]">
                            Unique Users
                        </div>
                    </div>

                    <div class="mt-3 h-[2px] w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 w-2/3 rounded-full"></div>
                    </div>
                </div>
            </div>
        `;
                }
            }
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(el, options);
        chart.render();
    };

    // Initial Sync
    setTimeout(() => renderChart($wire.chartData.labels, $wire.chartData.values), 100);

    // Livewire Event
    $wire.on('update-chart', (payload) => {
        const data = payload.chartData;
        if (chart) {
            chart.updateOptions({ xaxis: { categories: data.labels } });
            chart.updateSeries([{ data: data.values }]);
        }
    });
</script>
@endscript