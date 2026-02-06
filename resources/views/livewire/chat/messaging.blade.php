<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Message;
use App\Events\UserTyping;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app.message')] class extends Component {
    use WithPagination, WithFileUploads;

    public $user;
    public $allusers;
    public $selectedUser;
    public $messageText = '';
    public $users = [];
    public $onlineUsers = [];
    public $selectedUserModel;
    public $selectedUserName;
    public $typing = false;
    public $activeTab = 'chats';
    public $attachment = null;
    public $attachmentPreview = null;
    public $isCallModalOpen = false;
    public $callType = 'audio';
    public $isRecording = false;
    public $isIncomingCall = false;
    public $showModal = false;
    public $openImageModal = false;
    public $searchTerm = '';
    public $audioRecording;
    public $replyMessage = null;
    public $editMessage = null;
    public $editedMessageText = '';
    public $authUserId;
    public $authUser;
    public $isTyping = false;
    public $typingTimeout = null;
    public $imageUrl = null;
    public $perPage = 20; // শুরুতে ২০টি মেসেজ দেখাবে

    public $isBlockedByMe = false;

    protected $listeners = [
        'messageSent' => 'loadMessages',
        'newMessageReceived' => 'loadMessages',
        'callInitiated' => 'handleIncomingCall',
        'callAccepted' => 'handleCallAccepted',
        'callEnded' => 'handleCallEnded',
        'typingStarted' => 'handleTypingStarted',
        'typingStopped' => 'handleTypingStopped',
        'focusMessageInput' => 'focusMessageInput',
        'setImageUrl' => 'setImageUrl',
        'scrollToBottom' => 'scrollToBottom',
        'markedAsRead' => 'loadMessagesIfUnread',
        'startRecording' => 'startRecording',
        'stopRecording' => 'stopRecording',
        'refreshMessages' => 'loadMessages',
        'incoming-message' => 'handleIncomingMessageEvent',
        'user-typing-event' => 'handleTypingEventReceived',
        'typing-event-received' => 'handleTypingEventReceived',
    ];

    public function getListeners()
    {
        $userId = auth()->id();

        return [
            // Private Channel listener
            "echo-private:chat.{$userId},.MessageSent" => 'handleIncomingMessage',
            "echo-private:chat.{$userId},UserTyping" => 'handleTypingEventReceived',

            // সাধারণ লিসেনার
            'messageSent' => 'loadMessages',
            'newMessageReceived' => 'loadMessages',
        ];
    }
    public function mount($slug = null)
    {
        $this->authUserId = auth()->id();
        $this->authUser = auth()->user();

        // if ($slug) {
        //     $this->user = User::where('slug', $slug)->firstOrFail();
        // }

        if ($slug) {
            // নিজের স্লাগ হলে স্কিপ করবে, অন্য কারও হলে ধরবে
            $targetUser = User::where('slug', $slug)->first();
            if ($targetUser && $targetUser->id !== $this->authUserId) {
                $this->user = $targetUser;
            }
        }

        $this->loadUsers();
        $this->loadOnlineUsers();
        $this->markAllAsRead();

        // Load messages if user is set
        if ($this->user && !$this->selectedUser) {
            $this->selectedUser = $this->user->id;
            $this->selectedUserName = $this->user->name;
            $this->selectedUserModel = $this->user;
            $this->loadMessages();
        }

        $this->checkBlockStatus();
    }

    public function handleIncomingMessage($payload)
    {
        // ১. যদি ডাটাটি স্ট্রিং হিসেবে আসে, তবে তাকে অ্যারেতে রূপান্তর (Decode) করুন
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        // ২. রিভার্ব পেলোড সাধারণত মেসেজ অবজেক্ট সরাসরি পাঠায় অথবা 'message' কী-র ভেতর পাঠায়
        $messageData = $payload['message'] ?? $payload;

        // ৩. নিশ্চিত হোন যে $messageData এখন একটি অ্যারে
        if (is_array($messageData)) {
            if (isset($messageData['sender_id']) && $messageData['sender_id'] == $this->selectedUser) {
                $this->loadMessages();
                $this->dispatch('scroll-to-bottom');
            } else {
                $this->loadUsers();
            }
        }
    }
    public function handleIncomingMessageEvent($payload)
    {
        // সরাসরি পেলোড পাস করুন, হ্যান্ডলার নিজেই ডিকোড করে নেবে
        $this->handleIncomingMessage($payload);
    }
    public function handleTypingEventReceived($payload)
    {
        // Handle typing event
        $this->isTyping = true;
        $this->dispatch('typing-indicator', ['userId' => $payload['user_id'] ?? null]);

        // Clear previous timeout
        if ($this->typingTimeout) {
            clearTimeout($this->typingTimeout);
        }

        // Set timeout to stop typing indicator
        $this->typingTimeout = setTimeout(function () {
            $this->isTyping = false;
        }, 1500);
    }

    public function shouldPoll()
    {
        if (!$this->selectedUser) {
            return false;
        }

        return Message::where('receiver_id', auth()->id())
            ->where('sender_id', $this->selectedUser)
            ->where('read', 0)
            ->exists();
    }

    public function loadMessagesIfUnread()
    {
        if ($this->shouldPoll()) {
            $this->loadMessages();
        }
    }

    public function loadUsers()
    {
        $authId = auth()->id();

        $users = User::where(function ($query) use ($authId) {
            $query
                ->whereHas('sentMessages', function ($q) use ($authId) {
                    $q->where('receiver_id', $authId);
                })
                ->orWhereHas('receivedMessages', function ($q) use ($authId) {
                    $q->where('sender_id', $authId);
                });
        })
            ->when($this->searchTerm, function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%');
            })
            ->where('id', '!=', $authId)
            ->with([
                'sentMessages' => function ($q) use ($authId) {
                    $q->where('receiver_id', $authId)->latest();
                },
                'receivedMessages' => function ($q) use ($authId) {
                    $q->where('sender_id', $authId)->latest();
                },
            ])
            ->get();

        if ($this->user && $this->user->id != $authId && !$users->contains('id', $this->user->id)) {
            $users->push($this->user);
        }

        $selectedUser = $users->firstWhere('id', $this->selectedUser ?? ($this->user->id ?? null));
        $otherUsers = $users->filter(fn($u) => $u->id !== ($selectedUser->id ?? null));

        $sortedOtherUsers = $otherUsers->sortByDesc(function ($user) {
            $lastSent = optional($user->sentMessages->first())->created_at;
            $lastReceived = optional($user->receivedMessages->first())->created_at;

            return max($lastSent?->timestamp ?? 0, $lastReceived?->timestamp ?? 0);
        });

        $this->users = collect();
        if ($selectedUser) {
            $this->users->push($selectedUser);
        }
        $this->users = $this->users->merge($sortedOtherUsers)->unique('id');

        if (!$this->selectedUser && $this->users->isNotEmpty()) {
            $this->selectUser($this->users->first()->id);
        }
    }

    public function loadOnlineUsers()
    {
        $this->onlineUsers = User::where('id', '!=', auth()->id())
            ->where('role', '!=', 'Admin')
            ->when($this->searchTerm, function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%');
            })
            ->get()
            ->filter(function ($user) {
                return $user->isOnline();
            })
            ->sortBy('name')
            ->values();
    }

    public function markAllAsRead()
    {
        if (!$this->selectedUser) {
            return;
        }

        Message::where('receiver_id', auth()->id())
            ->where('sender_id', $this->selectedUser)
            ->where('read', 0)
            ->update(['read' => 1, 'read_at' => now()]);

        auth()
            ->user()
            ->unreadNotifications()
            ->where('type', NewMessageNotification::class)
            ->where(function ($query) {
                $query->where('data->sender_id', $this->selectedUser)->orWhere('data->url', route('messages', ['slug' => User::find($this->selectedUser)->slug]));
            })
            ->update(['read_at' => now()]);

        $this->dispatch('markedAsRead');
    }

    public function loadMessages()
    {
        if (!$this->selectedUser) {
            return;
        }
        $this->loadUsers();
        $this->dispatch('scroll-to-bottom');
        $this->dispatch('new-message');
        $this->markAllAsRead();
    }

    public function loadMore()
    {
        $this->perPage += 20; // প্রতি ক্লিকে আরও ২০টি করে মেসেজ বাড়বে
    }

    public function with()
    {
        $messagesQuery = collect();

        if ($this->selectedUser) {
            $messagesQuery = Message::where(function ($query) {
                $query
                    ->where(function ($q) {
                        $q->where('sender_id', auth()->id())->where('receiver_id', $this->selectedUser);
                    })
                    ->orWhere(function ($q) {
                        $q->where('sender_id', $this->selectedUser)->where('receiver_id', auth()->id());
                    });
            })
                ->with(['sender', 'receiver', 'parent'])
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);

            // যদি আপনি asc অর্ডারে মেসেজ দেখান, তবে কালেকশনটি reverse করে নিতে পারেন
            $messagesQuery->setCollection($messagesQuery->getCollection()->reverse());
        }

        return [
            'messages' => $messagesQuery,
        ];
    }

    public function sendMessage()
    {

        // চেক করুন আপনি তাকে ব্লক করেছেন কি না বা সে আপনাকে ব্লক করেছে কি না
        $isBlocked = \App\Models\Block::where(function ($q) {
            $q->where('user_id', auth()->id())->where('blocked_user_id', $this->selectedUser);
        })->orWhere(function ($q) {
            $q->where('user_id', $this->selectedUser)->where('blocked_user_id', auth()->id());
        })->exists();

        if ($isBlocked) {
            session()->flash('error', 'You cannot send messages to this user.');
            return;
        }

        $this->validate([
            'messageText' => 'required_without:attachment|string|max:2000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedUser,
            'message' => $this->messageText,
            'parent_id' => $this->replyMessage,
        ]);

        if ($this->attachment) {
            $extension = $this->attachment->getClientOriginalExtension();
            $mime = $this->attachment->getMimeType();

            // ১. অ্যাপের নাম ঠিক যেভাবে আছে সেভাবে নেওয়া (যেমন: MyChat.com থেকে MyChat)
            $rawAppName = config('app.name', 'App');
            $brand = explode('.', $rawAppName)[0]; // শুধু .com বা .net অংশটুকু বাদ দেবে, বাকি নাম যেমন আছে তেমনই থাকবে

            // ২. ফাইল টাইপ প্রেফিক্স (এগুলোও আপনি যেমন খুশি রাখতে পারেন)
            $type = match (true) {
                str_contains($mime, 'image/') => 'Img',
                str_contains($mime, 'video/') => 'Vid',
                str_contains($mime, 'audio/') => 'Aud',
                $extension === 'pdf' => 'Pdf',
                default => 'File',
            };

            // ৩. ফাইনাল ফরম্যাট: MyChat_Img_20260116_F2A1.jpg
            $customFileName = sprintf(
                '%s_%s_%s_%s.%s',
                $brand,                                 // আপনার অ্যাপের অরিজিনাল নাম
                $type,                                  // টাইপ
                now()->format('Ymd_Hi'),               // টাইমস্ট্যাম্প
                strtoupper(bin2hex(random_bytes(2))),   // ইউনিক র‍্যান্ডম কোড
                $extension
            );

            $message->addMedia($this->attachment->getRealPath())
                ->usingFileName($customFileName)
                ->usingName($this->attachment->getClientOriginalName())
                ->toMediaCollection('attachments');
        }
        // রিসেট এবং অন্যান্য কাজ...
        $this->reset(['messageText', 'attachment', 'attachmentPreview', 'replyMessage']);
        $this->loadMessages();

        $receiver = User::find($message->receiver_id);
        $receiver->notify(new NewMessageNotification($message, auth()->user()));

        // sendMessage ফাংশনের ভেতরে শেষে যোগ করুন
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        broadcast(new \App\Events\NewMessageNotification($message));

        $this->dispatch('notificationReceived');
    }
    public function updateMessage()
    {
        $this->validate(
            [
                'editedMessageText' => 'required|string|max:2000',
            ],
            [
                'editedMessageText.required' => 'Message cannot be empty.',
            ],
        );

        $message = Message::find($this->editMessage);

        if ($message && $message->sender_id == auth()->id()) {
            $message->update([
                'message' => $this->editedMessageText,
                'edited_at' => now(),
            ]);

            $this->reset(['editMessage', 'editedMessageText']);
            $this->loadMessages();
        }
    }

    public function deleteMessage($messageId)
    {
        $message = Message::find($messageId);

        if ($message && ($message->sender_id == auth()->id() || $message->receiver_id == auth()->id())) {
            // আলাদা করে Storage::delete করার দরকার নেই, এটা অটো হবে
            $message->delete();
            $this->loadMessages();
        }
    }

    protected function getAttachmentType($file)
    {
        $mime = $file->getMimeType();

        if (str_contains($mime, 'image/')) {
            return 'image';
        } elseif (str_contains($mime, 'video/')) {
            return 'video';
        } elseif (str_contains($mime, 'audio/')) {
            return 'audio';
        } elseif ($mime === 'application/pdf') {
            return 'pdf';
        } elseif (in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'document';
        }

        return 'file';
    }

    public function updatedAttachment()
    {
        if (!$this->attachment) {
            return;
        }

        $this->attachmentPreview = 'file_selected';
    }

    public function removeAttachment()
    {
        $this->reset(['attachment', 'attachmentPreview']);
    }

    public function selectUser($userId)
    {
        $this->selectedUser = $userId;
        $this->selectedUserName = User::find($userId)->name;
        $this->selectedUserModel = User::find($userId);

        $this->checkBlockStatus();
        $this->loadMessages();
        $this->reset(['replyMessage', 'editMessage']);
        $this->dispatch('userSelected');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function closeImageModal()
    {
        $this->openImageModal = false;
    }

    public function setReplyMessage($messageId)
    {
        $this->replyMessage = $messageId;
        $this->editMessage = null;
        $this->dispatch('focus-message-input');
    }

    public function closeReplyMessage()
    {
        $this->replyMessage = null;
    }

    public function setEditMessage($messageId)
    {
        $message = Message::find($messageId);

        if ($message && $message->sender_id == auth()->id()) {
            $this->editMessage = $messageId;
            $this->editedMessageText = $message->message; // Fetch message from database
            $this->replyMessage = null;
            $this->dispatch('focus-message-input');
        }
    }

    public function cancelEdit()
    {
        $this->reset(['editMessage', 'editedMessageText', 'replyMessage']);
    }

    public function focusMessageInput()
    {
        $this->dispatch('focus-message-input');
    }

    public function setImageUrl($url)
    {
        $this->imageUrl = $url;
    }

    public function startTypingIndicator()
    {
        $this->isTyping = true;
        $this->dispatch('start-typing-timeout');
    }

    public function stopTypingIndicator()
    {
        $this->isTyping = false;
    }

    public function checkBlockStatus()
    {
        if ($this->selectedUser) {
            // আপনার দেওয়া blockedUsers রিলেশনশিপ ব্যবহার করে চেক
            $this->isBlockedByMe = auth()->user()->blockedUsers()
                ->where('blocked_user_id', $this->selectedUser)
                ->exists();
        }
    }


    public function blockUser()
    {
        if (!$this->selectedUser)
            return;

        if ($this->isBlockedByMe) {
            // আনব্লক করা
            auth()->user()->blockedUsers()->detach($this->selectedUser);
            session()->flash('success', 'User unblocked successfully.');
        } else {
            // ব্লক করা
            auth()->user()->blockedUsers()->attach($this->selectedUser);
            session()->flash('error', 'User blocked successfully.');
        }

        // স্টেট আপডেট করা
        $this->checkBlockStatus();
        $this->loadUsers();
    }
}; ?>


