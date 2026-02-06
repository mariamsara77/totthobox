<!-- Attachment Preview -->
@if ($attachment)
    @php
        $mime = $attachment->getMimeType();
    @endphp

    {{-- Image Preview --}}
    @if (str_contains($mime, 'image'))
        <img src="{{ $attachment->temporaryUrl() }}" alt="Preview" class="w-full rounded-xl max-h-60 object-cover">

        {{-- Video Preview --}}
    @elseif(str_contains($mime, 'video'))
        <video controls class="w-full rounded-xl max-h-40">
            <source src="{{ $attachment->temporaryUrl() }}" type="{{ $mime }}">
        </video>

        {{-- Audio Preview --}}
    @elseif(str_contains($mime, 'audio'))
        <div class="p-4 bg-gray-50 rounded-xl dark:bg-zinc-800">
            <audio controls class="w-full">
                <source src="{{ $attachment->temporaryUrl() }}" type="{{ $mime }}">
            </audio>
        </div>

        {{-- Other Files --}}
    @else
        <div class="p-3 flex items-center gap-3 rounded-xl">
            <flux:icon name="document" variant="solid" color="green" />
            <flux:text class="break-all">
                {{ $attachment->getClientOriginalName() }}
            </flux:text>
            <flux:text size="sm">
                {{ round($attachment->getSize() / 1024, 1) }} KB
            </flux:text>
        </div>
    @endif

    {{-- Remove Button --}}
    <div class="absolute top-2 right-2">
        <flux:button @click="$wire.removeAttachment()" icon="x-mark" size="xs" variant="ghost">
        </flux:button>
    </div>
@endif