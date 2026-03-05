<?php

use Livewire\Volt\Component;
use App\Models\Para;
use App\Models\Sura;
use App\Models\Quran;
use Livewire\Attributes\Url;

new class extends Component {
    #[Url(as: 'q', history: true)]
    public $search = '';

    #[Url(as: 'para', history: true)]
    public $selectedPara = null;

    #[Url(as: 'sura', history: true)]
    public $selectedSura = null;

    public $paras = [];
    public $suras = [];
    public $quranData = [];

    // Mount function - runs once when component initializes
    public function mount(): void
    {
        $this->paras = Para::all();
        $this->loadSuras();
        $this->filterData();
    }

    // Helper method to load suras based on selected para
    protected function loadSuras(): void
    {
        if ($this->selectedPara) {
            $this->suras = Sura::where('para_id', $this->selectedPara)->get();
        } else {
            $this->suras = Sura::all();
        }
    }

    // When selectedPara updates
    public function updatedSelectedPara($value): void
    {
        $this->selectedSura = null; // Reset sura when para changes
        $this->loadSuras();
        $this->filterData();
    }

    // When selectedSura updates
    public function updatedSelectedSura($value): void
    {
        $this->filterData();
    }

    // When search updates
    public function updatedSearch($value): void
    {
        $this->filterData();
    }

    // Main filter logic
    public function filterData(): void
    {
        $query = Quran::query()->active();

        if ($this->selectedPara) {
            $query->where('para_id', $this->selectedPara);
        }

        if ($this->selectedSura) {
            $query->where('sura_id', $this->selectedSura);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('text_arabic', 'like', '%' . $this->search . '%')
                    ->orWhere('text_bangla', 'like', '%' . $this->search . '%')
                    ->orWhere('text_english', 'like', '%' . $this->search . '%');
            });
        }

        $this->quranData = $query->orderBy('sura_id')->orderBy('ayat_no')->get();
    }

    // Reset filters
    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedPara = null;
        $this->selectedSura = null;
        $this->loadSuras();
        $this->filterData();
    }
};

?>