<section class="flex flex-col h-screen overflow-hidden p-2">


    <!-- Chat Area -->

    @if ($selectedUser)

                @php $selUser = \App\Models\User::find($selectedUser); @endphp

                <div
                    class="flex-none flex items-center justify-between px-4 shadow-sm z-10 border-b pb-2 pt-1 border-zinc-400/25">
                    <div class="flex items-center gap-3">
                        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" size="xs" />
                        <div>
                            <flux:avatar src="{{ $selUser->avatar }}" name="{{ $selectedUserName }}" badge
                                badge:color="{{ $selectedUserModel->isOnline() ? 'green' : 'zinc' }}" color="auto"
                                color:seed="{{ $selectedUserModel->id }}" />
                        </div>
                        <div>
                            <flux:heading>
                                {{ $selectedUserName }}
                            </flux:heading>

                            <flux:text size="sm" color="{{ $selectedUserModel->isOnline() ? 'green' : null }}">
                                @if ($selectedUserModel->isOnline())
                                    Online
                                @else
                                    Offline
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($isTyping)
                            <flux:text size="sm" color="red">
                                typing...
                            </flux:text>
                        @endif
                    </div>
                    @php
                        // সরাসরি রিলেশনশিপ চেক
                        $isBlocked = auth()->user()->blockedUsers->contains($selectedUser);
                    @endphp

                    <div class="flex items-center gap-2">
                        {{-- আপনার আগের কোড... --}}

                       <flux:dropdown>
            <flux:button icon="information-circle" variant="subtle" size="sm"></flux:button>

            <flux:menu class="dark:!bg-zinc-900 !border-0">
                <flux:menu.item icon="user" href="{{ route('users.show', $selUser) }}">
                    Profile Details
                </flux:menu.item>

                {{-- সিম্পল ইফ-এলস --}}
                @if ($isBlockedByMe)
                    <flux:menu.item wire:click="blockUser" icon="lock-open">
                        Unblock Profile
                    </flux:menu.item>
                @else
                    <flux:menu.item wire:click="blockUser" icon="no-symbol" variant="danger">
                        Block Profile
                    </flux:menu.item>
                @endif
            </flux:menu>
        </flux:dropdown>
                    </div>

                </div>
                <!-- Messages -->
                <div x-ref="messagesContainer" class="flex-1 pt-8 pb-4 overflow-y-auto scroll-smooth relative overflow-x-hidden"
                    x-data="{
                        isScrolling: false,
                        scrollPosition: 0,
                        showScrollBottom: false,

                        scrollToBottom(behavior = 'smooth') {
                            this.isScrolling = true;
                            this.$nextTick(() => {
                                const container = this.$refs.messagesContainer;
                                if (!container) return;

                                container.scrollTo({
                                    top: container.scrollHeight,
                                    behavior: behavior
                                });

                                setTimeout(() => {
                                    this.isScrolling = false;
                                    this.showScrollBottom = false;
                                }, 500);
                            });
                        },

                        checkScrollPosition() {
                            const container = this.$refs.messagesContainer;
                            if (!container) return;

                            const threshold = 400;
                            const atBottom = container.scrollHeight - container.scrollTop - container.clientHeight <= threshold;

                            this.showScrollBottom = !atBottom;
                            return atBottom;
                        },

                        scrollToMessage(id) {
                            const element = document.getElementById(id);
                            if (element) {
                                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                element.classList.add('bg-blue-50', 'dark:bg-blue-900', 'bg-opacity-50');
                                setTimeout(() => {
                                    element.classList.remove('bg-blue-50', 'dark:bg-blue-900', 'bg-opacity-50');
                                }, 2000);
                            }
                        },

                        handleScroll() {
                            if (!this.isScrolling) {
                                this.checkScrollPosition();
                            }
                        }
                    }" 
                    x-init="scrollToBottom('auto')" 
                    @scroll.debounce.100ms="handleScroll"
                    @new-message.window="scrollToBottom('smooth')"
                    @scroll-to-bottom.window="scrollToBottom('smooth')">

                    <div x-show="showScrollBottom" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                        class="fixed bottom-30 left-1/2 transform -translate-x-1/2 z-50">
                        <flux:button @click="scrollToBottom()" size="sm" variant="subtle" icon="arrow-down">
                        </flux:button>
                    </div>

                    <div class="space-y-4">
                        @if ($messages->hasMorePages())
                            <div class="flex justify-center my-4">
                                <flux:button wire:click="loadMore" class="!rounded-full" variant="ghost" size="sm">
                                    Load More
                                    Messages
                                </flux:button>
                            </div>
                        @endif
                        @foreach ($messages as $message)
                                    <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}"
                                        wire:key="message-{{ $message->id }}" id="message-{{ $message->id }}">
                                        <div class="max-w-[80%] md:max-w-md lg:max-w-lg w-fit w-full">
                                            <flux:callout
                                                class="!border-0 relative !pb-0 {{ $message->sender_id == auth()->id() ? '!bg-zinc-400/10' : '!bg-zinc-400/25' }}">

                                        @if ($message->parent)
                                            @php
                                                $parent = $message->parent;
                                                $media = $parent->getFirstMedia('attachments');
                                            @endphp

                                            <div class="mb-3 p-2 rounded-lg border-l-4 border-l-green-500 text-xs bg-black/5 dark:bg-white/5 cursor-pointer hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
                                                @click="scrollToMessage('message-{{ $parent->id }}')">

                                                <div class="flex items-center gap-2">

                                                    {{-- মিডিয়া প্রিভিউ সেকশন --}}
                                                    @if($media)
                                                        <div
                                                            class="flex-shrink-0 w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden flex items-center justify-center">
                                                            @if(str_contains($media->mime_type, 'image'))
                                                                <img src="{{ $media->getUrl('thumb') }}" class="w-full h-full object-cover">
                                                            @elseif(str_contains($media->mime_type, 'video'))
                                                                <div class="relative w-full h-full flex items-center justify-center bg-black">
                                                                    <flux:icon name="play-circle" variant="micro" class="text-white size-5 z-10" />
                                                                    {{-- ভিডিওর থাম্বনেইল থাকলে দিতে পারেন, না থাকলে আইকন --}}
                                                                </div>
                                                            @elseif(str_contains($media->mime_type, 'audio'))
                                                                <flux:icon name="musical-note" variant="micro" class="text-gray-500 size-5" />
                                                            @else
                                                                {{-- ডকুমেন্ট বা অন্যান্য ফাইলের জন্য আইকন --}}
                                                                <flux:icon name="document-text" variant="micro" class="text-gray-500 size-5" />
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-bold text-green-600 dark:text-green-400 flex items-center gap-1">
                                                            <span class="opacity-70 font-normal">Replying to</span>
                                                            {{ $parent->sender_id == auth()->id() ? 'You' : ($parent->sender->name ?? 'User') }}
                                                        </div>

                                                        <div class="truncate opacity-80 italic flex items-center gap-1">
                                                            @if($media)
                                                                {{-- ফাইলের ধরন অনুযায়ী টেক্সট দেখানো --}}
                                                                @if(str_contains($media->mime_type, 'image'))
                                                                    <flux:icon name="photo" variant="micro" class="size-3" />
                                                                    <span>Photo</span>
                                                                @elseif(str_contains($media->mime_type, 'video'))
                                                                    <flux:icon name="video-camera" variant="micro" class="size-3" />
                                                                    <span>Video</span>
                                                                @elseif(str_contains($media->mime_type, 'audio'))
                                                                    <flux:icon name="microphone" variant="micro" class="size-3" />
                                                                    <span>Audio</span>
                                                                @else
                                                                    <flux:icon name="paper-clip" variant="micro" class="size-3" />
                                                                    <span>{{ $media->file_name }}</span>
                                                                @endif

                                                                {{-- যদি ফাইলের সাথে কোনো মেসেজ থাকে --}}
                                                                @if($parent->message)
                                                                    <span class="ml-1 text-gray-600 dark:text-gray-400">- {{ $parent->message }}</span>
                                                                @endif
                                                            @else
                                                                {{-- যদি কোনো ফাইল না থাকে শুধু টেক্সট থাকে --}}
                                                                {{ $parent->message ?? '[Message unavailable]' }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                              {{-- ২. মাল্টি-মিডিয়া গ্রিড লেআউট --}}
                                    @if($message->hasMedia('attachments'))
                                        <div class="mb-2 grid gap-1 {{ $message->getMedia('attachments')->count() > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                                            @foreach($message->getMedia('attachments') as $media)
                                                <div class="relative rounded-2xl overflow-hidden bg-black/5 border border-black/5 dark:border-white/10 group/media transition-transform active:scale-95">
                                                    @if(str_contains($media->mime_type, 'image'))
                                                          <flux:media :media="$message->getMedia('attachments')" columns="3" />
                                                    @elseif(str_contains($media->mime_type, 'video'))
                                                        <div class="relative h-48 bg-black flex items-center justify-center cursor-pointer"
                                                             @click="$dispatch('open-lightbox', { type: 'video', url: '{{ $media->getUrl() }}' })">
                                                            <video class="w-full h-full object-cover opacity-80"><source src="{{ $media->getUrl() }}"></video>
                                                            <div class="absolute inset-0 flex items-center justify-center">
                                                                <div class="p-3 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white"><flux:icon name="play" variant="solid" /></div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- ডকুমেন্টের জন্য সুন্দর কার্ড ডিজাইন --}}
                                                        <a href="{{ $media->getUrl() }}" target="_blank" class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800/50">
                                                            <flux:icon name="document-text" class="text-indigo-500" />
                                                            <div class="flex-1 truncate text-[11px] font-medium">{{ $media->file_name }}</div>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                                @if (!empty($message->meta['url']))
                                                    <a href="{{ $message->meta['url'] }}" target="_blank"
                                                        class="block group no-underline">
                                                        <div
                                                            class="mb-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 shadow-sm transition-all group-hover:shadow-md group-hover:border-zinc-300 dark:group-hover:border-zinc-700">
                                                            <div class="flex flex-row">
                                                                @if (isset($message->meta['image']))
                                                                    <div
                                                                        class="w-24 flex-shrink-0 border-r border-zinc-100 dark:border-zinc-800">
                                                                        <img src="{{ $message->meta['image'] }}"
                                                                            class="h-full w-full object-cover" alt="Meta image">
                                                                    </div>
                                                                @endif

                                                                <div class="flex flex-col justify-center p-3 overflow-hidden">
                                                                    @if (isset($message->meta['title']))
                                                                        <flux:heading size="sm" class="font-semibold break-words">
                                                                            {{ $message->meta['title'] }}
                                                                        </flux:heading>
                                                                    @endif

                                                                    @if (isset($message->meta['price']))
                                                                        <div class="mt-1 flex items-center gap-1.5">
                                                                            <flux:badge size="sm" color="indigo"
                                                                                inset="top bottom">
                                                                                ৳ {{ $message->meta['price'] }}
                                                                            </flux:badge>
                                                                            <flux:text size="xs" class="text-zinc-400">
                                                                                টাকা</flux:text>
                                                                        </div>
                                                                    @endif

                                                                    <div class="mt-2 flex items-center gap-1">
                                                                        <flux:text size="xs" class="truncate text-zinc-400 flex-1">
                                                                            {{ parse_url($message->meta['url'], PHP_URL_HOST) }}
                                                                        </flux:text>
                                                                        <flux:icon name="arrow-up-right" variant="micro"
                                                                            class="size-3 text-zinc-300 group-hover:text-zinc-500" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                @endif

                                                @if ($editMessage == $message->id)
                                                    <flux:textarea wire:model="editedMessageText" row="auto" resize="none">
                                                    </flux:textarea>
                                                    <div class="flex justify-end gap-2 mt-2">
                                                        <flux:button wire:click="cancelEdit" size="xs">Cancel
                                                        </flux:button>
                                                        <flux:button wire:click="updateMessage" size="xs">Save
                                                        </flux:button>
                                                    </div>
                                                @elseif($message->message)
                                                    <flux:text class="text-sm leading-snug break-words">
                                                        {!! linkify($message->message) !!}
                                                    </flux:text>
                                                @endif

                                                <div class="flex justify-between items-center mt-2 gap-3">
                                                    <flux:text class="text-xs">
                                                        {{ $message->updated_at?->diffForHumans(['short' => true]) }}
                                                        @if ($message->edited_at)
                                                            (edited)
                                                        @endif
                                                    </flux:text>
                                                    @if ($message->sender_id == auth()->id())
                                                        <div class="flex items-center ml-1">
                                                            @if ($message->read_at)
                                                                <div class="flex items-center">
                                                                    <flux:icon name="check" class="size-3 -mr-1.5 text-blue-500"
                                                                        variant="micro" />
                                                                    <flux:icon name="check" class="size-3 text-blue-500"
                                                                        variant="micro" />
                                                                </div>
                                                            @else
                                                                <flux:icon name="check" class="size-3 text-zinc-400"
                                                                    variant="micro" />
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($message->sender_id == auth()->id())
                                                    <div
                                                        class="absolute bottom-1/2 {{ $message->sender_id == auth()->id() ? 'left-0 -translate-x-8' : 'right-0 translate-x-8' }}">
                                                        <flux:dropdown
                                                            placement="{{ $message->sender_id == auth()->id() ? 'bottom-start' : 'bottom-end' }}">
                                                            <flux:button size="xs" variant="subtle" icon="ellipsis-vertical">
                                                            </flux:button>

                                                            <flux:menu class="dark:!bg-zinc-900 !border-0">
                                                                @if ($message->sender_id == auth()->id())
                                                                    <flux:menu.item icon="pencil"
                                                                        wire:click="setEditMessage({{ $message->id }})">
                                                                        Edit
                                                                    </flux:menu.item>

                                                                    {{-- <flux:menu.separator /> --}}
                                                                @endif

                                                                <flux:menu.item icon="arrow-uturn-left"
                                                                    @click="$wire.setReplyMessage({{ $message->id }})">
                                                                    Reply
                                                                </flux:menu.item>

                                                                @if ($message->sender_id == auth()->id())
                                                                    {{-- <flux:menu.separator /> --}}

                                                                    <flux:menu.item variant="danger" icon="trash"
                                                                        wire:click="deleteMessage({{ $message->id }})"
                                                                        wire:confirm="Are Your sure for delete this message? ({{ $message->message }})">
                                                                        Delete
                                                                    </flux:menu.item>
                                                                @endif
                                                            </flux:menu>
                                                        </flux:dropdown>
                                                    </div>
                                                @else
                                                    <div
                                                        class="absolute bottom-1/2 {{ !$message->sender_id == auth()->id() ? 'left-0 -translate-x-8' : 'right-0 translate-x-8' }}">
                                                        <flux:button icon="arrow-uturn-left" size="xs" variant="subtle"
                                                            wire:click="setReplyMessage({{ $message->id }})">
                                                        </flux:button>
                                                    </div>
                                                @endif
                                            </flux:callout>
                                        </div>
                                    </div>
                        @endforeach
                    </div>
                </div>

                <!-- Message Input -->
                <flux:callout class="w-full flex-none bottom-0 dark:!border-0 relative !p-0 ">

                    @if ($attachment)
                        {{-- <div
                                    class="rounded-xl max-w-2xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 p-3 shadow-sm"> --}}
                        <x:attachements-preview :attachment="$attachment" lazy />
                        {{-- </div> --}}
                    @endif



                    @if ($replyMessage)
                        @php 
                                                                                                                        $replyTo = $messages->firstWhere('id', $replyMessage);
                            $media = $replyTo->getFirstMedia('attachments'); 
                        @endphp

                        <div class="flex justify-between items-center gap-3 rounded-xl border-l-4 border-l-green-500 p-2 bg-zinc-400/10 dark:bg-zinc-800/40 backdrop-blur-sm shadow-sm transition-all">

                            <div class="flex-1 min-w-0 flex items-center gap-3">
                                {{-- Right side preview thumbnail --}}
                                <div class="h-10 w-10 rounded-lg bg-zinc-200 dark:bg-zinc-700 overflow-hidden shrink-0 flex items-center justify-center border border-white/5">
                                    @if($media)
                                        @if(str_contains($media->mime_type, 'image'))
                                            <img src="{{ $media->getUrl('thumb') }}" class="h-full w-full object-cover">
                                        @elseif(str_contains($media->mime_type, 'video'))
                                            <div class="bg-indigo-600 w-full h-full flex items-center justify-center italic font-bold text-[10px] text-white">
                                                <flux:icon name="play-circle" variant="micro" class="size-5" />
                                            </div>
                                        @elseif(str_contains($media->mime_type, 'audio'))
                                            <div class="bg-amber-500 w-full h-full flex items-center justify-center">
                                                <flux:icon name="musical-note" variant="micro" class="size-5 text-white" />
                                            </div>
                                        @else
                                            {{-- Onnano file er jonno dynamic icon --}}
                                            <div class="bg-zinc-500 w-full h-full flex items-center justify-center">
                                                <flux:icon name="document-text" variant="micro" class="size-5 text-white" />
                                            </div>
                                        @endif
                                    @else
                                        {{-- Shudhu text message hole --}}
                                        <div class="bg-green-500/20 w-full h-full flex items-center justify-center text-green-600">
                                            <flux:icon name="chat-bubble-bottom-center-text" variant="micro" class="size-5" />
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <flux:text size="sm" color="green" class="font-bold leading-none">
                                        Replying to: {{ $replyTo->sender_id == auth()->id() ? 'You' : ($replyTo->sender->name ?? 'User') }}
                                    </flux:text>

                                    <div class="flex items-center gap-1.5 mt-1">
                                        {{-- "File" lekhar bodole eikhane Icon --}}
                                     @if($media)
                                        <flux:badge 
                                            size="sm" class="gap-2">
                                            @php
                                                $mime = $media->mime_type;
                                                $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

                                                // টাইপ নির্ধারণ
                                                $displayType = match (true) {
                                                    str_contains($mime, 'image') => 'Photo',
                                                    str_contains($mime, 'video') => 'Video',
                                                    str_contains($mime, 'audio') => 'Audio',
                                                    $extension === 'pdf' => 'PDF',
                                                    default => strtoupper($extension) ?: 'File',
                                                };

                                                // আইকন নির্ধারণ
                                                $icon = match (true) {
                                                    str_contains($mime, 'image') => 'photo',
                                                    str_contains($mime, 'video') => 'video-camera',
                                                    str_contains($mime, 'audio') => 'microphone',
                                                    default => 'paper-clip',
                                                };
                                            @endphp

                                            <flux:icon :name="$icon" variant="micro" class="size-3" />
                                            <span>{{ $displayType }}</span>
                                        </flux:badge>
                                    @endif

                                        <flux:text class="text-sm dark:text-zinc-300 truncate opacity-90">
                                            {{ $replyTo->message ?: 'No message' }}
                                        </flux:text>
                                    </div>
                                </div>
                            </div>

                            {{-- Close Button --}}
                            <div class="shrink-0">
                                <flux:button size="xs" icon="x-mark" variant="subtle" square wire:click="closeReplyMessage" />
                            </div>
                        </div>
                    @endif


                    <div class="flex items-end gap-1">
                        <div class="flex items-center" x-data="{
                            openFile(accept, capture = '') {
                                let input = $refs.fileInput;
                                input.accept = accept;
                                capture ? input.setAttribute('capture', capture) : input.removeAttribute('capture');
                                input.click();
                            }
                        }">
                            <input type="file" wire:model="attachment" class="hidden" x-ref="fileInput" />

                            <flux:dropdown>
                                <flux:button icon="paper-clip" variant="subtle" class="rounded-full" />

                                <flux:menu class="w-48 !bg-zinc-100 dark:!bg-zinc-900 border-0">
                                    <div class="grid grid-cols-3 gap-2">
                                        <flux:menu.item x-on:click="openFile('.pdf,.doc,.docx')"
                                            class="flex-col !items-center gap-2 py-3">
                                            <flux:icon name="document-text" class="text-blue-500" />
                                            <span class="text-[10px]">Document</span>
                                        </flux:menu.item>

                                        <flux:menu.item x-on:click="openFile('image/*', 'environment')"
                                            class="flex-col !items-center gap-2 py-3">
                                            <flux:icon name="camera" class="text-pink-500" />
                                            <span class="text-xs">Camera</span>
                                        </flux:menu.item>

                                        <flux:menu.item x-on:click="openFile('image/*,video/*')"
                                            class="flex-col !items-center gap-2 py-3">
                                            <flux:icon name="photo" class="text-purple-500" />
                                            <span class="text-xs">Gallery</span>
                                        </flux:menu.item>

                                        {{-- <flux:menu.item wire:click="shareLocation"
                                                    class="flex-col !items-center gap-2 py-3">
                                                    <flux:icon name="map-pin" class="text-green-500" />
                                                    <span class="text-xs">Location</span>
                                                </flux:menu.item>

                                                <flux:menu.item wire:click="shareContact"
                                                    class="flex-col !items-center gap-2 py-3">
                                                    <flux:icon name="user-circle" class="text-blue-600" />
                                                    <span class="text-xs">Contact</span>
                                                </flux:menu.item> --}}
                                    </div>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                        <flux:textarea x-ref="textarea" wire:model="messageText" placeholder="একটি বার্তা টাইপ করুন..."
                            autofocus
                            class="!border-0 !bg-transparent w-full !outline-none !resize-none !shadow-none max-h-40 break-all !pb-1 !px-0"
                            resize="none" rows="auto"></flux:textarea>

                        <flux:button wire:click="sendMessage" icon="paper-airplane" size="sm"
                            variant="{{ $messageText || $attachment ? 'ghost' : 'subtle' }}" class="shrink-0 !p-0">
                        </flux:button>
                    </div>
                </flux:callout>
    @else
        <div class="flex-1 flex items-center justify-center p-4 md:p-6">
            <div class="text-center max-w-md">
                <flux:icon name="chat-bubble-left-right" class="mx-auto size-10 md:size-12 text-zinc-400" />

                <flux:heading level="h3" size="lg" class="mt-4">
                    No conversation selected
                </flux:heading>

                <flux:text size="base" class="mt-2">
                    Select a conversation from the sidebar to start chatting.
                </flux:text>

                <div class="mt-6">
                    <flux:modal.trigger name="open-conversations-modal">
                        <flux:button variant="primary">
                            Open Conversations
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </div>
    @endif

    @include('partials.toast')
    <livewire:chat.user-list />

