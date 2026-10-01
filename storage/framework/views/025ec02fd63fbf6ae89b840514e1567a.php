
<?php
$ds = $card->design_settings ?? [];
$tpl = $card->template_name ?? 'aurora';

$primary = $ds['primary_color'] ?? '#4F46E5';
$secondary = $ds['secondary_color'] ?? '#7C3AED';
$accent = $ds['accent_color'] ?? '#F59E0B';

$showPhoto = (bool) ($ds['show_photo'] ?? true);
$showLogo = (bool) ($ds['show_logo'] ?? true);
$showBlood = (bool) ($ds['show_blood'] ?? true);
$showQr = (bool) ($ds['show_qr'] ?? true);

$photoShape = match ($ds['photo_shape'] ?? 'rounded') {
'circle' => 'rounded-full',
'square' => 'rounded-none',
default => 'rounded-xl',
};
$photoSizeClass = match ($ds['photo_size'] ?? 'md') {
'sm' => 'w-12 h-14',
'lg' => 'w-20 h-24',
'xl' => 'w-24 h-28',
default => 'w-16 h-20',
};
$logoPx = $ds['logo_max_height'] ?? 40;

// Resolve photo / logo sources (compatible with both Model + stdClass)
$photoSrc = $card->photo_url ?? null;
$logoSrc = $card->logo_url ?? null;

// Language-aware field resolution
$lang = $card->language ?? 'both';
$pick = fn($en, $bn) => match ($lang) {
'en' => ($en ?: $bn),
'bn' => ($bn ?: $en),
default => ($bn ?: $en),
};

$name = $pick($card->name_en ?? '', $card->name_bn ?? '');
$subName = ($lang === 'both' && !empty($card->name_en) && !empty($card->name_bn))
? ($card->name_en ?? null)
: null;
$designation = $pick($card->designation_en ?? '', $card->designation_bn ?? '');
$department = $pick($card->department_en ?? '', $card->department_bn ?? '');
$organization= !empty($card->organization_bn) ? $card->organization_bn : ($card->organization_en ?? '');
$cardNumber = $card->card_number ?? 'IDC-PREVIEW';
$bloodGroup = $card->blood_group ?? '';
$qrUrl = ($showQr && method_exists($card, 'qrImageUrl'))
? $card->qrImageUrl(80)
: ($showQr ? ('https://api.qrserver.com/v1/create-qr-code/?size=80x80&data='.urlencode($card->uuid ?? 'preview')) :
null);

