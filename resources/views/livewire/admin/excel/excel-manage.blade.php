<?php

use Livewire\Volt\Component;
use App\Models\ExcelTutorial;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate};
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithFileUploads, WithPagination;

    public ?int $tutorialId = null;

    // Form fields
    #[Validate('required|min:3|max:255')]
    public string $title = '';

    #[Validate('required|min:3|max:255|unique:excel_tutorials,slug,{tutorialId}')]
    public string $slug = '';

    public string $chapter_name = '';
    public string $new_chapter = '';

    #[Validate('required|min:10')]
    public string $description = '';

    public string $excel_formula = '';

    #[Validate('required|integer|min:0')]
    public int $position = 0;

    public $image = null; // TemporaryUploadedFile | null
    public ?string $imagePreview = null;

    public bool $is_published = true;

    // UI states
    public string $viewType = 'active';
    public string $search = '';
    public string $chapterInputType = 'select';

    public function updatedViewType(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Auto-generate slug from title only when creating
     * and the slug field is still empty.
     */
    public function updatedTitle(string $value): void
    {
        if (!$this->tutorialId && blank($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    #[Computed]
    public function tutorials()
    {
        return ($this->viewType === 'trashed' ? ExcelTutorial::onlyTrashed() : ExcelTutorial::query())
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query
                        ->where('title', 'like', "%{$this->search}%")
                        ->orWhere('chapter_name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('chapter_name')
            ->orderBy('position')
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'availableChapters' => ExcelTutorial::query()->whereNotNull('chapter_name')->distinct()->orderBy('chapter_name')->pluck('chapter_name')->toArray(),
        ];
    }

    public function showCreateForm(): void
    {
        $this->resetValidation();
        $this->reset(['tutorialId', 'title', 'slug', 'chapter_name', 'new_chapter', 'description', 'excel_formula', 'position', 'image', 'imagePreview', 'chapterInputType']);
        $this->is_published = true;
        $this->position = 0;

        $this->dispatch('modal-show', name: 'tutorial-form');
    }

    public function showEditForm(int $id): void
    {
        $this->resetValidation();

        $tutorial = ExcelTutorial::withTrashed()->findOrFail($id);

        $this->tutorialId = $tutorial->id;
        $this->title = $tutorial->title;
        $this->slug = $tutorial->slug;
        $this->chapter_name = $tutorial->chapter_name ?? '';
        $this->description = $tutorial->description;
        $this->excel_formula = $tutorial->excel_formula ?? '';
        $this->position = (int) $tutorial->position;
        $this->is_published = (bool) $tutorial->is_published;
        $this->chapterInputType = 'select';

        $this->imagePreview = $tutorial->hasMedia('lesson_image') ? $tutorial->getFirstMediaUrl('lesson_image') : null;

        $this->image = null;

        $this->dispatch('modal-show', name: 'tutorial-form');
    }

    public function removeImage(): void
    {
        // If editing and there is an existing media, delete it
        if ($this->tutorialId && $this->imagePreview) {
            $tutorial = ExcelTutorial::withTrashed()->find($this->tutorialId);
            $tutorial?->clearMediaCollection('lesson_image');
        }

        $this->image = null;
        $this->imagePreview = null;
    }

    public function save(): void
    {
        // Unique slug rule (ignore current record on edit)
        $this->validate([
            'slug' => ['required', 'min:3', 'max:255', 'unique:excel_tutorials,slug,' . ($this->tutorialId ?? 'NULL')],
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
            'position' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120', // 5MB
        ]);

        // Determine chapter
        $chapter = $this->chapterInputType === 'create' && filled($this->new_chapter) ? trim($this->new_chapter) : $this->chapter_name;

        $data = [
            'title' => $this->title,
            'slug' => $this->slug, // fully custom
            'chapter_name' => $chapter ?: null,
            'description' => $this->description,
            'excel_formula' => $this->excel_formula ?: null,
            'position' => $this->position,
            'is_published' => $this->is_published,
        ];

        if ($this->tutorialId) {
            $tutorial = ExcelTutorial::withTrashed()->findOrFail($this->tutorialId);
            $tutorial->update($data);
        } else {
            $tutorial = ExcelTutorial::create($data);
        }

        // ===== Process Single Image (same method as Sign) =====
        if ($this->image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $fileName = $this->slug . '-totthobox-excel-tutorial-' . Str::lower(Str::random(6)) . '.webp';
            $tempPath = sys_get_temp_dir() . '/' . $fileName;

            // Convert to clean WebP (strip metadata)
            Image::read($this->image->getRealPath())
                ->toWebp(85)
                ->save($tempPath);

            // Clear previous image
            $tutorial->clearMediaCollection('lesson_image');

            $tutorial
                ->addMedia($tempPath)
                ->usingFileName($fileName)
                ->usingName("{$tutorial->title} - Totthobox Excel Tutorial")
                ->toMediaCollection('lesson_image');

            @unlink($tempPath);
        }

        $this->dispatch('modal-close', name: 'tutorial-form');
        $this->dispatch('toast', variant: 'success', heading: 'Success', text: 'Tutorial saved successfully.');

        $this->reset(['image', 'imagePreview', 'tutorialId', 'new_chapter']);
    }

    public function delete(int $id): void
    {
        ExcelTutorial::findOrFail($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Lesson moved to trash.');
    }

    public function restore(int $id): void
    {
        ExcelTutorial::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Lesson restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $tutorial = ExcelTutorial::onlyTrashed()->findOrFail($id);
        $tutorial->clearMediaCollection('lesson_image');
        $tutorial->forceDelete();

        $this->dispatch('toast', variant: 'error', text: 'Lesson permanently deleted.');
    }
};
?>

<div class="p-1">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Excel Expert Panel</flux:heading>
            <flux:subheading>Manage Excel lessons, formulas and chapters.</flux:subheading>
        </div>

        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>

            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">
                Add Lesson
            </flux:button>
        </div>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title, chapter or slug..."
            icon="magnifying-glass" />
    </div>

    <flux:table :paginate="$this->tutorials">
        <flux:table.columns>
            <flux:table.column>Pos</flux:table.column>
            <flux:table.column>Title</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Chapter</flux:table.column>
            <flux:table.column>Formula</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->tutorials as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell class="text-zinc-500 font-mono text-sm">
                        #{{ $item->position }}
                    </flux:table.cell>

                    <flux:table.cell class="font-medium">
                        {{ $item->title }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <code class="text-xs text-zinc-500">{{ $item->slug }}</code>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($item->chapter_name)
                            <flux:badge size="sm" color="green" inset="top bottom">
                                {{ $item->chapter_name }}
                            </flux:badge>
                        @else
                            <span class="text-zinc-400 text-sm">—</span>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="font-mono text-xs text-blue-600 dark:text-blue-400">
                        {{ Str::limit($item->excel_formula, 25) ?: '—' }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button size="sm" variant="ghost" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" color="red"
                                wire:confirm="Move this lesson to trash?" wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button size="sm" variant="ghost" icon="arrow-path" color="green"
                                wire:click="restore({{ $item->id }})" />
                            <flux:button size="sm" variant="ghost" icon="x-mark" color="red"
                                wire:confirm="Permanently delete this lesson?"
                                wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-10 text-zinc-400">
                        No lessons found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- ==================== CREATE / EDIT MODAL ==================== --}}
    <flux:modal name="tutorial-form" class="md:w-[42rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $tutorialId ? 'Edit Excel Lesson' : 'Create New Excel Lesson' }}
                </flux:heading>
                <flux:subheading>
                    Fill in the details below. Slug is fully customizable.
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="title" label="Lesson Title" placeholder="e.g. How to use VLOOKUP" />

                <flux:input wire:model="slug" label="Slug" placeholder="custom-slug-here"
                    description="Fully custom. Auto-generated from title only on create if left empty." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <div class="flex justify-between items-center mb-2.5">
                        <flux:label>Chapter / Category</flux:label>
                        <flux:button type="button" variant="ghost" size="xs"
                            wire:click="$set('chapterInputType', '{{ $chapterInputType === 'select' ? 'create' : 'select' }}')">
                            {{ $chapterInputType === 'select' ? 'New Chapter' : 'Select Existing' }}
                        </flux:button>
                    </div>

                    @if ($chapterInputType === 'select')
                        <flux:select wire:model="chapter_name">
                            <option value="">Choose chapter...</option>
                            @foreach ($availableChapters as $chap)
                                <option value="{{ $chap }}">{{ $chap }}</option>
                            @endforeach
                        </flux:select>
                    @else
                        <flux:input wire:model="new_chapter" placeholder="e.g. Advanced Formulas" />
                    @endif
                </flux:field>

                <flux:input type="number" wire:model="position" label="Position (Order)" min="0" />
            </div>

            <flux:input wire:model="excel_formula" label="Excel Formula (Highlight Box)" icon="variable"
                placeholder="=SUM(A1:A10)" />

            <div wire:ignore>
                <flux:label class="mb-2 block">Lesson Content</flux:label>
                <flux:editor wire:model="description" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <flux:checkbox wire:model="is_published" label="Publish this lesson" />

                <flux:field>
                    <flux:label>Lesson Image</flux:label>
                    <flux:input type="file" wire:model="image" accept="image/*" size="sm" />
                </flux:field>
            </div>

            {{-- Image Preview + Remove --}}
            @if ($image || $imagePreview)
                <div class="relative inline-block">
                    <img src="{{ $image ? $image->temporaryUrl() : $imagePreview }}"
                        class="h-36 w-auto object-cover rounded-lg border border-zinc-400/25 shadow-sm" alt="Preview">
                    <button type="button" wire:click="removeImage"
                        class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm shadow"
                        title="Remove image">
                        ×
                    </button>
                </div>
            @endif

            <div class="flex justify-end gap-4 pt-4 border-t border-zinc-400/25">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Lesson
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
