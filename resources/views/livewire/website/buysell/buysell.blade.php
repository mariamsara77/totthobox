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

    public function mount()
    {
        $this->categories = BuySellCategory::select('id', 'name')->get();
        $this->divisions = Division::select('id', 'name')->get();
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

    // public function sendMessage()
    // {
    //     $this->validate([
    //         'message' => 'required|string|min:10|max:500',
    //     ]);

    //     $newMessage = Message::create([
    //         'from_id' => auth()->id(),
    //         'to_id' => $this->selectedPost->user_id,
    //         'message' => $this->message,
    //         'buy_sell_post_id' => $this->selectedPost->id,
    //     ]);

    //     broadcast(new MessageSent($newMessage))->toOthers();

    //     session()->flash('message', 'আপনার বার্তাটি সফলভাবে পাঠানো হয়েছে! বিক্রেতা শীঘ্রই আপনার সাথে যোগাযোগ করবেন.');

    //     $this->message = '';
    // }

    public function getPostsProperty()
    {
        return BuySellPost::where('status', 'published')
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->filterCategory, function ($query, $categoryId) {
                $query->where('buy_sell_category_id', $categoryId);
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
            ->with(['category', 'item', 'division', 'district', 'thana'])
            ->orderBy('published_at', 'desc')
            ->paginate(12);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterCondition', 'filterDivision', 'filterDistrict', 'filterThana', 'minPrice', 'maxPrice', 'showNegotiableOnly']);
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
        <!-- Header Section -->
        @include('partials.buy-sell.header')

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
