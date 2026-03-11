<?php

use App\Models\HistoryBd;
use Livewire\Volt\Component;

new class extends Component
{
    public $history;

    public function mount($slug)
    {
        $this->history = HistoryBd::with(['division', 'district', 'thana', 'creator', 'media'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // ভিউ কাউন্ট ইনক্রিমেন্ট
        $sessionKey = 'history_view_' . $this->history->id;
        if (!session()->has($sessionKey)) {
            $this->history->incrementViews();
            session()->put($sessionKey, true);
        }
    }
}; ?>

<section class="max-w-2xl mx-auto space-y-6">
    <flux:button href="{{ route('bangladesh.history') }}" variant="subtle" icon="arrow-long-left">ফিরে যান</flux:button>

    <header>
        <flux:heading size="3xl">{{ $history->title }}</flux:heading>
        <div class="text-sm text-zinc-500 mt-2">প্রকাশিত: {{ $history->published_at?->format('d M, Y') }} • ভিউ: {{ $history->view_count }}</div>
    </header>

    @if($history->media->isNotEmpty())
        <div class="rounded-2xl overflow-hidden">
            <flux:media :media="$history->media" />
        </div>
    @endif

    <div class="prose dark:prose-invert">
        {!! $history->description !!}
    </div>

    @if($history->creator)
        <flux:card class="flex items-center gap-4">
            <flux:avatar name="{{ $history->creator->name }}" />
            <div>
                <div class="text-xs text-zinc-500">তথ্য প্রদানকারী</div>
                <flux:heading size="sm">{{ $history->creator->name }}</flux:heading>
            </div>
        </flux:card>
    @endif
</section>