// Issue / expiry
$issueDateStr = isset($card->issue_date) ? (is_string($card->issue_date) ? $card->issue_date :
$card->issue_date->format('d/m/Y')) : null;
$expiryDateStr = isset($card->expiry_date) ? (is_string($card->expiry_date) ? $card->expiry_date :
$card->expiry_date->format('d/m/Y')) : null;
?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tpl === 'aurora'): ?>
<div class="relative w-full h-full overflow-hidden text-white select-none"
    style="background: linear-gradient(135deg, <?php echo e($primary); ?> 0%, <?php echo e($secondary); ?> 60%, #0f0c29 100%); border-radius: 1.25rem;">
    
    <div class="absolute -top-12 -right-12 w-44 h-44 rounded-full opacity-20 blur-3xl"
        style="background: radial-gradient(circle, white, transparent)"></div>
    <div class="absolute -bottom-10 -left-10 w-36 h-36 rounded-full opacity-15 blur-2xl"
        style="background: radial-gradient(circle, #67e8f9, transparent)"></div>
    
    <div class="absolute top-0 left-0 right-0 h-0.5 opacity-50"
        style="background: linear-gradient(90deg, transparent, white, transparent)"></div>

    <div class="relative z-10 p-5 h-full flex flex-col justify-between">
        
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: brightness(0) invert(1);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.2em] opacity-60 font-medium">Identity Card</p>
                <h3 class="text-base font-bold leading-tight truncate mt-0.5"><?php echo e($name ?: 'Name'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] opacity-70 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] opacity-80 font-medium truncate mt-0.5"><?php echo e($designation); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <img src="<?php echo e($photoSrc); ?>" alt="Photo"
                class="<?php echo e($photoSizeClass); ?> object-cover border-2 border-white/40 shadow-xl <?php echo e($photoShape); ?>">
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div
                class="<?php echo e($photoSizeClass); ?> <?php echo e($photoShape); ?> border-2 border-white/25 bg-white/10 flex items-center justify-center">
                <svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div>
            <div class="text-[11px] opacity-70 truncate mb-2"><?php echo e($organization); ?></div>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-[9px] opacity-50 uppercase tracking-widest">Card No.</p>
                    <p class="font-mono text-sm font-semibold"><?php echo e($cardNumber); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiryDateStr): ?>
                    <p class="text-[10px] opacity-60 mt-0.5">Exp: <?php echo e($expiryDateStr); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                    <div class="bg-white/20 backdrop-blur px-2.5 py-1 rounded-lg text-center">
                        <p class="text-[9px] opacity-70">Blood</p>
                        <p class="text-sm font-bold"><?php echo e($bloodGroup); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                    <div class="bg-white p-0.5 rounded-lg shadow">
                        <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-9 h-9">
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'corporate'): ?>
<div class="relative w-full h-full overflow-hidden bg-white text-slate-800 border border-slate-200 select-none"
    style="border-radius: 1.25rem;">
    
    <div class="absolute top-0 left-0 right-0 h-2" style="background: <?php echo e($primary); ?>"></div>

    <div class="relative z-10 p-5 pt-6 h-full flex flex-col justify-between">
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto;">
                <?php else: ?>
                <div class="w-8 h-8 rounded mb-2 flex items-center justify-center" style="background: <?php echo e($primary); ?>">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                    </svg>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.15em] font-semibold mb-1" style="color: <?php echo e($primary); ?>">
                    <?php echo e(ucfirst($card->card_type ?? 'Employee')); ?> ID
                </p>
                <h3 class="text-base font-bold leading-tight truncate text-slate-900"><?php echo e($name ?: 'Full Name'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] text-slate-500 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] font-semibold mt-0.5 truncate" style="color: <?php echo e($primary); ?>"><?php echo e($designation); ?>

                </p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($department): ?>
                <p class="text-[10px] text-slate-500 truncate"><?php echo e($department); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <img src="<?php echo e($photoSrc); ?>" alt="Photo" class="<?php echo e($photoSizeClass); ?> object-cover shadow <?php echo e($photoShape); ?>"
                style="border: 2px solid <?php echo e($primary); ?>">
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div
                class="<?php echo e($photoSizeClass); ?> <?php echo e($photoShape); ?> bg-slate-100 border-2 border-slate-200 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="border-t border-slate-100 pt-3">
            <p class="text-[10px] text-slate-500 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider">Card No.</p>
                    <p class="font-mono text-xs font-bold text-slate-700"><?php echo e($cardNumber); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiryDateStr): ?>
                    <p class="text-[9px] text-slate-400 mt-0.5">Valid till: <?php echo e($expiryDateStr); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                    <div class="px-2 py-1 rounded text-xs font-bold text-white" style="background: <?php echo e($primary); ?>">
                        <?php echo e($bloodGroup); ?>

                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                    <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-9 h-9">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="absolute bottom-0 left-0 right-0 h-1 opacity-60" style="background: <?php echo e($primary); ?>"></div>
</div>


