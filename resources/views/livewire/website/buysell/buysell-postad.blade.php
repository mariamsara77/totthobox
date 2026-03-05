<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\BuySellPost;
use App\Models\BuySellCategory;
use App\Models\BuySellItem;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Illuminate\Support\Arr;

new class extends Component {
    use WithPagination, WithFileUploads;

    // State Management
    public $state = [
        'current_step' => 1,
        'show_create_form' => false,
        'editing_post_id' => null,
        'total_steps' => 3,
    ];

    public $perPage = 10;

    // Form Data
    public $form = [
        'title' => '',
        'description' => '',
        'note' => '',
        'price' => '',
        'discount_price' => '',
        'condition' => 'used_good',
        'currency' => 'BDT',
        'is_negotiable' => false,
        'buy_sell_category_id' => '',
        'buy_sell_item_id' => '',
        'status' => 'draft',
        'phone' => '',
        'whatsapp' => '',
        'imo' => '',
        'email' => '',
        'division_id' => '',
        'district_id' => '',
        'thana_id' => '',
        'address' => '',
        'stock' => 1,
        'sku' => '',
    ];

    // Spatie Media Management (Intro BD Pattern)
    public $images = []; // নতুন এবং পুরনো সব ইমেজ একসাথে
    public $tempImage;
    public $primaryImageIndex = 0;

    // UI State
    public $filters = [
        'search' => '',
        'status' => '',
        'category' => '',
    ];

    // Cached Data
    public $categories = [];
    public $items = [];
    public $divisions = [];
    public $districts = [];
    public $thanas = [];

    protected $queryString = [
        'filters.search' => ['except' => '', 'as' => 'search'],
        'filters.status' => ['except' => ''],
        'filters.category' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        $this->loadInitialData();

        if ($this->state['editing_post_id']) {
            $this->loadPostData($this->state['editing_post_id']);
        }
    }

    private function loadInitialData()
    {
        $this->categories = BuySellCategory::select('id', 'name')->get()->toArray();
        $this->items = BuySellItem::select('id', 'title')->get()->toArray();
        $this->divisions = Division::select('id', 'name')->get()->toArray();
    }

    public function loadPostData($postId)
    {
        $post = BuySellPost::with(['category', 'item'])->where('user_id', auth()->id())->find($postId);

        if ($post) {
            $this->form = array_merge($this->form, $post->only(array_keys($this->form)));
            $this->loadLocationData($post->division_id, $post->district_id);

            // Intro BD Pattern: সব ইমেজ একসাথে images array তে
            $this->images = $post->getMedia('posts')->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl('thumb'),
                'original_url' => $media->getUrl(),
                'is_primary' => $media->getCustomProperty('is_primary', false),
                'sort_order' => $media->getCustomProperty('sort_order', 0),
                'is_existing' => true // ডাটাবেজের ইমেজ চেনার জন্য
            ])->sortBy('sort_order')->values()->toArray();

            // Primary image index set করা
            $primaryIndex = collect($this->images)->search(fn($img) => $img['is_primary']);
            $this->primaryImageIndex = $primaryIndex !== false ? $primaryIndex : 0;
        }
    }

    private function loadLocationData($divisionId = null, $districtId = null)
    {
        if ($divisionId) {
            $this->districts = District::where('division_id', $divisionId)->get()->toArray();
        }
        if ($districtId) {
            $this->thanas = Thana::where('district_id', $districtId)->get()->toArray();
        }
    }

    // Location Handlers
    public function updatedFormDivisionId($value)
    {
        $this->districts = $value ? District::where('division_id', $value)->get()->toArray() : [];
        $this->form['district_id'] = '';
        $this->form['thana_id'] = '';
        $this->thanas = [];
    }

    public function updatedFormDistrictId($value)
    {
        $this->thanas = $value ? Thana::where('district_id', $value)->get()->toArray() : [];
        $this->form['thana_id'] = '';
    }

    // Image Management (Intro BD Pattern)
    public function updatedTempImage()
    {
        if (count($this->images) >= 10) {
            session()->flash('image_error', 'আপনি সর্বোচ্চ ১০টি ছবি আপলোড করতে পারবেন।');
            $this->tempImage = null;
            return;
        }

        if ($this->tempImage) {
            try {
                $this->validate([
                    'tempImage' => 'image|max:5120',
                ], [
                    'tempImage.image' => 'শুধুমাত্র ছবি ফাইল (JPG, PNG, GIF, WEBP) আপলোড করা যাবে।',
                    'tempImage.max' => 'ছবির সাইজ ৫MB এর বেশি হতে পারবে না।',
                ]);

                // Intro BD Pattern: সরাসরি images array তে যোগ
                $this->images[] = $this->tempImage;
                $this->tempImage = null;

            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->tempImage = null;
                session()->flash('image_error', $e->validator->errors()->first('tempImage'));
            }
        }
    }

    // Intro BD Pattern: removeImage method (একটাই method সব জন্য)
   public function removeImage($propertyName, $index)
    {
        $file = $this->{$propertyName}[$index] ?? null;

        if (!$file)
            return;

        // যদি এটি ডাটাবেজের ইমেজ হয়, তবে মিডিয়া লাইব্রেরি থেকে ডিলিট করো
        if (is_array($file) && isset($file['is_existing'])) {
            $intro = BuySellPost::withTrashed()->findOrFail($this->introBdId);
            $intro->deleteMedia($file['id']);
        }

        // অ্যারে থেকে রিমুভ করা
        unset($this->{$propertyName}[$index]);
        $this->{$propertyName} = array_values($this->{$propertyName});
    }

    public function setPrimaryImage($index)
    {
        if ($index < count($this->images)) {
            $this->primaryImageIndex = $index;
        }
    }

    private function getTotalImagesCount()
    {
        return count($this->images);
    }

    // Form Navigation
    public function nextStep()
    {
        $this->validateCurrentStep();
        
        if ($this->state['current_step'] < $this->state['total_steps']) {
            $this->state['current_step']++;
        }
    }

    public function prevStep()
    {
        if ($this->state['current_step'] > 1) {
            $this->state['current_step']--;
        }
    }

    private function validateCurrentStep()
    {
        $rules = $this->getStepValidationRules();
        $messages = $this->getValidationMessages();
        
        if (!empty($rules)) {
            $this->validate($rules, $messages);
        }
    }

   private function getStepValidationRules()
{
    $baseRules = [
        'form.title' => 'required|string|max:255',
        'form.description' => 'required|string',
        'form.note' => 'nullable|string',
        'form.price' => 'required|numeric|min:0',
        'form.discount_price' => 'nullable|numeric|min:0|lte:form.price',
        'form.condition' => 'required|string',
        'form.currency' => 'required|string|max:3',
        'form.buy_sell_category_id' => 'required|exists:buy_sell_categories,id',
        'form.buy_sell_item_id' => 'required|exists:buy_sell_items,id',
        'form.phone' => 'required|string|max:20',
        'form.whatsapp' => 'nullable|string|max:20',
        'form.imo' => 'nullable|string|max:20',
        'form.email' => 'nullable|email',
        'form.status' => 'required|string|in:draft,published,pending,rejected',
        'form.division_id' => 'required|exists:divisions,id',
        'form.district_id' => 'required|exists:districts,id',
        'form.thana_id' => 'required|exists:thanas,id',
        'form.address' => 'nullable|string|max:500',
        'form.stock' => 'required|integer|min:1',
        'form.sku' => 'nullable|string|max:100|unique:buy_sell_posts,sku,' . $this->state['editing_post_id'],
    ];

    $stepRules = [
        1 => ['form.title', 'form.buy_sell_category_id', 'form.buy_sell_item_id', 'form.condition', 'form.description', 'form.note'],
        2 => ['form.price', 'form.discount_price', 'form.currency', 'form.stock', 'form.sku', 'form.is_negotiable'],
        3 => ['form.phone', 'form.whatsapp', 'form.imo', 'form.email', 'form.division_id', 'form.district_id', 'form.thana_id', 'form.address', 'form.status'],
    ];

    // ফিল্টার করে শুধু নির্দিষ্ট স্টেপের রুলসগুলো নেওয়া
    $currentStepRules = $stepRules[$this->state['current_step']] ?? [];
    
   return Arr::only($baseRules, $stepRules[$this->state['current_step']] ?? []);
}
    private function getValidationMessages()
    {
        return [
            'form.title.required' => 'পোস্টের শিরোনাম আবশ্যক।',
            'form.title.max' => 'শিরোনাম ২৫৫ অক্ষরের বেশি হতে পারবে না।',
            'form.buy_sell_category_id.required' => 'ক্যাটাগরি নির্বাচন করুন।',
            'form.buy_sell_category_id.exists' => 'নির্বাচিত ক্যাটাগরি সঠিক নয়।',
            'form.buy_sell_item_id.required' => 'আইটেম নির্বাচন করুন।',
            'form.buy_sell_item_id.exists' => 'নির্বাচিত আইটেম সঠিক নয়।',
            'form.condition.required' => 'পণ্যের অবস্থা নির্বাচন করুন।',
            'form.description.required' => 'বিবরণ আবশ্যক।',
            'form.price.numeric' => 'দাম সংখ্যা হতে হবে।',
            'form.price.min' => 'দাম ০ এর কম হতে পারবে না।',
            'form.discount_price.lte' => 'ডিসকাউন্ট মূল্য মূল্যের চেয়ে বেশি হতে পারবে না।',
            'form.currency.required' => 'মুদ্রা নির্বাচন করুন।',
            'form.stock.required' => 'স্টক সংখ্যা আবশ্যক।',
            'form.stock.integer' => 'স্টক পূর্ণ সংখ্যা হতে হবে।',
            'form.stock.min' => 'স্টক কমপক্ষে ১ হতে হবে।',
            'form.phone.required' => 'ফোন নম্বর আবশ্যক।',
            'form.division_id.required' => 'বিভাগ নির্বাচন করুন।',
            'form.district_id.required' => 'জেলা নির্বাচন করুন।',
            'form.thana_id.required' => 'থানা নির্বাচন করুন।',
            'form.status.required' => 'স্ট্যাটাস নির্বাচন করুন।',
            'form.status.in' => 'স্ট্যাটাস সঠিক নয়।',
            'form.sku.unique' => 'এই SKU ইতিমধ্যে ব্যবহৃত হয়েছে।',
        ];
    }

    // CRUD Operations
    public function save()
    {
        // Full validation
        $this->validate($this->getStepValidationRulesForAll(), $this->getValidationMessages());

        if (count($this->images) === 0) {
            session()->flash('error', 'কমপক্ষে একটি ছবি আপলোড করতে হবে।');
            return;
        }

        DB::beginTransaction();

        try {
            $post = $this->persistPost();
            $this->handleMediaUploads($post);

            DB::commit();

            $this->handleSuccessResponse($post);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'ত্রুটি: ' . $e->getMessage());
        }
    }

    private function getStepValidationRulesForAll()
    {
        return [
            'form.title' => 'required|string|max:255',
            'form.description' => 'required|string',
            'form.note' => 'nullable|string',
            'form.price' => 'required|numeric|min:0',
            'form.discount_price' => 'nullable|numeric|min:0|lte:form.price',
            'form.condition' => 'required|string',
            'form.currency' => 'required|string|max:3',
            'form.buy_sell_category_id' => 'required|exists:buy_sell_categories,id',
            'form.buy_sell_item_id' => 'required|exists:buy_sell_items,id',
            'form.phone' => 'required|string|max:20',
            'form.whatsapp' => 'nullable|string|max:20',
            'form.imo' => 'nullable|string|max:20',
            'form.email' => 'nullable|email',
            'form.status' => 'required|string|in:draft,published,pending,rejected',
            'form.division_id' => 'required|exists:divisions,id',
            'form.district_id' => 'required|exists:districts,id',
            'form.thana_id' => 'required|exists:thanas,id',
            'form.address' => 'nullable|string|max:500',
            'form.stock' => 'required|integer|min:1',
            'form.sku' => 'nullable|string|max:100|unique:buy_sell_posts,sku,' . $this->state['editing_post_id'],
        ];
    }

    private function persistPost()
    {
        $postData = array_merge($this->form, [
            'slug' => $this->generateSlug(),
            'user_id' => auth()->id(),
            'is_active' => true,
            'published_at' => $this->shouldPublish() ? now() : null,
        ]);

        return BuySellPost::updateOrCreate(
            ['id' => $this->state['editing_post_id']],
            $postData
        );
    }

    private function generateSlug()
    {
        if ($this->state['editing_post_id']) {
            return BuySellPost::find($this->state['editing_post_id'])->slug;
        }
        
        $slug = Str::slug($this->form['title']);
        $count = BuySellPost::where('slug', 'like', $slug . '%')->count();
        
        return $count ? $slug . '-' . ($count + 1) : $slug;
    }

    private function shouldPublish()
    {
        return $this->form['status'] === 'published' && !$this->state['editing_post_id'];
    }

    private function handleMediaUploads($post)
    {
        // পুরনো সব media এর primary status false করে দিন (আপডেটের জন্য)
        if ($this->state['editing_post_id']) {
            foreach ($post->getMedia('posts') as $media) {
                $media->setCustomProperty('is_primary', false);
                $media->save();
            }
        }

        // Intro BD Pattern: সব ইমেজ একসাথে handle
        foreach ($this->images as $index => $image) {
            $isPrimary = ($index === $this->primaryImageIndex);

            // যদি ইমেজ array হয় এবং existing=true থাকে, তাহলে সেটা আপডেট করুন
            if (is_array($image) && isset($image['is_existing'])) {
                $media = $post->getMedia('posts')->where('id', $image['id'])->first();
                if ($media) {
                    $media->setCustomProperty('is_primary', $isPrimary);
                    $media->setCustomProperty('sort_order', $index);
                    $media->setCustomProperty('alt_text', $this->form['title']);
                    $media->save();
                }
            } 
            // নতুন আপলোড করা ফাইল
            elseif ($image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $post->addMedia($image->getRealPath())
                    ->usingFileName(Str::random(20) . '.' . $image->getClientOriginalExtension())
                    ->withCustomProperties([
                        'is_primary' => $isPrimary,
                        'sort_order' => $index,
                        'alt_text' => $this->form['title'],
                    ])
                    ->toMediaCollection('posts');
            }
        }
    }

    private function handleSuccessResponse($post)
    {
        session()->flash('success', $this->state['editing_post_id'] 
            ? 'পোস্ট সফলভাবে আপডেট করা হয়েছে।' 
            : 'পোস্ট সফলভাবে তৈরি করা হয়েছে।');
        
        $this->cancel();
    }

    // UI Actions
    public function createPost()
    {
        $this->resetForm();
        $this->state['show_create_form'] = true;
        $this->state['editing_post_id'] = null;
    }

    public function editPost($id)
    {
        $this->resetForm();
        $this->state['editing_post_id'] = $id;
        $this->state['show_create_form'] = true;
        $this->loadPostData($id);
    }

    public function deletePost($id)
    {
        $post = BuySellPost::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if ($post) {
            $post->clearMediaCollection('posts');
            $post->delete();
            
            session()->flash('success', 'পোস্ট সফলভাবে মুছে ফেলা হয়েছে।');
        } else {
            session()->flash('error', 'পোস্ট পাওয়া যায়নি বা মুছার অনুমতি নেই।');
        }
    }

    public function cancel()
    {
        $this->resetForm();
        $this->state['show_create_form'] = false;
        $this->state['current_step'] = 1;
    }

    private function resetForm()
    {
        $this->form = [
            'title' => '',
            'description' => '',
            'note' => '',
            'price' => '',
            'discount_price' => '',
            'condition' => 'used_good',
            'currency' => 'BDT',
            'is_negotiable' => false,
            'buy_sell_category_id' => '',
            'buy_sell_item_id' => '',
            'status' => 'draft',
            'phone' => '',
            'whatsapp' => '',
            'imo' => '',
            'email' => '',
            'division_id' => '',
            'district_id' => '',
            'thana_id' => '',
            'address' => '',
            'stock' => 1,
            'sku' => '',
        ];

        $this->images = [];
        $this->tempImage = null;
        $this->primaryImageIndex = 0;
        $this->districts = [];
        $this->thanas = [];
        $this->state['current_step'] = 1;
        $this->state['editing_post_id'] = null;
    }

    // Data Fetching
    public function getPostsProperty()
    {
        return BuySellPost::where('user_id', auth()->id())
            ->when($this->filters['search'], function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($this->filters['status'], function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->filters['category'], function ($query, $categoryId) {
                $query->where('buy_sell_category_id', $categoryId);
            })
            ->with(['category', 'item'])
            ->withCount('media as images_count')
            ->latest()
            ->paginate($this->perPage === 'all' ? 1000000 : $this->perPage);
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
};
?>

<div class="max-w-2xl mx-auto space-y-4">
    
    
    <!-- Main Content -->
    <div>
        @if ($state['show_create_form'])
            <!-- Create/Edit Form -->
            <div class="">
                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-center">
                        @foreach (range(1, $state['total_steps']) as $step)
                            <div class="flex items-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300
                                        {{ $state['current_step'] >= $step
                                            ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/25'
                                            : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400' }}
                                        {{ $state['current_step'] == $step ? 'ring-4 ring-blue-100 dark:ring-blue-900/30 scale-110' : '' }}">
                                        <span class="font-bold text-sm">{{ $step }}</span>
                                    </div>
                                    <span class="text-xs mt-2 font-medium text-center max-w-[80px] leading-tight px-1
                                        {{ $state['current_step'] >= $step ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                                        @switch($step)
                                            @case(1) পণ্যের বিবরণ @break
                                            @case(2) মূল্য ও স্টক @break
                                            @case(3) যোগাযোগ ও অবস্থান @break
                                        @endswitch
                                    </span>
                                </div>

                                @if ($step < $state['total_steps'])
                                    <div class="w-16 lg:w-24 h-1 mx-2 mt-6 rounded-full transition-all duration-500
                                        {{ $state['current_step'] > $step
                                            ? 'bg-gradient-to-r from-blue-500 to-blue-400'
                                            : 'bg-gray-200 dark:bg-gray-600' }}">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <form wire:submit.prevent="save" class="space-y-8">
                    <!-- Step 1: Product Details -->
                    @if ($state['current_step'] === 1)
                        <div class="space-y-6">
                            <div class="text-center">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">পণ্যের তথ্য</h2>
                                <p class="text-gray-600 dark:text-gray-400 mt-2">আপনার পণ্য সম্পর্কে বিস্তারিত তথ্য প্রদান করুন</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                              
                                    <flux:input 
                                        type="text" 
                                        wire:model="form.title"
                                        placeholder="যেমন: আইফোন ১৩ প্রো ম্যাক্স ২৫৬জিবি" 
                                        label="পণ্যের শিরোনাম *" 
                                    />

                                    <flux:select 
                                        wire:model="form.buy_sell_category_id" 
                                        label="ক্যাটাগরি *"
                                    >
                                        <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select 
                                        wire:model="form.buy_sell_item_id" 
                                        label="আইটেম টাইপ"
                                    >
                                        <option value="">আইটেম নির্বাচন করুন</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['title'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select 
                                        wire:model="form.condition" 
                                        label="অবস্থা *"
                                    >
                                        <option value="new">ব্র্যান্ড নিউ</option>
                                        <option value="like_new">নতুনের মত</option>
                                        <option value="used_good">ব্যবহৃত - ভাল</option>
                                        <option value="used_fair">ব্যবহৃত - মোটামুটি</option>
                                        <option value="refurbished">রিফার্বিশড</option>
                                        <option value="for_parts">পার্টস/মেরামতের জন্য</option>
                                    </flux:select>                                   
                            </div>
                            <div class="space-y-6">
                                <flux:editor 
                                    wire:model="form.description" 
                                    placeholder="আপনার পণ্যটি বিস্তারিত বর্ণনা করুন..." 
                                    label="বিবরণ"
                                />

                                    <flux:textarea 
                                        wire:model="form.note" 
                                        rows="auto" resize="none"
                                        placeholder="ক্রেতাদের জন্য বিশেষ নোট..." 
                                        label="অতিরিক্ত নোট"
                                    />
                            </div>
                            <div>
                                {{-- Media Upload --}}
                                <flux:file-upload wire:model.live="images" multiple />
                            </div>
                         
                        </div>
                    @endif

                    <!-- Step 2: Pricing & Stock (Same as before) -->
                    @if ($state['current_step'] === 2)
                        <div class="space-y-6">
                            <div class="text-center">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">মূল্য ও ইনভেন্টরি</h2>
                                <p class="text-gray-600 dark:text-gray-400 mt-2">আপনার পণ্যের মূল্য ও স্টক তথ্য প্রদান করুন</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <div class="lg:col-span-2 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <flux:input 
                                            type="number" 
                                            wire:model="form.price" 
                                            placeholder="0.00"
                                            label="মূল্য *"
                                            step="0.01"
                                            min="0"
                                        />

                                        <flux:input 
                                            type="number" 
                                            wire:model="form.discount_price"
                                            placeholder="0.00" 
                                            label="ডিসকাউন্ট মূল্য"
                                            step="0.01"
                                            min="0"
                                        />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <flux:select 
                                            wire:model="form.currency" 
                                            label="মুদ্রা *"
                                        >
                                            <option value="BDT">BDT - ৳</option>
                                            <option value="USD">USD - $</option>
                                        </flux:select>

                                        <flux:input 
                                            type="number" 
                                            wire:model="form.stock" 
                                            min="1"
                                            label="স্টক সংখ্যা *"
                                        />
                                    </div>

                                    <flux:input 
                                        type="text" 
                                        wire:model="form.sku"
                                        placeholder="পণ্য আইডেন্টিফায়ার" 
                                        label="SKU (ঐচ্ছিক)"
                                    />

                                    <div class="flex items-center gap-2">
                                        <flux:checkbox wire:model="form.is_negotiable" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">মূল্য আলোচনা সাপেক্ষ</span>
                                    </div>
                                </div>

                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6">
                                    <h3 class="font-bold text-blue-900 dark:text-blue-100 mb-4 text-lg">মূল্য নির্ধারণ টিপস</h3>
                                    <ul class="space-y-3 text-sm text-blue-800 dark:text-blue-200">
                                        <li class="flex items-start gap-3">
                                            <span class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">১</span>
                                            প্রতিযোগিতামূলক মূল্যের জন্য অনুরূপ আইটেম গবেষণা করুন
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <span class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">২</span>
                                            মূল্য নির্ধারণের সময় আইটেমের অবস্থা বিবেচনা করুন
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <span class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">৩</span>
                                            আলোচনা সাপেক্ষ মূল্য সাধারণত বেশি ক্রেতা আকর্ষণ করে
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Step 3: Contact & Location (Same as before) -->
                    @if ($state['current_step'] === 3)
                        <div class="space-y-6">
                            <div class="text-center">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">যোগাযোগ ও অবস্থান</h2>
                                <p class="text-gray-600 dark:text-gray-400 mt-2">ক্রেতাদের সাথে যোগাযোগের তথ্য প্রদান করুন</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">যোগাযোগ তথ্য</h3>

                                    <flux:input 
                                        type="text" 
                                        wire:model="form.phone" 
                                        placeholder="+৮৮০..."
                                        label="ফোন নম্বর *"
                                    />

                                    <flux:input 
                                        type="text" 
                                        wire:model="form.whatsapp" 
                                        placeholder="+৮৮০..."
                                        label="হোয়াটসঅ্যাপ (ঐচ্ছিক)"
                                    />

                                    <flux:input 
                                        type="text" 
                                        wire:model="form.imo" 
                                        placeholder="+৮৮০..."
                                        label="IMO (ঐচ্ছিক)"
                                    />

                                    <flux:input 
                                        type="email" 
                                        wire:model="form.email"
                                        placeholder="example@example.com" 
                                        label="ইমেইল (ঐচ্ছিক)"
                                    />

                                    <flux:select 
                                        wire:model="form.status" 
                                        label="পোস্ট স্ট্যাটাস *"
                                    >
                                        <option value="draft">খসড়া হিসেবে সংরক্ষণ করুন</option>
                                        <option value="published">এখনই প্রকাশ করুন</option>
                                    </flux:select>
                                </div>

                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">অবস্থান</h3>

                                    <flux:select 
                                        wire:model.live="form.division_id" 
                                        label="বিভাগ *"
                                    >
                                        <option value="">বিভাগ নির্বাচন করুন</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division['id'] }}">{{ $division['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select 
                                        wire:model.live="form.district_id" 
                                        label="জেলা *"
                                    >
                                        <option value="">জেলা নির্বাচন করুন</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district['id'] }}">{{ $district['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select 
                                        wire:model.live="form.thana_id" 
                                        label="থানা/উপজেলা *"
                                    >
                                        <option value="">থানা নির্বাচন করুন</option>
                                        @foreach ($thanas as $thana)
                                            <option value="{{ $thana['id'] }}">{{ $thana['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:textarea 
                                        wire:model="form.address" 
                                        placeholder="বাড়ি নং, রোড নং, এলাকা..."
                                        label="বিস্তারিত ঠিকানা" 
                                        rows="3"
                                    />
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            @if ($state['current_step'] === 1)
                                <flux:button type="button" wire:click="cancel" variant="outline">
                                    বাতিল করুন
                                </flux:button>
                            @else
                                <flux:button type="button" wire:click="prevStep" variant="outline">
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="chevron-left" class="w-4 h-4" />
                                        <span>পূর্ববর্তী</span>
                                    </div>
                                </flux:button>
                            @endif
                        </div>

                        <div class="flex gap-3">
                            @if ($state['current_step'] < $state['total_steps'])
                                <flux:button type="button" wire:click="nextStep" variant="primary">
                                    <div class="flex items-center gap-2">
                                        <span>পরবর্তী</span>
                                        <flux:icon name="chevron-right" class="w-4 h-4" />
                                    </div>
                                </flux:button>
                            @else
                                <flux:button type="submit" variant="primary" class="bg-green-600 hover:bg-green-700">
                                    {{ $state['editing_post_id'] ? 'পোস্ট আপডেট করুন' : 'পণ্য প্রকাশ করুন' }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- Dashboard View (Same as before, but fixed media collection name) -->
            <div class="space-y-6">
                <!-- Header -->
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">আপনার জিনিসপত্র বিক্রি করুন</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        সাশ্রয়ী দামে নতুন ও ব্যবহৃত জিনিস কিনুন/বিক্রি করুন।
                    </p>
                </div>

                <!-- Create Button -->
                <div class="text-center">
                    <flux:button wire:click="createPost" size="sm">
                        <div class="flex items-center gap-2">
                            <flux:icon name="plus" class="w-5 h-5" />
                            <span>নতুন পোস্ট তৈরি করুন</span>
                        </div>
                    </flux:button>
                </div>

                <!-- Posts Section -->
                <div class="">
                    <!-- Section Header -->
                    <div class="mb-6 text-center">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">আপনার পোস্টসমূহ</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                            আপনার তালিকাভুক্ত আইটেমগুলি পরিচালনা করুন
                        </p>
                        <div class="flex items-center justify-center text-gray-500 mt-2">
                            <flux:icon name="information-circle" class="w-4 h-4 mr-1" />
                            <span>মোট {{ bn_num($this->posts->total()) }} টি পোস্ট</span>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <flux:input 
                                    wire:model.live.debounce.300ms="filters.search" 
                                    placeholder="আপনার পোস্ট খুঁজুন..."
                                >
                                    <x-slot name="iconTrailing">
                                        <flux:icon.search class="w-4 h-4 text-gray-400" />
                                    </x-slot>
                                </flux:input>
                            </div>
                            
                            <div class="w-40">
                                <flux:select wire:model.live="filters.status">
                                    <option value="">সব স্ট্যাটাস</option>
                                    <option value="draft">খসড়া</option>
                                    <option value="published">প্রকাশিত</option>
                                    <option value="pending">বিচারাধীন</option>
                                </flux:select>
                            </div>
                            
                            <div class="w-40">
                                <flux:select wire:model.live="filters.category">
                                    <option value="">সব ক্যাটাগরি</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                    @endforeach
                                </flux:select>
                            </div>

                            <div class="w-40">
                                <flux:select wire:model.live="perPage">
                                    <option value="10">১০ প্রতি পৃষ্ঠা</option>
                                    <option value="25">২৫ প্রতি পৃষ্ঠা</option>
                                    <option value="50">৫০ প্রতি পৃষ্ঠা</option>
                                    <option value="100">১০০ প্রতি পৃষ্ঠা</option>
                                    <option value="all">সব দেখুন</option>
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    <!-- Posts Table -->
                    <div class="overflow-x-auto">
                      {{-- Table --}}
<flux:table :paginate="$this->posts">
    <flux:table.columns>
        <flux:table.column>পণ্য</flux:table.column>
        <flux:table.column sortable>মূল্য</flux:table.column>
        <flux:table.column>অবস্থা</flux:table.column>
        <flux:table.column>স্ট্যাটাস</flux:table.column>
        <flux:table.column>তারিখ</flux:table.column>
        <flux:table.column align="end">কর্ম</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @forelse ($this->posts as $post)
            <flux:table.row :key="$post->id">
                {{-- মিডিয়া/পণ্য কলাম --}}
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        @php $images = $post->getMedia('posts'); @endphp
                        
                        <flux:avatar.group>
                            @foreach($images->take(2) as $media)
                                <flux:avatar src="{{ $media->getUrl('thumb') }}" />
                            @endforeach
                            
                            @if($images->count() > 2)
                                <flux:avatar initials="+{{ $images->count() - 2 }}" />
                            @endif
                            
                            @if($images->count() === 0)
                                <flux:avatar icon="photo" />
                            @endif
                        </flux:avatar.group>

                        <div class="flex flex-col">
                            <span class="text-sm font-medium">{{ Str::limit($post->title, 30) }}</span>
                            <span class="text-xs text-zinc-500">{{ $post->category?->name }}</span>
                        </div>
                    </div>
                </flux:table.cell>

                {{-- মূল্য --}}
                <flux:table.cell>
                    <div class="font-medium">
                        @if ($post->discount_price)
                            <span class="text-red-500">৳{{ bn_num(number_format($post->discount_price)) }}</span>
                            <span class="text-xs text-zinc-400 line-through ml-1">৳{{ bn_num(number_format($post->price)) }}</span>
                        @else
                            ৳{{ bn_num(number_format($post->price)) }}
                        @endif
                    </div>
                </flux:table.cell>

                {{-- অবস্থা --}}
                <flux:table.cell>
                    <span class="capitalize text-zinc-600 dark:text-zinc-400">
                        {{ str_replace('_', ' ', $post->condition) }}
                    </span>
                </flux:table.cell>

                {{-- স্ট্যাটাস --}}
                <flux:table.cell>
                    @php
                        $statusConfig = [
                            'draft' => ['color' => 'zinc', 'label' => 'ড্রাফট'],
                            'published' => ['color' => 'green', 'label' => 'পাবলিশড'],
                            'pending' => ['color' => 'yellow', 'label' => 'পেন্ডিং'],
                            'rejected' => ['color' => 'red', 'label' => 'রিজেক্টেড'],
                        ];
                        $config = $statusConfig[$post->status] ?? $statusConfig['draft'];
                    @endphp
                    <flux:badge size="sm" :color="$config['color']" inset="top bottom">
                        {{ $config['label'] }}
                    </flux:badge>
                </flux:table.cell>

                {{-- তারিখ --}}
                <flux:table.cell class="text-zinc-500">
                    {{ bn_num($post->created_at->format('d M, Y')) }}
                </flux:table.cell>

                {{-- একশন --}}
                <flux:table.cell align="end">
                    <div class="flex justify-end gap-1">
                        <flux:button variant="ghost" size="sm" icon="pencil-square" 
                            wire:click="editPost({{ $post->id }})" />
                        
                        <flux:button variant="danger" size="sm" icon="trash"
                            wire:confirm="আপনি কি নিশ্চিত?" 
                            wire:click="deletePost({{ $post->id }})" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="text-center py-12 text-zinc-400">
                    <div class="flex flex-col items-center">
                        <flux:icon name="document-text" class="w-10 h-10 mb-2 opacity-50" />
                        <p>কোন রেকর্ড পাওয়া যায়নি।</p>
                        <flux:button size="sm" wire:click="createPost">নতুন পোস্ট তৈরি করুন</flux:button>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table.rows>
</flux:table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $this->posts->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>