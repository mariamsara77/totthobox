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

    // Spatie Media Management
    public $images = []; // নতুন সিলেক্ট করা ইমেজ
    public $existingMedia = []; // এডিট মোডে পুরনো ইমেজ দেখানোর জন্য
    public $tempImage;
    public $primaryMediaId = null; // Spatie Media ID or Temp index

    // UI & Data State
    public $filters = ['search' => '', 'status' => '', 'category' => ''];
    public $categories = [], $items = [], $divisions = [], $districts = [], $thanas = [];

    protected $queryString = [
        'filters.search' => ['except' => '', 'as' => 'search'],
        'filters.status' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount() {
        $this->loadInitialData();
    }

    private function loadInitialData() {
        $this->categories = BuySellCategory::select('id', 'name')->get()->toArray();
        $this->items = BuySellItem::select('id', 'title')->get()->toArray();
        $this->divisions = Division::select('id', 'name')->get()->toArray();
    }

    public function editPost($id) {
        $this->resetForm();
        $this->state['editing_post_id'] = $id;
        $this->state['show_create_form'] = true;
        
        $post = BuySellPost::with(['media'])->where('user_id', auth()->id())->findOrFail($id);
        
        $this->form = array_merge($this->form, $post->only(array_keys($this->form)));
        $this->loadLocationData($post->division_id, $post->district_id);

        // Spatie Media Load
        $this->existingMedia = $post->getMedia('posts')->map(fn($media) => [
            'id' => $media->id,
            'url' => $media->getUrl('thumb'),
            'is_primary' => $media->getCustomProperty('is_primary', false)
        ])->toArray();
    }

    public function updatedTempImage() {
        $this->validate(['tempImage' => 'image|max:2048']);
        if (count($this->images) + count($this->existingMedia) < 10) {
            $this->images[] = $this->tempImage;
        }
        $this->tempImage = null;
    }

    public function removeNewImage($index) {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function deleteExistingMedia($mediaId) {
        // এডিট মোডে সরাসরি মিডিয়া ডিলিট
        $post = BuySellPost::find($this->state['editing_post_id']);
        $media = $post->getMedia('posts')->where('id', $mediaId)->first();
        if ($media) $media->delete();
        
        $this->existingMedia = collect($this->existingMedia)->where('id', '!=', $mediaId)->toArray();
    }

    public function save() {
        $this->validate($this->getStepValidationRules(), $this->getValidationMessages());

        if ((count($this->images) + count($this->existingMedia)) === 0) {
            session()->flash('error', 'কমপক্ষে একটি ছবি আপলোড করুন।');
            return;
        }

        DB::beginTransaction();
        try {
            $post = BuySellPost::updateOrCreate(
                ['id' => $this->state['editing_post_id']],
                array_merge($this->form, [
                    'user_id' => auth()->id(),
                    'is_active' => true,
                    'published_at' => ($this->form['status'] === 'published' && !$this->state['editing_post_id']) ? now() : null,
                ])
            );

            // Spatie Media Upload
            foreach ($this->images as $index => $image) {
                $post->addMedia($image->getRealPath())
                    ->usingFileName(Str::random(10) . '.' . $image->getClientOriginalExtension())
                    ->withCustomProperties(['is_primary' => ($index === 0 && count($this->existingMedia) === 0)])
                    ->toMediaCollection('posts');
            }

            // Update Image Count
            $post->update(['images_count' => $post->getMedia('posts')->count()]);

            DB::commit();
            
            session()->flash('success', 'পোস্ট সফলভাবে সংরক্ষিত হয়েছে।');
            $this->cancel();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'ভুল হয়েছে: ' . $e->getMessage());
        }
    }

    // Location & Steps Logic
    public function updatedFormDivisionId($value) {
        $this->districts = $value ? District::where('division_id', $value)->get()->toArray() : [];
        $this->form['district_id'] = '';
        $this->thanas = [];
    }

    public function updatedFormDistrictId($value) {
        $this->thanas = $value ? Thana::where('district_id', $value)->get()->toArray() : [];
        $this->form['thana_id'] = '';
    }

    private function loadLocationData($divId, $distId) {
        if ($divId) $this->districts = District::where('division_id', $divId)->get()->toArray();
        if ($distId) $this->thanas = Thana::where('district_id', $distId)->get()->toArray();
    }

    public function nextStep() {
        $this->validate($this->getStepValidationRules());
        $this->state['current_step']++;
    }

    public function prevStep() {
        $this->state['current_step']--;
    }

    private function getStepValidationRules() {
        $rules = [
            1 => [
                'form.title' => 'required|max:255',
                'form.buy_sell_category_id' => 'required',
                'form.condition' => 'required',
            ],
            2 => [
                'form.price' => 'required|numeric',
                'form.stock' => 'required|integer',
            ],
            3 => [
                'form.phone' => 'required',
                'form.division_id' => 'required',
            ]
        ];
        return $rules[$this->state['current_step']] ?? [];
    }

    private function getValidationMessages() {
        return ['form.title.required' => 'শিরোনাম দিন', 'form.price.numeric' => 'সঠিক দাম লিখুন'];
    }

    public function cancel() {
        $this->resetForm();
        $this->state['show_create_form'] = false;
        $this->state['current_step'] = 1;
    }

    private function resetForm() {
        $this->reset(['form', 'images', 'existingMedia', 'state']);
        $this->state['total_steps'] = 3;
        $this->state['current_step'] = 1;
    }

    public function getPostsProperty() {
        return BuySellPost::where('user_id', auth()->id())
            ->when($this->filters['search'], fn($q, $s) => $q->where('title', 'like', "%$s%"))
            ->latest()
            ->paginate($this->perPage);
    }
};
?>

