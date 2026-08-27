<?php

use Livewire\Volt\Component;
use App\Models\Holiday;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed, Locked};
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination, WithFileUploads;

    public array $activities = [];
    public ?array $selectedActivity = null;

    public $images = [];

    // Form Fields
    #[Locked]
    public $holidayId = null;

    #[Validate('required|min:3|max:255')]
    public $title = '';

    public $slug = '';

    // true = user has manually customized the slug → never auto-overwrite
    public bool $slugIsCustom = false;

    #[Validate('required|date')]
    public $date;

    #[Validate('required')]
    public $type = '';

    #[Validate('nullable|string')]
    public $details = '';

    #[Validate('boolean')]
    public $is_annual = true;

    #[Validate('required|in:1,0')]
    public $status = 1;

    // UI State
    public $viewType = 'active';
    public $search = '';

    // বাংলাদেশ গেজেট অনুযায়ী ছুটির ধরণ
    public $holidayTypes = [
        'Public' => 'সাধারণ ছুটি',
        'Executive' => 'নির্বাহী আদেশে ছুটি',
        'Religious' => 'ধর্মীয় ছুটি (ঐচ্ছিক)',
        'National' => 'জাতীয় দিবস',
        'International' => 'আন্তর্জাতিক দিবস',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    /**
     * Title change → only auto-update slug when it is NOT customized
     */
    public function updatedTitle($value)
    {
        if (!$this->slugIsCustom) {
            $this->slug = $this->generateUniqueSlug($value);
        }
    }

    /**
     * User typed something in the slug field → mark as customized
     */
    public function updatedSlug($value)
    {
        $this->slugIsCustom = true;
        $this->slug = Str::slug($value);
    }

    /**
     * Reset slug back to auto-generated from current title
     */
    public function resetSlugToAuto()
    {
        $this->slugIsCustom = false;
        $this->slug = $this->generateUniqueSlug($this->title);
    }

    /**
     * Generate a unique slug (ignores current holiday when editing)
     */
    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        if (empty($base)) {
            $base = 'holiday-' . Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 1;

        while (Holiday::withTrashed()->where('slug', $slug)->when($this->holidayId, fn($q) => $q->where('id', '!=', $this->holidayId))->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    #[Computed]
    public function holidays()
    {
        return ($this->viewType === 'trashed' ? Holiday::onlyTrashed() : Holiday::query())
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query
                        ->where('title', 'like', "%{$this->search}%")
                        ->orWhere('type', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('date', 'desc')
            ->paginate(10);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['holidayId', 'title', 'slug', 'slugIsCustom', 'date', 'type', 'details', 'is_annual', 'status', 'images']);

        $this->is_annual = true;
        $this->status = 1;
        $this->slugIsCustom = false;

        $this->dispatch('modal-show', name: 'holiday-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $this->reset(['images']);

        $item = Holiday::withTrashed()->findOrFail($id);

        $this->holidayId = $item->id;
        $this->title = $item->title;
        $this->slug = $item->slug;
        $this->slugIsCustom = true; // important: existing slug is treated as customized
        $this->date = $item->date->format('Y-m-d');
        $this->type = $item->type;
        $this->details = $item->details;
        $this->is_annual = (bool) $item->is_annual;
        $this->status = $item->status ? 1 : 0;

        // Load existing media
        $this->images = $item
            ->getMedia('holiday_images')
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'url' => $m->getUrl(),
                    'is_existing' => true,
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'holiday-form');
    }

    public function save()
    {
        // Dynamic unique rule for slug
        $slugRules = [
            'required',
            'string',
            'max:255',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            \Illuminate\Validation\Rule::unique('holidays', 'slug')->ignore($this->holidayId)->whereNull('deleted_at'), // soft-delete aware if you want
        ];

        $this->validate([
            'title' => 'required|min:3|max:255',
            'slug' => $slugRules,
            'date' => 'required|date',
            'type' => 'required',
            'details' => 'nullable|string',
            'is_annual' => 'boolean',
            'status' => 'required|in:1,0',
        ]);

        // Separate new uploads
        $newImages = array_filter($this->images, fn($img) => $img instanceof TemporaryUploadedFile);

        if (!empty($newImages)) {
            $validator = Validator::make(
                ['new_uploads' => $newImages],
                ['new_uploads.*' => 'image|max:2048'],
                [
                    'new_uploads.*.image' => 'ফাইলটি অবশ্যই একটি ইমেজ হতে হবে।',
                    'new_uploads.*.max' => 'ইমেজের সাইজ ২MB এর বেশি হতে পারবে না।',
                ],
            );

            if ($validator->fails()) {
                $this->addError('images', $validator->errors()->first('new_uploads.*'));
                return;
            }
        }

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'date' => $this->date,
            'type' => $this->type,
            'details' => $this->details,
            'is_annual' => $this->is_annual,
            'status' => $this->status,
        ];

        $holiday = Holiday::updateOrCreate(['id' => $this->holidayId], $data);

        // Process new images → strip metadata + convert to WebP
        if (!empty($newImages)) {
            foreach ($newImages as $file) {
                $fileName = $this->slug . '-totthobox-holiday-' . Str::lower(Str::random(6)) . '.webp';

                $processed = Image::read($file->getRealPath());
                $processed->toWebp(85)->save($file->getRealPath());

                $holiday
                    ->addMedia($file->getRealPath())
                    ->usingFileName($fileName)
                    ->usingName("{$holiday->title} - Totthobox Holiday")
                    ->toMediaCollection('holiday_images');
            }
        }

        $this->dispatch('modal-close', name: 'holiday-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'ছুটির তথ্য সফলভাবে সংরক্ষিত হয়েছে।');

        $this->reset(['holidayId', 'images', 'slugIsCustom']);
    }

    public function forceDelete($id)
    {
        $item = Holiday::onlyTrashed()->findOrFail($id);
        $item->clearMediaCollection('holiday_images');
        $item->forceDelete();

        $this->dispatch('toast', variant: 'error', text: 'আইটেমটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file) {
            return;
        }

        if (is_array($file) && isset($file['is_existing']) && $file['is_existing']) {
            $item = Holiday::withTrashed()->findOrFail($this->holidayId);
            $item->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function delete($id)
    {
        Holiday::findOrFail($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে পাঠানো হয়েছে।');
    }

    public function restore($id)
    {
        Holiday::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'আইটেমটি রিস্টোর করা হয়েছে।');
    }

    // ========== Activity Logs ==========

    public function viewLogs(int $id): void
    {
        try {
            $this->activities = Activity::query()
                ->with('causer')
                ->where('subject_id', $id)
                ->where('subject_type', Holiday::class)
                ->latest()
                ->get()
                ->map(function ($activity) {
                    try {
                        $properties = $activity->properties ?? collect();
                        $propertiesArray = $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties;

                        if ($activity->event === 'updated' && isset($propertiesArray['changes'])) {
                            $activity->formatted_changes = $this->formatChanges($propertiesArray['changes']);
                        } elseif ($activity->event === 'created' && isset($propertiesArray['attributes'])) {
                            $activity->formatted_data = $propertiesArray['attributes'];
                        } elseif (in_array($activity->event, ['deleted', 'force_deleted']) && isset($propertiesArray['old'])) {
                            $activity->formatted_data = $propertiesArray['old'];
                        }

                        return $activity;
                    } catch (\Exception $e) {
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
            if (!is_array($change) || !isset($change['old']) || !isset($change['new'])) {
                continue;
            }

            $type = in_array($field, ['details', 'description', 'content']) ? 'textarea' : 'text';

            $oldValue = $change['old'] ?? null;
            $newValue = $change['new'] ?? null;

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
            'title' => 'Title',
            'slug' => 'Slug',
            'date' => 'Date',
            'type' => 'Type',
            'details' => 'Details',
            'is_annual' => 'Is Annual',
            'status' => 'Status',
        ];

        return $names[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

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
            <flux:heading size="xl">ছুটি ব্যবস্থাপনা</flux:heading>
            <flux:subheading>বাংলাদেশের সরকারি ও বেসরকারি ছুটির তালিকা পরিচালনা করুন।</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">
                নতুন ছুটি যোগ করুন
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="নাম, স্লাগ বা টাইপ দিয়ে খুঁজুন..."
            icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->holidays">
        <flux:table.columns>
            <flux:table.column>Image</flux:table.column>
            <flux:table.column sortable>ছুটির নাম</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column sortable>তারিখ</flux:table.column>
            <flux:table.column>ধরণ</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->holidays as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>
                        @php
                            $mediaItems = $item->getMedia('holiday_images');
                            $mediaCount = $mediaItems->count();
                        @endphp

                        <flux:avatar.group class="size-10">
                            @if ($mediaCount > 0)
                                @foreach ($mediaItems->take(1) as $media)
                                    <flux:avatar src="{{ $media->getUrl('thumb') }}" />
                                @endforeach
                                @if ($mediaCount > 1)
                                    <flux:avatar initials="+{{ bn_num($mediaCount - 1) }}" />
                                @endif
                            @else
                                <flux:avatar initials="{{ mb_substr($item->title, 0, 2) }}" />
                            @endif
                        </flux:avatar.group>
                    </flux:table.cell>

                    <flux:table.cell class="font-medium">
                        <div>{{ $item->title }}</div>
                        @if ($item->is_annual)
                            <div class="text-xs text-indigo-500">বার্ষিক ছুটি</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <code class="text-xs text-zinc-500">{{ $item->slug }}</code>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-sm">{{ bn_date($item->date->format('d F, Y')) }}</div>
                        <div class="text-xs text-zinc-500">{{ bn_day($item->date->format('l')) }}</div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" variant="outline">
                            {{ $holidayTypes[$item->type] ?? $item->type }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($item->status)
                            <flux:badge size="sm" color="green">Published</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">Draft</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $item->id }})" title="View Activity Logs" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="আপনি কি নিশ্চিত?" wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="স্থায়ীভাবে ডিলিট করতে চান?"
                                wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-10 text-zinc-400">
                        কোনো ছুটির তথ্য পাওয়া যায়নি।
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="holiday-form" class="md:w-[45rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $holidayId ? 'ছুটি সংশোধন করুন' : 'নতুন ছুটি যোগ করুন' }}
                </flux:heading>
                <flux:subheading>সঠিক তথ্য দিয়ে ফর্মটি পূরণ করুন।</flux:subheading>
            </div>

            <flux:input wire:model.live.debounce.300ms="title" label="ছুটির নাম (বাংলায়)"
                placeholder="উদা: বিজয় দিবস" />

            {{-- Slug Field with Custom Control --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <flux:label>Slug (URL)</flux:label>
                    @if ($slugIsCustom)
                        <flux:button type="button" size="xs" variant="ghost" wire:click="resetSlugToAuto"
                            icon="arrow-path">
                            Auto-generate করুন
                        </flux:button>
                    @else
                        <span class="text-xs text-zinc-500">Auto-generated</span>
                    @endif
                </div>

                <flux:input wire:model.live.debounce.400ms="slug" placeholder="auto-generated-slug"
                    class="font-mono text-sm" />

                <p class="text-xs text-zinc-500">
                    @if ($slugIsCustom)
                        আপনি স্লাগ কাস্টমাইজ করেছেন। টাইটেল পরিবর্তন করলেও স্লাগ পরিবর্তন হবে না।
                    @else
                        টাইটেল থেকে অটো জেনারেট হচ্ছে। চাইলে নিজে এডিট করতে পারেন।
                    @endif
                </p>
                @error('slug')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <flux:input wire:model.live="date" type="date" label="তারিখ" />
                    @if ($date)
                        <p class="text-xs text-indigo-500 font-medium">
                            নির্বাচিত দিন: {{ bn_day(\Carbon\Carbon::parse($date)->format('l')) }}
                        </p>
                    @endif
                </div>

                <flux:select wire:model="type" label="ছুটির ধরণ">
                    <option value="">ধরণ নির্বাচন করুন</option>
                    @foreach ($holidayTypes as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="status" label="Status">
                    <option value="1">Published</option>
                    <option value="0">Draft</option>
                </flux:select>

                <div class="pt-6">
                    <flux:checkbox wire:model="is_annual" label="এটি প্রতি বছর একই তারিখে হয়" />
                </div>
            </div>

            <flux:editor wire:model="details" label="বিস্তারিত বিবরণ (ঐচ্ছিক)" rows="4"
                placeholder="ছুটি সম্পর্কে অতিরিক্ত তথ্য..." />

            <div>
                <flux:file-upload wire:model="images" multiple />
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">বাতিল</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    সংরক্ষণ করুন
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
                @forelse ($activities as $log)
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
                        class="relative pl-4 border-l-2
                                    {{ $event === 'created'
                                        ? 'border-green-500'
                                        : ($event === 'updated'
                                            ? 'border-blue-500'
                                            : ($event === 'restored'
                                                ? 'border-yellow-500'
                                                : 'border-red-500')) }}">
                        <div class="flex flex-col gap-4">
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
                                <span class="text-xs text-zinc-500">{{ $createdAt }}</span>
                            </div>

                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-zinc-600">By:</span>
                                <flux:profile :chevron="false" name="{{ $causerName }}"
                                    avatar="{{ $causerAvatar }}" />
                            </div>

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
                                            <div class="text-sm font-medium text-zinc-700 mb-2">{{ $field }}
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
                            @elseif (in_array($event, ['created', 'deleted', 'force_deleted']) && !empty($formattedData))
                                <div class="mt-2 bg-zinc-50 rounded-lg p-3">
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach ($formattedData as $key => $value)
                                            @php
                                                $displayValue = is_array($value)
                                                    ? implode(', ', $value)
                                                    : (string) $value;
                                            @endphp
                                            <div class="flex">
                                                <span class="text-xs font-medium text-zinc-500 w-24">
                                                    {{ ucfirst($key) }}:
                                                </span>
                                                <span class="text-sm break-words">
                                                    {{ $displayValue ?: '(empty)' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

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
