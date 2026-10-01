<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($card->card_number); ?> - Print</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
    <style>
        @page {
            size: 85.6mm 53.98mm;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 85.6mm;
            height: 53.98mm;
            overflow: hidden;
            background: white;
        }

        .card-container {
            width: 85.6mm;
            height: 53.98mm;
            overflow: hidden;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="card-container">
        <?php echo $__env->make('id-cards.templates.' . $card->template_name, [
            'card' => $card,
            'preview' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</body>

</html><?php /**PATH /var/www/html/totthobox/resources/views/livewire/website/tools/print.blade.php ENDPATH**/ ?>