<div class="">
    @include('partials.toast')
    <!-- Main Content -->
    <div class="">
        @if ($state['show_create_form'])
            <!-- Create/Edit Form -->
            <div class="">
                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-center">
                        @foreach (range(1, $state['total_steps']) as $step)
                            <div class="flex items-center">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300
                                        {{ $state['current_step'] >= $step
                                            ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/25'
                                            : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400' }}
                                        {{ $state['current_step'] == $step ? 'ring-4 ring-blue-100 dark:ring-blue-900/30 scale-110' : '' }}">
                                        <span class="font-bold text-sm">{{ $step }}</span>
                                    </div>
                                    <span
                                        class="text-xs mt-2 font-medium text-center max-w-[80px] leading-tight px-1
                                        {{ $state['current_step'] >= $step ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                                        @switch($step)
                                            @case(1)
                                                পণ্যের বিবরণ
                                            @break

                                            @case(2)
                                                মূল্য ও স্টক
                                            @break

                                            @case(3)
                                                যোগাযোগ ও অবস্থান
                                            @break
                                        @endswitch
                                    </span>
                                </div>

                                @if ($step < $state['total_steps'])
                                    <div
                                        class="w-16 lg:w-24 h-1 mx-2 mt-6 rounded-full transition-all duration-500
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
                                <p class="text-gray-600 dark:text-gray-400 mt-2">আপনার পণ্য সম্পর্কে বিস্তারিত তথ্য
                                    প্রদান করুন</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-6">
                                    <flux:input type="text" wire:model="form.title"
                                        placeholder="যেমন: আইফোন ১৩ প্রো ম্যাক্স ২৫৬জিবি" label="পণ্যের শিরোনাম *" />

                                    <flux:select wire:model="form.buy_sell_category_id" label="ক্যাটাগরি *">
                                        <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select wire:model="form.buy_sell_item_id" label="আইটেম টাইপ">
                                        <option value="">আইটেম নির্বাচন করুন</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['title'] }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="space-y-6">
                                    <flux:select wire:model="form.condition" label="অবস্থা *">
                                        <option value="new">ব্র্যান্ড নিউ</option>
                                        <option value="like_new">নতুনের মত</option>
                                        <option value="used_good">ব্যবহৃত - ভাল</option>
                                        <option value="used_fair">ব্যবহৃত - মোটামুটি</option>
                                        <option value="refurbished">রিফার্বিশড</option>
                                        <option value="for_parts">পার্টস/মেরামতের জন্য</option>
                                    </flux:select>

                                    <flux:textarea wire:model="form.description" rows="3"
                                        placeholder="আপনার পণ্যটি বিস্তারিত বর্ণনা করুন..." label="বিবরণ" />

                                    <flux:textarea wire:model="form.note" rows="2"
                                        placeholder="ক্রেতাদের জন্য বিশেষ নোট..." label="অতিরিক্ত নোট" />
                                </div>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ছবি সংযুক্ত করুন</h3>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ bn_num(count($images) + count($existingMedia)) }}/১০টি ছবি
        </span>
    </div>

    <div wire:loading wire:target="tempImage" class="mb-4">
        <div x-data="{ progress: 0 }"
             x-on:livewire-upload-start="progress = 0"
             x-on:livewire-upload-progress.window="progress = $event.detail.progress"
             x-on:livewire-upload-finish="progress = 100" class="w-full">
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

    @error('tempImage')
        <span class="text-red-600 text-sm block mb-4">{{ $message }}</span>
    @enderror

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        
        @foreach ($existingMedia as $media)
            <div class="relative group aspect-square rounded-xl border-2 {{ $media['is_primary'] ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-900/50' : 'border-gray-200 dark:border-gray-600' }} overflow-hidden bg-gray-50 dark:bg-gray-700 transition-all duration-200">
                
                <img src="{{ $media['url'] }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">

                @if ($media['is_primary'])
                    <div class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full shadow-sm">প্রধান</div>
                @endif

                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <div class="flex gap-2">
                        <button type="button" wire:click="deleteExistingMedia({{ $media['id'] }})" wire:confirm="আপনি কি এই ছবিটি মুছে ফেলতে চান?" class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 group/btn transition">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        @foreach ($images as $index => $image)
            <div class="relative group aspect-square rounded-xl border-2 border-gray-200 dark:border-gray-600 overflow-hidden bg-gray-50 dark:bg-gray-700 transition-all duration-200">
                
                <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">

                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <button type="button" wire:click="removeNewImage({{ $index }})" class="p-2 bg-white/90 dark:bg-gray-800/90 rounded-lg hover:bg-red-50 transition">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach

        @if ((count($images) + count($existingMedia)) < 10)
            <label class="aspect-square rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200 flex items-center justify-center cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 group">
                <div class="text-center p-4">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-blue-500 mx-auto mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400 font-medium group-hover:text-blue-600 transition-colors">ছবি যোগ করুন</span>
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
                                <p class="text-gray-600 dark:text-gray-400 mt-2">আপনার পণ্যের মূল্য ও স্টক তথ্য প্রদান
                                    করুন</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <div class="lg:col-span-2 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <flux:input type="number" wire:model="form.price" placeholder="0.00"
                                            label="মূল্য" />

                                        <flux:input type="number" wire:model="form.discount_price"
                                            placeholder="0.00" label="ডিসকাউন্ট মূল্য" />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <flux:select wire:model="form.currency" label="মুদ্রা">
                                            <option value="BDT">BDT - ৳</option>
                                            <option value="USD">USD - $</option>
                                        </flux:select>

                                        <flux:input type="number" wire:model="form.stock" min="1"
                                            label="স্টক সংখ্যা" />
                                    </div>

                                    <flux:input type="text" wire:model="form.sku"
                                        placeholder="পণ্য আইডেন্টিফায়ার" label="SKU (ঐচ্ছিক)" />

                                    <flux:checkbox wire:model="form.is_negotiable" label="মূল্য আলোচনা সাপেক্ষ" />
                                </div>

                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6">
                                    <h3 class="font-bold text-blue-900 dark:text-blue-100 mb-4 text-lg">মূল্য নির্ধারণ
                                        টিপস</h3>
                                    <ul class="space-y-3 text-sm text-blue-800 dark:text-blue-200">
                                        <li class="flex items-start gap-3">
                                            <span
                                                class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">১</span>
                                            প্রতিযোগিতামূলক মূল্যের জন্য অনুরূপ আইটেম গবেষণা করুন
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <span
                                                class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">২</span>
                                            মূল্য নির্ধারণের সময় আইটেমের অবস্থা বিবেচনা করুন
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <span
                                                class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs">৩</span>
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
                                <p class="text-gray-600 dark:text-gray-400 mt-2">ক্রেতাদের সাথে যোগাযোগের তথ্য প্রদান
                                    করুন</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">যোগাযোগ তথ্য</h3>

                                    <flux:input type="text" wire:model="form.phone" placeholder="+৮৮০..."
                                        label="ফোন নম্বর" />

                                    <flux:input type="text" wire:model="form.whatsapp" placeholder="+৮৮০..."
                                        label="হোয়াটসঅ্যাপ (ঐচ্ছিক)" />

                                    <flux:input type="text" wire:model="form.imo" placeholder="+৮৮০..."
                                        label="IMO (ঐচ্ছিক)" />

                                    <flux:input type="email" wire:model="form.email"
                                        placeholder="example@example.com" label="ইমেইল (ঐচ্ছিক)" />

                                    <flux:select wire:model="form.status" label="পোস্ট স্ট্যাটাস *">
                                        <option value="draft">খসড়া হিসেবে সংরক্ষণ করুন</option>
                                        <option value="published">এখনই প্রকাশ করুন</option>
                                    </flux:select>
                                </div>

                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">অবস্থান</h3>

                                    <flux:select wire:model.live="form.division_id" label="বিভাগ">
                                        <option value="">বিভাগ নির্বাচন করুন</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division['id'] }}">{{ $division['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select wire:model.live="form.district_id" label="জেলা">
                                        <option value="">জেলা নির্বাচন করুন</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district['id'] }}">{{ $district['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select wire:model.live="form.thana_id" label="থানা/উপজেলা">
                                        <option value="">থানা নির্বাচন করুন</option>
                                        @foreach ($thanas as $thana)
                                            <option value="{{ $thana['id'] }}">{{ $thana['name'] }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:textarea wire:model="form.address" placeholder="বাড়ি নং, রোড নং, এলাকা..."
                                        label="বিস্তারিত ঠিকানা" rows="3" />
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
                                    ← পূর্ববর্তী
                                </flux:button>
                            @endif
                        </div>

                        <div class="flex gap-3">
                            @if ($state['current_step'] < $state['total_steps'])
                                <flux:button type="button" wire:click="nextStep" variant="primary">
                                    পরবর্তী →
                                </flux:button>
                            @else
                                <flux:button type="submit" variant="primary" color="green">
                                    {{ $state['editing_post_id'] ? 'পোস্ট আপডেট করুন' : 'পণ্য প্রকাশ করুন' }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- Dashboard View -->
            <div class="">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="text-xl font-bold">আপনার জিনিসপত্র বিক্রি করুন</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl mx-auto whitespace-nowrap truncate">
                        সাশ্রয়ী দামে নতুন ও ব্যবহৃত জিনিস কিনুন/বিক্রি করুন।
                    </p>
                </div>

                <!-- Create Button -->
                <div class="text-center mb-4">
                    <flux:button wire:click="createPost" icon="plus">
                        নতুন পোস্ট তৈরি করুন
                    </flux:button>
                </div>

                <!-- Posts Section -->
                <div class="">
                    <!-- Section Header -->
                    <div class="mb-3 text-center">
                        <div>
                            <h2 class="text-xl font-bold">আমার পোস্টসমূহ</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                আপনার তালিকাভুক্ত আইটেমগুলি পরিচালনা করুন
                            </p>
                        </div>

                        <div class="flex items-center justify-center text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            মোট {{ $this->posts->total() }} টি পোস্ট
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div class="mb-4">
                        <div class="flex gap-4 overflow-x-auto">
                            <div class="">
                                <flux:input wire:model.live.debounce.300ms="filters.search" icon="search"
                                    placeholder="আপনার পোস্ট খুঁজুন..." />
                            </div>
                            <div class="">
                                <flux:select wire:model.live="filters.status">
                                    <option value="">সব স্ট্যাটাস</option>
                                    <option value="draft">খসড়া</option>
                                    <option value="published">প্রকাশিত</option>
                                    <option value="pending">বিচারাধীন</option>
                                </flux:select>
                            </div>
                            <div class="">
                                <flux:select wire:model.live="filters.category">
                                    <option value="">সব ক্যাটাগরি</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                    @endforeach
                                </flux:select>
                            </div>

                            <!-- Per Page Selector -->
                            <div class="">
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

                   <div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-zinc-400/10">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">পণ্য</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">মূল্য</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">অবস্থা</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">স্ট্যাটাস</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">তারিখ</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">কর্ম</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse($this->posts as $post)
                <tr class="hover:bg-zinc-400/10 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            {{-- Spatie Media Library ইমেজ ডিসপ্লে --}}
                           <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-600 
            overflow-hidden flex-shrink-0 bg-gray-50 dark:bg-gray-700">

    @if ($post->hasMedia('posts'))
        <img
            class="w-12 h-12 object-cover"
            src="{{ $post->getPrimaryImageUrl('thumb') }}"
            alt="{{ $post->title }}"
        >
    @else
        <div class="w-full h-full flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16
                         m-2-2l1.586-1.586a2 2 0 012.828 0L20 14
                         m-6-6h.01M6 20h12a2 2 0 002-2V6
                         a2 2 0 00-2-2H6a2 2 0 00-2 2v12
                         a2 2 0 002 2z" />
            </svg>
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deletePost({{ $post->id }})"
                                wire:confirm="আপনি কি নিশ্চিত যে আপনি এই পোস্টটি মুছে ফেলতে চান?"
                                class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200 border border-red-200 dark:border-red-800"
                                title="মুছুন">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-400 dark:text-gray-500 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m8-6V4a1 1 0 00-1-1h-2a1 1 0 00-1 1v3M7 7h10"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">কোন পোস্ট পাওয়া যায়নি</h3>
                        <p class="text-gray-500 dark:text-gray-400">নতুন পোস্ট তৈরি করে শুরু করুন</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

                    <!-- Pagination -->
                    <div class="">
                        {{ $this->posts->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
