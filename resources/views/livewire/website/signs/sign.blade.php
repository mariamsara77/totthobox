<?php

use Livewire\Volt\Component;
use Livewire\Volt\Volt;
use App\Models\SignCategory;
use App\Models\Sign;

new class extends Component 
{
    public $slug;
    public $signs;
    public $category;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->category = SignCategory::where('slug', $slug)->firstOrFail();
        $this->signs = Sign::where('sign_category_id', $this->category->id)->get();
    }

};
?>

<section class="max-w-7xl mx-auto transition-colors duration-300">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white leading-tight">
            {{ $category->title }}
        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
            এই ক্যাটাগরির অন্তর্ভুক্ত সকল চিহ্নের তালিকা।
        </p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
        @foreach($signs as $sign)
        <div class="border border-zinc-400/25 rounded-xl p-3 flex flex-col items-center text-center transition-transform duration-200 hover:scale-105 dark:hover:bg-zinc-700">
            <img src="{{ Storage::url($sign->image) }}" alt="{{ $sign->name_en }}" class="h-24 object-contain rounded-md">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $sign->name_bn }}</h3>
            <p class="text-md text-gray-500 dark:text-gray-400">{{ $sign->name_en }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $sign->description_bn }}</p>
        </div>
        @endforeach
    </div>


</section>