<?php elseif($tpl === 'neon'): ?>
<div class="relative w-full h-full overflow-hidden text-cyan-300 select-none"
    style="background: #030712; border-radius: 1.25rem; border: 1px solid rgba(34,211,238,0.3); box-shadow: 0 0 30px rgba(34,211,238,0.1), inset 0 0 60px rgba(34,211,238,0.04);">
    
    <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full blur-3xl opacity-20"
        style="background: radial-gradient(circle, #22d3ee, transparent)"></div>
    <div class="absolute -bottom-12 left-1/2 w-48 h-24 blur-3xl opacity-10"
        style="background: radial-gradient(ellipse, #a855f7, transparent)"></div>
    
    <div class="absolute inset-0 opacity-5"
        style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(34,211,238,0.1) 2px, rgba(34,211,238,0.1) 3px)">
    </div>

    <div class="relative z-10 p-5 h-full flex flex-col justify-between">
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: hue-rotate(180deg) brightness(1.5);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.25em] text-cyan-500 font-medium">ID //
                    <?php echo e(ucfirst($card->card_type ?? 'card')); ?></p>
                <h3 class="text-base font-bold leading-tight truncate mt-1 text-cyan-200"
                    style="text-shadow: 0 0 12px rgba(34,211,238,0.6)"><?php echo e($name ?: 'NAME'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] text-cyan-500 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] text-fuchsia-300 font-medium truncate mt-0.5"><?php echo e($designation); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <div class="p-0.5 rounded-xl" style="background: linear-gradient(135deg, #22d3ee, #a855f7)">
                <img src="<?php echo e($photoSrc); ?>" alt="Photo" class="<?php echo e($photoSizeClass); ?> object-cover rounded-xl">
            </div>
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div
                class="<?php echo e($photoSizeClass); ?> rounded-xl border border-cyan-500/40 bg-cyan-950/50 flex items-center justify-center">
                <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <p class="text-[10px] text-cyan-600 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="border-t border-cyan-900 pt-2">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-[9px] text-cyan-700 uppercase tracking-widest">Card No.</p>
                        <p class="font-mono text-sm text-cyan-300"><?php echo e($cardNumber); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiryDateStr): ?>
                        <p class="text-[9px] text-cyan-700 mt-0.5">EXP: <?php echo e($expiryDateStr); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                        <div class="border border-fuchsia-500/50 px-2 py-1 rounded text-center"
                            style="box-shadow: 0 0 8px rgba(217,70,239,0.3)">
                            <p class="text-[8px] text-fuchsia-500">BLOOD</p>
                            <p class="text-xs font-bold text-fuchsia-300"><?php echo e($bloodGroup); ?></p>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                        <div class="p-0.5 rounded" style="background: linear-gradient(135deg, #22d3ee, #a855f7)">
                            <div class="bg-white p-0.5 rounded">
                                <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'royal'): ?>
<div class="relative w-full h-full overflow-hidden text-amber-50 select-none"
    style="background: linear-gradient(145deg, #1c0f05 0%, #2d1810 40%, #1c0f05 100%); border-radius: 1.25rem; border: 1px solid #c4973a;">
    
    <div class="absolute top-0 left-0 right-0 h-1"
        style="background: linear-gradient(90deg, #92600a, #f5c842, #e4a020, #f5c842, #92600a)"></div>
    
    <div class="absolute bottom-0 left-0 right-0 h-1"
        style="background: linear-gradient(90deg, #92600a, #f5c842, #e4a020, #f5c842, #92600a)"></div>
    
    <div class="absolute top-2 left-2 w-4 h-4 border-t border-l border-amber-500 opacity-60"></div>
    <div class="absolute top-2 right-2 w-4 h-4 border-t border-r border-amber-500 opacity-60"></div>
    <div class="absolute bottom-2 left-2 w-4 h-4 border-b border-l border-amber-500 opacity-60"></div>
    <div class="absolute bottom-2 right-2 w-4 h-4 border-b border-r border-amber-500 opacity-60"></div>

    <div class="relative z-10 p-5 h-full flex flex-col justify-between">
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: sepia(1) saturate(2) hue-rotate(5deg) brightness(1.2);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.2em] text-amber-400 opacity-80 font-semibold">
                    <?php echo e(ucfirst($card->card_type ?? 'Royal')); ?> Card</p>
                <h3 class="text-base font-bold leading-tight truncate text-amber-100 mt-0.5"
                    style="text-shadow: 0 1px 4px rgba(0,0,0,0.6)"><?php echo e($name ?: 'Name'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] text-amber-400/70 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] text-amber-300 font-medium truncate mt-0.5"><?php echo e($designation); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <div class="p-0.5"
                style="background: linear-gradient(135deg, #f5c842, #92600a, #f5c842); border-radius: 0.75rem;">
                <img src="<?php echo e($photoSrc); ?>" alt="Photo" class="<?php echo e($photoSizeClass); ?> object-cover rounded-xl">
            </div>
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div
                class="<?php echo e($photoSizeClass); ?> rounded-xl border border-amber-600/40 bg-amber-900/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <div class="border-t border-amber-800 pt-2">
                <p class="text-[10px] text-amber-500/80 truncate mb-1.5"><?php echo e($organization); ?></p>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-[9px] text-amber-700 uppercase tracking-widest">Card No.</p>
                        <p class="font-mono text-sm text-amber-300"><?php echo e($cardNumber); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                        <div class="bg-amber-900/60 border border-amber-600/50 px-2 py-1 rounded text-center">
                            <p class="text-[8px] text-amber-500">BLOOD</p>
                            <p class="text-xs font-bold text-amber-200"><?php echo e($bloodGroup); ?></p>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                        <div class="bg-white p-0.5 rounded shadow">
                            <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'glass'): ?>
