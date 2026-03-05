<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Redirect;

new class extends Component {
    public function redirectToGoogle()
    {
        return Redirect::route('auth.google.redirect');
    }
}; ?>

<div>
    <flux:button wire:click="redirectToGoogle" wire:loading.attr="disabled" class="w-full !rounded-full py-6"
        icon="google" variant="primary" color="amber">
        <span wire:loading.remove>{{ __('গুগল দিয়ে লগইন করুন') }}</span>
        <span wire:loading>{{ __('অপেক্ষা করুন...') }}</span>
    </flux:button>
</div>