</section>

@push('sidebar')

    <flux:modal.trigger name="open-conversations-modal">
        <div class="mb-2">
                <flux:sidebar.search placeholder="Search..." />
        </div>
    </flux:modal.trigger>
    <div x-data="{ activeTab: 'chats' }" class="w-full">

        <flux:radio.group x-model="activeTab" variant="segmented" class="w-full mt-1">
            <flux:radio label="Chats ({{ count($users) }})" value="chats" />
            <flux:radio label="Online ({{ count($onlineUsers) }})" value="online" />
        </flux:radio.group>

        <div class="relative mt-3 min-h-[380px]">

            <div x-cloak x-show="activeTab === 'chats'" x-transition.opacity.duration.200ms class="absolute inset-0">
                @forelse($users as $user)
                    <a href="{{ route('messages', $user->slug) }}" wire:navigate.hover>
                        <div @class([
                            'flex items-center gap-4 px-4 py-3 transition-colors hover:bg-zinc-400/10 transition-transform duration-200 hover:scale-105 rounded-xl',
                            'bg-zinc-400/10' => request('slug') === $user->slug,
                            '' => request('slug') !== $user->slug,
                        ])>

                            <div>
                                <flux:avatar src="{{ $user->avatar }}" name="{{ $user->name }}" badge
                                    badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}" color="auto"
                                    color:seed="{{ $user->id }}" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <flux:heading class="truncate">
                                        {{ $user->name }}
                                    </flux:heading>

                                    <flux:text size="sm" color="green"
                                        class="text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap ml-2">
                                        @if ($user->sentMessages->first() || $user->receivedMessages->first())
                                                                                                                                                                                            {{ max(
                                                $user->sentMessages->first()->created_at ?? null,
                                                $user->receivedMessages->first()->created_at ?? null,
                                            )?->shortRelativeDiffForHumans() }}
                                        @endif
                                    </flux:text>
                                </div>
                                <div class="flex gap-3 justify-between item-center">
                                    <flux:text size="sm"
                                        class="text-sm text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                        @php
                                            $sent = $user->sentMessages->first();
                                            $recv = $user->receivedMessages->first();
                                        @endphp

                                        @if ($sent && $recv)
                                            @if ($sent->created_at > $recv->created_at)
                                                {{ Str::limit($sent->message, 18) }}
                                            @else
                                                You: {{ Str::limit($recv->message, 18) }}
                                            @endif
                                        @elseif($sent)
                                            {{ Str::limit($sent->message, 18) }}
                                        @elseif($recv)
                                            You: {{ Str::limit($recv->message, 18) }}
                                        @endif
                                    </flux:text>
                                    @if ($user->receivedMessages->where('read', 0)->count() > 0)
                                        <flux:badge color="blue" size="sm" class="!p-0 h-2 w-2" />
                                    @endif
                                </div>
                            </div>



                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-zinc-500 dark:text-zinc-400">
                        No conversations found
                    </div>
                @endforelse
            </div>

            <div x-cloak x-show="activeTab === 'online'" x-transition.opacity.duration.200ms class="absolute inset-0">
                @forelse($onlineUsers as $user)
                    <a href="{{ route('messages', $user->slug) }}" wire:navigate.hover>
                        <div @class([
                            'flex items-center gap-4 px-4 py-3 hover:bg-zinc-400/10 transition-transform duration-200 hover:scale-105 rounded-xl',
                            'bg-zinc-400/10' => request('slug') === $user->slug,
                            '' => request('slug') !== $user->slug,
                        ])>

                            <div>
                                <flux:avatar src="{{ $user->avatar }}" name="{{ $user->name }}" badge
                                    badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}" color="auto"
                                    color:seed="{{ $user->id }}" />
                            </div>

                            <div>
                                <flux:heading>
                                    {{ $user->name }}
                                </flux:heading>
                                <flux:text size="sm" color="green">
                                    Online
                                </flux:text>
                            </div>

                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-zinc-500 dark:text-zinc-400">
                        No online users found
                    </div>
                @endforelse
            </div>

        </div>

    </div>


@endpush


