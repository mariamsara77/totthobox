<?php
use Livewire\Volt\Component;

new class extends Component {
    public function redirectToFacebook()
    {
        return redirect()->to(route('login.facebook'));
    }
}; ?>

<div>
    <flux:button wire:click="redirectToFacebook" wire:loading.attr="disabled" class="w-full !rounded-full py-6"
        icon="facebook" variant="primary" color="blue">
        <span wire:loading.remove>{{ __('ফেসবুক দিয়ে লগইন করুন') }}</span>
        <span wire:loading>{{ __('রিডাইরেক্ট হচ্ছে...') }}</span>
    </flux:button>
</div>