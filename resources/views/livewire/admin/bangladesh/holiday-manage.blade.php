<?php

use Livewire\Volt\Component;
use App\Models\Holiday;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Validate, On};

new class extends Component {
    use WithFileUploads, WithPagination;

    public $holidayId;
    public $viewType = 'active';
    public $search = '';

    // Form Fields
    #[Validate('required|min:3|max:255')]
    public $title = ''; // উদা: শহীদ দিবস ও আন্তর্জাতিক মাতৃভাষা দিবস

    #[Validate('required|date')]
    public $date;

    #[Validate('required')]
    public $type = '';

    #[Validate('nullable|string')]
    public $details = '';

    #[Validate('boolean')]
    public $is_annual = true; // বার্ষিক ছুটি কিনা

    #[Validate('boolean')]
    public $status = true;

    public $images = [];

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

    #[Computed]
    public function rows()
    {
        return ($this->viewType === 'trashed' ? Holiday::onlyTrashed() : Holiday::query())
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('type', 'like', "%{$this->search}%");
            })
            ->orderBy('date', 'desc')
            ->paginate(10);
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['holidayId', 'title', 'date', 'type', 'details', 'is_annual', 'status', 'images']);
        $this->dispatch('modal-show', name: 'holiday-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $holiday = Holiday::withTrashed()->findOrFail($id);

        $this->holidayId = $holiday->id;
        $this->title = $holiday->title;
        $this->date = $holiday->date->format('Y-m-d');
        $this->type = $holiday->type;
        $this->details = $holiday->details;
        $this->is_annual = (bool) $holiday->is_annual;
        $this->status = (bool) $holiday->status;

        $this->images = $holiday->getMedia('holiday_images')->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl(),
            'is_existing' => true
        ])->toArray();

        $this->dispatch('modal-show', name: 'holiday-form');
    }

    public function save()
    {
        $this->validate();

        $holiday = Holiday::updateOrCreate(['id' => $this->holidayId], [
            'title' => $this->title,
            'date' => $this->date,
            'type' => $this->type,
            'details' => $this->details,
            'is_annual' => $this->is_annual,
            'status' => $this->status,
            'slug' => Str::slug($this->title),
            'user_id' => auth()->id(),
        ]);

        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $holiday->addMedia($image->getRealPath())
                        ->toMediaCollection('holiday_images');
                }
            }
        }

        $this->dispatch('modal-close', name: 'holiday-form');
        $this->dispatch('toast', variant: 'success', heading: 'সফল', text: 'ছুটির তথ্য ডেটাবেজে সংরক্ষিত হয়েছে।');
    }

    public function delete($id)
    {
        Holiday::findOrFail($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'আইটেমটি ট্র্যাশে সরানো হয়েছে।');
    }

    public function restore($id)
    {
        Holiday::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'সফলভাবে রিস্টোর করা হয়েছে।');
    }

    public function forceDelete($id)
    {
        $holiday = Holiday::onlyTrashed()->findOrFail($id);
        $holiday->clearMediaCollection('holiday_images');
        $holiday->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'স্থায়ীভাবে মুছে ফেলা হয়েছে।');
    }
}; ?>

<div class="p-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <flux:heading size="xl" class="flex items-center gap-2">
                <flux:icon name="calendar" variant="mini" class="text-indigo-500" />
                ছুটি ব্যবস্থাপনা
            </flux:heading>
            <flux:subheading>বাংলাদেশের সরকারি ও বেসরকারি ছুটির তালিকা পরিচালনা করুন।</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="সক্রিয়" />
                <flux:radio value="trashed" label="রিসাইকেল বিন" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" size="sm" variant="primary">নতুন ছুটি যোগ করুন
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="md:col-span-3">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="নাম বা টাইপ দিয়ে খুঁজুন..."
                icon="magnifying-glass" />
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden">
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column>মিডিয়া</flux:table.column>
                <flux:table.column sortable>ছুটির নাম</flux:table.column>
                <flux:table.column sortable>তারিখ ও বার</flux:table.column>
                <flux:table.column>ধরণ</flux:table.column>
                <flux:table.column align="end">অ্যাকশন</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->rows as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>
                            @php $media = $row->getFirstMediaUrl('holiday_images'); @endphp
                            <flux:avatar src="{{ $media ?: 'https://ui-avatars.com/api/?name=' . urlencode($row->title) }}"
                                class="rounded-lg" />
                        </flux:table.cell>

                        <flux:table.cell class="font-bold">
                            {{ $row->title }}
                            @if($row->is_annual)
                            <flux:badge size="xs" color="indigo" class="ml-1">বার্ষিক</flux:badge> @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="text-sm">{{ bn_date($row->date->format('d F, Y')) }}</div>
                            <div class="text-xs text-zinc-500">{{ bn_day($row->date->format('l')) }}</div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" variant="outline">{{ $holidayTypes[$row->type] ?? $row->type }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-1">
                                @if($viewType === 'active')
                                    <flux:button variant="ghost" size="sm" icon="pencil-square"
                                        wire:click="showEditForm({{ $row->id }})" />
                                    <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                        wire:confirm="আপনি কি নিশ্চিত?" wire:click="delete({{ $row->id }})" />
                                @else
                                    @can('restore data')
                                        <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                            wire:click="restore({{ $row->id }})" />
                                    @endcan
                                    @can('permanent delete')
                                        <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                            wire:confirm="স্থায়ীভাবে মুছে ফেলবেন?" wire:click="forceDelete({{ $row->id }})" />
                                    @endcan
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-20 text-zinc-400">কোন ছুটির দিন পাওয়া যায়নি।
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Modal Form --}}
    <flux:modal name="holiday-form" class="md:w-[50rem] space-y-6">
        <div class="flex items-center gap-3">
            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl text-indigo-600">
                <flux:icon name="calendar-days" />
            </div>
            <div>
                <flux:heading size="lg">{{ $holidayId ? 'ছুটি সংশোধন করুন' : 'নতুন ছুটি যোগ করুন' }}</flux:heading>
                <flux:subheading>সঠিক তথ্য দিয়ে নিচের ফর্মটি পূরণ করুন।</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input wire:model="title" label="ছুটির নাম (বাংলায়)" placeholder="উদা: বিজয় দিবস" copyable />

                <div class="space-y-2">
                    <flux:input wire:model.live="date" type="date" label="তারিখ নির্বাচন করুন" />
                    @if($date)
                        <p class="text-[10px] text-indigo-500 font-medium">নির্বাচিত দিন:
                            {{ bn_day(\Carbon\Carbon::parse($date)->format('l')) }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:select wire:model="type" label="ছুটির ধরণ">
                    <option value="">ধরণ নির্বাচন করুন</option>
                    @foreach($holidayTypes as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </flux:select>

                <div class="flex items-end pb-2 gap-4">
                    <flux:checkbox wire:model="is_annual" label="এটি কি প্রতি বছর একই তারিখে হয়?" />
                </div>
            </div>

            <flux:textarea wire:model="details" label="বিস্তারিত বিবরণ (ঐচ্ছিক)" rows="4"
                placeholder="ছুটি সম্পর্কে অতিরিক্ত তথ্য..." />

            <div class="space-y-3">
                <flux:label>ছবি বা ডকুমেন্ট আপলোড (Intro BD স্টাইল)</flux:label>
                <flux:file-upload wire:model.live="images" multiple />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">বাতিল</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="px-8">সংরক্ষণ করুন</flux:button>
            </div>
        </form>
    </flux:modal>
</div>