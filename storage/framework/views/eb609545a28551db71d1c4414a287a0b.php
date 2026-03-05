<?php

use Livewire\Volt\Component;
use App\Models\Visitor;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

?>

<div class="">
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::check() && Auth::user()->hasRole('admin')): ?>
    <div class="w-full lg:w-80 bg-zinc-50 dark:bg-zinc-900/50 border-r border-zinc-200 dark:border-zinc-800 flex flex-col h-[30%] lg:h-full">
        <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['size' => 'xl','weight' => 'bold']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xl','weight' => 'bold']); ?>Chats <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['color' => 'zinc','inset' => 'top bottom']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'zinc','inset' => 'top bottom']); ?><?php echo e(count($chatList)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $chatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <button wire:click="selectVisitor(<?php echo e($chat->visitor_id); ?>)" 
                    class="w-full text-left p-3 rounded-2xl transition-all <?php echo e($activeVisitorId == $chat->visitor_id ? 'bg-indigo-600 shadow-lg shadow-indigo-200 dark:shadow-none' : 'hover:bg-zinc-200 dark:hover:bg-zinc-800'); ?>">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <?php if (isset($component)) { $__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::avatar.index','data' => ['initials' => ''.e(substr($chat->visitor->name ?? 'G', 0, 1)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['initials' => ''.e(substr($chat->visitor->name ?? 'G', 0, 1)).'','size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690)): ?>
<?php $attributes = $__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690; ?>
<?php unset($__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690)): ?>
<?php $component = $__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690; ?>
<?php unset($__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690); ?>
<?php endif; ?>
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-zinc-900 rounded-full"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate <?php echo e($activeVisitorId == $chat->visitor_id ? 'text-white' : 'text-zinc-900 dark:text-zinc-100'); ?>">
                                <?php echo e($chat->visitor->name ?? 'Guest User'); ?>

                            </p>
                            <p class="text-[11px] <?php echo e($activeVisitorId == $chat->visitor_id ? 'text-indigo-100' : 'text-zinc-500'); ?>">
                                ID: #<?php echo e($chat->visitor_id); ?>

                            </p>
                        </div>
                    </div>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-10 opacity-50 italic text-sm">No active threads</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex-1 flex flex-col bg-white dark:bg-zinc-950 relative">
        
        <header class="h-20 px-6 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-white/80 dark:bg-zinc-950/80 backdrop-blur-xl z-30">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                    <?php if (isset($component)) { $__componentOriginal91fd129b4949918c402a9ed21c33e67f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fd129b4949918c402a9ed21c33e67f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chat-bubble-left-right','data' => ['variant' => 'mini']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chat-bubble-left-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'mini']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fd129b4949918c402a9ed21c33e67f)): ?>
<?php $attributes = $__attributesOriginal91fd129b4949918c402a9ed21c33e67f; ?>
<?php unset($__attributesOriginal91fd129b4949918c402a9ed21c33e67f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fd129b4949918c402a9ed21c33e67f)): ?>
<?php $component = $__componentOriginal91fd129b4949918c402a9ed21c33e67f; ?>
<?php unset($__componentOriginal91fd129b4949918c402a9ed21c33e67f); ?>
<?php endif; ?>
                </div>
                <div>
                    <?php if (isset($component)) { $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::heading','data' => ['weight' => 'bold']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['weight' => 'bold']); ?>Live Assistance <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $attributes = $__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__attributesOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9)): ?>
<?php $component = $__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9; ?>
<?php unset($__componentOriginale0fd5b6a0986beffac17a0a103dfd7b9); ?>
<?php endif; ?>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-bold text-green-600 uppercase">Online</span>
                    </div>
                </div>
            </div>
        </header>

        
        <div id="chat-container" class="flex-1 overflow-y-auto p-4 lg:p-8 space-y-6 scroll-smooth" 
             x-data="{ scroll: () => { $el.scrollTop = $el.scrollHeight } }" 
             x-init="scroll()" 
             @scroll-bottom.window="setTimeout(() => scroll(), 100)">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php 
                    $isMe = ($msg->is_admin && Auth::check() && Auth::user()->hasRole('admin')) || 
                            (!$msg->is_admin && $msg->visitor_id == $currentVisitorId);
                    $senderLabel = $msg->is_admin ? ($msg->user->name ?? 'Agent') : ($msg->visitor->name ?? 'Guest');
                ?>
                
                <div class="flex <?php echo e($isMe ? 'justify-end' : 'justify-start'); ?> items-end gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isMe): ?>
                        <?php if (isset($component)) { $__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::avatar.index','data' => ['initials' => ''.e(substr($senderLabel, 0, 1)).'','size' => 'xs','class' => 'mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['initials' => ''.e(substr($senderLabel, 0, 1)).'','size' => 'xs','class' => 'mb-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690)): ?>
<?php $attributes = $__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690; ?>
<?php unset($__attributesOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690)): ?>
<?php $component = $__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690; ?>
<?php unset($__componentOriginal4dcb6e757bd07b9aa3bf7ee84cfc8690); ?>
<?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="flex flex-col <?php echo e($isMe ? 'items-end' : 'items-start'); ?> max-w-[85%] lg:max-w-[70%]">
                        
                        <div class="flex gap-2 mb-1 px-2">
                            <button wire:click="setReply(<?php echo e($msg->id); ?>)" class="text-[10px] font-bold text-zinc-400 hover:text-indigo-500 transition-colors uppercase tracking-widest">Reply</button>
                            <button wire:click="deleteMessage(<?php echo e($msg->id); ?>)" class="text-[10px] font-bold text-zinc-400 hover:text-red-500 transition-colors uppercase tracking-widest text-red-400/70">Delete</button>
                        </div>

                        <div class="px-4 py-3 rounded-2xl shadow-sm <?php echo e($isMe ? 'bg-zinc-900 dark:bg-indigo-600 text-white rounded-tr-none' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-tl-none'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->replyTo): ?>
                                <div class="mb-2 p-2 rounded-lg bg-white/10 dark:bg-black/20 border-l-2 border-white/30 text-[11px] italic truncate">
                                    <?php echo e($msg->replyTo->message); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->hasMedia('chat_images')): ?>
                                <img src="<?php echo e($msg->getFirstMediaUrl('chat_images')); ?>" class="rounded-lg mb-2 max-w-full h-auto border border-white/10" alt="Shared image">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <p class="text-sm leading-relaxed"><?php echo e($msg->message); ?></p>

                            <div class="mt-1 flex items-center justify-end gap-1 opacity-40">
                                <span class="text-[9px] font-black uppercase"><?php echo e($msg->created_at->format('h:i a')); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMe): ?> <?php if (isset($component)) { $__componentOriginal9c2dfd6cb98f4df18e26d1694500af11 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.check','data' => ['class' => 'w-3 h-3','strokeWidth' => '3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-3 h-3','stroke-width' => '3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11)): ?>
