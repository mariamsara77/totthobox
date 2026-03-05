<?php

use Livewire\Volt\Component;
use App\Models\Dowa;

new class extends Component {
    public $dowas;

    public function mount()
    {
        $this->dowas = Dowa::all();
    }
}; ?>

<section class="max-w-2xl mx-auto space-y-4">
    <div class="text-center">
        <flux:heading level="1" size="xl">
            দোয়া সংগ্রহ
        </flux:heading>
        <flux:subheading level="2">
            প্রতিদিনের প্রয়োজনীয় দোয়া ও আমলসমূহ
        </flux:subheading>
    </div>

    <div class="grid gap-8">
        @forelse($dowas as $dowa)
            <div>
                <div class="flex flex-col gap-6">
                    <flux:heading level="2" size="lg" class="text-center font-semibold">
                        {{ $dowa->bangla_name }}
                    </flux:heading>

                    <div>
                        <flux:text size="xl" class="block leading-loose font-serif">
                            {{ $dowa->arabic_text }}
                        </flux:text>
                    </div>

                    <div class="grid gap-4">
                        <div class="flex flex-col gap-1">
                            <flux:label>উচ্চারণ</flux:label>
                            <flux:text class="text-justify">
                                {{ $dowa->bangla_text }}
                            </flux:text>
                        </div>

                        <div class="flex flex-col gap-1">
                            <flux:label>অর্থ</flux:label>
                            <flux:text variant="subtle" class="italic text-justify">
                                {{ $dowa->bangla_meaning }}
                            </flux:text>
                        </div>

                        @if($dowa->bangla_fojilot)
                            <flux:separator variant="subtle" />
                            <div class="flex flex-col gap-1">
                                <flux:label class="text-amber-600 dark:text-amber-400">ফজিলত ও আমল</flux:label>
                                <flux:text size="sm" class="text-justify leading-relaxed">
                                    {{ $dowa->bangla_fojilot }}
                                </flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <livewire:global.nodata-message :title="'দোয়া সংগ্রহ'" />
        @endforelse
    </div>
</section>