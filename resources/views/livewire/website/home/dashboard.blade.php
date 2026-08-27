<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    #[Computed]
    public function analytics(): array
    {
        return Cache::remember('api_user_analytics_data_v2', 30, function () {
            $actualCount = Visitor::where('is_bot', false)->count();

            return [
                'total_users' => $this->formatKilo($actualCount + 100000),
                'status' => 'success',
            ];
        });
    }

    private function formatKilo(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 2) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 2) . 'k';
        }
        return (string) $number;
    }
}; ?>

<div class="flex justify-center items-center">
    <flux:card>

        {{-- মডার্ন টেক্সট কপি --}}
        <flux:text class="text-xl" variant="strong">
            প্ল্যাটফর্মটি ব্যবহার করেছেন
            <span class="text-green-700 dark:text-green-400 font-bold">
                {{ $this->analytics['total_users'] }}+
            </span>
        </flux:text>

        {{-- মিনিমালিস্ট লোডিং ইন্ডিকেটর --}}
        <span wire:loading class="text-xs text-zinc-400 dark:text-zinc-600 italic ">
            (আপডেট হচ্ছে...)
        </span>
    </flux:card>
</div>
