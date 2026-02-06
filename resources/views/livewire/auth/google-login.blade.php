<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Redirect;

new class extends Component {
    public function redirectToGoogle()
    {
        // RedirectResponse অবশ্যই return করতে হবে
        return Redirect::route('auth.google.redirect');
    }
};

?>

<div>
    <flux:button wire:click="redirectToGoogle" class="w-full !rounded-full py-6" icon="google" variant="primary"
        color="amber">
        {{ __('Continue with Google') }}
    </flux:button>
</div>
