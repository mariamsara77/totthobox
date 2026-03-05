<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Notifications\MissingDataNotification;
use App\Events\MissingDataReported;
use Illuminate\Support\Facades\Notification;

new class extends Component {
    public $pageTitle;
    public $searchQuery;
    public $submitted = false;

    public function mount($title = null, $search = null)
    {
        $this->pageTitle = $title ?? "তথ্য পাওয়া যায়নি";

        // URL থেকে অথবা সরাসরি পাস করা সার্চ শব্দ খোঁজা
        $query = $search ?: request()->query('q');

        // যদি সার্চ শব্দ থাকে তবে সেটা রাখবে, নয়তো null থাকবে
        $this->searchQuery = ($query && $query !== '') ? $query : null;
    }

    public function notifyAdmin()
    {
        if ($this->submitted)
            return;

        // Spatie role: 'admin' (আপনার ডাটাবেসে যেভাবে আছে)
        $admins = User::role('admin')->get();

        if ($admins->isEmpty()) {
            $admins = User::where('id', 1)->get();
        }

        if ($admins->isNotEmpty()) {
            $actualUrl = request()->header('referer') ?? url()->current();

            $details = [
                'title' => $this->pageTitle,
                'url' => $actualUrl,
                'search_query' => $this->searchQuery ?? 'N/A (General Page)',
                'sender_id' => auth()->id(),
                'time' => now()->toTimeString(),
            ];

            Notification::send($admins, new MissingDataNotification($details));

            foreach ($admins as $admin) {
                broadcast(new \App\Events\MissingDataReported($details, $admin->id))->toOthers();
            }

            $this->submitted = true;
            session()->flash('success', 'ধন্যবাদ! অ্যাডমিনকে জানানো হয়েছে।');
        }
    }
}; ?>

<section class="flex flex-col items-center justify-center py-12 px-4">
    <div
        class="w-full max-w-lg text-center p-8 rounded-[2.5rem] bg-zinc-400/10 border border-zinc-200/50 dark:border-zinc-800/50 shadow-sm transition-all">
        <div class="mb-6 flex justify-center">
            <div class="p-5 bg-amber-50 dark:bg-amber-950/30 rounded-3xl animate-pulse">
                @if($searchQuery)
                    <flux:icon.magnifying-glass variant="outline" class="w-12 h-12 text-amber-500" />
                @else
                    <flux:icon.document-magnifying-glass variant="outline" class="w-12 h-12 text-amber-500" />
                @endif
            </div>
        </div>

        <flux:heading size="xl" class="mb-3 font-bold">{{ $pageTitle }}</flux:heading>

        <flux:subheading class="mb-8 px-4 text-base leading-relaxed">
            @if($searchQuery)
                আপনি <span
                    class="font-bold text-zinc-900 dark:text-white underline decoration-amber-500/50">"{{ $searchQuery }}"</span>
                লিখে অনুসন্ধান করেছেন।
            @else
                দুঃখিত, এই মুহূর্তে এখানে দেখানোর মতো কোনো তথ্য আমাদের কাছে নেই।
            @endif
            <br>
            আপনি কি চান আমরা এই বিষয়টি রিভিউ করি এবং প্রয়োজনীয় তথ্য যুক্ত করি?
        </flux:subheading>

        @if(!$submitted)
            <flux:button wire:click="notifyAdmin" wire:loading.attr="disabled" variant="primary" icon="paper-airplane"
                class="rounded-full px-10 py-3 font-bold shadow-lg shadow-amber-500/20 active:scale-95 transition-transform">
                <span wire:loading.remove>অ্যাডমিনকে জানান</span>
                <span wire:loading>প্রসেস হচ্ছে...</span>
            </flux:button>
        @else
            <div
                class="flex flex-col items-center justify-center gap-2 text-green-600 font-medium animate-in fade-in zoom-in duration-300">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-full mb-1">
                    <flux:icon.check-circle variant="solid" class="w-6 h-6" />
                </div>
                অনুরোধ পাঠানো হয়েছে
            </div>
        @endif
    </div>
</section>