<?php

use Livewire\Volt\Component;
use App\Models\SignCategory;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public $slug;
    public $signs;
    public $category;

    public function mount($slug)
    {
        $this->slug = $slug;

        // ১ ঘণ্টার জন্য ক্যাশ করে রাখা হচ্ছে
        // ক্যাশ কি (Key) হিসেবে স্লাগ ব্যবহার করা হয়েছে যাতে আলাদা আলাদা ক্যাটাগরি আলাদাভাবে ক্যাশ হয়
        $data = Cache::remember("sign_page_{$slug}", 3600, function () use ($slug) {
            $cat = SignCategory::with(['signs'])->where('slug', $slug)->first();

            if (!$cat)
                return null;

            return [
                'category' => $cat,
                'signs' => $cat->signs
            ];
        });

        // যদি ক্যাশ বা ডাটাবেজে না পাওয়া যায় তবে ৪-০-৪ এরর দিবে
        if (!$data) {
            abort(404);
        }

        $this->category = $data['category'];
        $this->signs = $data['signs'];
    }
};
?>
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
            <div
                class="border border-zinc-400/25 rounded-xl p-3 flex flex-col items-center text-center transition-transform duration-200 hover:scale-105 dark:hover:bg-zinc-700">
                <img src="{{ Storage::url($sign->image) }}" alt="{{ $sign->name_en }}"
                    class="h-24 object-contain rounded-md">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $sign->name_bn }}</h3>
                <p class="text-md text-gray-500 dark:text-gray-400">{{ $sign->name_en }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $sign->description_bn }}</p>
            </div>
        @endforeach
    </div>


</section>