<div class="relative w-full h-full overflow-hidden text-white select-none"
    style="border-radius: 1.5rem; background: linear-gradient(135deg, <?php echo e($primary); ?>cc, <?php echo e($secondary); ?>99);">
    
    <div class="absolute top-0 right-0 w-40 h-40 rounded-full blur-3xl opacity-50"
        style="background: radial-gradient(circle, <?php echo e($accent); ?>80, transparent)"></div>
    <div class="absolute bottom-0 left-0 w-48 h-32 rounded-full blur-3xl opacity-40"
        style="background: radial-gradient(circle, white, transparent)"></div>

    
    <div class="absolute inset-2 rounded-2xl flex flex-col justify-between p-4"
        style="background: rgba(255,255,255,0.12); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.25); box-shadow: inset 0 1px 0 rgba(255,255,255,0.3);">

        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: brightness(0) invert(1);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.2em] opacity-60 font-medium">Glass ID Card</p>
                <h3 class="text-base font-bold leading-tight truncate mt-0.5"><?php echo e($name ?: 'Name'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] opacity-70 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] opacity-80 truncate mt-0.5"><?php echo e($designation); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <div class="p-0.5 rounded-xl" style="background: rgba(255,255,255,0.3)">
                <img src="<?php echo e($photoSrc); ?>" alt="Photo" class="<?php echo e($photoSizeClass); ?> object-cover rounded-xl opacity-90">
            </div>
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div class="<?php echo e($photoSizeClass); ?> rounded-xl flex items-center justify-center"
                style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2)">
                <svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <p class="text-[10px] opacity-70 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="border-t pt-2" style="border-color: rgba(255,255,255,0.2)">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-[9px] opacity-50 uppercase tracking-widest">Card No.</p>
                        <p class="font-mono text-sm"><?php echo e($cardNumber); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiryDateStr): ?>
                        <p class="text-[9px] opacity-50 mt-0.5">Exp: <?php echo e($expiryDateStr); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                        <div class="text-center rounded-lg px-2 py-1"
                            style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3)">
                            <p class="text-[8px] opacity-60">Blood</p>
                            <p class="text-xs font-bold"><?php echo e($bloodGroup); ?></p>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                        <div class="bg-white p-0.5 rounded-lg">
                            <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'geometric'): ?>
