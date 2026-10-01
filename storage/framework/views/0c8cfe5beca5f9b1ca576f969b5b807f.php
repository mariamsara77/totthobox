<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন যোগাযোগ বার্তা</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f5;
        }
        .container {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            color: #4f46e5;
            font-size: 22px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge-urgent {
            background: #fee2e2;
            color: #dc2626;
        }
        .badge-normal {
            background: #e0e7ff;
            color: #4f46e5;
        }
        .info-row {
            margin-bottom: 12px;
        }
        .label {
            font-weight: 600;
            color: #52525b;
            display: inline-block;
            width: 110px;
        }
        .message-box {
            background: #f4f4f5;
            border-left: 4px solid #4f46e5;
            padding: 16px 20px;
            border-radius: 0 8px 8px 0;
            margin-top: 20px;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e4e4e7;
            font-size: 13px;
            color: #71717a;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                নতুন যোগাযোগ বার্তা
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($priority === 'urgent'): ?>
                    <span class="badge badge-urgent">URGENT</span>
                <?php else: ?>
                    <span class="badge badge-normal">Normal</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h1>
        </div>

        <div class="info-row">
            <span class="label">নাম:</span>
            <strong><?php echo e($name); ?></strong>
        </div>

        <div class="info-row">
            <span class="label">ইমেইল:</span>
            <a href="mailto:<?php echo e($email); ?>"><?php echo e($email); ?></a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($phone)): ?>
        <div class="info-row">
            <span class="label">ফোন:</span>
            <a href="tel:<?php echo e($phone); ?>"><?php echo e($phone); ?></a>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($subject)): ?>
        <div class="info-row">
            <span class="label">বিষয়:</span>
            <?php echo e($subject); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($category)): ?>
        <div class="info-row">
            <span class="label">ক্যাটাগরি:</span>
            <?php echo e($category); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="message-box">
            <strong>মেসেজ:</strong><br><br>
            <?php echo e($messageContent); ?>

        </div>

        <div class="footer">
            এই মেসেজটি <?php echo e(config('app.name')); ?> ওয়েবসাইটের Contact Form থেকে পাঠানো হয়েছে।
        </div>
    </div>
</body>
</html><?php /**PATH /var/www/html/totthobox/resources/views/emails/contact.blade.php ENDPATH**/ ?>