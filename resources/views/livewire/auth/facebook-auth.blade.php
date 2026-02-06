<?php

use Livewire\Volt\Component;

new class extends Component {
    public function redirectToFacebook()
    {
        return redirect()->to(route('login.facebook')); // web.php route
    }
}; ?>

<div class="">
    <flux:button wire:click="redirectToFacebook" class="w-full !rounded-full py-6" icon="facebook" variant="primary"
        color="blue">
        Log in with Facebook
    </flux:button>
</div>