<div class="relative w-full h-full overflow-hidden text-white select-none bg-zinc-950" style="border-radius: 1.25rem;">
    
    <div class="absolute -top-8 -right-8 w-40 h-40 rotate-45 opacity-80" style="background: <?php echo e($primary); ?>"></div>
    <div class="absolute -top-4 right-14 w-24 h-24 rotate-12 opacity-50" style="background: <?php echo e($secondary); ?>"></div>
    <div class="absolute bottom-0 left-0 w-32 h-24 -rotate-12 opacity-70"
        style="background: linear-gradient(135deg, <?php echo e($secondary); ?>, <?php echo e($primary); ?>)"></div>
    <div class="absolute bottom-6 left-8 w-12 h-12 rotate-45 opacity-30 bg-white"></div>

    <div class="relative z-10 p-5 h-full flex flex-col justify-between">
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: brightness(0) invert(1);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.2em] opacity-60">Geometric ID</p>
                <h3 class="text-lg font-black leading-tight truncate mt-0.5 uppercase tracking-wide">
                    <?php echo e($name ?: 'NAME'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] opacity-60 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] font-medium opacity-80 truncate mt-0.5" style="color: <?php echo e($accent); ?>">
                    <?php echo e($designation); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <img src="<?php echo e($photoSrc); ?>" alt="Photo" class="<?php echo e($photoSizeClass); ?> object-cover shadow-2xl"
                style="border-radius: 0; clip-path: polygon(0 0, 90% 0, 100% 10%, 100% 100%, 10% 100%, 0 90%); border: 2px solid <?php echo e($primary); ?>">
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div class="<?php echo e($photoSizeClass); ?> bg-zinc-800 border-2 flex items-center justify-center"
                style="clip-path: polygon(0 0, 90% 0, 100% 10%, 100% 100%, 10% 100%, 0 90%); border-color: <?php echo e($primary); ?>">
                <svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <p class="text-[10px] text-zinc-400 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-[9px] text-zinc-500 uppercase tracking-widest">Card No.</p>
                    <p class="font-mono text-sm font-bold"><?php echo e($cardNumber); ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                    <div class="text-xs font-bold px-2 py-1 rounded" style="background: <?php echo e($primary); ?>; color: white">
                        <?php echo e($bloodGroup); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                    <div class="bg-white p-0.5 rounded shadow">
                        <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'minimal'): ?>
<div class="relative w-full h-full overflow-hidden bg-white text-zinc-900 select-none"
    style="border-radius: 1.25rem; border: 1px solid #e5e7eb;">
    
    <div class="absolute top-0 left-0 bottom-0 w-1 rounded-l-xl" style="background: <?php echo e($primary); ?>"></div>

    <div class="relative z-10 pl-6 pr-5 py-5 h-full flex flex-col justify-between">
        <div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
            <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-3"
                style="max-height: <?php echo e($logoPx); ?>px; width: auto;">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex items-start gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
                <img src="<?php echo e($photoSrc); ?>" alt="Photo"
                    class="<?php echo e($photoSizeClass); ?> object-cover <?php echo e($photoShape); ?> shrink-0">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold leading-tight truncate text-zinc-900"><?php echo e($name ?: 'Full Name'); ?></h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                    <p class="text-xs text-zinc-400 truncate"><?php echo e($subName); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="text-xs font-semibold mt-1 truncate" style="color: <?php echo e($primary); ?>"><?php echo e($designation); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($department): ?>
                    <p class="text-xs text-zinc-400 truncate"><?php echo e($department); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <p class="text-xs text-zinc-500 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="border-t border-zinc-100 pt-2 flex items-center justify-between">
                <div>
                    <p class="font-mono text-xs text-zinc-400"><?php echo e($cardNumber); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiryDateStr): ?>
                    <p class="text-[9px] text-zinc-400 mt-0.5">Valid till <?php echo e($expiryDateStr); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                        style="background: <?php echo e($primary); ?>20; color: <?php echo e($primary); ?>"><?php echo e($bloodGroup); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                    <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'split'): ?>