<?php $attributes = $__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11; ?>
<?php unset($__attributesOriginal9c2dfd6cb98f4df18e26d1694500af11); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c2dfd6cb98f4df18e26d1694500af11)): ?>
<?php $component = $__componentOriginal9c2dfd6cb98f4df18e26d1694500af11; ?>
<?php unset($__componentOriginal9c2dfd6cb98f4df18e26d1694500af11); ?>
<?php endif; ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="h-full flex flex-col items-center justify-center text-zinc-300 dark:text-zinc-700">
                    <?php if (isset($component)) { $__componentOriginale40bf8b1f19e4a4fd93249c1554fc03a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale40bf8b1f19e4a4fd93249c1554fc03a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chat-bubble-bottom-center','data' => ['class' => 'w-12 h-12 mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chat-bubble-bottom-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-12 h-12 mb-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale40bf8b1f19e4a4fd93249c1554fc03a)): ?>
<?php $attributes = $__attributesOriginale40bf8b1f19e4a4fd93249c1554fc03a; ?>
<?php unset($__attributesOriginale40bf8b1f19e4a4fd93249c1554fc03a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale40bf8b1f19e4a4fd93249c1554fc03a)): ?>
<?php $component = $__componentOriginale40bf8b1f19e4a4fd93249c1554fc03a; ?>
<?php unset($__componentOriginale40bf8b1f19e4a4fd93249c1554fc03a); ?>
<?php endif; ?>
                    <p class="text-sm font-medium">No messages yet. Say hi!</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <footer class="p-4 bg-white dark:bg-zinc-950 border-t border-zinc-100 dark:border-zinc-800">
            <?php if(Auth::check() && Auth::user()->hasRole('admin') && !$activeVisitorId): ?>
                <div class="flex items-center justify-center p-4 bg-zinc-100 dark:bg-zinc-900 rounded-2xl text-zinc-500 text-sm font-bold">
                    Please select a chat to respond
                </div>
            <?php else: ?>
                <div class="max-w-4xl mx-auto relative">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($replyPreview): ?>
                        <div class="flex items-center justify-between bg-zinc-100 dark:bg-zinc-800 px-4 py-2 rounded-t-xl border-x border-t border-zinc-200 dark:border-zinc-700 animate-in fade-in slide-in-from-bottom-1">
                            <span class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 truncate italic">Replying: <?php echo e($replyPreview); ?></span>
                            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','icon' => 'x-mark','size' => 'xs','wire:click' => '$set(\'replyPreview\', null)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','icon' => 'x-mark','size' => 'xs','wire:click' => '$set(\'replyPreview\', null)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all">
                        <div class="flex gap-1">
                            <label class="cursor-pointer">
                                <input type="file" wire:model="image" class="hidden">
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['as' => 'span','variant' => 'ghost','icon' => 'paper-clip','class' => '!rounded-xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'span','variant' => 'ghost','icon' => 'paper-clip','class' => '!rounded-xl']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                            </label>
                        </div>

                        <input 
                            wire:model="message" 
                            wire:keydown.enter.prevent="sendMessage" 
                            type="text" 
                            placeholder="Write your message..." 
                            class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 px-1 text-zinc-800 dark:text-zinc-100 placeholder-zinc-400"
                        >

                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'sendMessage','variant' => 'filled','icon' => 'paper-airplane']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'sendMessage','variant' => 'filled','icon' => 'paper-airplane']); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </footer>
    </div>
</div><?php /**PATH /var/www/html/totthobox/resources/views/livewire/global/supprt-message.blade.php ENDPATH**/ ?>