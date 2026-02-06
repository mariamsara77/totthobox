<?php

use Livewire\Volt\Component;
use App\Models\Visitor;
use App\Models\VisitorSession;
use App\Models\PageView;
use App\Models\VisitorEvent;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    public $visitorId;
    public $visitor;
    public $activeTab = 'sessions';
    public $sessionDetails = null;

    // Remove the paginator properties - we'll use separate methods
    public $sessions = [];

    protected $queryString = ['activeTab'];

    public function mount($visitorId)
    {
        $this->visitorId = $visitorId;
        $this->loadVisitor();
        $this->loadSessions();
    }

    public function loadVisitor()
    {
        $this->visitor = Visitor::with(['sessions', 'pageViews', 'events'])->findOrFail($this->visitorId);
    }

    public function loadSessions()
    {
        if ($this->visitor) {
            $this->sessions = $this->visitor->sessions()->orderByDesc('started_at')->get();
        }
    }

    // Separate method for page views with pagination
    public function getPageViewsProperty()
    {
        if (!$this->visitor) {
            return collect();
        }

        return $this->visitor->pageViews()->orderByDesc('created_at')->paginate(15);
    }

    // Separate method for events with pagination
    public function getEventsProperty()
    {
        if (!$this->visitor) {
            return collect();
        }

        return $this->visitor->events()->orderByDesc('created_at')->paginate(15);
    }

    public function loadSessionDetails($sessionId)
    {
        $this->sessionDetails = VisitorSession::with([
            'pageViews' => function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            },
            'events' => function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            },
        ])->find($sessionId);

        $this->activeTab = 'sessions'; // Ensure we're on the sessions tab
    }

    public function resetSessionDetails()
    {
        $this->sessionDetails = null;
    }

    // Handle tab change manually
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
        $this->sessionDetails = null;
    }
}; ?>
<div class="p-6 space-y-6">
    <flux:card>
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl">Visitor Details</flux:heading>
                <flux:subheading>Detailed activity logs for this visitor</flux:subheading>
            </div>
            <flux:button variant="ghost" href="{{ route('admin.dashboard') }}" icon="arrow-left">
                Back to Dashboard
            </flux:button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:card variant="subtle" class="p-3">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">First Seen</p>
                <p class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $visitor->first_seen_at->format('M j, Y H:i') }}
                </p>
            </flux:card>

            <flux:card variant="subtle" class="p-3">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Seen</p>
                <p class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $visitor->last_seen_at->format('M j, Y H:i') }}
                </p>
            </flux:card>

            <flux:card variant="subtle" class="p-3">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</p>
                <p class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $visitor->city && $visitor->country ? $visitor->city . ', ' . $visitor->country : 'Unknown' }}
                </p>
            </flux:card>

            <flux:card variant="subtle" class="p-3">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Device</p>
                <p class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $visitor->device ?: 'Unknown' }}
                </p>
            </flux:card>
        </div>
    </flux:card>

    <flux:tabs>
        <flux:tab wire:click="changeTab('sessions')" :selected="$activeTab === 'sessions'" icon="clock">
            Sessions
        </flux:tab>

        <flux:tab wire:click="changeTab('pageviews')" :selected="$activeTab === 'pageviews'" icon="eye">
            Page Views
        </flux:tab>

        <flux:tab wire:click="changeTab('events')" :selected="$activeTab === 'events'" icon="bolt">
            Events
        </flux:tab>
    </flux:tabs>

    @if ($activeTab === 'sessions')
        <flux:card>
            @if ($sessionDetails)
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">Session Breakdown</flux:heading>
                        <flux:button size="sm" variant="subtle" wire:click="resetSessionDetails">
                            View All Sessions
                        </flux:button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl">
                            <flux:label>Started</flux:label>
                            <p class="font-medium">{{ $sessionDetails->started_at->format('M j, Y H:i:s') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl">
                            <flux:label>Duration</flux:label>
                            <p class="font-medium">
                                {{ $sessionDetails->duration ? gmdate('H:i:s', $sessionDetails->duration) : 'Active' }}
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl">
                            <flux:label>End Status</flux:label>
                            <p class="font-medium">
                                {{ $sessionDetails->ended_at ? $sessionDetails->ended_at->format('H:i:s') : 'Ongoing' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <flux:heading size="md">Activities in this Session</flux:heading>
                        <div class="">
                            @foreach ($sessionDetails->pageViews as $pageView)
                                <div
                                    class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $pageView->url }}</span>
                                        <span class="text-xs text-gray-500">Method: {{ $pageView->method }} | Status:
                                            {{ $pageView->status_code }}</span>
                                    </div>
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $pageView->created_at->format('H:i:s') }}
                                    </flux:badge>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Started</flux:table.column>
                        <flux:table.column>Duration</flux:table.column>
                        <flux:table.column>Views</flux:table.column>
                        <flux:table.column>Events</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($sessions as $session)
                            <flux:table.row>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ $session->started_at->format('M j, Y H:i:s') }}
                                </flux:table.cell>
                                <flux:table.cell>{{ $session->duration ? gmdate('H:i:s', $session->duration) : 'N/A' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">{{ $session->pageViews->count() }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">{{ $session->events->count() }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" variant="ghost" wire:click="loadSessionDetails('{{ $session->id }}')">
                                        Details</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center">No sessions found.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    @endif

    @if ($activeTab === 'pageviews')
        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Time</flux:table.column>
                    <flux:table.column>URL</flux:table.column>
                    <flux:table.column>Method</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Load Time</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->pageViews as $pageView)
                        <flux:table.row>
                            <flux:table.cell>{{ $pageView->created_at->format('H:i:s') }}</flux:table.cell>
                            <flux:table.cell class="max-w-xs truncate">{{ $pageView->url }}</flux:table.cell>
                            <flux:table.cell>{{ $pageView->method }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $pageView->status_code < 400 ? 'green' : 'red' }}">
                                    {{ $pageView->status_code }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                {{ $pageView->load_time ? number_format($pageView->load_time, 0) . 'ms' : '-' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center">No page views recorded.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <!-- Pagination for Page Views -->
            @if ($this->pageViews->hasPages())
                <div class="mt-4">
                    {{ $this->pageViews->links() }}
                </div>
            @endif
        </flux:card>
    @endif

    @if ($activeTab === 'events')
        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Time</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Event Name</flux:table.column>
                    <flux:table.column>Data</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->events as $event)
                        <flux:table.row>
                            <flux:table.cell>{{ $event->created_at->format('H:i:s') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge variant="subtle">{{ $event->event_type }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="font-medium">{{ $event->event_name }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($event->event_data)
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 p-1 rounded">
                                                                                                                                                                                                                                                                {{ json_encode($event->event_data) }}
                                                                                                                                                                                                                                                            </code>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center">No events found.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <!-- Pagination for Events -->
            @if ($this->events->hasPages())
                <div class="mt-4">
                    {{ $this->events->links() }}
                </div>
            @endif
        </flux:card>
    @endif
</div>