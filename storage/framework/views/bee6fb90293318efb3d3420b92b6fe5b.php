<?php

use Livewire\Volt\Component;
use App\Http\Controllers\Auth\GoogleLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

?>

<div wire:ignore>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
        <div id="g_id_onload" data-client_id="<?php echo e(config('services.google.client_id')); ?>"
            data-callback="handleCredentialResponse" data-auto_prompt="true" data-itp_support="true">
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /var/www/html/totthobox/resources/views/livewire/auth/google-one-tap.blade.php ENDPATH**/ ?>