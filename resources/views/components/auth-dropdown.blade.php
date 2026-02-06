<!-- resources/views/components/auth-dropdown.blade.php -->


<!-- User Info -->
<flux:menu.radio.group>
    <div class="p-0 text-sm font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:profile
                :avatar="auth()->user()->getFirstMediaUrl('avatars', 'thumb') ? auth()->user()->getFirstMediaUrl('avatars', 'thumb') : null"
                :initials="auth()->user()->initials()" :icon-trailing="false" />
            <div class="grid flex-1 text-start text-sm leading-tight text-zinc-700 dark:text-zinc-100">
                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
            </div>
        </div>
    </div>
</flux:menu.radio.group>
<flux:menu.separator />

<!-- Theme Toggle -->
<flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
    <flux:radio value="light" icon="sun" />
    <flux:radio value="dark" icon="moon" />
    <flux:radio value="system" icon="computer-desktop" />
</flux:radio.group>

<flux:menu.separator />

<!-- Settings -->
<flux:menu.radio.group>
    <flux:menu.item :href="route('settings.profile')" icon="user" wire:navigate.hover>
        {{ __('প্রোফাইল') }}
    </flux:menu.item>

    <flux:modal.trigger name="settings">
        <flux:menu.item icon="cog">
            {{ __('সেটিংস') }}
        </flux:menu.item>

    </flux:modal.trigger>

    <flux:menu.item :href="route('messages', auth()->user()->slug)" icon="chat-bubble-left-right" wire:navigate.hover>
        {{ __('মেসেজ') }}
    </flux:menu.item>
</flux:menu.radio.group>

<flux:menu.separator />
@can('')

@endcan
{{-- Dashboard --}}
<flux:menu.radio.group>
    <flux:menu.item :href="route('admin.dashboard')" icon="home" wire:navigate.hover>
        {{ __('Dashboard') }}
    </flux:menu.item>
</flux:menu.radio.group>

<flux:menu.separator />

<!-- Logout -->
<form method="POST" action="{{ route('logout') }}" class="w-full">
    @csrf
    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
        {{ __('Log Out') }}
    </flux:menu.item>
</form>