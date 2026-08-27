<?php

use App\Models\NewsHeading;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Flux\Flux;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $language = '';

    // Activity Log State
    public $logs = [];
    public $selectedNewsTitle = '';

    protected $queryString = ['search', 'category', 'language'];

    public function delete($id)
    {
        $news = NewsHeading::findOrFail($id);
        $news->delete();
        Flux::toast(variant: 'success', heading: 'Moved to Trash', text: 'News item has been soft deleted.');
    }

    public function restore($id)
    {
        NewsHeading::withTrashed()->findOrFail($id)->restore();
        Flux::toast(variant: 'success', heading: 'Restored', text: 'News item is now active again.');
    }

    public function forceDelete($id)
    {
        $news = NewsHeading::withTrashed()->findOrFail($id);
        $news->clearMediaCollection();
        $news->forceDelete();
        Flux::toast(variant: 'danger', heading: 'Deleted Permanently', text: 'Data and media have been wiped.');
    }

    public function viewLogs($id)
    {
        $news = NewsHeading::withTrashed()->findOrFail($id);
        $this->selectedNewsTitle = $news->title;

        $this->logs = Activity::forSubject($news)->with('causer')->latest()->get()->map(
            fn($log) => [
                'event' => strtoupper($log->description),
                'user' => $log->causer?->name ?? 'System',
                'time' => $log->created_at->diffForHumans(),
                'properties' => $log->changes(),
            ],
        );

        $this->modal('audit-logs')->show();
    }

    public function with()
    {
        return [
            'newsItems' => NewsHeading::query()->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))->when($this->category, fn($q) => $q->where('category', $this->category))->when($this->language, fn($q) => $q->where('language', $this->language))->withTrashed()->latest()->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">News Headlines</flux:heading>
            <flux:subheading>Broadcast and manage your latest news items and sources.</flux:subheading>
        </div>
        <flux:button icon="plus" href="/news/create" variant="primary">
            Create News
        </flux:button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:card>
            <flux:text>Total News</flux:text>
            <flux:heading size="lg">{{ NewsHeading::count() }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text>In Trash</flux:text>
            <flux:heading size="lg">{{ NewsHeading::onlyTrashed()->count() }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text>Published Today</flux:text>
            <flux:heading size="lg">{{ NewsHeading::whereDate('created_at', today())->count() }}</flux:heading>
        </flux:card>
    </div>

    {{-- Filters --}}
    <flux:card>
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <flux:input wire:model.live.debounce.400ms="search" label="Search" placeholder="Search by title..."
                    icon="magnifying-glass" />
            </div>

            <flux:select wire:model.live="category" label="Category" class="w-full md:w-48">
                <flux:select.option value="">All Categories</flux:select.option>
                <flux:select.option value="national">National</flux:select.option>
                <flux:select.option value="international">International</flux:select.option>
                <flux:select.option value="sports">Sports</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="language" label="Language" class="w-full md:w-32">
                <flux:select.option value="">All</flux:select.option>
                <flux:select.option value="bn">Bangla</flux:select.option>
                <flux:select.option value="en">English</flux:select.option>
            </flux:select>

            <flux:button icon="arrow-path" wire:click="$set('search', '')">
                Reset
            </flux:button>
        </div>
    </flux:card>

    {{-- Table --}}
    <flux:table :paginate="$newsItems">
        <flux:table.columns>
            <flux:table.column>Preview</flux:table.column>
            <flux:table.column sortable>Title & Source</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($newsItems as $item)
                <flux:table.row :key="$item->id" class="{{ $item->trashed() ? 'opacity-60' : '' }}">
                    <flux:table.cell>
                        <img src="{{ $item->image_url ?: 'https://ui-avatars.com/api/?name=News' }}" alt="News"
                            class="size-12 rounded object-cover bg-zinc-200" />
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:text class="font-medium">{{ str($item->title)->limit(50) }}</flux:text>
                        <flux:text size="sm">{{ $item->source_name }}</flux:text>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:badge size="sm" :color="$item->language === 'bn' ? 'blue' : 'orange'">
                                {{ strtoupper($item->language) }}
                            </flux:badge>
                            <flux:badge size="sm" variant="outline">
                                {{ ucfirst($item->category) }}
                            </flux:badge>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:text size="sm">{{ $item->created_at->format('M d, Y') }}</flux:text>
                        <flux:text size="xs">{{ $item->created_at->format('h:i A') }}</flux:text>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            @if (!$item->trashed())
                                <flux:button size="sm" variant="ghost" icon="pencil-square"
                                    href="/news/{{ $item->id }}/edit" />
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
                                    <flux:menu>
                                        <flux:menu.item icon="clock" wire:click="viewLogs({{ $item->id }})">
                                            History
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="delete({{ $item->id }})" wire:confirm="Move to trash?">
                                            Move to Trash
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                <flux:button size="sm" variant="ghost" icon="arrow-path"
                                    wire:click="restore({{ $item->id }})" tooltip="Restore" />
                                <flux:button size="sm" variant="ghost" color="red" icon="trash"
                                    wire:click="forceDelete({{ $item->id }})"
                                    wire:confirm="Permanently delete this news?" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Audit Logs Modal --}}
    <flux:modal name="audit-logs" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Activity History</flux:heading>
                <flux:subheading>{{ $selectedNewsTitle }}</flux:subheading>
            </div>

            <div class="space-y-6 max-h-96 overflow-y-auto">
                @forelse ($logs as $log)
                    <div class="flex gap-4">
                        <div class="shrink-0 mt-1">
                            <div class="size-3 rounded-full bg-indigo-500"></div>
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <flux:text class="font-semibold">{{ $log['event'] }}</flux:text>
                                <flux:text size="xs">{{ $log['time'] }}</flux:text>
                            </div>
                            <flux:text size="sm">
                                Performed by <span class="font-medium">{{ $log['user'] }}</span>
                            </flux:text>

                            @if (isset($log['properties']['attributes']))
                                <flux:card size="sm" class="mt-2">
                                    @foreach ($log['properties']['attributes'] as $key => $val)
                                        <flux:text size="xs">
                                            <span class="text-zinc-500">{{ $key }}:</span>
                                            {{ is_array($val) ? 'Media/Array' : Str::limit($val, 50) }}
                                        </flux:text>
                                    @endforeach
                                </flux:card>
                            @endif
                        </div>
                    </div>
                @empty
                    <flux:text>No activity recorded yet.</flux:text>
                @endforelse
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
