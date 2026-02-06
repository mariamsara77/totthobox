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

<section class="max-w-2xl mx-auto">
    <div class="text-center mb-12">
        <flux:heading size="xl" class="font-black tracking-tight">
            দোয়া সংগ্রহ
        </flux:heading>
        <flux:subheading size="lg" class="mt-2 text-zinc-500">
            প্রতিদিনের প্রয়োজনীয় দোয়া ও আমলসমূহ
        </flux:subheading>
    </div>

    <div class="grid gap-8 mt-8">
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
            <div class="flex flex-col items-center justify-center py-20 border-2 border-dashed border-zinc-200 rounded-2xl">
                <flux:icon.magnifying-glass size="xl" class="text-zinc-300" />
                <flux:text class="mt-4">কোনো তথ্য খুঁজে পাওয়া যায়নি।</flux:text>
            </div>
        @endforelse
    </div>
</section>