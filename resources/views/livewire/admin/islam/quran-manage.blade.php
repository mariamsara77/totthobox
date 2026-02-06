<?php

use Livewire\Volt\Component;
use App\Models\Quran;
use App\Models\Sura;
use App\Models\Para;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads, WithPagination;

    // Form fields
    public $quranId;
    public $ayat_no;
    public $arabic_text = '';
    public $bangla_meaning = '';
    public $english_meaning = '';
    public $para_id;
    public $sura_id;
    public $is_active = true;
    public $audio; // For Spatie Media Upload

    // UI states
    public $showForm = false;
    public $formType = 'create';
    public $search = '';

    // Pagination / Sorting
    public $perPage = 10;
    public $sortField = 'ayat_no';
    public $sortDirection = 'asc';

    public function getQuransProperty()
    {
        return Quran::query()
            ->with(['sura', 'para'])
            ->when($this->search, function ($query) {
                $query->where('arabic_text', 'like', '%' . $this->search . '%')
                    ->orWhere('bangla_meaning', 'like', '%' . $this->search . '%')
                    ->orWhere('ayat_no', 'like', '%' . $this->search . '%');
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

    public function showEditForm(Quran $quran)
    {
        $this->resetForm();
        $this->quranId = $quran->id;
        $this->ayat_no = $quran->ayat_no;
        $this->arabic_text = $quran->arabic_text;
        $this->bangla_meaning = $quran->bangla_meaning;
        $this->english_meaning = $quran->english_meaning;
        $this->para_id = $quran->para_id;
        $this->sura_id = $quran->sura_id;
        $this->is_active = $quran->is_active;

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->reset(['quranId', 'ayat_no', 'arabic_text', 'bangla_meaning', 'english_meaning', 'para_id', 'sura_id', 'is_active', 'audio']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $rules = [
            'ayat_no' => 'required|integer',
            'arabic_text' => 'required|string',
            'bangla_meaning' => 'required|string',
            'english_meaning' => 'nullable|string',
            'para_id' => 'required|exists:paras,id',
            'sura_id' => 'required|exists:suras,id',
            'is_active' => 'boolean',
            'audio' => 'nullable|mimes:mp3,wav|max:10240',
        ];

        $validated = $this->validate($rules);

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

        if ($this->formType === 'edit') {
            $quran = Quran::find($this->quranId);
            $quran->update($data);
            $message = 'Ayat updated successfully!';
        } else {
            $quran = Quran::create($data);
            $message = 'Ayat created successfully!';
        }

        // Spatie Media Library Upload
        if ($this->audio) {
            $quran->addMedia($this->audio->getRealPath())
                ->usingFileName($this->audio->getClientOriginalName())
                ->toMediaCollection('ayat_audio');
        }

        $this->showForm = false;
        $this->resetForm();
        Flux::toast($message);
    }

    public function deleteQuran(Quran $quran)
    {
        $quran->delete(); // Spatie will auto-handle media deletion if configured
        Flux::toast('Ayat deleted successfully!');
    }
}; ?>

<section>
    <div class="flex flex-col space-y-6">
        @if ($showForm)
            <div class="">
                <header class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">
                        {{ $formType === 'create' ? 'নতুন আয়াত যুক্ত করুন' : 'আয়াত এডিট করুন' }}
                    </h3>
                    <flux:button wire:click="$set('showForm', false)" variant="ghost">ফিরে যান</flux:button>
                </header>

                <form wire:submit.prevent="save" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:select wire:model="sura_id" label="সূরা নির্বাচন করুন" placeholder="সিলেক্ট সূরা">
                            @foreach(Sura::all() as $sura)
                                <option value="{{ $sura->id }}">{{ $sura->sura_no }}. {{ $sura->name_bangla }}</option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="para_id" label="পারা নির্বাচন করুন" placeholder="সিলেক্ট পারা">
                            @foreach(Para::all() as $para)
                                <option value="{{ $para->id }}">পারা {{ $para->para_number }}</option>
                            @endforeach
                        </flux:select>
                        <flux:input type="number" wire:model="ayat_no" label="আয়াত নম্বর" required />
                    </div>

                    <flux:textarea wire:model="arabic_text" label="আরবি টেক্সট (Arabic)" resize="none" rows="auto"
                        class="font-arabic text-right text-lg" required />
                    <flux:textarea wire:model="bangla_meaning" label="বাংলা অর্থ" resize="none" rows="auto" required />
                    <flux:textarea wire:model="english_meaning" label="English Meaning" resize="none" rows="auto" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <flux:input type="file" wire:model="audio" label="আয়াত অডিও (MP3)"
                            help="Spatie Media Library-র মাধ্যমে আপলোড হবে" />
                        <flux:checkbox wire:model="is_active" label="এই আয়াতটি ওয়েবসাইটে দেখাবে (Active)" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button wire:click="$set('showForm', false)">Cancel</flux:button>
                        <flux:button type="submit" variant="primary">সেভ করুন</flux:button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="text-2xl font-bold">Quran Ayats</h2>
                <div class="flex gap-2">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="খুঁজুন (আয়াত বা অর্থ)..."
                        icon="magnifying-glass" />
                    <flux:button wire:click="showCreateForm" variant="primary" icon="plus">Add Ayat</flux:button>
                </div>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column wire:click="sortBy('ayat_no')" :sortable="true"
                        :direction="$sortField === 'ayat_no' ? $sortDirection : null">No</flux:table.column>
                    <flux:table.column>Ayat (Arabic & Meaning)</flux:table.column>
                    <flux:table.column>Sura/Para</flux:table.column>
                    <flux:table.column>Audio</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->qurans as $quran)
                        <flux:table.row :key="$quran->id">
                            <flux:table.cell class="font-bold">{{ $quran->ayat_no }}</flux:table.cell>
                            <flux:table.cell class="max-w-md">
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="font-arabic text-lg text-right text-emerald-700 leading-loose">{{ Str::limit($quran->arabic_text, 100) }}</span>
                                    <span
                                        class="text-sm text-gray-600 italic">{{ Str::limit($quran->bangla_meaning, 80) }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-xs">
                                    <div class="font-medium">Sura: {{ $quran->sura?->name_bangla }}</div>
                                    <div class="text-gray-500">Para: {{ $quran->para?->para_number }}</div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($quran->hasMedia('ayat_audio'))
                                    <flux:button icon="play" size="sm" variant="ghost"
                                        onclick="new Audio('{{ $quran->getFirstMediaUrl('ayat_audio') }}').play()" />
                                @else
                                    <span class="text-gray-300 text-xs italic">N/A</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$quran->is_active ? 'green' : 'red'">
                                    {{ $quran->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-1">
                                    <flux:button wire:click="showEditForm({{ $quran->id }})" variant="ghost" size="sm"
                                        icon="pencil-square" />
                                    <flux:button wire:confirm="মুছে ফেলতে চান?" wire:click="deleteQuran({{ $quran->id }})"
                                        variant="ghost" size="sm" color="red" icon="trash" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-12 text-gray-400">আয়াত খুঁজে পাওয়া যায়নি।
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{ $this->qurans->links() }}
        @endif
    </div>
</section>