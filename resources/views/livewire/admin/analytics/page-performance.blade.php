<?php

use Livewire\Volt\Component;
use App\Models\PageView;
use App\Models\Visitor;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    // Filters
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $country = '';
    public string $originType = '';
    public string $originSource = '';
    public string $deviceType = '';
    public string $isBot = '';
    public string $isPwa = '';
    public string $routeName = '';
    public string $search = '';
    public string $sortBy = 'avg_load_time';
    public string $sortDirection = 'desc';
    public int $perPage = 20;
    public string $tab = 'pages';

    protected $queryString = ['dateFrom', 'dateTo', 'country', 'originType', 'originSource', 'deviceType', 'isBot', 'isPwa', 'routeName', 'search', 'sortBy', 'sortDirection', 'tab'];

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updated($property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo', 'country', 'originType', 'originSource', 'deviceType', 'isBot', 'isPwa', 'routeName', 'search', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['country', 'originType', 'originSource', 'deviceType', 'isBot', 'isPwa', 'routeName', 'search']);
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    // ---------- Base Query (fixed columns) ----------
    protected function baseQuery()
    {
        return PageView::query()
            ->join('visitors', 'page_views.visitor_id', '=', 'visitors.id')
            ->leftJoin('visitor_sessions', 'page_views.session_id', '=', 'visitor_sessions.id')
            ->when($this->dateFrom, fn($q) => $q->whereDate('page_views.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('page_views.created_at', '<=', $this->dateTo))
            ->when($this->country, fn($q) => $q->where('visitors.country_code', $this->country))
            ->when($this->deviceType, fn($q) => $q->where('visitors.device_type', $this->deviceType))
            ->when($this->isBot !== '', fn($q) => $q->where('visitors.is_bot', (bool) $this->isBot))
            ->when($this->isPwa !== '', fn($q) => $q->where('visitors.is_pwa', (bool) $this->isPwa))
            ->when($this->originType, fn($q) => $q->where('visitor_sessions.origin_type', $this->originType))
            ->when($this->originSource, fn($q) => $q->where('visitor_sessions.origin_source', 'like', '%' . $this->originSource . '%'))
            ->when($this->routeName, fn($q) => $q->where('page_views.route_name', $this->routeName))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('page_views.title', 'like', '%' . $this->search . '%')
                        ->orWhere('page_views.url', 'like', '%' . $this->search . '%')
                        ->orWhere('page_views.route_name', 'like', '%' . $this->search . '%');
                });
            });
    }

    // ---------- Unique Pages ----------
    public function getPagesProperty()
    {
        return $this->baseQuery()
            ->select(['page_views.url', 'page_views.title', 'page_views.route_name', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT page_views.visitor_id) as unique_visitors'), DB::raw('ROUND(AVG(page_views.load_time_ms), 0) as avg_load_time'), DB::raw('MIN(page_views.load_time_ms) as min_load_time'), DB::raw('MAX(page_views.load_time_ms) as max_load_time'), DB::raw('ROUND(AVG(CASE WHEN page_views.load_time_ms > 1000 THEN 1 ELSE 0 END) * 100, 1) as slow_percentage')])
            ->groupBy('page_views.url', 'page_views.title', 'page_views.route_name')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // ---------- By Route ----------
    public function getRoutesProperty()
    {
        return $this->baseQuery()
            ->select(['page_views.route_name', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT page_views.visitor_id) as unique_visitors'), DB::raw('ROUND(AVG(page_views.load_time_ms), 0) as avg_load_time'), DB::raw('MIN(page_views.load_time_ms) as min_load_time'), DB::raw('MAX(page_views.load_time_ms) as max_load_time')])
            ->whereNotNull('page_views.route_name')
            ->groupBy('page_views.route_name')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // ---------- Summary Cards ----------
    public function getSummaryProperty(): array
    {
        $base = $this->baseQuery();

        return [
            'total_views' => (clone $base)->count(),
            'unique_pages' => (clone $base)->distinct('page_views.url')->count('page_views.url'),
            'avg_load_time' => (int) round((clone $base)->avg('page_views.load_time_ms') ?? 0),
            'slow_views' => (clone $base)->where('page_views.load_time_ms', '>', 1000)->count(),
            'unique_visitors' => (clone $base)->distinct('page_views.visitor_id')->count('page_views.visitor_id'),
        ];
    }

    // ---------- Filter Options ----------
    public function getCountriesProperty()
    {
        return Visitor::query()->whereNotNull('country_code')->distinct()->orderBy('country_code')->pluck('country_code');
    }

    public function getOriginTypesProperty()
    {
        return DB::table('visitor_sessions')->whereNotNull('origin_type')->distinct()->orderBy('origin_type')->pluck('origin_type');
    }

    public function getDeviceTypesProperty()
    {
        return Visitor::query()->whereNotNull('device_type')->distinct()->orderBy('device_type')->pluck('device_type');
    }

    public function getRouteNamesProperty()
    {
        return PageView::query()->whereNotNull('route_name')->distinct()->orderBy('route_name')->pluck('route_name');
    }
}; ?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Page Performance Analytics</flux:heading>
            <flux:subheading>All visitors • Unique pages • Load time insights</flux:subheading>
        </div>
        <flux:button variant="ghost" href="{{ route('admin.dashboard') }}" icon="arrow-left">
            Back to Dashboard
        </flux:button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <flux:card class="p-4">
            <flux:text>Total Views</flux:text>
            <flux:text>{{ number_format($this->summary['total_views']) }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:text>Unique Pages</flux:text>
            <flux:text>{{ number_format($this->summary['unique_pages']) }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:text>Avg Load Time</flux:text>
            <flux:text>
                {{ $this->summary['avg_load_time'] }}ms
            </flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:text>Slow Views (>1s)</flux:text>
            <flux:text>{{ number_format($this->summary['slow_views']) }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:text>Unique Visitors</flux:text>
            <flux:text>{{ number_format($this->summary['unique_visitors']) }}</flux:text>
        </flux:card>
    </div>

    {{-- Filters --}}
    <flux:card>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:field>
                <flux:label>From</flux:label>
                <flux:input type="date" wire:model.live="dateFrom" />
            </flux:field>

            <flux:field>
                <flux:label>To</flux:label>
                <flux:input type="date" wire:model.live="dateTo" />
            </flux:field>

            <flux:field>
                <flux:label>Country</flux:label>
                <flux:select wire:model.live="country">
                    <flux:select.option value="">All Countries</flux:select.option>
                    @foreach ($this->countries as $code)
                        <flux:select.option value="{{ $code }}">{{ $code }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Origin Type</flux:label>
                <flux:select wire:model.live="originType">
                    <flux:select.option value="">All Sources</flux:select.option>
                    @foreach ($this->originTypes as $type)
                        <flux:select.option value="{{ $type }}">{{ ucfirst($type) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Referral / Source</flux:label>
                <flux:input wire:model.live.debounce.400ms="originSource" placeholder="google, facebook..." />
            </flux:field>

            <flux:field>
                <flux:label>Device</flux:label>
                <flux:select wire:model.live="deviceType">
                    <flux:select.option value="">All Devices</flux:select.option>
                    @foreach ($this->deviceTypes as $device)
                        <flux:select.option value="{{ $device }}">{{ ucfirst($device) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Bot / Real</flux:label>
                <flux:select wire:model.live="isBot">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="0">Real Users</flux:select.option>
                    <flux:select.option value="1">Bots</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>PWA</flux:label>
                <flux:select wire:model.live="isPwa">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="1">PWA Installed</flux:select.option>
                    <flux:select.option value="0">Web Browser</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Route Name</flux:label>
                <flux:select wire:model.live="routeName">
                    <flux:select.option value="">All Routes</flux:select.option>
                    @foreach ($this->routeNames as $route)
                        <flux:select.option value="{{ $route }}">{{ $route }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Search (Title / URL / Route)</flux:label>
                <flux:input wire:model.live.debounce.400ms="search" placeholder="Search pages..."
                    icon="magnifying-glass" />
            </flux:field>

            <div class="flex items-end gap-4">
                <flux:button variant="primary" wire:click="$refresh" icon="funnel">Apply</flux:button>
                <flux:button variant="ghost" wire:click="resetFilters" icon="x-mark">Reset</flux:button>
            </div>
        </div>
    </flux:card>

    {{-- Tabs --}}
    <flux:tab.group>
        <flux:tabs wire:model="tab">
            <flux:tab name="pages" icon="document">Unique Pages</flux:tab>
            <flux:tab name="routes" icon="map">By Route</flux:tab>
        </flux:tabs>

        {{-- Unique Pages --}}
        <flux:tab.panel name="pages">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Page</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('views')">Views</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('unique_visitors')">Unique</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('avg_load_time')">Avg Load</flux:table.column>
                    <flux:table.column>Min / Max</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('slow_percentage')">Slow %</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->pages as $page)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium max-w-xs truncate" title="{{ $page->title }}">
                                    {{ $page->title ?: 'No Title' }}
                                </div>
                                <div class="text-xs text-zinc-500 truncate max-w-xs" title="{{ $page->url }}">
                                    {{ $page->url }}
                                </div>
                                @if ($page->route_name)
                                    <flux:badge size="sm" color="zinc" class="mt-1">{{ $page->route_name }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>{{ number_format($page->views) }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($page->unique_visitors) }}</flux:table.cell>

                            <flux:table.cell>
                                <span @class([
                                    'font-semibold',
                                    'text-orange-500' => $page->avg_load_time > 1000,
                                    'text-yellow-500' =>
                                        $page->avg_load_time > 500 && $page->avg_load_time <= 1000,
                                    'text-green-500' => $page->avg_load_time <= 500,
                                ])>
                                    {{ $page->avg_load_time }}ms
                                </span>
                            </flux:table.cell>

                            <flux:table.cell class="text-xs text-zinc-500">
                                {{ $page->min_load_time }} / {{ $page->max_load_time }}ms
                            </flux:table.cell>

                            <flux:table.cell>
                                <span @class([
                                    'text-red-500' => $page->slow_percentage > 30,
                                    'text-orange-500' =>
                                        $page->slow_percentage > 10 && $page->slow_percentage <= 30,
                                    'text-zinc-500' => $page->slow_percentage <= 10,
                                ])>
                                    {{ $page->slow_percentage }}%
                                </span>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-12 text-zinc-500">
                                No page views found for the selected filters.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $this->pages->links() }}
            </div>
        </flux:tab.panel>

        {{-- By Route --}}
        <flux:tab.panel name="routes">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Route Name</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('views')">Views</flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('unique_visitors')">Unique Visitors
                    </flux:table.column>
                    <flux:table.column sortable wire:click="sortBy('avg_load_time')">Avg Load Time</flux:table.column>
                    <flux:table.column>Min / Max</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->routes as $route)
                        <flux:table.row>
                            <flux:table.cell>
                                <flux:badge>{{ $route->route_name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ number_format($route->views) }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($route->unique_visitors) }}</flux:table.cell>
                            <flux:table.cell>
                                <span @class([
                                    'font-semibold',
                                    'text-orange-500' => $route->avg_load_time > 1000,
                                    'text-green-500' => $route->avg_load_time <= 1000,
                                ])>
                                    {{ $route->avg_load_time }}ms
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="text-xs text-zinc-500">
                                {{ $route->min_load_time }} / {{ $route->max_load_time }}ms
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-12 text-zinc-500">
                                No data found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $this->routes->links() }}
            </div>
        </flux:tab.panel>
    </flux:tab.group>
</div>
