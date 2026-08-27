<?php

use Livewire\Volt\Component;
use App\Models\Quran;
use App\Models\Sura;
use App\Models\Para;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed, Validate, On};
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public array $activities = [];
    public ?array $selectedActivity = null;

    // Form fields
    public $quranId;

    #[Validate('required|integer')]
    public $ayat_no = '';

    #[Validate('required|string')]
    public $arabic_text = '';

    #[Validate('required|string')]
    public $bangla_meaning = '';

    #[Validate('nullable|string')]
    public $english_meaning = '';

    #[Validate('required|exists:paras,id')]
    public $para_id = '';

    #[Validate('required|exists:suras,id')]
    public $sura_id = '';

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('nullable|mimes:mp3,wav|max:10240')]
    public $audio;

    // UI states
    public $viewType = 'active';
    public $search = '';
    public $perPage = 10;
    public $sortField = 'ayat_no';
    public $sortDirection = 'asc';

    // Collections
    public $suras = [];
    public $paras = [];

    public function mount()
    {
        $this->suras = Sura::select('id', 'sura_no', 'name_bangla')->orderBy('sura_no')->get();
        $this->paras = Para::select('id', 'para_number')->orderBy('para_number')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    #[Computed]
    public function qurans()
    {
        return ($this->viewType === 'trashed' ? Quran::onlyTrashed() : Quran::query())
            ->with(['sura', 'para'])
            ->when($this->search, function ($query) {
                $query
                    ->where('arabic_text', 'like', '%' . $this->search . '%')
                    ->orWhere('bangla_meaning', 'like', '%' . $this->search . '%')
                    ->orWhere('english_meaning', 'like', '%' . $this->search . '%')
                    ->orWhere('ayat_no', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'suras' => $this->suras,
            'paras' => $this->paras,
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['quranId', 'ayat_no', 'arabic_text', 'bangla_meaning', 'english_meaning', 'para_id', 'sura_id', 'is_active', 'audio']);
        $this->dispatch('modal-show', name: 'quran-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $quran = Quran::withTrashed()->findOrFail($id);

        $this->quranId = $quran->id;
        $this->ayat_no = $quran->ayat_no;
        $this->arabic_text = $quran->arabic_text;
        $this->bangla_meaning = $quran->bangla_meaning;
        $this->english_meaning = $quran->english_meaning;
        $this->para_id = $quran->para_id;
        $this->sura_id = $quran->sura_id;
        $this->is_active = (bool) $quran->is_active;

        $this->dispatch('modal-show', name: 'quran-form');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'ayat_no' => $this->ayat_no,
            'arabic_text' => $this->arabic_text,
            'bangla_meaning' => $this->bangla_meaning,
            'english_meaning' => $this->english_meaning,
            'para_id' => $this->para_id,
            'sura_id' => $this->sura_id,
            'is_active' => $this->is_active,
            'slug' => Str::slug("sura-{$this->sura_id}-ayat-{$this->ayat_no}"),
        ];

        $quran = Quran::updateOrCreate(['id' => $this->quranId], $data);

        // Spatie Media Library Upload
        if ($this->audio) {
            // Clear existing audio if updating
            if ($this->quranId) {
                $quran->clearMediaCollection('ayat_audio');
            }

            $quran
                ->addMedia($this->audio->getRealPath())
                ->usingFileName(Str::random(10) . '.' . $this->audio->getClientOriginalExtension())
                ->toMediaCollection('ayat_audio');
        }

        $this->dispatch('modal-close', name: 'quran-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'আয়াতটি সংরক্ষিত হয়েছে।');
        $this->reset(['audio', 'quranId']);
    }

    #[On('audio-uploaded')]
    public function handleAudioUpload($fileInfo)
    {
        $this->audio = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($fileInfo);
    }

    public function delete($id)
    {
        Quran::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আয়াতটি ট্র্যাশে পাঠানো হয়েছে।');
    }

    public function restore($id)
    {
        Quran::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'আয়াতটি রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $quran = Quran::onlyTrashed()->findOrFail($id);
        $quran->clearMediaCollection('ayat_audio');
        $quran->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'আয়াতটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        try {
            $this->activities = Activity::query()
                ->with('causer')
                ->where('subject_id', $id)
                ->where('subject_type', Quran::class)
                ->latest()
                ->get()
                ->map(function ($activity) {
                    try {
                        $properties = $activity->properties ?? collect();

                        // Safely convert to array if it's a collection
                        $propertiesArray = $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties;

                        // Format the changes for display with null checks
                        if ($activity->event === 'updated' && isset($propertiesArray['changes'])) {
                            $activity->formatted_changes = $this->formatChanges($propertiesArray['changes']);
                        } elseif ($activity->event === 'created' && isset($propertiesArray['attributes'])) {
                            $activity->formatted_data = $propertiesArray['attributes'];
                        } elseif ($activity->event === 'deleted' && isset($propertiesArray['old'])) {
                            $activity->formatted_data = $propertiesArray['old'];
                        }

                        return $activity;
                    } catch (\Exception $e) {
                        // Return a safe version of the activity if there's an error
                        $activity->formatted_changes = [];
                        $activity->formatted_data = [];
                        return $activity;
                    }
                })
                ->toArray();
        } catch (\Exception $e) {
            $this->activities = [];
            $this->dispatch('toast', variant: 'error', text: 'Error loading activity logs.');
        }

        $this->dispatch('modal-show', name: 'activity-logs');
    }

    private function formatChanges($changes): array
    {
        $formatted = [];

        if (!is_array($changes) || empty($changes)) {
            return $formatted;
        }

        foreach ($changes as $field => $change) {
            // Skip if change is not an array with old/new structure
            if (!is_array($change) || !isset($change['old']) || !isset($change['new'])) {
                continue;
            }

            // Determine field type for display
            $type = 'text';
            if (in_array($field, ['arabic_text', 'bangla_meaning', 'english_meaning'])) {
                $type = 'textarea';
            }

            // Safely get old and new values
            $oldValue = $change['old'] ?? null;
            $newValue = $change['new'] ?? null;

            // Format arrays and objects
            if (is_array($oldValue) || is_object($oldValue)) {
                $oldValue = json_encode($oldValue, JSON_UNESCAPED_UNICODE);
            }
            if (is_array($newValue) || is_object($newValue)) {
                $newValue = json_encode($newValue, JSON_UNESCAPED_UNICODE);
            }

            $formatted[] = [
                'field' => $this->formatFieldName($field),
                'type' => $type,
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $formatted;
    }

    private function formatFieldName($field): string
    {
        $names = [
            'ayat_no' => 'Ayat Number',
            'arabic_text' => 'Arabic Text',
            'bangla_meaning' => 'Bangla Meaning',
            'english_meaning' => 'English Meaning',
            'para_id' => 'Para',
            'sura_id' => 'Sura',
            'is_active' => 'Status',
            'slug' => 'Slug',
        ];

        return $names[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    // View specific activity details
    public function viewActivityDetails(int $activityId): void
    {
        try {
            $activity = Activity::with('causer')->find($activityId);

            if ($activity) {
                $properties = $activity->properties ?? collect();

                $this->selectedActivity = [
                    'id' => $activity->id,
                    'event' => $activity->event ?? 'unknown',
                    'description' => $activity->description ?? '',
                    'causer' => $activity->causer?->name ?? 'System',
                    'created_at' => $activity->created_at ? $activity->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A'),
                    'properties' => $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties,
                ];

                $this->dispatch('modal-show', name: 'activity-detail');
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', variant: 'error', text: 'Error loading activity details.');
        }
    }
}; ?>

<div>
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Quran Ayat Management</flux:heading>
            <flux:subheading>Manage Quranic verses with Arabic text, translations, and audio recitations.
            </flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Add New Ayat
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search"
            placeholder="Search by Ayat number, Arabic text or meanings..." icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->qurans">
        <flux:table.columns>
            <flux:table.column wire:click="sortBy('ayat_no')" :sortable="true"
                :direction="$sortField === 'ayat_no' ? $sortDirection : null">
                Ayat No
            </flux:table.column>
            <flux:table.column>Arabic Text & Meaning</flux:table.column>
            <flux:table.column>Sura / Para</flux:table.column>
            <flux:table.column>Audio</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->qurans as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell class="font-bold text-lg">{{ $item->ayat_no }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col gap-4">
                            <span class="font-arabic text-right text-emerald-700 leading-loose text-base">
                                {{ Str::limit($item->arabic_text, 100) }}
                            </span>
                            <span class="text-sm text-gray-600">
                                {{ Str::limit($item->bangla_meaning, 80) }}
                            </span>
                            @if ($item->english_meaning)
                                <span class="text-xs text-gray-500 italic">
                                    {{ Str::limit($item->english_meaning, 80) }}
                                </span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">
                            <div class="font-medium">{{ $item->sura?->sura_no }}. {{ $item->sura?->name_bangla }}</div>
                            <div class="text-xs text-gray-500">পারা {{ $item->para?->para_number }}</div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($item->hasMedia('ayat_audio'))
                            <div class="flex items-center gap-4">
                                <flux:button size="sm" variant="ghost" icon="play"
                                    onclick="new Audio('{{ $item->getFirstMediaUrl('ayat_audio') }}').play()"
                                    title="Play Audio" />
                                <span class="text-xs text-gray-500">Audio available</span>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">No audio</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$item->is_active ? 'green' : 'red'">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $item->id }})" title="View Activity Logs" />

                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="Are you sure?" wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="This will be deleted permanently!"
                                wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-10 text-zinc-400">No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="quran-form" class="md:w-240">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $quranId ? 'Edit Ayat' : 'Add New Ayat' }}</flux:heading>
                <flux:subheading>Manage Quranic verse details including Arabic text, translations, and audio.
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select wire:model="sura_id" label="Sura" placeholder="Select Sura">
                    <option value="">Select Sura</option>
                    @foreach ($suras as $sura)
                        <option value="{{ $sura->id }}">{{ $sura->sura_no }}. {{ $sura->name_bangla }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="para_id" label="Para" placeholder="Select Para">
                    <option value="">Select Para</option>
                    @foreach ($paras as $para)
                        <option value="{{ $para->id }}">পারা {{ $para->para_number }}</option>
                    @endforeach
                </flux:select>

                <flux:input type="number" wire:model="ayat_no" label="Ayat Number"
                    placeholder="Enter ayat number..." />
            </div>

            <div wire:ignore>
                <flux:textarea wire:model="arabic_text" label="Arabic Text" placeholder="Enter Arabic text..."
                    class="font-arabic text-right text-lg" rows="4" />
            </div>

            <div wire:ignore>
                <flux:editor wire:model="bangla_meaning" label="Bangla Meaning" placeholder="Enter Bangla meaning..." />
            </div>

            <div wire:ignore>
                <flux:editor wire:model="english_meaning" label="English Meaning"
                    placeholder="Enter English meaning..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <flux:heading size="sm">Audio Recitation</flux:heading>
                    <flux:file-upload wire:model.live="audio" accept="audio/*" />
                    @if ($audio)
                        <div class="text-sm text-green-600">
                            <flux:icon name="check-circle" class="inline w-4 h-4" /> Audio file selected
                        </div>
                    @endif
                </div>
                <div class="pt-6">
                    <flux:checkbox wire:model="is_active" label="Active (Show on website)" />
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Ayat
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Activity Logs Modal --}}
    <flux:modal name="activity-logs" class="w-full">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <flux:heading size="lg">Activity History</flux:heading>
                    <flux:subheading>Detailed change log with before/after values</flux:subheading>
                </div>
            </div>

            <div class="space-y-6 pr-2">
                @forelse($activities as $log)
                    @php
                        $event = $log['event'] ?? 'unknown';
                        $createdAt = isset($log['created_at'])
                            ? \Carbon\Carbon::parse($log['created_at'])->format('d M Y, h:i A')
                            : '';
                        $causer = $log['causer'] ?? null;
                        $causerName = $causer['name'] ?? 'System';
                        $causerAvatar = $causer['avatar_url'] ?? null;
                        $formattedChanges = $log['formatted_changes'] ?? [];
                        $formattedData = $log['formatted_data'] ?? [];
                    @endphp

                    <div
                        class="relative pl-4 border-l-2 {{ $event === 'created'
                            ? 'border-green-500'
                            : ($event === 'updated'
                                ? 'border-blue-500'
                                : ($event === 'restored'
                                    ? 'border-yellow-500'
                                    : 'border-red-500')) }}">
                        <div class="flex flex-col gap-4">
                            {{-- Header --}}
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <flux:badge size="sm"
                                        color="{{ $event === 'created'
                                            ? 'green'
                                            : ($event === 'updated'
                                                ? 'blue'
                                                : ($event === 'restored'
                                                    ? 'yellow'
                                                    : 'red')) }}">
                                        {{ ucfirst($event) }}
                                    </flux:badge>
                                    <span class="text-sm font-medium text-zinc-700">
                                        {{ $log['description'] ?? 'No description' }}
                                    </span>
                                </div>
                                <span class="text-xs text-zinc-500">
                                    {{ $createdAt }}
                                </span>
                            </div>

                            {{-- User --}}
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-zinc-600">By:</span>
                                <flux:profile :chevron="false" name="{{ $causerName }}"
                                    avatar="{{ $causerAvatar }}" />
                            </div>

                            {{-- Changes Display --}}
                            @if ($event === 'updated' && !empty($formattedChanges))
                                <div class="mt-2 space-y-3">
                                    @foreach ($formattedChanges as $change)
                                        @php
                                            $field = $change['field'] ?? 'Unknown Field';
                                            $type = $change['type'] ?? 'text';
                                            $oldValue = $change['old'] ?? '(empty)';
                                            $newValue = $change['new'] ?? '(empty)';
                                        @endphp

                                        <div class="bg-zinc-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-zinc-700 mb-2">
                                                {{ $field }}
                                            </div>

                                            @if ($type === 'textarea')
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div
                                                            class="text-xs bg-white p-2 rounded border border-zinc-200 max-h-32 overflow-y-auto">
                                                            {!! nl2br(e($oldValue)) !!}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-xs bg-white p-2 rounded border border-zinc-200 max-h-32 overflow-y-auto">
                                                            {!! nl2br(e($newValue)) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-red-500 mb-2">Before:</div>
                                                        <div
                                                            class="text-sm bg-white p-2 rounded border border-zinc-200 break-words">
                                                            {{ $oldValue }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs text-green-500 mb-2">After:</div>
                                                        <div
                                                            class="text-sm bg-white p-2 rounded border border-zinc-200 break-words">
                                                            {{ $newValue }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(in_array($event, ['created', 'deleted', 'force_deleted']) && !empty($formattedData))
                                <div class="mt-2 bg-zinc-50 rounded-lg p-3">
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach ($formattedData as $key => $value)
                                            @php
                                                $displayValue = is_array($value)
                                                    ? implode(', ', $value)
                                                    : (string) $value;
                                            @endphp
                                            <div class="flex">
                                                <span
                                                    class="text-xs font-medium text-zinc-500 w-42">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span
                                                    class="text-sm break-words">{{ $displayValue ?: '(empty)' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- View Details Button --}}
                            <div class="flex justify-end">
                                <flux:button size="xs" variant="ghost" icon="eye"
                                    wire:click="viewActivityDetails({{ $log['id'] ?? 0 }})">
                                    View Full Details
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-400">
                        <flux:icon name="clock" class="w-12 h-12 mx-auto mb-2" />
                        <p>No activity logs found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </flux:modal>

    {{-- Activity Detail Modal --}}
    <flux:modal name="activity-detail" class="w-full max-w-3xl">
        @if ($selectedActivity)
            @php
                $event = $selectedActivity['event'] ?? 'unknown';
                $eventColor = $event === 'created' ? 'green' : ($event === 'updated' ? 'blue' : 'yellow');
                $description = $selectedActivity['description'] ?? '';
                $causer = $selectedActivity['causer'] ?? 'System';
                $createdAt = $selectedActivity['created_at'] ?? '';
                $properties = $selectedActivity['properties'] ?? null;
            @endphp

            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <flux:heading size="lg">Activity Details</flux:heading>
                    <flux:badge color="{{ $eventColor }}">
                        {{ ucfirst($event) }}
                    </flux:badge>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <flux:label>Description</flux:label>
                        <div class="text-sm bg-zinc-50 p-3 rounded-lg border border-zinc-200">
                            {!! $description !!}
                        </div>
                    </div>

                    <flux:field>
                        <flux:label>Performed by</flux:label>
                        <flux:input readonly value="{{ $causer }}" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Date & Time</flux:label>
                        <flux:input readonly value="{{ $createdAt }}" />
                    </flux:field>
                </div>

                @if ($properties)
                    <flux:field>
                        <flux:label>Full Properties</flux:label>
                        <div
                            class="bg-zinc-950 p-4 rounded-lg text-emerald-400 text-xs font-mono overflow-auto max-h-60 border border-zinc-800 whitespace-pre-wrap">
                            {{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </div>
                    </flux:field>
                @endif
            </div>
        @endif
    </flux:modal>
</div>
