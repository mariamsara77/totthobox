<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\BuySellPost;
use App\Models\BuySellCategory;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;

?>

<section class="max-w-2xl mx-auto space-y-4">

    <!-- Header Section -->
    <?php echo $__env->make('partials.buy-sell.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Filters Section -->
    <?php echo $__env->make('partials.buy-sell.filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Results Section -->
    <?php echo $__env->make('partials.buy-sell.results', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Contact Modal -->
    

    <!-- Scripts -->
    <?php echo $__env->make('partials.buy-sell.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</section><?php /**PATH /var/www/html/totthobox/resources/views/livewire/website/buysell/buysell.blade.php ENDPATH**/ ?>