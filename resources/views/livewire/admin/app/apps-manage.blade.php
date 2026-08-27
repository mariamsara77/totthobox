<?php

use Livewire\Volt\Component;
use App\Models\AppResource;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    // Properties
    public $appResourceId;
    public $viewType = 'active';
    public $search = '';

    // Form Fields
    #[Validate('required|min:3|max:255')]
    public $name = '';

    #[Validate('required|min:3|max:255')]
    public $slug = '';

    #[Validate('nullable|string|max:50')]
    public $version = '';

    #[Validate('required|string')]
    public $platform = 'Android';

    #[Validate('nullable|string')]
    public $description = '';

    #[Validate('required|in:local,external')]
    public $download_type = 'external';

    #[Validate('nullable|url')]
    public $external_url = '';

    #[Validate('nullable|string|max:50')]
    public $download_password = '';

    #[Validate('nullable|string|max:10')]
    public $masked_extension = '';

    // Media Properties
    public $icons = [];          // App Icon (single / multiple support)
    public $file;                // Local downloadable file (single)

    /**
     * Lifecycle & Logic Methods
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function updatedName($value)
    {
        // শুধুমাত্র create mode-এ auto-generate
        if (!$this->appResourceId) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug($value)
    {
        $this->slug = Str::slug($value);
    }

    /**
     * Computed Data
     */
    #[Computed]
    public function rows()
    {
        return ($this->viewType === 'trashed' ? AppResource::onlyTrashed() : AppResource::query())
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%")
                ->orWhere('platform', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

  public function with(): array
{
    $query = $this->viewType === 'trashed'
        ? AppResource::onlyTrashed()
        : AppResource::query();

    $rows = $query
        ->when($this->search, function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('slug', 'like', "%{$this->search}%")
              ->orWhere('platform', 'like', "%{$this->search}%");
        })
        ->latest()
        ->paginate(10);

    return [
        'rows' => $rows,
    ];
}

    /**
     * Action Methods
     */
    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset([
            'appResourceId',
            'name',
            'slug',
            'version',
            'platform',
            'description',
            'download_type',
            'external_url',
            'download_password',
            'masked_extension',
            'icons',
            'file',
        ]);

        $this->platform = 'Android';
        $this->download_type = 'external';

        $this->dispatch('modal-show', name: 'app-resource-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $item = AppResource::withTrashed()->findOrFail($id);

        $this->appResourceId     = $item->id;
        $this->name              = $item->name;
        $this->slug              = $item->slug;
        $this->version           = $item->version;
        $this->platform          = $item->platform;
        $this->description       = $item->description;
        $this->download_type     = $item->download_type;
        $this->external_url      = $item->external_url;
        $this->download_password = $item->download_password;
        $this->masked_extension  = $item->masked_extension;

        // Map Spatie Media for Icon
        $this->icons = $item
            ->getMedia('app_icons')
            ->map(
                fn($m) => [
                    'id'          => $m->id,
                    'url'         => $m->getUrl('thumb') ?? $m->getUrl(),
                    'is_existing' => true,
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'app-resource-form');
    }

    public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file) {
            return;
        }

        // Existing image হলে Media Library থেকে মুছে ফেলো
        if (is_array($file) && isset($file['is_existing']) && $file['is_existing']) {
            $item = AppResource::withTrashed()->findOrFail($this->appResourceId);
            $item->deleteMedia($file['id']);
        }

        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function save()
    {
        $uniqueRule = $this->appResourceId
            ? Rule::unique('app_resources', 'slug')->ignore($this->appResourceId)
            : Rule::unique('app_resources', 'slug');

        $this->validate([
            'name'              => 'required|min:3|max:255',
            'slug'              => ['required', 'min:3', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $uniqueRule],
            'version'           => 'nullable|string|max:50',
            'platform'          => 'required|string',
            'description'       => 'nullable|string',
            'download_type'     => 'required|in:local,external',
            'external_url'      => 'nullable|url',
            'download_password' => 'nullable|string|max:50',
            'masked_extension'  => 'nullable|string|max:10',
        ]);

        // যদি slug খালি থাকে
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        $item = AppResource::updateOrCreate(
            ['id' => $this->appResourceId],
            [
                'name'              => $this->name,
                'slug'              => $this->slug,
                'version'           => $this->version,
                'platform'          => $this->platform,
                'description'       => $this->description,
                'download_type'     => $this->download_type,
                'external_url'      => $this->download_type === 'external' ? $this->external_url : null,
                'download_password' => $this->download_password,
                'masked_extension'  => $this->masked_extension,
            ],
        );

        // ============================================
        // Local File Upload (original file রাখবে)
        // ============================================
        if ($this->download_type === 'local' && $this->file) {
            $item->clearMediaCollection('app_files');
            $item->addMedia($this->file->getRealPath())
                ->usingFileName($this->file->getClientOriginalName())
                ->toMediaCollection('app_files');
        }

        // ============================================
        // App Icon Upload (WebP + metadata strip)
        // ============================================
        if (!empty($this->icons)) {
            $files = is_array($this->icons) ? $this->icons : [$this->icons];

            foreach ($files as $file) {
                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $slug = Str::slug($item->name);
                    $fileName = "{$slug}-totthobox-app-icon-" . Str::lower(Str::random(6)) . '.webp';

                    // Metadata remove + WebP convert
                    $processedImage = Image::read($file->getRealPath());
                    $processedImage->toWebp(85)->save($file->getRealPath());

                    // পুরনো icon মুছে নতুনটা সেভ
                    $item->clearMediaCollection('app_icons');

                    $item->addMedia($file->getRealPath())
                        ->usingFileName($fileName)
                        ->usingName("{$item->name} - Totthobox App Icon")
                        ->toMediaCollection('app_icons');

                    // Single icon রাখার জন্য break (multiple চাইলে remove করুন)
                    break;
                }
            }
        }

        $this->dispatch('modal-close', name: 'app-resource-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'অ্যাপটি সফলভাবে সংরক্ষিত হয়েছে।');
        $this->reset(['icons', 'file', 'appResourceId']);
    }

    public function delete($id)
    {
        AppResource::findOrFail($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে সরানো হয়েছে।');
    }

    public function restore($id)
    {
        AppResource::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'সফলভাবে রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $item = AppResource::onlyTrashed()->findOrFail($id);
        $item->clearMediaCollection('app_icons');
        $item->clearMediaCollection('app_files');
        $item->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'স্থায়ীভাবে মুছে ফেলা হয়েছে।');
    }
}; ?>

