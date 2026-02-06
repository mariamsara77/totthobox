<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Notifications\MissingDataNotification;
use App\Events\MissingDataReported;

new class extends Component {
    public $pageTitle;
    public $searchQuery;

    public function mount($title = null, $search = null)
    {
        $this->pageTitle = $title ?? "Data Not Found";
        $this->searchQuery = ($search && $search !== '')
            ? $search
            : (request()->query('q') ?? "No specific search term");
    }

    public function notifyAdmin()
    {
        // ১. 'Admin' রোলে থাকা সকল ইউজারকে গেট করা
        $admins = User::role('Admin')->get();

        // ২. যদি কোনো অ্যাডমিন না থাকে, তবে আইডি ১-কে ব্যাকআপ হিসেবে নেওয়া
        if ($admins->isEmpty()) {
            $admins = User::where('id', 1)->get();
        }

        if ($admins->isNotEmpty()) {
            $actualUrl = request()->header('referer') ?? url()->current();

            $details = [
                'title' => $this->pageTitle,
                'url' => $actualUrl,
                'search_query' => $this->searchQuery,
                'sender_id' => auth()->id(),
            ];

            // ৩. লুপ চালিয়ে প্রত্যেক অ্যাডমিনকে আলাদাভাবে পাঠানো
            foreach ($admins as $admin) {
                // ডাটাবেসে সেভ
                $admin->notify(new MissingDataNotification($details));

                // রিয়েলটাইম ব্রডকাস্ট (প্রতিটি অ্যাডমিনের নিজস্ব চ্যানেলে)
                broadcast(new \App\Events\MissingDataReported($details, $admin->id))->toOthers();
            }

            session()->flash('message', 'Thank you! The admins have been notified.');
        }
    }
}; ?>
<section class="flex flex-col items-center justify-center py-12">
    @include('partials.toast')

    <div class="w-full max-w-lg text-center p-8 rounded-[2.5rem] bg-zinc-400/10">
        <div class="mb-6 flex justify-center">
            <div class="p-5 bg-amber-50 dark:bg-amber-950/30 rounded-3xl animate-pulse">
                <flux:icon.magnifying-glass variant="outline" class="w-12 h-12 text-amber-500" />
            </div>
        </div>

        <flux:heading size="xl" class="mb-3">কোনো তথ্য পাওয়া যায়নি!</flux:heading>

        <flux:subheading class="mb-8 px-4 text-base">
            আপনি <span
                class="font-bold text-zinc-900 dark:text-zinc-100 underline decoration-amber-400">"{{ $searchQuery }}"</span>
            লিখে অনুসন্ধান করেছেন।
            আপনি কি চান আমরা এই তথ্যটি আমাদের ডেটাবেজে যুক্ত করি?
        </flux:subheading>

        <flux:button wire:click="notifyAdmin" variant="primary" icon="paper-airplane"
            class="rounded-full px-10 py-3 font-bold">
            অ্যাডমিনকে জানান
        </flux:button>
    </div>
</section>