<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\BuySellPost;
use App\Models\BuySellCategory;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;

new class extends Component {
    use WithPagination;

    // Search and filters
    public $search = '';
    public $filterCategory = '';
    public $filterCondition = '';
    public $filterDivision = '';
    public $filterDistrict = '';
    public $filterThana = '';
    public $minPrice = '';
    public $maxPrice = '';
    public $showNegotiableOnly = false;

    // Category slug parameter
    public $categorySlug;
    public $category;

    // Data
    public $categories = [];
    public $divisions = [];
    public $districts = [];
    public $thanas = [];

    // Contact modal
    public $showContactModal = false;
    public $selectedPost = null;
    public $message = '';

    public $activePostId = null;

    public $currentCategory = null;

    public function mount($categorySlug = null)
    {
        $this->categorySlug = $categorySlug;
        $this->categories = BuySellCategory::select('id', 'name', 'slug')->get();
        $this->divisions = Division::select('id', 'name')->get();

        // Auto-select category if slug is provided
        if ($categorySlug && $categorySlug !== 'null') {
            $category = BuySellCategory::where('slug', $categorySlug)->first();
            if ($category) {
                $this->filterCategory = $category->id;
            }
        }
        $this->category = $category;
    }

    public function updatedFilterDivision($value)
    {
        $this->districts = $value ? District::where('division_id', $value)->get() : [];
        $this->filterDistrict = '';
        $this->filterThana = '';
        $this->thanas = [];
    }

    public function updatedFilterDistrict($value)
    {
        $this->thanas = $value ? Thana::where('district_id', $value)->get() : [];
        $this->filterThana = '';
    }

    public function showContact($postId)
    {
        // If same post clicked again → close it
        if ($this->activePostId === $postId) {
            $this->activePostId = null;
            $this->showContactModal = false;
            return;
        }

        // Otherwise open a new one
        $this->selectedPost = BuySellPost::with(['user', 'division', 'district', 'thana', 'images'])->find($postId);

        $this->activePostId = $postId;
        $this->showContactModal = true;
    }

    public function closeContactModal()
    {
        $this->showContactModal = false;
        $this->selectedPost = null;
        $this->message = '';
    }

    public function getPostsProperty()
    {
        return BuySellPost::where('status', 'published')
            ->when($this->categorySlug && $this->categorySlug !== 'null', function ($query) {
                $category = BuySellCategory::where('slug', $this->categorySlug)->first();
                if ($category) {
                    $query->where('buy_sell_category_id', $category->id);
                }
            })
            ->when($this->filterCategory, function ($query, $categoryId) {
                $query->where('buy_sell_category_id', $categoryId);
            })
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->filterCondition, function ($query, $condition) {
                $query->where('condition', $condition);
            })
            ->when($this->filterDivision, function ($query, $divisionId) {
                $query->where('division_id', $divisionId);
            })
            ->when($this->filterDistrict, function ($query, $districtId) {
                $query->where('district_id', $districtId);
            })
            ->when($this->filterThana, function ($query, $thanaId) {
                $query->where('thana_id', $thanaId);
            })
            ->when($this->minPrice, function ($query, $minPrice) {
                $query->where(function ($q) use ($minPrice) {
                    $q->where('discount_price', '>=', $minPrice)->orWhere('price', '>=', $minPrice);
                });
            })
            ->when($this->maxPrice, function ($query, $maxPrice) {
                $query->where(function ($q) use ($maxPrice) {
                    $q->where('discount_price', '<=', $maxPrice)->orWhere('price', '<=', $maxPrice);
                });
            })
            ->when($this->showNegotiableOnly, function ($query) {
                $query->where('is_negotiable', true);
            })
            ->with(['category', 'item', 'images', 'division', 'district', 'thana'])
            ->orderBy('published_at', 'desc')
            ->paginate(12);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterCondition', 'filterDivision', 'filterDistrict', 'filterThana', 'minPrice', 'maxPrice', 'showNegotiableOnly']);

        // Keep the category filter if category slug is provided
        if ($this->categorySlug && $this->categorySlug !== 'null') {
            $category = BuySellCategory::where('slug', $this->categorySlug)->first();
            if ($category) {
                $this->filterCategory = $category->id;
            }
        }

        $this->districts = [];
        $this->thanas = [];
    }

    // Quick Message Functionality
    public function sendQuickMessage($type, $postId, $receiverId)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $post = BuySellPost::with('images')->findOrFail($postId);
        $receiver = User::findOrFail($receiverId);
        $messages = [
            'want_buy' => 'আমি আপনার পণ্যটি কিনতে আগ্রহী',
            'price' => 'পণ্যটির দাম কত জানাবেন?',
            'contact' => 'পণ্যটি নিয়ে কথা বলতে চাই',
            'availability' => 'পণ্যটি এখনো অ্যাভেইলেবল আছে?',
            'meetup' => 'পণ্যটি দেখতে কোথায় আসব?',
        ];
        // Prepare product info
        $productUrl = route('buysell.buysell-single', $post->slug);
        $productImg = asset($post->images->where('is_primary', true)->first()?->path ?? $post->images->first()?->path);

        // Create message
        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $receiver->id,
            'message' => $messages[$type],
            'meta' => [
                'title' => $post->title,
                'price' => $post->price,
                'url' => $productUrl,
                'image' => $productImg,
            ],
        ]);

        // Close the contact modal
        $this->closeContactModal();

        // Redirect to messages page with the specific user
        return $this->redirect(route('messages', $receiver->slug), navigate: true);
    }
};
?>
<section class="">
    <div class="max-w-2xl mx-auto">
        <!-- Category Page Header -->
        <div class="text-center mb-6">
            <flux:heading size="xl" class="mb-2">
                @if ($category)
                    {{ $category->name }} এর জিনিসপত্র
                @else
                    সব ক্যাটাগরি খুঁজুন
                @endif
            </flux:heading>

            <flux:text>
                আপনার **পছন্দের জিনিস** খুঁজে নিতে তালিকা থেকে ব্রাউজ করুন অথবা নতুন কিছু যোগ করুন।
            </flux:text>
        </div>

        <!-- Header Section -->
        {{-- @include('partials.buy-sell.header') --}}

        <!-- Filters Section -->
        @include('partials.buy-sell.filters')

        <!-- Results Section -->
        @include('partials.buy-sell.results')
    </div>

    <!-- Contact Modal -->
    {{-- @include('partials.buy-sell.contact-modal') --}}

    <!-- Scripts -->
    @include('partials.buy-sell.scripts')
</section>
