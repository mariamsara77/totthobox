{{-- resources/views/components/global-notifications.blade.php --}}
@php
    $notifications = [
        'success' => ['icon' => 'check-circle', 'color' => 'text-green-500'],
        'error' => ['icon' => 'x-circle', 'color' => 'text-red-500'],
        'status' => ['icon' => 'info', 'color' => 'text-blue-500'],
        'message' => ['icon' => 'bell', 'color' => 'text-zinc-500'],
    ];
@endphp

<div
    class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-sm px-4 flex flex-col gap-3 pointer-events-none">

    {{-- 1. Session Notifications (Flash Messages) --}}
    @foreach ($notifications as $type => $settings)
        @if (session()->has($type))
            <div class="pointer-events-auto">
                <x-notification-card :icon="$settings['icon']" :color="$settings['color']" :message="session($type)"
                    :type="$type" />
            </div>
        @endif
    @endforeach

    {{-- 2. Validation Error for 'messageText' --}}
    @error('messageText')
        <div class="pointer-events-auto">
            <x-notification-card icon="exclamation-triangle" color="text-red-500" :message="$message" type="error" />
        </div>
    @enderror

    {{-- 3. Dynamic Variable Notification ($errorMessage) --}}
    @if (isset($errorMessage) && $errorMessage)
        <div class="pointer-events-auto">
            <x-notification-card icon="x-circle" color="text-red-500" :message="$errorMessage" type="error" />
        </div>
    @endif
</div>