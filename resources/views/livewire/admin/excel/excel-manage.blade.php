<?php

use Livewire\Volt\Component;
use App\Models\ExcelTutorial;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithFileUploads, WithPagination;

    // Form fields
    public $tutorialId;
    public $title = '';
    public $chapter_name = '';
    public $new_chapter = '';
    public $description = '';
    public $excel_formula = '';
    public $position = 0;
    public $image;
    public $imagePreview;
    public $is_published = true;

    // UI states
    public $viewType = 'active';
    public $search = '';
    public $chapterInputType = 'select';

    public function updatedViewType()
    {
        $this->resetPage();
    }
    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function tutorials()
    {
        $query = $this->viewType === 'trashed' ? ExcelTutorial::onlyTrashed() : ExcelTutorial::query();

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('chapter_name', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('chapter_name', 'asc')
            ->orderBy('position', 'asc')
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'availableChapters' => ExcelTutorial::distinct()
                ->whereNotNull('chapter_name')
                ->pluck('chapter_name')
                ->toArray(),
        ];
    }

    public function showCreateForm()
    {
        $this->reset(['tutorialId', 'title', 'chapter_name', 'new_chapter', 'description', 'excel_formula', 'position', 'image', 'imagePreview']);
        $this->is_published = true;
        $this->dispatch('modal-show', name: 'tutorial-form');
    }

    public function showEditForm($id)
    {
        $this->resetErrorBag();
        $tutorial = ExcelTutorial::withTrashed()->findOrFail($id);

        $this->tutorialId = $tutorial->id;
        $this->title = $tutorial->title;
        $this->chapter_name = $tutorial->chapter_name;
        $this->description = $tutorial->description;
        $this->excel_formula = $tutorial->excel_formula;
        $this->position = $tutorial->position;
        $this->is_published = (bool) $tutorial->is_published;

        $this->imagePreview = $tutorial->hasMedia('lesson_image') ? $tutorial->getFirstMediaUrl('lesson_image') : null;
        $this->image = null;

        $this->dispatch('modal-show', name: 'tutorial-form');
    }

    public function save()
    {
        $this->validate([
            'title' => 'required',
            'description' => 'required',
            'position' => 'required|numeric',
        ]);

        $chapter = $this->chapterInputType === 'create' ? $this->new_chapter : $this->chapter_name;

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'chapter_name' => $chapter,
            'description' => $this->description,
            'excel_formula' => $this->excel_formula,
            'position' => $this->position,
            'is_published' => $this->is_published,
        ];

        if ($this->tutorialId) {
            $tutorial = ExcelTutorial::withTrashed()->find($this->tutorialId);
            $tutorial->update($data);
        } else {
            $tutorial = ExcelTutorial::create($data);
        }

        if ($this->image) {
            $tutorial->addMedia($this->image->getRealPath())->toMediaCollection('lesson_image');
        }

        $this->dispatch('modal-close', name: 'tutorial-form');
        $this->dispatch('toast', message: 'Tutorial saved successfully!');
    }

    public function delete($id)
    {
        ExcelTutorial::find($id)->delete();
    }

    public function restore($id)
    {
        ExcelTutorial::onlyTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete($id)
    {
        $tutorial = ExcelTutorial::onlyTrashed()->findOrFail($id);
        $tutorial->forceDelete(); // Spatie will automatically delete files
    }
}; ?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Excel Expert Panel</flux:heading>
        <div class="flex items-center gap-4">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trashed" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary">Add Lesson</flux:button>
        </div>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title or chapter..."
            icon="magnifying-glass" />
    </div>

    <flux:table :paginate="$this->tutorials">
        <flux:table.columns>
            <flux:table.column>Pos</flux:table.column>
            <flux:table.column>Title</flux:table.column>
            <flux:table.column>Chapter</flux:table.column>
            <flux:table.column>Formula</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->tutorials as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell class="text-zinc-500">#{{ $item->position }}</flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $item->title }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="green">{{ $item->chapter_name }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="font-mono text-xs text-blue-600">{{ Str::limit($item->excel_formula, 20) }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button.group>
                            @if($viewType === 'active')
                                <flux:button size="sm" variant="ghost" icon="pencil-square"
                                    wire:click="showEditForm({{ $item->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" color="red"
                                    wire:click="delete({{ $item->id }})" />
                            @else
                                <flux:button size="sm" variant="ghost" icon="arrow-path" color="green"
                                    wire:click="restore({{ $item->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" color="red"
                                    wire:click="forceDelete({{ $item->id }})" />
                            @endif
                        </flux:button.group>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="tutorial-form" class="md:w-[40rem]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $tutorialId ? 'Edit' : 'Create' }} Excel Lesson</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Lesson Title</flux:label>
                    <flux:input wire:model="title" placeholder="e.g. How to use VLOOKUP" />
                </flux:field>
                <flux:field>
                    <flux:label>Position (Order)</flux:label>
                    <flux:input type="number" wire:model="position" />
                </flux:field>
            </div>

            <flux:field>
                <div class="flex justify-between mb-2">
                    <flux:label>Chapter/Category</flux:label>
                    <flux:button variant="ghost" size="xs"
                        wire:click="$set('chapterInputType', '{{ $chapterInputType === 'select' ? 'create' : 'select' }}')">
                        {{ $chapterInputType === 'select' ? 'New Chapter' : 'Select Existing' }}
                    </flux:button>
                </div>
                @if($chapterInputType === 'select')
                    <flux:select wire:model="chapter_name">
                        <option value="">Choose chapter...</option>
                        @foreach($availableChapters as $chap)
                            <option value="{{ $chap }}">{{ $chap }}</option>
                        @endforeach
                    </flux:select>
                @else
                    <flux:input wire:model="new_chapter" placeholder="e.g. Advanced Formulas" />
                @endif
            </flux:field>

            <flux:field>
                <flux:label>Excel Formula (Highlight Box)</flux:label>
                <flux:input wire:model="excel_formula" icon="variable" placeholder="=SUM(A1:A10)" />
            </flux:field>

            <div wire:ignore>
                <flux:label class="mb-2 block">Lesson Content (Bangla)</flux:label>
                <flux:editor wire:model="description" />
            </div>

            <div class="flex items-center justify-between">
                <flux:checkbox wire:model="is_published" label="Publish this lesson" />
                <flux:field>
                    <flux:input type="file" wire:model="image" size="sm" />
                </flux:field>
            </div>

            @if ($image || $imagePreview)
                <div class="mt-2 relative inline-block">
                    <img src="{{ $image ? $image->temporaryUrl() : $imagePreview }}"
                        class="h-32 w-full object-cover rounded-md border shadow-sm">
                </div>
            @endif

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Lesson</flux:button>
            </div>
        </form>
    </flux:modal>
</div>