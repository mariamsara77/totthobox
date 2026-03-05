<?php

namespace App\Livewire\Message;

use Livewire\Volt\Component;
use App\Models\Visitor;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads;

    public $message = '';
    public $image;
    public $replyToId = null;
    public $replyPreview = null;
    public $activeVisitorId = null;

    public function mount()
    {
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            $this->activeVisitorId = Session::get('active_chat_visitor');
        }
    }

    public function getVisitorId()
    {
        if (!Session::has('chat_visitor_id')) {
            $visitor = Visitor::firstOrCreate(
                ['ip_address' => request()->ip()],
                ['name' => 'Guest-' . rand(1000, 9999)]
            );
            Session::put('chat_visitor_id', $visitor->id);
        }
        return Session::get('chat_visitor_id');
    }

    public function setReply($id)
    {
        $msg = SupportMessage::find($id);
        if ($msg) {
            $this->replyToId = $id;
            $this->replyPreview = Str::limit($msg->message, 40);
        }
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'required_without:image|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $isAdmin = Auth::check() && Auth::user()->hasRole('admin');
        $vId = $this->getVisitorId();
        $targetVisitorId = $isAdmin ? $this->activeVisitorId : $vId;

        if ($isAdmin && !$targetVisitorId) return;

        $newMsg = SupportMessage::create([
            'message' => $this->message,
            'is_admin' => $isAdmin,
            'user_id' => Auth::id(),
            'visitor_id' => $targetVisitorId,
            'reply_to_id' => $this->replyToId,
        ]);

        if ($this->image) {
            $newMsg->addMedia($this->image->getRealPath())->toMediaCollection('chat_images');
        }

        $this->reset(['message', 'image', 'replyToId', 'replyPreview']);
        $this->dispatch('scroll-bottom');
    }

    public function selectVisitor($id)
    {
        $this->activeVisitorId = $id;
        Session::put('active_chat_visitor', $id);
        $this->dispatch('scroll-bottom');
    }

    public function deleteMessage($id)
    {
        $msg = SupportMessage::findOrFail($id);
        if ((Auth::check() && Auth::user()->hasRole('admin')) || ($msg->visitor_id == $this->getVisitorId())) {
            $msg->delete();
        }
    }

    public function with()
    {
        $isAdmin = Auth::check() && Auth::user()->hasRole('admin');
        $vId = $this->getVisitorId();

        $query = SupportMessage::with(['user', 'visitor', 'replyTo', 'media']);
        
        if ($isAdmin) {
            $query->where('visitor_id', $this->activeVisitorId ?: 0);
        } else {
            $query->where('visitor_id', $vId);
        }

        return [
            'messages' => $query->oldest()->get(),
            'chatList' => $isAdmin ? SupportMessage::select('visitor_id')->distinct()->with('visitor')->latest()->get() : [],
            'currentVisitorId' => $vId
        ];
    }
}; ?>

