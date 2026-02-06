<?php
use Livewire\Volt\Component;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

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
            ->selectRaw("DATE_FORMAT(created_at, '%d %b') as label")
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("MIN(created_at) as sort_date")
            ->where('created_at', '>=', $startDate)
            ->groupBy('label')
            ->orderBy('sort_date', 'ASC')
            ->get();

        $this->chartData = [
            'labels' => $results->pluck('label')->toArray(),
            'values' => $results->pluck('total')->map(fn($item) => (int) $item)->toArray(),
        ];

        $this->dispatch('update-my-chart', chartData: $this->chartData);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight text-slate-800 dark:text-white">Visitor Analytics
            </flux:heading>
            <flux:subheading>Monitor your site traffic</flux:subheading>
        </div>

        <div class="">
            <flux:select wire:model.live="timeRange" variant="listbox" class="" placeholder="Range"
                class="min-w-[120px]">
                <flux:select.option value="today">Today</flux:select.option>
                <flux:select.option value="7days">Last 7 Days</flux:select.option>
                <flux:select.option value="month">Month</flux:select.option>
                <flux:select.option value="year">This Year</flux:select.option>
            </flux:select>
        </div>
    </div>

    <flux:callout icon="arrow-trending-up" title="Analytics Overview" variant="info">
        <div class="mt-4">
            <div wire:ignore id="apex-chart-element" class="w-full"></div>
        </div>
    </flux:callout>
</div>

@script

<script>
    let chart;

    const initChart = (labels, values) => {
        const el = document.querySelector("#apex-chart-element");
        if (!el || typeof ApexCharts === 'undefined') return;

        const options = {
            chart: {
                type: 'area',
                height: 380,
                fontFamily: 'Plus Jakarta Sans, Inter, system-ui',
                toolbar: { show: false },
                sparkline: { enabled: false },
                animations: {
                    enabled: true,
                    easing: 'easeout',
                    speed: 1000,
                    animateGradually: { enabled: true, delay: 150 },
                    dynamicAnimation: { enabled: true, speed: 350 }
                },
                dropShadow: {
                    enabled: true,
                    top: 10,
                    left: 0,
                    blur: 8,
                    color: '#f97316',
                    opacity: 0.15
                }
            },
            series: [{
                name: 'Visitors',
                data: values
            }],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    type: "vertical",
                    opacityFrom: 0.5,
                    opacityTo: 0.0,
                    stops: [0, 90, 100],
                    colorStops: [
                        { offset: 0, color: "#ff00ff", opacity: 0.4 },
                        { offset: 100, color: "#555", opacity: 0 }
                    ]
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2,
                lineCap: 'round'
            },
            colors: ['#ff00ff'],
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.1)',
                strokeDashArray: 4,
                padding: { left: 20, right: 20, top: 0, bottom: 0 }
            },
            markers: {
                size: 0,
                colors: ['#ff00ff'],
                strokeColors: '#ff00ff',
                strokeWidth: 0,
                hover: { size: 4 }
            },
            xaxis: {
                categories: labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#94a3b8', fontSize: '12px', fontWeight: 500 }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#94a3b8', fontSize: '12px', fontWeight: 500 },
                    formatter: (val) => val.toLocaleString()
                }
            },
            tooltip: {
                theme: 'dark',
                x: { show: true },
                y: {
                    formatter: (val) => `<b>${val}</b> Visitors`,
                    title: { formatter: () => '' }
                },
                style: { fontSize: '12px' },
                marker: { show: false },
                items: { display: 'flex' }
            },
            dataLabels: { enabled: false }
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(el, options);
        chart.render();
    };

    initChart($wire.chartData.labels, $wire.chartData.values);

    $wire.on('update-my-chart', (payload) => {
        const data = payload.chartData;
        if (chart) {
            chart.updateOptions({
                xaxis: { categories: data.labels }
            });
            chart.updateSeries([{
                data: data.values
            }]);
        } else {
            initChart(data.labels, data.values);
        }
    });
</script>
@endscript