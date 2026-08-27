<?php

use Livewire\Volt\Component;
use App\Models\Sign;
use App\Models\SignCategory;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed, Validate};
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public ?int $signId = null;

    // Activity log properties
    public array $activities = [];
    public ?array $selectedActivity = null;

    #[Validate('required|min:3|max:255')]
    public string $name = '';

    #[Validate('required|min:3|max:255|unique:signs,slug,{signId}')]
    public string $slug = '';

    #[Validate('required|min:10')]
    public string $description = '';

    #[Validate('nullable|integer')]
    public ?int $sign_category_id = null;

    #[Validate('nullable|string|min:3|required_if:categoryInputType,create')]
    public string $new_category = '';

    #[Validate('boolean')]
    public bool $is_featured = false;

    // Holds existing array format or new UploadedFile objects
    public array $images = [];

    // Table Filter States
    public string $viewType = 'active';
    public string $search = '';
    public string $categoryInputType = 'select';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedViewType(): void
    {
        $this->resetPage();
    }

    /**
     * Auto-generate slug from name only when creating a new record
     * and the slug field is still empty.
     */
    public function updatedName(string $value): void
    {
        if (!$this->signId && blank($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    #[Computed]
    public function signs()
    {
        return ($this->viewType === 'trashed' ? Sign::onlyTrashed() : Sign::query())
            ->with(['category', 'media'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('slug', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'availableCategories' => SignCategory::active()->get(),
        ];
    }

    public function showCreateForm(): void
    {
        $this->resetValidation();
        $this->reset(['signId', 'name', 'slug', 'description', 'sign_category_id', 'new_category', 'is_featured', 'images', 'categoryInputType']);
        $this->dispatch('modal-show', name: 'sign-form');
    }

    public function showEditForm(int $id): void
    {
        $this->resetValidation();

        $sign = Sign::withTrashed()->findOrFail($id);

        $this->signId = $sign->id;
        $this->name = $sign->name;
        $this->slug = $sign->slug;
        $this->description = $sign->description;
        $this->sign_category_id = $sign->sign_category_id;
        $this->is_featured = (bool) $sign->is_featured;
        $this->categoryInputType = 'select';

        $this->images = $sign
            ->getMedia('images')
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'url' => $m->getUrl('thumb'),
                    'original_url' => $m->getUrl(),
                    'is_existing' => true,
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'sign-form');
    }

    public function removeImage(string $propertyName, int $index): void
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file) {
            return;
        }

        // Existing media → delete from media library
        if (is_array($file) && ($file['is_existing'] ?? false)) {
            $sign = Sign::withTrashed()->findOrFail($this->signId);
            $sign->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save(): void
    {
        // Make unique rule ignore current record on edit
        $this->validate([
            'slug' => ['required', 'min:3', 'max:255', 'unique:signs,slug,' . ($this->signId ?? 'NULL')],
        ]);

        $this->validate();

        // Create new category if needed
        if ($this->categoryInputType === 'create' && filled($this->new_category)) {
            $category = SignCategory::firstOrCreate(
                ['slug' => Str::slug($this->new_category)],
                [
                    'name' => $this->new_category,
                    'status' => 1,
                ],
            );

            $this->sign_category_id = $category->id;
        }

        $sign = Sign::updateOrCreate(
            ['id' => $this->signId],
            [
                'name' => $this->name,
                'slug' => $this->slug, // fully custom
                'description' => $this->description,
                'sign_category_id' => $this->sign_category_id ?: null,
                'is_featured' => $this->is_featured,
                'status' => 1,
            ],
        );

        // Process & upload new images (strip metadata + convert to WebP)
        foreach ($this->images as $file) {
            if (is_array($file)) {
                continue; // existing media
            }

            $fileName = $this->slug . '-totthobox-sign-' . Str::lower(Str::random(6)) . '.webp';

            // Create a clean temporary WebP file
            $tempPath = sys_get_temp_dir() . '/' . $fileName;

            Image::read($file->getRealPath())->toWebp(85)->save($tempPath);

            $sign
                ->addMedia($tempPath)
                ->usingFileName($fileName)
                ->usingName("{$sign->name} - Totthobox Sign")
                ->toMediaCollection('images');

            // Clean up temp file
            @unlink($tempPath);
        }

        $this->dispatch('modal-close', name: 'sign-form');
        $this->dispatch('toast', variant: 'success', heading: 'Success', text: 'Data saved successfully.');

        $this->reset(['images', 'signId', 'new_category', 'slug']);
    }

    public function delete(int $id): void
    {
        Sign::findOrFail($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Item moved to trash.');
    }

    public function restore(int $id): void
    {
        Sign::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Item restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $sign = Sign::onlyTrashed()->findOrFail($id);
        $sign->clearMediaCollection('images');
        $sign->forceDelete();

        $this->dispatch('toast', variant: 'error', text: 'Item deleted permanently.');
    }

    public function viewLogs(int $id): void
    {
        $this->activities = Activity::query()
            ->with('causer')
            ->where('subject_id', $id)
            ->where('subject_type', Sign::class)
            ->latest()
            ->get()
            ->map(function ($activity) {
                $properties = $activity->properties;

                if ($activity->event === 'updated' && isset($properties['attributes'], $properties['old'])) {
                    $activity->formatted_changes = $this->formatChanges($properties->toArray());
                } elseif (in_array($activity->event, ['created', 'deleted', 'force_deleted'])) {
                    $activity->formatted_data = $properties['attributes'] ?? [];
                }

                return $activity;
            })
            ->toArray();

        $this->dispatch('modal-show', name: 'activity-logs');
    }

    public function viewActivityDetails(int $activityId): void
    {
        $activity = Activity::with('causer')->find($activityId);

        if ($activity) {
            $this->selectedActivity = [
                'id' => $activity->id,
                'event' => $activity->event,
                'description' => $activity->description,
                'causer' => $activity->causer?->name ?? 'System',
                'created_at' => $activity->created_at->format('d M Y, h:i A'),
                'properties' => $activity->properties,
            ];

            $this->dispatch('modal-show', name: 'activity-detail');
        }
    }

    private function formatChanges(array $changes): array
    {
        $formatted = [];
        $old = $changes['old'] ?? [];
        $new = $changes['attributes'] ?? [];

        foreach ($new as $key => $value) {
            $formatted[] = [
                'field' => ucfirst(str_replace('_', ' ', $key)),
                'old' => is_scalar($old[$key] ?? null) ? $old[$key] : 'None',
                'new' => is_scalar($value ?? null) ? $value : 'None',
            ];
        }

        return $formatted;
    }
};
?>

<div class="p-1">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Sign Management</flux:heading>
            <flux:subheading>Manage your visual signals and records safely.</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">
                Create New
            </flux:button>
        </div>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by name or slug..."
            icon="magnifying-glass" />
    </div>

    <flux:table :paginate="$this->signs">
        <flux:table.columns>
            <flux:table.column>Media</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->signs as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        @php $mediaItems = $item->getMedia('images'); @endphp
                        <flux:avatar.group>
                            @if ($mediaItems->isEmpty())
                                <flux:icon.photo class="text-zinc-300 w-8 h-8" />
                            @else
                                @foreach ($mediaItems->take(3) as $media)
                                    <flux:avatar src="{{ $media->getUrl('thumb') }}" />
                                @endforeach
                                @if ($mediaItems->count() > 3)
                                    <flux:avatar initials="+{{ $mediaItems->count() - 3 }}" />
                                @endif
                            @endif
                        </flux:avatar.group>
                    </flux:table.cell>

                    <flux:table.cell class="font-medium">
                        {{ $item->name }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <code class="text-xs text-zinc-500">{{ $item->slug }}</code>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">
                            {{ $item->category->name ?? 'General' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $item->id }})" title="Logs" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="Are you sure?" wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="Permanently delete this?" wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-10 text-zinc-400">
                        No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- ==================== CREATE / EDIT MODAL ==================== --}}
    <flux:modal name="sign-form" class="md:w-180">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $signId ? 'Edit Sign Record' : 'Add New Sign' }}
                </flux:heading>
                <flux:subheading>
                    Update details and asset images safely.
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="name" label="Name" placeholder="Enter sign name..." />

                <flux:input wire:model="slug" label="Slug" placeholder="custom-slug-here"
                    description="Fully custom. Auto-generated from name only on create if left empty." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                <flux:field>
                    <flux:label>Category</flux:label>
                    <div class="flex gap-4">
                        @if ($categoryInputType === 'select')
                            <flux:select wire:model="sign_category_id" class="flex-1">
                                <option value="">Select Category</option>
                                @foreach ($availableCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </flux:select>
                        @else
                            <flux:input wire:model="new_category" class="flex-1" placeholder="New category name..." />
                        @endif

                        <flux:button variant="subtle" size="sm" type="button"
                            wire:click="$set('categoryInputType', '{{ $categoryInputType === 'select' ? 'create' : 'select' }}')">
                            {{ $categoryInputType === 'select' ? 'New' : 'List' }}
                        </flux:button>
                    </div>
                </flux:field>

                <div class="pb-2">
                    <flux:checkbox wire:model="is_featured" label="Show as Featured" />
                </div>
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Detailed Description" />
            </div>

            <flux:field>
                <flux:label>Images</flux:label>
                <flux:file-upload wire:model.live="images" multiple accept="image/*" />
            </flux:field>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Configuration
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ==================== ACTIVITY LOGS ==================== --}}
    <flux:modal name="activity-logs" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Sign History Trail</flux:heading>
                <flux:subheading>Full structural lifecycle changes logging</flux:subheading>
            </div>

            <div class="space-y-6 overflow-y-auto max-h-[60vh] pr-2">
                @forelse ($activities as $log)
                    <div
                        class="relative pl-4 border-l-2
                                                {{ $log['event'] === 'created'
                                                    ? 'border-green-500'
                                                    : ($log['event'] === 'updated'
                                                        ? 'border-blue-500'
                                                        : ($log['event'] === 'restored'
                                                            ? 'border-yellow-500'
                                                            : 'border-red-500')) }}">
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <flux:badge size="sm"
                                        color="{{ $log['event'] === 'created'
                                            ? 'green'
                                            : ($log['event'] === 'updated'
                                                ? 'blue'
                                                : ($log['event'] === 'restored'
                                                    ? 'yellow'
                                                    : 'red')) }}">
                                        {{ ucfirst($log['event']) }}
                                    </flux:badge>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $log['description'] }}
                                    </span>
                                </div>
                                <span class="text-xs text-zinc-500">
                                    {{ \Carbon\Carbon::parse($log['created_at'])->format('d M Y, h:i A') }}
                                </span>
                            </div>

                            <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                Actor:
                                <strong class="text-zinc-800 dark:text-zinc-200">
                                    {{ $log['causer']['name'] ?? 'System Process' }}
                                </strong>
                            </div>

                            @if ($log['event'] === 'updated' && isset($log['formatted_changes']))
                                <div class="mt-2 space-y-2">
                                    @foreach ($log['formatted_changes'] as $change)
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-3 border border-zinc-400/25">
                                            <div class="text-xs font-semibold text-zinc-500 mb-2">
                                                {{ $change['field'] }}
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <span class="text-xs text-red-500 block">Before:</span>
                                                    <div class="text-xs font-mono truncate">{{ $change['old'] }}</div>
                                                </div>
                                                <div>
                                                    <span class="text-xs text-green-500 block">After:</span>
                                                    <div class="text-xs font-mono truncate">{{ $change['new'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex justify-end">
                                <flux:button size="xs" variant="ghost" icon="eye"
                                    wire:click="viewActivityDetails({{ $log['id'] }})">
                                    Full Meta Payload
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-400">
                        <p>No lifecycle logs captured for this record.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </flux:modal>

    {{-- ==================== ACTIVITY DETAIL ==================== --}}
    <flux:modal name="activity-detail" class="w-full max-w-2xl">
        @if ($selectedActivity)
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <flux:heading size="lg">Activity Meta Stack</flux:heading>
                    <flux:badge size="sm">{{ $selectedActivity['event'] }}</flux:badge>
                </div>
                <div
                    class="bg-zinc-950 p-4 rounded-lg text-emerald-400 text-xs font-mono overflow-auto max-h-72 border border-zinc-800 whitespace-pre-wrap">
                    {{ json_encode($selectedActivity['properties'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                </div>
            </div>
        @endif
    </flux:modal>
</div>
