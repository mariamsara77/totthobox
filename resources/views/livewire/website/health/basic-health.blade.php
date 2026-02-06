<?php

use Livewire\Volt\Component;
use App\Models\BasicHealth;

new class extends Component {
    public $healths;
    public $search = '';
    public $selectedHealth = null;

    public function mount()
    {
        $this->healths = BasicHealth::latest()->get();
    }

    public function updatedSearch()
    {
        $this->healths = BasicHealth::query()
            ->where('title', 'like', "%{$this->search}%")
            ->orWhere('type', 'like', "%{$this->search}%")
            ->orWhere('summary', 'like', "%{$this->search}%")
            ->latest()
            ->get();
    }

    public function showHealth($id)
    {
        // Clear out any old data first
        $this->selectedHealth = null;

        // Load fresh data
        $this->selectedHealth = BasicHealth::find($id);
    }

    public function closeModal()
    {
        $this->selectedHealth = null;
    }
}; ?>

<section class="">
    {{-- Search box --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
            placeholder="স্বাস্থ্য বিষয় লিখে খুঁজুন..." class="w-full" />
    </div>

    {{-- List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @forelse($healths as $health)
            <div class="border border-zinc-400/25 rounded-xl p-4">
                <h3 class="font-semibold text-lg mb-1">
                    {{ $health->title }}
                </h3>
                @if ($health->type)
                    <p class="text-sm mb-2 text-gray-500">{{ $health->type }}</p>
                @endif
                <p class="text-sm mb-2">
                    {{ Str::limit($health->summary ?? $health->description, 80) }}
                </p>
                <flux:modal.trigger name="details" wire:click="showHealth({{ $health->id }})">
                    <flux:button class="" size="xs">
                        বিস্তারিত দেখুন
                    </flux:button>
                </flux:modal.trigger>
            </div>
        @empty
            <p class=" text-center col-span-full py-4">
                কোনো স্বাস্থ্য বিষয় পাওয়া যায়নি
            </p>
        @endforelse
    </div>

    {{-- Modal --}}
    <flux:modal name="details" class="">
        <div class="flex justify-end">
            <flux:button variant="subtle" inset position="top-right" icon="x-mark"
                x-on:click="$flux.modal('details').close()">

            </flux:button>
        </div>
        <div wire:loading wire:target="showHealth" class="text-center py-16">
            লোড হচ্ছে...
        </div>

        <div wire:loading.remove wire:target="showHealth">
            @if ($selectedHealth)
                <div class="">
                    <div class="">

                        {{-- Image --}}
                        @if ($selectedHealth->image)
                            <div class="mb-4 text-center">
                                <img src="{{ asset('storage/' . $selectedHealth->image) }}"
                                    alt="{{ $selectedHealth->title }}"
                                    class="mx-auto h-32 w-auto object-cover rounded-md">
                            </div>
                        @endif

                        {{-- Title --}}
                        <h2 class="text-xl font-semibold mb-3 text-center">
                            {{ $selectedHealth->title }}
                        </h2>

                        {{-- Details --}}
                        <div class="space-y-2 text-sm text-black dark:text-white">
                            @if ($selectedHealth->type)
                                <p><strong>Type:</strong> {{ $selectedHealth->type }}</p>
                            @endif
                            @if ($selectedHealth->summary)
                                <p><strong>Summary:</strong> {{ $selectedHealth->summary }}</p>
                            @endif
                            @if ($selectedHealth->description)
                                <strong>Description:</strong>{!! $selectedHealth->description !!}
                            @endif
                            @if ($selectedHealth->tags)
                                <p><strong>Tags:</strong>
                                    {{ is_array($selectedHealth->tags) ? implode(', ', $selectedHealth->tags) : $selectedHealth->tags }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <p class="text-center py-16">Loading...</p>
            @endif
        </div>

    </flux:modal>

</section>
