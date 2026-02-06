<?php

use Livewire\Volt\Component;
use App\Models\Sura;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads, WithPagination;

    // Form fields
    public $suraId;
    public $sura_no;
    public $name_arabic = '';
    public $name_english = '';
    public $name_bangla = '';
    public $revelation_place = '';
    public $total_ayat;
    public $audio;
    public $existingAudio;
    public $is_active = true;

    // UI states
    public $showForm = false;
    public $formType = 'create';
    public $search = '';

    // Pagination / Sorting
    public $perPage = 10;
    public $sortField = 'sura_no';
    public $sortDirection = 'asc';

    public function getSurasProperty()
    {
        return Sura::query()
            ->when($this->search, function ($query) {
                $query->where('name_bangla', 'like', '%' . $this->search . '%')
                    ->orWhere('name_english', 'like', '%' . $this->search . '%')
                    ->orWhere('sura_no', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function sortBy($field)
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc') ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->formType = 'create';
        $this->showForm = true;
    }

    public function showEditForm(Sura $sura)
    {
        $this->resetForm();
        $this->suraId = $sura->id;
        $this->sura_no = $sura->sura_no;
        $this->name_arabic = $sura->name_arabic;
        $this->name_english = $sura->name_english;
        $this->name_bangla = $sura->name_bangla;
        $this->revelation_place = $sura->revelation_place;
        $this->total_ayat = $sura->total_ayat;
        $this->is_active = $sura->is_active;
        $this->existingAudio = $sura->audio;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['suraId', 'sura_no', 'name_arabic', 'name_english', 'name_bangla', 'revelation_place', 'total_ayat', 'audio', 'existingAudio', 'is_active']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $validated = $this->validate([
            'sura_no' => 'required|integer|unique:suras,sura_no,' . $this->suraId,
            'name_arabic' => 'required|string',
            'name_english' => 'required|string',
            'name_bangla' => 'required|string',
            'revelation_place' => 'nullable|string',
            'total_ayat' => 'nullable|integer',
            'audio' => 'nullable|file|mimes:mp3,wav|max:10240',
            'is_active' => 'boolean',
        ]);

        if ($this->audio) {
            if ($this->existingAudio) {
                Storage::disk('public')->delete($this->existingAudio);
            }
            $validated['audio'] = $this->audio->store('suras/audio', 'public');
        }

        $validated['slug'] = Str::slug($this->name_english);

        if ($this->formType === 'edit') {
            Sura::find($this->suraId)->update($validated);
            $message = 'Sura updated successfully!';
        } else {
            Sura::create($validated);
            $message = 'Sura created successfully!';
        }

        $this->showForm = false;
        $this->resetForm();
        Flux::toast($message);
    }

    public function deleteSura(Sura $sura)
    {
        if ($sura->audio) {
            Storage::disk('public')->delete($sura->audio);
        }
        $sura->delete();
        Flux::toast('Sura deleted successfully!');
    }
}; ?>

<section>
    <div class="flex flex-col space-y-6">
        @if ($showForm)
            <div class="">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">
                        {{ $formType === 'create' ? 'নতুন সূরা যুক্ত করুন' : 'সূরা তথ্য এডিট করুন' }}
                    </h3>
                    <flux:button wire:click="$set('showForm', false)" variant="ghost" size="sm">Back</flux:button>
                </div>

                <form wire:submit.prevent="save" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:input type="number" wire:model="sura_no" label="সূরা নম্বর" />
                        <flux:input wire:model="name_arabic" label="নাম (আরবি)" />
                        <flux:input wire:model="name_bangla" label="নাম (বাংলা)" />
                        <flux:input wire:model="name_english" label="নাম (ইংরেজি)" />
                        <flux:input wire:model="revelation_place" label="অবতীর্ণ হওয়ার স্থান" />
                        <flux:input type="number" wire:model="total_ayat" label="মোট আয়াত সংখ্যা" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <flux:input type="file" wire:model="audio" label="অডিও ফাইল (MP3)" />
                        <div class="flex items-center pb-2">
                            <flux:checkbox wire:model="is_active" label="Active Status" />
                        </div>
                    </div>

                    @if ($existingAudio && !$audio)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">বর্তমান অডিও:</p>
                            <audio controls class="h-8 w-full">
                                <source src="{{ Storage::url($existingAudio) }}" type="audio/mpeg">
                            </audio>
                        </div>
                    @endif

                    <div class="mt-8 flex justify-end gap-3">
                        <flux:button type="button" wire:click="$set('showForm', false)">Cancel</flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ $formType === 'create' ? 'সূরা সেভ করুন' : 'আপডেট করুন' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Sura Management</h2>
                <flux:button wire:click="showCreateForm" variant="primary" icon="plus">Create New Sura</flux:button>
            </div>

            <flux:input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or sura number..."
                icon="magnifying-glass" />

            <flux:table>
                <flux:table.columns>
                    <flux:table.column wire:click="sortBy('sura_no')" :sortable="true"
                        :direction="$sortField === 'sura_no' ? $sortDirection : null">No</flux:table.column>
                    <flux:table.column>Sura Name</flux:table.column>
                    <flux:table.column>Place & Ayat</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->suras as $sura)
                        <flux:table.row :key="$sura->id">
                            <flux:table.cell class="font-bold text-gray-900">{{ $sura->sura_no }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ $sura->name_bangla }}</span>
                                    <span class="text-xs text-gray-500 font-arabic">{{ $sura->name_arabic }}
                                        ({{ $sura->name_english }})</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-sm">
                                    <span class="text-gray-600">{{ $sura->revelation_place }}</span> •
                                    <span class="text-gray-500">{{ $sura->total_ayat }} Ayats</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$sura->is_active ? 'green' : 'red'" inset="top">
                                    {{ $sura->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="showEditForm({{ $sura->id }})" variant="ghost" size="sm"
                                        icon="pencil-square" />
                                    <flux:button wire:confirm="Are you sure you want to delete this Sura?"
                                        wire:click="deleteSura({{ $sura->id }})" variant="ghost" size="sm" color="red"
                                        icon="trash" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-12 text-gray-400">
                                No suras found in the database.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $this->suras->links() }}
            </div>
        @endif
    </div>
</section>