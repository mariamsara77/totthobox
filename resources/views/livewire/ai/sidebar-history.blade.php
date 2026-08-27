<?php

use App\Models\ChatSession;
use Livewire\Attributes\Url;
use Livewire\Component;
/**
 * Pathkouri AI Tutor — Sidebar Chat History Component
 *
 * Groups sessions by subject and shows class badge.
 * Handles session creation events and deletion.
 */
new class extends Component {
    #[Url(as: 'uuid')]
    public string $currentUuid = '';

    public string $newlyCreatedUuid = '';

    protected $listeners = ['session-created' => 'handleSessionCreated'];

    public function mount(): void
    {
        $this->currentUuid = request()->route('uuid') ?? '';
    }

    public function handleSessionCreated(string $uuid): void
    {
        $this->currentUuid = $uuid;
        $this->newlyCreatedUuid = $uuid;
    }

    public function getSessionsProperty()
    {
        return ChatSession::where('user_id', auth()->id())
            ->has('messages')
            ->latest()
            ->get();
    }

    public function deleteSession(int $id): void
    {
        $session = ChatSession::where('user_id', auth()->id())->findOrFail($id);
        $isCurrent = $this->currentUuid === $session->uuid;

        $session->delete();

        if ($isCurrent) {
            $this->redirect(route('ai.chat.show'), navigate: true);
        }
    }
}; ?>

<flux:sidebar.nav class="overflow-hidden p-2">

    {{-- New chat button --}}
    <div class="mb-3 px-1">
        <flux:button href="{{ route('ai.chat.show') }}" wire:navigate size="sm" variant="ghost" icon="plus" class="w-full justify-start text-xs text-emerald-600 dark:text-emerald-400
                   hover:bg-emerald-50 dark:hover:bg-emerald-950/30
                   rounded-xl">
            নতুন প্রশ্ন শুরু করো
        </flux:button>
    </div>

    {{-- Section label --}}
    @if ($this->sessions->isNotEmpty())
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 px-2 mb-2">
        আগের প্রশ্নগুলো
    </p>
    @endif

    {{-- Session list --}}
    @forelse ($this->sessions as $session)
    @php
    $isNew = $newlyCreatedUuid === $session->uuid;
    @endphp

    <div wire:key="sidebar-item-{{ $session->uuid }}" x-data="{ isNew: @js($isNew), show: @js(!$isNew) }"
        x-init="if (isNew) { setTimeout(() => show = true, 80) }" x-show="show"
        x-transition:enter=" ease-in duration-[1500ms]" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" class="mb-2">
        <flux:sidebar.item href="{{ route('ai.chat.show', $session->uuid) }}" wire:navigate
            :current="$currentUuid === $session->uuid" class="group relative rounded-xl py-2 pr-2">
            <div class="flex flex-col min-w-0 flex-1 gap-0.5">
                <span class="truncate text-sm leading-snug">{{ $session->title }}</span>

                {{-- Subject & class badges --}}
                {{-- @if ($session->subject || $session->student_class)
                    <div class="flex items-center gap-1 flex-wrap">
                        @if ($session->student_class)
                        <span
                            class="text-[9px] px-1.5 py-0.5 rounded-md
                                                                                                     bg-emerald-100 dark:bg-emerald-900/40
                                                                                                     text-emerald-600 dark:text-emerald-400
                                                                                                     font-medium leading-none">
                            ক্লাস {{ $session->student_class }}
                </span>
                @endif
                @if ($session->subject)
                <span
                    class="text-[9px] px-1.5 py-0.5 rounded-md
                                                                                                     bg-teal-100 dark:bg-teal-900/40
                                                                                                     text-teal-600 dark:text-teal-400
                                                                                                     font-medium leading-none truncate max-w-[80px]">
                    {{ $session->subject }}
                </span>
                @endif
            </div>
            @endif --}}
    </div>

    {{-- Delete button --}}
    <div
        class="absolute right-1 top-0 bottom-0 flex items-center
                                                                                    opacity-0 group-hover:opacity-100 ">
        <flux:button variant="ghost" size="xs" icon="trash" wire:click.stop="deleteSession({{ $session->id }})"
            wire:confirm="এই প্রশ্নটি মুছে ফেলতে চাও?" class="text-zinc-400 hover:text-zinc-400/25" />
    </div>
    </flux:sidebar.item>
    </div>

    @empty
    <div class="flex flex-col items-center justify-center py-8 gap-2 opacity-50">
        <span class="text-2xl">📚</span>
        <p class="text-xs text-zinc-400 text-center">
            এখনো কোনো প্রশ্ন করোনি।<br>শুরু করো!
        </p>
    </div>
    @endforelse

</flux:sidebar.nav>