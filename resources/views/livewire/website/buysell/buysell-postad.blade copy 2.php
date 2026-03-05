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
use Models\Thana;

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

    // Form Data (Full validation from old code)
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

    // Spatie Media Management
    public $images = [];
    public $existingMedia = [];
    public $tempImage;
    public $primaryMediaId = null;
    public $imagesToDelete = [];

    // UI State (Full filters from old code)
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

            // Load Spatie Media
            $this->existingMedia = $post->getMedia('posts')->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl('thumb'),
                'original_url' => $media->getUrl(),
                'is_primary' => $media->getCustomProperty('is_primary', false),
                'sort_order' => $media->getCustomProperty('sort_order', 0),
            ])->sortBy('sort_order')->values()->toArray();

            // Set primary media ID
            $primary = collect($this->existingMedia)->firstWhere('is_primary', true);
            $this->primaryMediaId = $primary ? $primary['id'] : ($this->existingMedia[0]['id'] ?? null);
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

    // Image Management (Improved from old code)
    public function updatedTempImage()
    {
        $totalImages = count($this->images) + count($this->existingMedia);
        
        if ($this->tempImage && $totalImages < 10) {
            try {
                $this->validate([
                    'tempImage' => 'image|max:5120', // 5MB max
                ], [
                    'tempImage.image' => 'শুধুমাত্র ছবি ফাইল (JPG, PNG, GIF, WEBP) আপলোড করা যাবে।',
                    'tempImage.max' => 'ছবির সাইজ ৫MB এর বেশি হতে পারবে না।',
                ]);

                $this->images[] = $this->tempImage;
                
                // Set as primary if first image
                if ($totalImages === 0) {
                    $this->primaryMediaId = 'temp_0';
                }
                
                $this->tempImage = null;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->tempImage = null;
                session()->flash('image_error', $e->validator->errors()->first('tempImage'));
            }
        } elseif ($totalImages >= 10) {
            session()->flash('image_error', 'আপনি সর্বোচ্চ ১০টি ছবি আপলোড করতে পারবেন।');
            $this->tempImage = null;
        }
    }

    public function removeNewImage($index)
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
        $this->adjustPrimaryMediaId();
    }

    public function removeExistingImage($mediaId)
    {
        $this->imagesToDelete[] = $mediaId;
        $this->existingMedia = collect($this->existingMedia)
            ->where('id', '!=', $mediaId)
            ->values()
            ->toArray();
        $this->adjustPrimaryMediaId();
    }

    private function adjustPrimaryMediaId()
    {
        $totalImages = count($this->images) + count($this->existingMedia);
        
        if ($totalImages === 0) {
            $this->primaryMediaId = null;
            return;
        }

        // Check if current primary still exists
        $primaryExists = false;
        
        if ($this->primaryMediaId && str_starts_with($this->primaryMediaId, 'temp_')) {
            $tempIndex = (int) substr($this->primaryMediaId, 5);
            $primaryExists = isset($this->images[$tempIndex]);
        } elseif ($this->primaryMediaId) {
            $primaryExists = collect($this->existingMedia)->contains('id', $this->primaryMediaId);
        }

        if (!$primaryExists) {
            // Set first available as primary
            if (count($this->existingMedia) > 0) {
                $this->primaryMediaId = $this->existingMedia[0]['id'];
            } elseif (count($this->images) > 0) {
                $this->primaryMediaId = 'temp_0';
            }
        }
    }

    public function setPrimaryImage($identifier)
    {
        $this->primaryMediaId = $identifier;
    }

    private function getTotalImagesCount()
    {
        return count($this->images) + count($this->existingMedia);
    }

    // Form Navigation (Enhanced from old code)
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
            'form.description' => 'nullable|string',
            'form.note' => 'nullable|string',
            'form.price' => 'nullable|numeric|min:0',
            'form.discount_price' => 'nullable|numeric|min:0|lte:form.price',
            'form.condition' => 'required|string',
            'form.currency' => 'required|string|max:3',
            'form.buy_sell_category_id' => 'required|exists:buy_sell_categories,id',
            'form.buy_sell_item_id' => 'nullable|exists:buy_sell_items,id',
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
            1 => ['form.title', 'form.buy_sell_category_id', 'form.condition'],
            2 => ['form.price', 'form.currency', 'form.stock'],
            3 => ['form.phone', 'form.division_id', 'form.district_id', 'form.thana_id', 'form.status'],
        ];

        return array_intersect_key($baseRules, array_flip($stepRules[$this->state['current_step']] ?? []));
    }

    private function getValidationMessages()
    {
        return [
            'form.title.required' => 'পোস্টের শিরোনাম আবশ্যক।',
            'form.title.max' => 'শিরোনাম ২৫৫ অক্ষরের বেশি হতে পারবে না।',
            'form.buy_sell_category_id.required' => 'ক্যাটাগরি নির্বাচন করুন।',
            'form.buy_sell_category_id.exists' => 'নির্বাচিত ক্যাটাগরি সঠিক নয়।',
            'form.condition.required' => 'পণ্যের অবস্থা নির্বাচন করুন।',
            'form.price.required' => 'মূল্য নির্ধারণ করুন।',
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
        $this->validate($this->getFullValidationRules(), $this->getValidationMessages());

        if ($this->getTotalImagesCount() === 0) {
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

    private function getFullValidationRules()
    {
        $rules = [
            'form.title' => 'required|string|max:255',
            'form.description' => 'nullable|string',
            'form.note' => 'nullable|string',
            'form.price' => 'required|numeric|min:0',
            'form.discount_price' => 'nullable|numeric|min:0|lte:form.price',
            'form.condition' => 'required|string',
            'form.currency' => 'required|string|max:3',
            'form.buy_sell_category_id' => 'required|exists:buy_sell_categories,id',
            'form.buy_sell_item_id' => 'nullable|exists:buy_sell_items,id',
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

        return $rules;
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
        // Delete marked images
        $this->deleteMarkedImages($post);

        // Update existing media order and primary status
        $this->updateExistingMedia($post);

        // Upload new images
        $this->uploadNewImages($post);

        // Update images count
        $post->update(['images_count' => $post->getMedia('posts')->count()]);
    }

    private function deleteMarkedImages($post)
    {
        if (!empty($this->imagesToDelete)) {
            $post->getMedia('posts')
                ->whereIn('id', $this->imagesToDelete)
                ->each->delete();
            
            $this->imagesToDelete = [];
        }
    }

    private function updateExistingMedia($post)
    {
        $mediaItems = $post->getMedia('posts');
        
        foreach ($mediaItems as $index => $media) {
            $isPrimary = $media->id == $this->primaryMediaId;
            
            $media->setCustomProperty('sort_order', $index);
            $media->setCustomProperty('is_primary', $isPrimary);
            $media->save();
        }
    }

    private function uploadNewImages($post)
    {
        $startOrder = count($this->existingMedia);
        
        foreach ($this->images as $index => $image) {
            $globalIndex = $startOrder + $index;
            $isPrimary = 'temp_' . $index === $this->primaryMediaId;
            
            $media = $post->addMedia($image->getRealPath())
                ->usingFileName(Str::random(20) . '.' . $image->getClientOriginalExtension())
                ->withCustomProperties([
                    'is_primary' => $isPrimary,
                    'sort_order' => $globalIndex,
                    'alt_text' => $this->form['title'],
                ])
                ->toMediaCollection('posts');
            
            // If this is the primary, update primaryMediaId to the actual media ID
            if ($isPrimary) {
                $this->primaryMediaId = $media->id;
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
            // Delete media files
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
        $this->existingMedia = [];
        $this->tempImage = null;
        $this->primaryMediaId = null;
        $this->imagesToDelete = [];
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

<div class="space-y-6">
    
    
    <!-- Main Content -->
    <div>
        @if ($state['show_create_form'])
            <!-- Create/Edit Form -->
            <div class=" rounded-2xl shadow-xl p-6">
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
                                <div class="space-y-6">
                                    <flux:input 
                                        type="text" 
                                        wire:model="form.title"
                                        placeholder="যেমন: আইফোন ১৩ প্রো ম্যাক্স ২৫৬জিবি" 
                                        label="পণ্যের শিরোনাম *" 
                                        error="{{ $errors->first('form.title') }}"
                                    />

                                    <flux:select 
                                        wire:model="form.buy_sell_category_id" 
                                        label="ক্যাটাগরি *"
                                        error="{{ $errors->first('form.buy_sell_category_id') }}"
                                    >
                                        <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select 
                                        wire:model="form.buy_sell_item_id" 
                                        label="আইটেম টাইপ"
                                        error="{{ $errors->first('form.buy_sell_item_id') }}"
                                    >
                                        <option value="">আইটেম নির্বাচন করুন</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['title'] }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="space-y-6">
                                    <flux:select 
                                        wire:model="form.condition" 
                                        label="অবস্থা *"
                                        error="{{ $errors->first('form.condition') }}"
                                    >
                                        <option value="new">ব্র্যান্ড নিউ</option>
                                        <option value="like_new">নতুনের মত</option>
                                        <option value="used_good">ব্যবহৃত - ভাল</option>
                                        <option value="used_fair">ব্যবহৃত - মোটামুটি</option>
                                        <option value="refurbished">রিফার্বিশড</option>
                                        <option value="for_parts">পার্টস/মেরামতের জন্য</option>
                                    </flux:select>

                                    <flux:textarea 
                                        wire:model="form.description" 
                                        rows="3"
                                        placeholder="আপনার পণ্যটি বিস্তারিত বর্ণনা করুন..." 
                                        label="বিবরণ"
                                        error="{{ $errors->first('form.description') }}"
                                    />

                                    <flux:textarea 
                                        wire:model="form.note" 
                                        rows="2"
                                        placeholder="ক্রেতাদের জন্য বিশেষ নোট..." 
                                        label="অতিরিক্ত নোট"
                                        error="{{ $errors->first('form.note') }}"
                                    />
                                </div>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ছবি সংযুক্ত করুন</h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ bn_num($this->getTotalImagesCount()) }}/১০টি ছবি
                                    </span>
                                </div>

                                <!-- Upload Progress -->
                                <div wire:loading wire:target="tempImage" class="mb-4">
                                    <div x-data="{ progress: 0 }"
                                         x-on:livewire-upload-start="progress = 0"
                                         x-on:livewire-upload-progress.window="progress = $event.detail.progress"
                                         x-on:livewire-upload-finish="progress = 100">
                                        <div class="flex items-center justify-between text-sm font-medium text-blue-600 mb-1">
                                            <div>ছবি আপলোড হচ্ছে...</div>
                                            <div x-text="progress + '%'"></div>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 bg-blue-500 dark:bg-blue-400 transition-all"
                                                 :style="'width: ' + progress + '%'"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                @error('tempImage')
                                    <div class="mb-4">
                                        <flux:error>{{ $message }}</flux:error>
                                    </div>
                                @enderror

                                <!-- Image Grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                    <!-- Existing Images -->
                                    @foreach ($existingMedia as $media)
                                        <div class="relative group aspect-square rounded-xl border-2 
                                            {{ $primaryMediaId === $media['id'] ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-900/50' : 'border-gray-200 dark:border-gray-600' }} 
                                            overflow-hidden bg-gray-50 dark:bg-gray-700 transition-all duration-200">
                                            
                                            <img src="{{ $media['url'] }}" 
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                 alt="Product image">

                                            @if ($primaryMediaId === $media['id'])
                                                <div class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full shadow-sm z-10">
                                                    প্রধান
                                                </div>
                                            @endif

                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                                @if ($primaryMediaId !== $media['id'])
                                                    <button type="button" 
                                                            wire:click="setPrimaryImage({{ $media['id'] }})"
                                                            class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg hover:bg-white dark:hover:bg-gray-700 transition"
                                                            title="প্রধান ছবি করুন">
                                                        <flux:icon name="check" class="w-4 h-4 text-blue-600" />
                                                    </button>
                                                @endif
                                                
                                                <button type="button" 
                                                        wire:click="removeExistingImage({{ $media['id'] }})"
                                                        wire:confirm="আপনি কি এই ছবিটি মুছে ফেলতে চান?"
                                                        class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                                        title="ছবি মুছুন">
                                                    <flux:icon name="trash" class="w-4 h-4 text-red-600" />
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- New Images -->
                                    @foreach ($images as $index => $image)
                                        @php $tempId = 'temp_' . $index; @endphp
                                        <div class="relative group aspect-square rounded-xl border-2 
                                            {{ $primaryMediaId === $tempId ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-900/50' : 'border-gray-200 dark:border-gray-600' }} 
                                            overflow-hidden bg-gray-50 dark:bg-gray-700 transition-all duration-200">
                                            
                                            <img src="{{ $image->temporaryUrl() }}" 
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                 alt="Preview image">

                                            @if ($primaryMediaId === $tempId)
                                                <div class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full shadow-sm z-10">
                                                    প্রধান
                                                </div>
                                            @endif

                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                                @if ($primaryMediaId !== $tempId)
                                                    <button type="button" 
                                                            wire:click="setPrimaryImage('{{ $tempId }}')"
                                                            class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg hover:bg-white dark:hover:bg-gray-700 transition"
                                                            title="প্রধান ছবি করুন">
                                                        <flux:icon name="check" class="w-4 h-4 text-blue-600" />
                                                    </button>
                                                @endif
                                                
                                                <button type="button" 
                                                        wire:click="removeNewImage({{ $index }})"
                                                        class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                                        title="ছবি মুছুন">
                                                    <flux:icon name="trash" class="w-4 h-4 text-red-600" />
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Add More Button -->
                                    @if ($this->getTotalImagesCount() < 10)
                                        <label class="aspect-square rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 
                                                     hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200 
                                                     flex items-center justify-center cursor-pointer 
                                                     bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                            <div class="text-center p-4">
                                                <flux:icon name="plus" class="w-8 h-8 text-gray-400 group-hover:text-blue-500 mx-auto mb-2 transition-colors" />
                                                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium group-hover:text-blue-600 transition-colors">
                                                    ছবি যোগ করুন
                                                </span>
                                            </div>
                                            <input type="file" wire:model="tempImage" accept="image/*" class="hidden">
                                        </label>
                                    @endif
                                </div>
                            </div>

                      
                        </div>
                    @endif

                    <!-- Step 2: Pricing & Stock -->
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
                                            error="{{ $errors->first('form.price') }}"
                                        />

                                        <flux:input 
                                            type="number" 
                                            wire:model="form.discount_price"
                                            placeholder="0.00" 
                                            label="ডিসকাউন্ট মূল্য"
                                            step="0.01"
                                            min="0"
                                            error="{{ $errors->first('form.discount_price') }}"
                                        />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <flux:select 
                                            wire:model="form.currency" 
                                            label="মুদ্রা *"
                                            error="{{ $errors->first('form.currency') }}"
                                        >
                                            <option value="BDT">BDT - ৳</option>
                                            <option value="USD">USD - $</option>
                                        </flux:select>

                                        <flux:input 
                                            type="number" 
                                            wire:model="form.stock" 
                                            min="1"
                                            label="স্টক সংখ্যা *"
                                            error="{{ $errors->first('form.stock') }}"
                                        />
                                    </div>

                                    <flux:input 
                                        type="text" 
                                        wire:model="form.sku"
                                        placeholder="পণ্য আইডেন্টিফায়ার" 
                                        label="SKU (ঐচ্ছিক)"
                                        error="{{ $errors->first('form.sku') }}"
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

                    <!-- Step 3: Contact & Location -->
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
                                        error="{{ $errors->first('form.phone') }}"
                                    />

                                    <flux:input 
                                        type="text" 
                                        wire:model="form.whatsapp" 
                                        placeholder="+৮৮০..."
                                        label="হোয়াটসঅ্যাপ (ঐচ্ছিক)"
                                        error="{{ $errors->first('form.whatsapp') }}"
                                    />

                                    <flux:input 
                                        type="text" 
                                        wire:model="form.imo" 
                                        placeholder="+৮৮০..."
                                        label="IMO (ঐচ্ছিক)"
                                        error="{{ $errors->first('form.imo') }}"
                                    />

                                    <flux:input 
                                        type="email" 
                                        wire:model="form.email"
                                        placeholder="example@example.com" 
                                        label="ইমেইল (ঐচ্ছিক)"
                                        error="{{ $errors->first('form.email') }}"
                                    />

                                    <flux:select 
                                        wire:model="form.status" 
                                        label="পোস্ট স্ট্যাটাস *"
                                        error="{{ $errors->first('form.status') }}"
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
                                        error="{{ $errors->first('form.division_id') }}"
                                    >
                                        <option value="">বিভাগ নির্বাচন করুন</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division['id'] }}">{{ $division['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select 
                                        wire:model.live="form.district_id" 
                                        label="জেলা *"
                                        error="{{ $errors->first('form.district_id') }}"
                                    >
                                        <option value="">জেলা নির্বাচন করুন</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district['id'] }}">{{ $district['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select 
                                        wire:model.live="form.thana_id" 
                                        label="থানা/উপজেলা *"
                                        error="{{ $errors->first('form.thana_id') }}"
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
                                        error="{{ $errors->first('form.address') }}"
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
            <!-- Dashboard View -->
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
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <!-- Section Header -->
                    <div class="mb-6 text-center">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">আমার পোস্টসমূহ</h2>
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
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">পণ্য</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">মূল্য</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">অবস্থা</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">স্ট্যাটাস</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">তারিখ</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">কর্ম</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->posts as $post)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-700">
                                                    @if ($post->getMedia('posts')->count() > 0)
                                                        @php
                                                            $primaryImage = $post->getMedia('posts')->firstWhere('custom_properties.is_primary', true) ?? $post->getMedia('posts')->first();
                                                        @endphp
                                                        <img class="w-12 h-12 object-cover"
                                                             src="{{ $primaryImage?->getUrl('thumb') }}"
                                                             alt="{{ $post->title }}">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center">
                                                            <flux:icon name="photo" class="w-6 h-6 text-gray-400" />
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        {{ Str::limit($post->title, 35) }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $post->category?->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                @if ($post->discount_price)
                                                    <span class="text-red-600 dark:text-red-400">৳{{ bn_num(number_format($post->discount_price)) }}</span>
                                                    <span class="text-gray-400 text-sm line-through ml-2">৳{{ bn_num(number_format($post->price)) }}</span>
                                                @else
                                                    ৳{{ bn_num(number_format($post->price)) }}
                                                @endif
                                            </div>
                                            @if ($post->is_negotiable)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 mt-1">
                                                    আলোচনা সাপেক্ষ
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-white capitalize">
                                                {{ str_replace('_', ' ', $post->condition) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusConfig = [
                                                    'draft' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-800 dark:text-gray-200', 'label' => 'ড্রাফট'],
                                                    'published' => ['bg' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-800 dark:text-green-200', 'label' => 'পাবলিশড'],
                                                    'pending' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/50', 'text' => 'text-yellow-800 dark:text-yellow-200', 'label' => 'পেন্ডিং'],
                                                    'rejected' => ['bg' => 'bg-red-100 dark:bg-red-900/50', 'text' => 'text-red-800 dark:text-red-200', 'label' => 'রিজেক্টেড'],
                                                ];
                                                $config = $statusConfig[$post->status] ?? $statusConfig['draft'];
                                            @endphp
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                                {{ $config['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ bn_num($post->created_at->format('d M, Y')) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <button wire:click="editPost({{ $post->id }})"
                                                        class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200 border border-blue-200 dark:border-blue-800"
                                                        title="এডিট করুন">
                                                    <flux:icon name="pencil" class="w-4 h-4" />
                                                </button>
                                                <button wire:click="deletePost({{ $post->id }})"
                                                        wire:confirm="আপনি কি নিশ্চিত যে আপনি এই পোস্টটি মুছে ফেলতে চান?"
                                                        class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200 border border-red-200 dark:border-red-800"
                                                        title="মুছুন">
                                                    <flux:icon name="trash" class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="text-gray-400 dark:text-gray-500 mb-4">
                                                <flux:icon name="document-text" class="w-16 h-16 mx-auto" />
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">কোন পোস্ট পাওয়া যায়নি</h3>
                                            <p class="text-gray-500 dark:text-gray-400 mb-4">নতুন পোস্ট তৈরি করে শুরু করুন</p>
                                            <flux:button wire:click="createPost" size="sm">
                                                নতুন পোস্ট তৈরি করুন
                                            </flux:button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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