<div class="relative w-full h-full overflow-hidden flex select-none" style="border-radius: 1.25rem;">
    
    <div class="w-2/5 flex flex-col items-center justify-center p-4 gap-3 relative overflow-hidden"
        style="background: linear-gradient(to bottom, <?php echo e($primary); ?>, <?php echo e($secondary); ?>)">
        <div class="absolute -top-10 -left-10 w-32 h-32 rounded-full opacity-20"
            style="background: radial-gradient(circle, white, transparent)"></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
        <img src="<?php echo e($photoSrc); ?>" alt="Photo"
            class="<?php echo e($photoSizeClass); ?> object-cover border-4 border-white/40 shadow-2xl <?php echo e($photoShape); ?>">
        <?php else: ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
        <div
            class="<?php echo e($photoSizeClass); ?> <?php echo e($photoShape); ?> border-4 border-white/25 bg-white/10 flex items-center justify-center">
            <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
        <div class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-3 py-1 text-center text-white">
            <p class="text-[8px] uppercase tracking-widest opacity-70">Blood</p>
            <p class="text-sm font-bold"><?php echo e($bloodGroup); ?></p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div
        class="w-3/5 bg-white dark:bg-zinc-900 p-4 flex flex-col justify-between text-zinc-900 dark:text-zinc-100 relative">
        <div class="absolute top-0 right-0 bottom-0 w-1 rounded-r-xl" style="background: <?php echo e($primary); ?>20"></div>
        <div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
            <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                style="max-height: <?php echo e($logoPx); ?>px; width: auto;">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p class="text-[9px] uppercase tracking-[0.15em] font-semibold mb-0.5" style="color: <?php echo e($primary); ?>">
                <?php echo e(ucfirst($card->card_type ?? 'ID')); ?> Card
            </p>
            <h3 class="text-sm font-bold leading-tight truncate"><?php echo e($name ?: 'Name'); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
            <p class="text-[10px] text-zinc-400 truncate"><?php echo e($subName); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p class="text-[11px] font-semibold mt-0.5 truncate" style="color: <?php echo e($primary); ?>"><?php echo e($designation); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($department): ?>
            <p class="text-[10px] text-zinc-400 truncate"><?php echo e($department); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <p class="text-[10px] text-zinc-500 truncate mb-1.5"><?php echo e($organization); ?></p>
            <div class="border-t border-zinc-100 dark:border-zinc-800 pt-1.5 flex items-end justify-between">
                <div>
                    <p class="text-[9px] text-zinc-400 uppercase tracking-wider">Card No.</p>
                    <p class="font-mono text-xs font-bold text-zinc-600 dark:text-zinc-300"><?php echo e($cardNumber); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-9 h-9">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'vertical'): ?>
<div class="relative w-full h-full overflow-hidden text-white flex flex-col select-none"
    style="background: linear-gradient(to bottom, <?php echo e($primary); ?>, <?php echo e($secondary); ?>); border-radius: 1.5rem;">
    <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full opacity-20"
        style="background: radial-gradient(circle, white, transparent)"></div>

    <div class="flex-1 flex flex-col items-center justify-center p-5 text-center gap-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
        <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain"
            style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: brightness(0) invert(1);">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
        <img src="<?php echo e($photoSrc); ?>" alt="Photo"
            class="w-24 h-28 object-cover border-4 border-white/40 shadow-2xl <?php echo e($photoShape); ?>">
        <?php else: ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
        <div class="w-20 h-24 <?php echo e($photoShape); ?> border-4 border-white/25 bg-white/10 flex items-center justify-center">
            <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div>
            <h3 class="text-xl font-bold leading-tight"><?php echo e($name ?: 'Name'); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
            <p class="text-sm opacity-70"><?php echo e($subName); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p class="text-sm opacity-60 mt-1"><?php echo e($designation); ?></p>
            <p class="text-xs opacity-50 mt-0.5"><?php echo e($organization); ?></p>
        </div>
    </div>

    <div class="bg-black/25 px-5 py-3 flex justify-between items-center text-xs">
        <div>
            <p class="font-mono"><?php echo e($cardNumber); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiryDateStr): ?>
            <p class="opacity-60 text-[10px]">Exp: <?php echo e($expiryDateStr); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="flex items-center gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
            <div class="bg-white/20 border border-white/30 rounded px-2 py-0.5 text-center">
                <p class="text-[8px] opacity-60">Blood</p>
                <p class="font-bold text-xs"><?php echo e($bloodGroup); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
            <div class="bg-white p-0.5 rounded">
                <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>