<div x-data="{ 
    showAyatNo: true,
    showArabic: true,
    showBangla: true,
    showMeaning: true,
    showPlayButton: true,
    showEnglishMeaning: false,
    showBanglaFojilot: false,
    activeAudio: null,
    
    toggleAudio(audioId) {
        if (this.activeAudio && this.activeAudio !== audioId) {
            let prevAudio = this.$refs['audio' + this.activeAudio];
            if (prevAudio) {
                prevAudio.pause();
                prevAudio.currentTime = 0;
            }
        }
        
        let currentAudio = this.$refs['audio' + audioId];
        if (currentAudio.paused) {
            currentAudio.play();
            this.activeAudio = audioId;
        } else {
            currentAudio.pause();
            this.activeAudio = null;
        }
    },
    
    convertToBengaliNumber(num) {
        const bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return num.toString().replace(/\d/g, digit => bengaliDigits[digit]);
    }
}" class="space-y-4 max-w-2xl mx-auto" wire:poll.keep-alive.60s>

    <div class="text-center">
        <flux:heading level="1" size="xl">
            আল-কুরআন
        </flux:heading>

        <flux:subheading level="2">
            কুরআনের আয়াতসমূহের আরবি, বাংলা উচ্চারণ, অর্থ এবং ফজিলতসহ বিস্তারিত তথ্য
        </flux:subheading>
    </div>


    <div class="flex overflow-x-auto items-center gap-4 py-2">

        <div class="flex-1">
            <flux:input wire:model.live.debounce.500ms="search" variant="filled" icon="magnifying-glass" size="sm"
                placeholder="আরবি, বাংলা বা ইংরেজিতে অনুসন্ধান..." clearable />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:select wire:model.live="selectedPara" placeholder="পারা" class="w-32 md:w-40" size="sm">
                @foreach ($paras as $para)
                    <flux:select.option value="{{ $para->id }}">{{ $para->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="selectedSura" placeholder="সুরা"
                :disabled="!$selectedPara && count($suras) === 0" class="w-32 md:w-48" size="sm">
                @foreach ($suras as $sura)
                    <flux:select.option value="{{ $sura->id }}">{{ $sura->name }}</flux:select.option>
                @endforeach
            </flux:select>

            @if($search || $selectedPara || $selectedSura)
                <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark" size="sm" />
            @endif

            <flux:badge color="zinc" variant="outline" class="whitespace-nowrap">
                {{ count($quranData) }} আয়াত
            </flux:badge>
        </div>
    </div>

    <!-- Toggle Buttons -->

    <div class="flex items-center overflow-x-auto gap-2 pb-4 no-scrollbar">
        @php
            $toggles = [
                'showArabic' => ['label' => 'আরবি', 'icon' => '🇸🇦'],
                'showAyatNo' => ['label' => 'আয়াত নং', 'icon' => '🔢'],
                'showBangla' => ['label' => 'উচ্চারণ', 'icon' => '🇧🇩'],
                'showMeaning' => ['label' => 'অর্থ', 'icon' => '📖'],
                'showPlayButton' => ['label' => 'প্লে বাটন', 'icon' => '▶️'],
                'showEnglishMeaning' => ['label' => 'ইংরেজি অর্থ', 'icon' => '🇬🇧'],
                'showBanglaFojilot' => ['label' => 'বাংলা ফজিলাত', 'icon' => '✨'],
            ];
        @endphp

        @foreach ($toggles as $key => $toggle)
            {{-- দুইটি আলাদা বাটন ব্যবহার করে toggle effect --}}
            <template x-if="{{ $key }}">
                <flux:button size="sm" variant="primary" x-on:click="{{ $key }} = false"
                    class="whitespace-nowrap flex-shrink-0">
                    <span class="mr-1.5">{{ $toggle['icon'] }}</span>
                    <span>{{ $toggle['label'] }}</span>
                </flux:button>
            </template>

            <template x-if="!{{ $key }}">
                <flux:button size="sm" variant="filled" x-on:click="{{ $key }} = true"
                    class="whitespace-nowrap flex-shrink-0">
                    <span class="mr-1.5">{{ $toggle['icon'] }}</span>
                    <span>{{ $toggle['label'] }}</span>
                </flux:button>
            </template>
        @endforeach
    </div>

    {{-- Add this CSS to hide scrollbar but keep functionality --}}
    <style>
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>

    <!-- Quran Verses Display -->
    @forelse ($quranData as $index => $ayat)
        <div class="p-6 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-150"
            wire:key="ayat-{{ $ayat->id }}">
            <!-- Ayah Header with Number and Play Button -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    @if ($ayat->audio_url)
                        <button x-on:click="toggleAudio({{ $ayat->id }})" type="button"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            x-show="showPlayButton">
                            <span x-text="$refs['audio{{ $ayat->id }}']?.paused ? '▶' : '⏸'" class="text-sm"></span>
                        </button>
                        <audio x-ref="audio{{ $ayat->id }}" src="{{ asset('storage/' . $ayat->audio_url) }}"
                            @ended="activeAudio = null" class="hidden"></audio>
                    @endif

                    <span x-show="showAyatNo"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <span x-text="convertToBengaliNumber({{ $ayat->sura_id }})"></span>:<span
                            x-text="convertToBengaliNumber({{ $ayat->ayat_no }})"></span>
                    </span>
                </div>

                <!-- Decorative Quranic Symbol -->
                <span class="text-2xl text-amber-500 dark:text-amber-400">۝</span>
            </div>

            <!-- Arabic Text -->
            <div x-show="showArabic" class="mb-4 text-right">
                <p class="text-3xl md:text-4xl font-arabic leading-loose text-zinc-900 dark:text-white"
                    style="font-family: 'Amiri', 'Traditional Arabic', serif; line-height: 2.5;">
                    {{ $ayat->text_arabic }}
                </p>
            </div>

            <!-- Bangla Pronunciation -->
            <div x-show="showBangla" class="mb-3">
                <p class="text-lg text-zinc-700 dark:text-zinc-300 font-noto"
                    style="font-family: 'Noto Sans Bengali', sans-serif;">
                    {{ $ayat->text_bangla }}
                </p>
            </div>

            <!-- Bangla Meaning -->
            <div x-show="showMeaning" class="mb-3">
                <p class="text-zinc-600 dark:text-zinc-400">
                    <span class="font-semibold text-zinc-900 dark:text-white">অর্থঃ</span>
                    {{ $ayat->bangla_meaning ?? 'অর্থ পাওয়া যায়নি' }}
                </p>
            </div>

            <!-- English Meaning -->
            <div x-show="showEnglishMeaning" class="mb-3">
                <p class="text-zinc-600 dark:text-zinc-400">
                    <span class="font-semibold text-zinc-900 dark:text-white">English Meaning:</span>
                    {{ $ayat->text_english ?? 'Meaning not available' }}
                </p>
            </div>

            <!-- Bangla Fojilot -->
            <div x-show="showBanglaFojilot"
                class="mb-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                <p class="text-amber-800 dark:text-amber-300">
                    <span class="font-semibold">ফজিলতঃ</span>
                    {{ $ayat->bangla_fojilot ?? 'ফজিলত পাওয়া যায়নি' }}
                </p>
            </div>

            <!-- Ayah Separator for better readability -->
            @if (!$loop->last)
                <div class="mt-4 text-center text-zinc-300 dark:text-zinc-600">
                    <span class="text-sm">◈</span>
                </div>
            @endif
        </div>
    @empty
        <livewire:global.nodata-message :title="'আয়াত'" :search="$search" />
    @endforelse

    <!-- Quick Stats Footer -->
    @if(count($quranData) > 0)
        <div class="text-center text-sm text-zinc-500 dark:text-zinc-400">
            মোট {{ count($quranData) }}টি আয়াত প্রদর্শিত হচ্ছে
        </div>
    @endif
</div>