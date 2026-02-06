<!-- Filters Section -->
<div class="">
    <div class="">
        <div class="overflow-x-auto">
            <div class="flex gap-4 min-w-max px-2 py-3">
                <!-- Search -->
                <div class="col-span-full">
                    <flux:input wire:model.live.debounce.500ms="search" icon="magnifying-glass"
                        placeholder="যেমন: আইফোন, ল্যাপটপ, ফার্নিচার..." class="w-full" />
                </div>

                @if (Route::is('buysell.all'))
                    <!-- Category -->
                    <div>
                        <flux:select wire:model.live.debounce.500ms="filterCategory" class="w-full">
                            <option value="">সব ক্যাটাগরি</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                <!-- Condition -->
                <div>
                    <flux:select wire:model.live.debounce.500ms="filterCondition" class="w-full">
                        <option value="">সব অবস্থা</option>
                        <option value="new">ব্র্যান্ড নিউ</option>
                        <option value="like_new">নতুনের মত</option>
                        <option value="used_good">ব্যবহৃত - ভাল</option>
                        <option value="used_fair">ব্যবহৃত - মোটামুটি</option>
                        <option value="refurbished">রিফার্বিশড</option>
                    </flux:select>
                </div>

                <!-- Price Range -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <flux:input type="number" wire:model.live.debounce.500ms="minPrice" placeholder="ন্যূনতম মূল্য"
                            class="w-full" />
                    </div>
                    <div>
                        <flux:input type="number" wire:model.live.debounce.500ms="maxPrice"
                            placeholder="সর্বোচ্চ মূল্য" class="w-full" />
                    </div>
                </div>

                <!-- Location Filters -->
                <div>
                    <flux:select wire:model.live.debounce.500ms="filterDivision" class="w-full">
                        <option value="">সব বিভাগ</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model.live.debounce.500ms="filterDistrict" class="w-full">
                        <option value="">সব জেলা</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model.live.debounce.500ms="filterThana" class="w-full">
                        <option value="">সব থানা</option>
                        @foreach ($thanas as $thana)
                            <option value="{{ $thana->id }}">{{ $thana->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Negotiable -->
                <div class="flex items-center flex-shrink-0">
                    <flux:checkbox wire:model.live.debounce.500ms="showNegotiableOnly" label="শুধু আলোচনা সাপেক্ষ"
                        class="" />
                </div>

                <!-- Reset Filters -->
                <div class="">
                    <flux:button wire:click="resetFilters" color="secondary" size="sm">
                        ফিল্টার রিসেট
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