<?php elseif($tpl === 'ocean'): ?>
<div class="relative w-full h-full overflow-hidden text-white select-none"
    style="background: linear-gradient(160deg, #0a2342 0%, #1e4d8c 50%, #1a7fb8 100%); border-radius: 1.25rem;">
    
    <svg class="absolute bottom-0 left-0 right-0 w-full opacity-25" viewBox="0 0 400 80" preserveAspectRatio="none"
        xmlns="http://www.w3.org/2000/svg">
        <path d="M0 40 C100 10 200 70 300 30 C350 10 380 50 400 35 L400 80 L0 80 Z" fill="white" />
        <path d="M0 55 C80 30 160 70 240 45 C320 20 360 60 400 50 L400 80 L0 80 Z" fill="rgba(255,255,255,0.5)" />
    </svg>
    <div class="absolute top-4 right-4 w-24 h-24 rounded-full opacity-10 bg-cyan-300 blur-2xl"></div>

    <div class="relative z-10 p-5 h-full flex flex-col justify-between">
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: brightness(0) invert(1);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.2em] text-blue-200/70">Ocean ID Card</p>
                <h3 class="text-base font-bold leading-tight truncate mt-0.5"><?php echo e($name ?: 'Name'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] opacity-70 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] text-cyan-200 font-medium truncate mt-0.5"><?php echo e($designation); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <img src="<?php echo e($photoSrc); ?>" alt="Photo"
                class="<?php echo e($photoSizeClass); ?> object-cover border-2 border-blue-300/40 shadow-xl <?php echo e($photoShape); ?>">
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div
                class="<?php echo e($photoSizeClass); ?> <?php echo e($photoShape); ?> border-2 border-blue-400/25 bg-blue-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="mt-2">
            <p class="text-[10px] text-blue-200/60 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-[9px] text-blue-300/50 uppercase tracking-widest">Card No.</p>
                    <p class="font-mono text-sm"><?php echo e($cardNumber); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                    <div class="bg-blue-900/60 border border-blue-400/30 rounded-lg px-2 py-1 text-center">
                        <p class="text-[8px] text-blue-300/70">Blood</p>
                        <p class="text-xs font-bold text-cyan-200"><?php echo e($bloodGroup); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                    <div class="bg-white p-0.5 rounded shadow">
                        <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'sunset'): ?>
<div class="relative w-full h-full overflow-hidden text-white select-none"
    style="background: linear-gradient(135deg, #7c2d12 0%, #c2410c 30%, #ea580c 60%, #f97316 80%, #fb923c 100%); border-radius: 1.25rem;">
    
    <div class="absolute -top-8 right-8 w-36 h-36 rounded-full opacity-30"
        style="background: radial-gradient(circle, #fbbf24, transparent)"></div>
    <div class="absolute bottom-4 -left-8 w-32 h-20 rounded-full opacity-20"
        style="background: radial-gradient(circle, #f472b6, transparent)"></div>
    <div class="absolute bottom-0 left-0 right-0 h-12 opacity-10"
        style="background: linear-gradient(to top, #7c3aed, transparent)"></div>

    <div class="relative z-10 p-5 h-full flex flex-col justify-between">
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: brightness(0) invert(1);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.2em] text-orange-200/70">
                    <?php echo e(ucfirst($card->card_type ?? 'ID')); ?> Card</p>
                <h3 class="text-base font-bold leading-tight truncate mt-0.5"><?php echo e($name ?: 'Name'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] opacity-70 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] text-yellow-200 font-medium truncate mt-0.5"><?php echo e($designation); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <img src="<?php echo e($photoSrc); ?>" alt="Photo"
                class="<?php echo e($photoSizeClass); ?> object-cover border-2 border-orange-200/50 shadow-xl <?php echo e($photoShape); ?>">
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div
                class="<?php echo e($photoSizeClass); ?> <?php echo e($photoShape); ?> border-2 border-orange-200/25 bg-orange-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <p class="text-[10px] text-orange-200/60 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="border-t border-orange-300/20 pt-2 flex items-end justify-between">
                <div>
                    <p class="text-[9px] text-orange-200/50 uppercase tracking-widest">Card No.</p>
                    <p class="font-mono text-sm"><?php echo e($cardNumber); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                    <div class="bg-orange-900/50 border border-orange-300/30 rounded-lg px-2 py-1 text-center">
                        <p class="text-[8px] text-orange-200/70">Blood</p>
                        <p class="text-xs font-bold text-yellow-200"><?php echo e($bloodGroup); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                    <div class="bg-white p-0.5 rounded shadow">
                        <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php elseif($tpl === 'carbon'): ?>
