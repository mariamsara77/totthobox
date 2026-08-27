@props([
    'icon' => 'exclamation-triangle',
    'bag' => 'default',
    'message' => null,
    'nested' => true,
    'name' => null,
])

@php
// ১. $errors ভেরিয়েবলটি আছে কি না আগে চেক করে নিচ্ছি
$errorBag = isset($errors) ? $errors->getBag($bag) : new \Illuminate\Support\MessageBag;

$message ??= $name ? $errorBag->first($name) : null;

if ($name && (is_null($message) || $message === '') && filter_var($nested, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false) {
    $message = $errorBag->first($name . '.*');
}

$classes = Flux::classes('text-sm font-medium text-red-600')
    ->add($message ? '' : 'hidden');
@endphp

<div role="alert" aria-live="polite" aria-atomic="true" {{ $attributes->class($classes) }} data-flux-error>
    @if ($message)
        @if ($icon)
            <flux:icon :name="$icon" variant="mini" class="inline" />
        @endif

        {{ $message }}
    @endif
</div>