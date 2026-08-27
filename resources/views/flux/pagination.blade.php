@props(['paginator'])

@if ($paginator->hasPages())
    <div>


        {{-- এটি আপনার কাস্টম ডিজাইন করা টেইলউইন্ড প্যাগিনেশন ফাইলটি লোড করবে --}}
        {{ $paginator->links() }}


    </div>
@endif