<div class="relative w-full h-full overflow-hidden text-zinc-100 select-none" style="background-color: #1a1a1a; border-radius: 1.25rem; border: 1px solid #333;
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(255,255,255,0.015) 2px,
                rgba(255,255,255,0.015) 4px
            ),
            repeating-linear-gradient(
                -45deg,
                transparent,
                transparent 2px,
                rgba(255,255,255,0.015) 2px,
                rgba(255,255,255,0.015) 4px
            );">
    
    <div class="absolute top-0 left-0 right-0 h-0.5"
        style="background: linear-gradient(90deg, transparent, <?php echo e($primary); ?>, transparent)"></div>
    <div class="absolute top-0 left-0 bottom-0 w-0.5"
        style="background: linear-gradient(to bottom, <?php echo e($primary); ?>, transparent)"></div>
    
    <div class="absolute top-0 left-0 w-24 h-24 rounded-full blur-3xl opacity-20" style="background: <?php echo e($primary); ?>">
    </div>

    <div class="relative z-10 p-5 h-full flex flex-col justify-between">
        <div class="flex justify-between items-start gap-3">
            <div class="flex-1 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoSrc): ?>
                <img src="<?php echo e($logoSrc); ?>" alt="Logo" class="object-contain mb-2"
                    style="max-height: <?php echo e($logoPx); ?>px; width: auto; filter: brightness(0) invert(1);">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[9px] uppercase tracking-[0.25em] text-zinc-500 font-medium">CARBON ID</p>
                <h3 class="text-base font-bold leading-tight truncate mt-0.5 text-zinc-100"><?php echo e($name ?: 'Name'); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subName): ?>
                <p class="text-[11px] text-zinc-500 truncate"><?php echo e($subName); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-[11px] font-semibold truncate mt-0.5" style="color: <?php echo e($primary); ?>"><?php echo e($designation); ?>

                </p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($department): ?>
                <p class="text-[10px] text-zinc-500 truncate"><?php echo e($department); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto && $photoSrc): ?>
            <div class="p-0.5 rounded-xl" style="background: linear-gradient(135deg, <?php echo e($primary); ?>, #333)">
                <img src="<?php echo e($photoSrc); ?>" alt="Photo" class="<?php echo e($photoSizeClass); ?> object-cover rounded-xl">
            </div>
            <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
            <div
                class="<?php echo e($photoSizeClass); ?> rounded-xl bg-zinc-800 border border-zinc-700 flex items-center justify-center">
                <svg class="w-6 h-6 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <p class="text-[10px] text-zinc-500 truncate mb-2"><?php echo e($organization); ?></p>
            <div class="border-t border-zinc-800 pt-2 flex items-end justify-between">
                <div>
                    <p class="text-[9px] text-zinc-600 uppercase tracking-widest">Card No.</p>
                    <p class="font-mono text-sm text-zinc-300"><?php echo e($cardNumber); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiryDateStr): ?>
                    <p class="text-[9px] text-zinc-600 mt-0.5">EXP: <?php echo e($expiryDateStr); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBlood && $bloodGroup): ?>
                    <div class="border rounded-lg px-2 py-1 text-center"
                        style="border-color: <?php echo e($primary); ?>40; background: <?php echo e($primary); ?>15">
                        <p class="text-[8px] text-zinc-500">BLOOD</p>
                        <p class="text-xs font-bold" style="color: <?php echo e($primary); ?>"><?php echo e($bloodGroup); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrUrl): ?>
                    <div class="bg-white p-0.5 rounded shadow">
                        <img src="<?php echo e($qrUrl); ?>" alt="QR" class="w-8 h-8">
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php else: ?>
<?php $tpl = 'aurora'; ?>
<?php echo $__env->make('id-cards.templates.card', compact('card'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH /var/www/html/totthobox/resources/views/id-cards/templates/card.blade.php ENDPATH**/ ?>