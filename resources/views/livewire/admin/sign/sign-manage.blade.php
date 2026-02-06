<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Sign;
use App\Models\SignCategory;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

new class extends Component {
    use WithPagination, WithFileUploads;

    // Properties
    public $signId;
    public $sign_category_id;
    public $name_bn;
    public $name_en;
    public $image;
    public $description_bn;
    public $description_en;
    public $details;
    public $others;
    public $status = 1;

    // Table & Search properties
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name_bn';
    public $sortDirection = 'asc';
    public $showTrashed = false;
    public $activeTab = 'index';

    public $imagePreview;
    public $currentImage;

    // Options for dropdowns
    public $categories = [];

    // Initialize component
    public function mount()
    {
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = SignCategory::latest()->get();
    }

    // Validation rules
    protected function rules()
    {
        return [
            'sign_category_id' => 'required|exists:sign_categories,id',
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'description_bn' => 'nullable|string',
            'description_en' => 'nullable|string',
            'details' => 'nullable|string',
            'others' => 'nullable|string',
            'status' => 'required|boolean',
        ];
    }

    // Switch tabs
    public function showTab($tabName)
    {
        $this->activeTab = $tabName;
        $this->resetFields();
    }

    // Reset form fields
    public function resetFields()
    {
        $this->reset([
            'signId',
            'sign_category_id',
            'name_bn',
            'name_en',
            'image',
            'description_bn',
            'description_en',
            'details',
            'others',
            'status',
            'imagePreview',
            'currentImage',
        ]);
        $this->resetErrorBag();
    }

    // Load sign for editing
    public function editSign($id)
    {
        $sign = Sign::withTrashed()->findOrFail($id);

        $this->signId = $sign->id;
        $this->sign_category_id = $sign->sign_category_id;
        $this->name_bn = $sign->name_bn;
        $this->name_en = $sign->name_en;
        $this->description_bn = $sign->description_bn;
        $this->description_en = $sign->description_en;
        $this->details = $sign->details;
        $this->others = $sign->others;
        $this->status = $sign->status;
        $this->currentImage = $sign->image;

        $this->activeTab = 'edit';
    }

    // Create or update sign
    public function saveSign()
    {
        $this->validate();

        $data = [
            'sign_category_id' => $this->sign_category_id,
            'name_bn' => $this->name_bn,
            'name_en' => $this->name_en,
            'description_bn' => $this->description_bn,
            'description_en' => $this->description_en,
            'details' => $this->details,
            'others' => $this->others,
            'status' => $this->status,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        // Handle image upload
        if ($this->image) {
            if ($this->currentImage) {
                Storage::disk('public')->delete('signs/' . basename($this->currentImage));
            }

            $baseName = Str::slug($this->name_bn);
            $imageName = $baseName . '-' . time() . '.webp';
            $savePath = storage_path('app/public/signs/' . $imageName);

            // Intervention with GD
            $manager = new ImageManager(\Intervention\Image\Drivers\Gd\Driver::class);
            $manager->read($this->image->getRealPath())
                ->toWebp(80)
                ->save($savePath);

            $data['image'] = 'signs/' . $imageName;
        }

        if ($this->signId) {
            // Update existing sign
            $sign = Sign::find($this->signId);
            $sign->update($data);
            session()->flash('success', 'Sign updated successfully.');
        } else {
            // Create new sign
            Sign::create($data);
            session()->flash('success', 'Sign created successfully.');
        }

        $this->resetFields();
        $this->activeTab = 'index';
    }

    // Delete sign
    public function deleteSign($id)
    {
        $sign = Sign::findOrFail($id);
        $sign->deleted_by = auth()->id();
        $sign->save();
        $sign->delete();
        session()->flash('success', 'Sign moved to trash.');
    }

    // Restore sign
    public function restoreSign($id)
    {
        $sign = Sign::withTrashed()->findOrFail($id);
        $sign->restore();
        session()->flash('success', 'Sign restored successfully.');
    }

    // Force delete sign
    public function forceDeleteSign($id)
    {
        $sign = Sign::withTrashed()->findOrFail($id);
        if ($sign->image) {
            Storage::disk('public')->delete($sign->image);
        }
        $sign->forceDelete();
        session()->flash('success', 'Sign permanently deleted.');
    }

    // Remove image
    public function removeImage()
    {
        if ($this->signId && $this->currentImage) {
            Storage::disk('public')->delete($this->currentImage);
            Sign::find($this->signId)->update(['image' => null]);
            $this->currentImage = null;
        }
        $this->image = null;
        $this->imagePreview = null;
    }

    // Updated image preview
    public function updatedImage()
    {
        $this->validate(['image' => 'nullable|image|max:2048']);
        $this->imagePreview = $this->image->temporaryUrl();
    }

    // Sort function
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Get signs for display
    public function getSignsProperty()
    {
        $query = Sign::query()->with('category');

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        return $query->when($this->search, function ($query) {
            return $query->where(function ($q) {
                $q->where('name_bn', 'like', '%' . $this->search . '%')
                    ->orWhere('name_en', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
}; ?>

<section class="p-6">
    <div class="flex flex-col space-y-6">

        @if($activeTab === 'create' || $activeTab === 'edit')
        <div class="p-6 rounded-lg shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">
                    {{ $activeTab === 'create' ? 'Create New Sign' : 'Edit Sign' }}
                </h3>
                <flux:button wire:click="showTab('index')" size="sm">
                    Back to List
                </flux:button>
            </div>

            <form wire:submit="saveSign" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-1">
                    <flux:select wire:model="sign_category_id" label="Category" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="lg:col-span-1">
                    <flux:input type="text" wire:model="name_bn" label="চিহ্নের নাম (বাংলা)" required />
                </div>
                <div class="lg:col-span-1">
                    <flux:input type="text" wire:model="name_en" label="Sign Name (English)" />
                </div>

                <div class="lg:col-span-1">
                    <flux:select wire:model="status" label="Status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </flux:select>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <flux:textarea wire:model="description_bn" label="ব্যাখ্যা (বাংলা)" rows="3" />
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <flux:textarea wire:model="description_en" label="Description (English)" rows="3" />
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <flux:textarea wire:model="details" label="Details" rows="3" />
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <flux:textarea wire:model="others" label="Others" rows="3" />
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <flux:input type="file" wire:model="image" accept="image/*" label="Image" />
                    @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <div class="mt-2">
                        @if ($imagePreview)
                        <img src="{{ $imagePreview }}" alt="Image Preview" class="h-20 w-auto rounded">
                        @elseif ($currentImage)
                        <img src="{{ asset('storage/' . $currentImage) }}" alt="Current Image" class="h-20 w-auto rounded">
                        <button type="button" wire:click="removeImage" class="mt-2 text-red-600 text-sm hover:text-red-800">
                            Remove Image
                        </button>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2 lg:col-span-3 flex justify-end space-x-3 mt-4">
                    <flux:button type="button" wire:click="resetFields">
                        Reset
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $signId ? 'Update Sign' : 'Create Sign' }}
                    </flux:button>
                </div>
            </form>
        </div>

        @else

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <h2 class="text-2xl font-bold">Sign Management</h2>

            <div class="flex space-x-2">
                <flux:button wire:click="showTab('create')" size="sm">
                    Create New
                </flux:button>
                <flux:button wire:click="$set('showTrashed', false)" size="sm" variant="{{ !$showTrashed ? 'primary' : 'outline' }}">
                    Active
                </flux:button>
                <flux:button wire:click="$set('showTrashed', true)" size="sm" variant="{{ $showTrashed ? 'primary' : 'outline' }}">
                    Trashed
                </flux:button>
            </div>
        </div>

        <div>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by Bangla or English name..." class="w-full" />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                            Image
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider cursor-pointer" wire:click="sortBy('name_bn')">
                            Name (Bangla)
                            @if($sortField === 'name_bn')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                            Status
                            @if($sortField === 'status')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium  uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($this->signs as $sign)
                    <tr class="hover:bg-zinc-400/10">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($sign->image)
                            <img src="{{ asset('storage/' . $sign->image) }}" alt="{{ $sign->name_bn }}" class="h-10 w-10 rounded-full">
                            @else
                            <div class="h-10 w-10 rounded-full0 flex items-center justify-center">
                                <span class=" text-sm">N/A</span>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm ">
                            {{ $sign->name_bn }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm ">
                            {{ $sign->category->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($sign->deleted_at)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Trashed
                            </span>
                            @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sign->status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $sign->status ? 'Active' : 'Inactive' }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($sign->deleted_at)
                            <button wire:click="restoreSign({{ $sign->id }})" class="text-green-600 hover:text-green-900 mr-3">
                                Restore
                            </button>
                            <button wire:click="forceDeleteSign({{ $sign->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to permanently delete this sign?')">
                                Permanently Delete
                            </button>
                            @else
                            <button wire:click="editSign({{ $sign->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                Edit
                            </button>
                            <button wire:click="deleteSign({{ $sign->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to move this sign to trash?')">
                                Delete
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-zinc-400/10">
                        <td colspan="5" class="px-6 py-4 text-center text-sm ">
                            No {{ $showTrashed ? 'trashed' : 'active' }} signs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($this->signs->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $this->signs->links() }}
            </div>
            @endif
        </div>
        @endif

    </div>
</section>
