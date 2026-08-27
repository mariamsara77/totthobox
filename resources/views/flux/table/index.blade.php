{{-- resources/views/components/flux/table/index.blade.php --}}
@props(['paginate' => null])

<div class="w-full">
    {{-- আপনার আগের টেবিল কন্টেইনার কোড এখানে থাকবে... --}}
    <div {{ $attributes->except('paginate')->class(['overflow-auto border ...']) }}>
        <table class="w-full text-left text-sm border-collapse">
            {{ $slot }}
        </table>
    </div>

    {{-- পেজিনেশন অংশ --}}
    @if ($paginate)
        <div class="mt-4">
            <flux:pagination :paginator="$paginate" />
        </div>
    @endif
</div>
