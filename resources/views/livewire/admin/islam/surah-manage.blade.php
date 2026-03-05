<?php

use Livewire\Volt\Component;
use App\Models\Sura;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate};

new class extends Component {
    use WithFileUploads, WithPagination;

    public $suraId;

    #[Validate('required|integer|unique:suras,sura_no,{suraId}')]
    public $sura_no;

    #[Validate('required|string')]
    public $name_arabic = '';

    #[Validate('required|string')]
    public $name_english = '';

    #[Validate('required|string')]
    public $name_bangla = '';

    #[Validate('nullable|string')]
    public $revelation_place = '';

    #[Validate('nullable|integer')]
    public $total_ayat;

    #[Validate('nullable|file|mimes:mp3,wav|max:20480')] // 20MB Max
    public $audio;

    public $existingAudio;
    public $is_active = true;
    public $search = '';
    public $viewType = 'active';

    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedViewType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function suras()
    {
        return ($this->viewType === 'trashed' ? Sura::onlyTrashed() : Sura::query())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name_bangla', 'like', '%' . $this->search . '%')
                        ->orWhere('name_english', 'like', '%' . $this->search . '%')
                        ->orWhere('sura_no', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('sura_no', 'asc')
            ->paginate(10);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['suraId', 'sura_no', 'name_arabic', 'name_english', 'name_bangla', 'revelation_place', 'total_ayat', 'audio', 'existingAudio', 'is_active']);
        $this->dispatch('modal-show', name: 'sura-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $sura = Sura::withTrashed()->findOrFail($id);

        $this->suraId = $sura->id;
        $this->sura_no = $sura->sura_no;
        $this->name_arabic = $sura->name_arabic;
        $this->name_english = $sura->name_english;
        $this->name_bangla = $sura->name_bangla;
        $this->revelation_place = $sura->revelation_place;
        $this->total_ayat = $sura->total_ayat;
        $this->is_active = (bool) $sura->is_active;
        $this->existingAudio = $sura->audio;

        $this->dispatch('modal-show', name: 'sura-form');
    }

    public function save()
    {
        $this->validate([
            'sura_no' => 'required|integer|unique:suras,sura_no,' . $this->suraId,
        ]);

        $data = [
            'sura_no' => $this->sura_no,
            'name_arabic' => $this->name_arabic,
            'name_english' => $this->name_english,
            'name_bangla' => $this->name_bangla,
            'revelation_place' => $this->revelation_place,
            'total_ayat' => $this->total_ayat,
            'is_active' => $this->is_active,
            'slug' => Str::slug($this->name_english),
        ];

        if ($this->audio) {
            if ($this->existingAudio) {
                Storage::disk('public')->delete($this->existingAudio);
            }
            $data['audio'] = $this->audio->store('suras/audio', 'public');
        }

        Sura::updateOrCreate(['id' => $this->suraId], $data);

        $this->dispatch('modal-close', name: 'sura-form');
        $this->dispatch('toast', variant: 'success', text: 'সূরার তথ্য সফলভাবে সংরক্ষিত হয়েছে।');
    }

    public function delete($id)
    {
        Sura::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'সূরাটি ট্র্যাশে সরানো হয়েছে।');
    }

    public function restore($id)
    {
        Sura::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'সূরাটি রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $sura = Sura::onlyTrashed()->findOrFail($id);
        if ($sura->audio) {
            Storage::disk('public')->delete($sura->audio);
        }
        $sura->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'সূরাটি স্থায়ীভাবে ডিলিট করা হয়েছে।');
    }
}; ?>

<div>
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <flux:heading size="xl">Sura Management</flux:heading>
            <flux:subheading>Manage all 114 Suras of the Holy Quran.</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trashed" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Add Sura</flux:button>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="সূরা নম্বর বা নাম দিয়ে খুঁজুন..."
            icon="magnifying-glass" />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->suras">
        <flux:table.columns>
            <flux:table.column>No</flux:table.column>
            <flux:table.column>Sura Name</flux:table.column>
            <flux:table.column>Details</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->suras as $sura)
                <flux:table.row :key="$sura->id">
                    <flux:table.cell class="font-bold text-zinc-800 dark:text-zinc-200">
                        {{ str_pad($sura->sura_no, 3, '0', STR_PAD_LEFT) }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $sura->name_bangla }}</span>
                            <span class="text-xs text-zinc-500 font-arabic italic">{{ $sura->name_arabic }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $sura->revelation_place }}</span>
                            <span class="mx-1 text-zinc-300">•</span>
                            <span class="text-zinc-500">{{ $sura->total_ayat }} Ayats</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$sura->is_active ? 'green' : 'red'" inset="top bottom">
                            {{ $sura->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            @if($viewType === 'active')
                                <flux:button variant="ghost" size="sm" icon="pencil-square"
                                    wire:click="showEditForm({{ $sura->id }})" />
                                <flux:button variant="ghost" size="sm" icon="trash" color="red" wire:confirm="আর ইউ সিওর?"
                                    wire:click="delete({{ $sura->id }})" />
                            @else
                                <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                    wire:click="restore({{ $sura->id }})" />
                                <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                    wire:confirm="স্থায়ীভাবে ডিলিট করতে চান?" wire:click="forceDelete({{ $sura->id }})" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-12 text-zinc-400">কোনো সূরা পাওয়া যায়নি।
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="sura-form" class="md:w-[45rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $suraId ? 'Edit Sura' : 'Create New Sura' }}</flux:heading>
                <flux:subheading>সূরার তথ্য এবং অডিও ফাইল আপডেট করুন।</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input type="number" wire:model="sura_no" label="সূরা নম্বর" placeholder="Ex: 1" required />
                <flux:input wire:model="name_arabic" label="নাম (আরবি)" placeholder="الفاتحة" required />
                <flux:input wire:model="name_bangla" label="নাম (বাংলা)" placeholder="আল-ফাতিহা" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input wire:model="name_english" label="নাম (ইংরেজি)" placeholder="Al-Fatiha" required />
                <flux:input wire:model="revelation_place" label="অবতীর্ণ হওয়ার স্থান" placeholder="মক্কা/মদীনা" />
                <flux:input type="number" wire:model="total_ayat" label="মোট আয়াত" placeholder="Ex: 7" />
            </div>

            <div class="space-y-3">
                <flux:input type="file" wire:model="audio" label="অডিও ফাইল (MP3/WAV)" />

                @if ($existingAudio && !$audio)
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                        <p class="text-[10px] uppercase tracking-wider font-bold text-zinc-400 mb-2">Current Audio Preview
                        </p>
                        <audio controls class="h-8 w-full scale-95">
                            <source src="{{ Storage::url($existingAudio) }}" type="audio/mpeg">
                        </audio>
                    </div>
                @endif
            </div>

            <flux:checkbox wire:model="is_active" label="এই সূরাটি অ্যাপে লাইভ থাকবে (Active Status)" />

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">বাতিল</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">তথ্য সংরক্ষণ করুন</flux:button>
            </div>
        </form>
    </flux:modal>
</div>