<div class="">
    
    {{-- Sidebar: Support Inbox --}}
    @if(Auth::check() && Auth::user()->hasRole('admin'))
    <div class="w-full lg:w-80 bg-zinc-50 dark:bg-zinc-900/50 border-r border-zinc-200 dark:border-zinc-800 flex flex-col h-[30%] lg:h-full">
        <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <flux:heading size="xl" weight="bold">Chats</flux:heading>
            <flux:badge color="zinc" inset="top bottom">{{ count($chatList) }}</flux:badge>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            @forelse($chatList as $chat)
                <button wire:click="selectVisitor({{ $chat->visitor_id }})" 
                    class="w-full text-left p-3 rounded-2xl transition-all {{ $activeVisitorId == $chat->visitor_id ? 'bg-indigo-600 shadow-lg shadow-indigo-200 dark:shadow-none' : 'hover:bg-zinc-200 dark:hover:bg-zinc-800' }}">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <flux:avatar initials="{{ substr($chat->visitor->name ?? 'G', 0, 1) }}" size="sm" />
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-zinc-900 rounded-full"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate {{ $activeVisitorId == $chat->visitor_id ? 'text-white' : 'text-zinc-900 dark:text-zinc-100' }}">
                                {{ $chat->visitor->name ?? 'Guest User' }}
                            </p>
                            <p class="text-[11px] {{ $activeVisitorId == $chat->visitor_id ? 'text-indigo-100' : 'text-zinc-500' }}">
                                ID: #{{ $chat->visitor_id }}
                            </p>
                        </div>
                    </div>
                </button>
            @empty
                <div class="text-center py-10 opacity-50 italic text-sm">No active threads</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Main Chat Area --}}
    <div class="flex-1 flex flex-col bg-white dark:bg-zinc-950 relative">
        {{-- Header --}}
        <header class="h-20 px-6 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-white/80 dark:bg-zinc-950/80 backdrop-blur-xl z-30">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                    <flux:icon.chat-bubble-left-right variant="mini" />
                </div>
                <div>
                    <flux:heading weight="bold">Live Assistance</flux:heading>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-bold text-green-600 uppercase">Online</span>
                    </div>
                </div>
            </div>
        </header>

        {{-- Messages --}}
        <div id="chat-container" class="flex-1 overflow-y-auto p-4 lg:p-8 space-y-6 scroll-smooth" 
             x-data="{ scroll: () => { $el.scrollTop = $el.scrollHeight } }" 
             x-init="scroll()" 
             @scroll-bottom.window="setTimeout(() => scroll(), 100)">
            
            @forelse($messages as $msg)
                @php 
                    $isMe = ($msg->is_admin && Auth::check() && Auth::user()->hasRole('admin')) || 
                            (!$msg->is_admin && $msg->visitor_id == $currentVisitorId);
                    $senderLabel = $msg->is_admin ? ($msg->user->name ?? 'Agent') : ($msg->visitor->name ?? 'Guest');
                @endphp
                
                <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} items-end gap-2">
                    @if(!$isMe)
                        <flux:avatar initials="{{ substr($senderLabel, 0, 1) }}" size="xs" class="mb-2" />
                    @endif

                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[85%] lg:max-w-[70%]">
                        {{-- Action Buttons (Mobile Friendly: Visible by default with low opacity or on tap) --}}
                        <div class="flex gap-2 mb-1 px-2">
                            <button wire:click="setReply({{ $msg->id }})" class="text-[10px] font-bold text-zinc-400 hover:text-indigo-500 transition-colors uppercase tracking-widest">Reply</button>
                            <button wire:click="deleteMessage({{ $msg->id }})" class="text-[10px] font-bold text-zinc-400 hover:text-red-500 transition-colors uppercase tracking-widest text-red-400/70">Delete</button>
                        </div>

                        <div class="px-4 py-3 rounded-2xl shadow-sm {{ $isMe ? 'bg-zinc-900 dark:bg-indigo-600 text-white rounded-tr-none' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-tl-none' }}">
                            @if($msg->replyTo)
                                <div class="mb-2 p-2 rounded-lg bg-white/10 dark:bg-black/20 border-l-2 border-white/30 text-[11px] italic truncate">
                                    {{ $msg->replyTo->message }}
                                </div>
                            @endif

                            @if($msg->hasMedia('chat_images'))
                                <img src="{{ $msg->getFirstMediaUrl('chat_images') }}" class="rounded-lg mb-2 max-w-full h-auto border border-white/10" alt="Shared image">
                            @endif

                            <p class="text-sm leading-relaxed">{{ $msg->message }}</p>

                            <div class="mt-1 flex items-center justify-end gap-1 opacity-40">
                                <span class="text-[9px] font-black uppercase">{{ $msg->created_at->format('h:i a') }}</span>
                                @if($isMe) <flux:icon.check class="w-3 h-3" stroke-width="3" /> @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-zinc-300 dark:text-zinc-700">
                    <flux:icon.chat-bubble-bottom-center class="w-12 h-12 mb-2" />
                    <p class="text-sm font-medium">No messages yet. Say hi!</p>
                </div>
            @endforelse
        </div>

        {{-- Input Section --}}
        <footer class="p-4 bg-white dark:bg-zinc-950 border-t border-zinc-100 dark:border-zinc-800">
            @if(Auth::check() && Auth::user()->hasRole('admin') && !$activeVisitorId)
                <div class="flex items-center justify-center p-4 bg-zinc-100 dark:bg-zinc-900 rounded-2xl text-zinc-500 text-sm font-bold">
                    Please select a chat to respond
                </div>
            @else
                <div class="max-w-4xl mx-auto relative">
                    @if($replyPreview)
                        <div class="flex items-center justify-between bg-zinc-100 dark:bg-zinc-800 px-4 py-2 rounded-t-xl border-x border-t border-zinc-200 dark:border-zinc-700 animate-in fade-in slide-in-from-bottom-1">
                            <span class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 truncate italic">Replying: {{ $replyPreview }}</span>
                            <flux:button variant="ghost" icon="x-mark" size="xs" wire:click="$set('replyPreview', null)" />
                        </div>
                    @endif

                    <div class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all">
                        <div class="flex gap-1">
                            <label class="cursor-pointer">
                                <input type="file" wire:model="image" class="hidden">
                                <flux:button as="span" variant="ghost" icon="paper-clip" class="!rounded-xl" />
                            </label>
                        </div>

                        <input 
                            wire:model="message" 
                            wire:keydown.enter.prevent="sendMessage" 
                            type="text" 
                            placeholder="Write your message..." 
                            class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 px-1 text-zinc-800 dark:text-zinc-100 placeholder-zinc-400"
                        >

                        <flux:button 
                            wire:click="sendMessage" 
                            variant="filled" icon="paper-airplane">
                        </flux:button>
                    </div>
                </div>
            @endif
        </footer>
    </div>
</div>