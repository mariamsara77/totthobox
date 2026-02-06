@auth
    <!-- Desktop User Menu -->
    <flux:dropdown position="top" align="end">
        <flux:profile
            :avatar="auth()->user()->getFirstMediaUrl('avatars', 'thumb') ? auth()->user()->getFirstMediaUrl('avatars', 'thumb') : null"
            class="cursor-pointer" :initials="auth()->user()->initials()">
        </flux:profile>
        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:profile :avatar="auth()->user()->avatar ? auth()->user()->avatar : null"
                            class="cursor-pointer" :initials="auth()->user()->initials()" :icon-trailing="false" />

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun" />
                <flux:radio value="dark" icon="moon" />
                <flux:radio value="system" icon="computer-desktop" />
            </flux:radio.group>
            <flux:menu.separator />

            <flux:menu.radio.group>
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate.hover>{{ __('Settings') }}
                </flux:menu.item>
            </flux:menu.radio.group>
            <flux:menu.radio.group>
                <flux:menu.item :href="route('messages', auth()->user()->slug)" icon="cog" wire:navigate.hover>
                    {{ __('Messages') }}
                </flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            @if (auth()->check() && auth()->user()->hasRole('Admin'))
                {{-- Dashboard --}}
                <flux:menu.radio.group>
                    <flux:menu.item :href="route('admin.dashboard')" icon="home" wire:navigate.hover>
                        {{ __('Dashboard') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />
            @endif


            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
@else
    <flux:tooltip content="Theme Toggle" position="bottom">
        <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
            aria-label="Toggle dark mode" />
    </flux:tooltip>

    <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0! font-bold ">
        <flux:tooltip :content="__('Login')" position="bottom">
            <flux:navlist.item icon="arrow-right-start-on-rectangle" wire:navigate href="{{ route('login') }}">
                {{ __('Login') }}
            </flux:navlist.item>
        </flux:tooltip>
    </flux:navbar>
@endauth