<div class="">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Safe Apps & Resources</flux:heading>
            <flux:subheading>Manage secure apps, APKs, fonts and resources.</flux:subheading>
        </div>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">
                Add New App
            </flux:button>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Search by name, slug or platform..."
            icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->rows">
        <flux:table.columns>
            <flux:table.column>Icon</flux:table.column>
            <flux:table.column sortable>Name</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Platform</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Downloads</flux:table.column>
            <flux:table.column>Security</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->rows as $row)
            <flux:table.row :key="$row->id">
                <flux:table.cell>
                    <flux:avatar src="{{ $row->getFirstMediaUrl('app_icons', 'thumb') }}" name="{{ $row->name }}"
                        alt="{{ $row->name }}" />
                </flux:table.cell>

                <flux:table.cell class="font-medium">
                    {{ $row->name }}
                    @if ($row->version)
                    <span class="text-xs text-zinc-400">v{{ $row->version }}</span>
                    @endif
                </flux:table.cell>

                <flux:table.cell>
                    <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $row->slug }}</code>
                </flux:table.cell>

                <flux:table.cell>{{ $row->platform }}</flux:table.cell>

                <flux:table.cell>
                    <flux:badge size="sm" :color="$row->download_type === 'local' ? 'green' : 'blue'">
                        {{ ucfirst($row->download_type) }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell>{{ $row->download_count ?? 0 }}</flux:table.cell>

                <flux:table.cell>
                    @if ($row->download_password)
                    <flux:badge size="sm" color="yellow" icon="lock-closed">Password</flux:badge>
                    @endif
                    @if ($row->masked_extension)
                    <flux:badge size="sm" color="purple">Masked ({{ $row->masked_extension }})</flux:badge>
                    @endif
                </flux:table.cell>

                <flux:table.cell align="end">
                    @if ($viewType === 'active')
                    <flux:button variant="ghost" size="sm" icon="pencil-square"
                        wire:click="showEditForm({{ $row->id }})" />
                    <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="Are you sure?"
                        wire:click="delete({{ $row->id }})" />
                    @else
                    @can('restore data')
                    <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                        wire:click="restore({{ $row->id }})" />
                    @endcan
                    @can('permanent delete')
                    <flux:button variant="ghost" size="sm" icon="x-mark" color="red" wire:confirm="Delete permanently?"
                        wire:click="forceDelete({{ $row->id }})" />
                    @endcan
                    @endif
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="8" class="text-center py-10 text-zinc-400">
                    No records found.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="app-resource-form" class="md:w-[50rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $appResourceId ? 'Edit App Resource' : 'Add New App Resource' }}
                </flux:heading>
                <flux:subheading>Provide app details, icon and download information.</flux:subheading>
            </div>

            {{-- Icon Upload --}}
            <div>
                <flux:file-upload wire:model.live="icons" label="App Icon" accept="image/*" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="name" label="App Name" placeholder="e.g., Avro Keyboard" />
                <flux:input wire:model="version" label="Version" placeholder="e.g., 5.6.0" />
            </div>

            <flux:input wire:model.live="slug" label="Slug (URL friendly)" placeholder="auto-generated-from-name"
                description="শুধুমাত্র a-z, 0-9 এবং hyphen। Unique হতে হবে।" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="platform" label="Platform">
                    <flux:select.option value="Android">Android (APK)</flux:select.option>
                    <flux:select.option value="Windows">Windows (EXE/ZIP)</flux:select.option>
                    <flux:select.option value="Fonts">Fonts (TTF/ZIP)</flux:select.option>
                    <flux:select.option value="Linux">Linux</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="download_type" label="Storage Hosting Type">
                    <flux:select.option value="local">Secure Local Storage</flux:select.option>
                    <flux:select.option value="external">External Cloud Link (Mega, MediaFire)</flux:select.option>
                </flux:select>
            </div>

            @if ($download_type === 'external')
            <div wire:key="wrapper-external">
                <flux:input wire:model="external_url" label="External Cloud URL"
                    placeholder="https://mediafire.com/..." />
            </div>
            @endif

            @if ($download_type === 'local')
            <div wire:key="wrapper-local">
                <flux:input type="file" wire:model="file" label="Upload File (APK / ZIP / EXE)" />
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="download_password" label="Zip Password (If Any)" placeholder="e.g., 1234"
                    icon="lock-closed" />
                <flux:input wire:model="masked_extension" label="Mask Extension (Optional)" placeholder="e.g., .dat" />
            </div>

            <div wire:ignore>
                <flux:editor wire:model="description" label="Instructions for Users" />
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>
</div>