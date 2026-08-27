<?php

use App\Jobs\BroadcastAnnouncementJob;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    #[Validate('required|string|max:65')]
    public string $title = '';

    #[Validate('required|string|max:180')]
    public string $body = '';

    #[Validate('nullable|url')]
    public string $url = '';

    public bool $sending = false;
    public ?string $successMessage = null;

    public function getSubscriberCountProperty(): int
    {
        return User::whereHas('pushSubscriptions')->count();
    }

    public function send(): void
    {
        $this->validate();
        $this->sending = true;

        BroadcastAnnouncementJob::dispatch(title: $this->title, body: $this->body, url: $this->url !== '' ? $this->url : null);

        $this->successMessage = "নোটিফিকেশন পাঠানো শুরু হয়েছে — {$this->subscriberCount} জন ব্যবহারকারীকে পাঠানো হবে।";
        $this->reset(['title', 'body', 'url']);
        $this->sending = false;
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">সকল ইউজারকে নোটিফিকেশন পাঠান</flux:heading>
            <flux:subheading>
                মোট সাবস্ক্রাইবড ইউজার:
                <flux:badge color="blue" size="sm">{{ $this->subscriberCount }}</flux:badge>
            </flux:subheading>
        </div>

        @if ($successMessage)
            <flux:callout variant="success" icon="check-circle" wire:key="success">
                <flux:callout.heading>পাঠানো হয়েছে</flux:callout.heading>
                <flux:callout.text>{{ $successMessage }}</flux:callout.text>
            </flux:callout>
        @endif

        <form wire:submit="send" class="space-y-4">
            <flux:input wire:model="title" label="শিরোনাম" placeholder="যেমন: নতুন আপডেট এসেছে!" maxlength="65" />

            <flux:textarea wire:model="body" label="বার্তা"
                placeholder="যেমন: অ্যাপে নতুন ফিচার যুক্ত হয়েছে, এখনই দেখুন।" rows="3" maxlength="180" />

            <flux:input wire:model="url" label="লিংক (ঐচ্ছিক)" placeholder="/updates" />

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled"
                wire:target="send">
                <span wire:loading.remove wire:target="send">সবাইকে পাঠান</span>
                <span wire:loading wire:target="send">পাঠানো হচ্ছে...</span>
            </flux:button>
        </form>
    </flux:card>
</div>
