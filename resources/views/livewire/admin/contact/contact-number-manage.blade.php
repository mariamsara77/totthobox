<?php

use Livewire\Volt\Component;
use App\Models\ContactNumber;
use App\Models\ContactCategory;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;

new class extends Component {
    public $contacts = [];
    public $categories = [];

    public $divisions = [];
    public $districts = [];
    public $thanas = [];

    // Form fields
    public $contactId;
    public $contact_category_id;
    public $name;
    public $designation;
    public $phone;
    public $alt_phone;
    public $email;
    public $address;
    public $division_id;
    public $district_id;
    public $thana_id;
    public $unit_name;
    public $type;
    public $status = 'active';
    public $is_active = true;
    public $is_featured = false;
    public $extra_attributes = [
        'ambulance_type' => '',
        'driver_name' => '',
        'available_time' => '',
    ];

    public function mount()
    {
        $this->loadData();

        $this->divisions = Division::all();
    }

    public function loadData()
    {
        $this->contacts = ContactNumber::with('category')->latest()->get();
        $this->categories = ContactCategory::all();
    }

    public function updatedDivisionId($value)
    {
        $this->districts = District::where('division_id', $value)->get();
        $this->thanas = [];
    }

    public function updatedDistrictId($value)
    {
        $this->thanas = Thana::where('district_id', $value)->get();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'contact_category_id' => 'required|exists:contact_categories,id',
            'phone' => 'nullable|string|max:50',
        ]);

        ContactNumber::updateOrCreate(
            ['id' => $this->contactId],
            [
                'contact_category_id' => $this->contact_category_id,
                'name' => $this->name,
                'designation' => $this->designation,
                'phone' => $this->phone,
                'alt_phone' => $this->alt_phone,
                'email' => $this->email,
                'address' => $this->address,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'thana_id' => $this->thana_id,
                'unit_name' => $this->unit_name,
                'type' => $this->type,
                'status' => $this->status,
                'is_active' => $this->is_active,
                'is_featured' => $this->is_featured,
                'extra_attributes' => $this->extra_attributes,
            ],
        );

        $this->resetForm();
        $this->loadData();
    }

    public function edit($id)
    {
        $contact = ContactNumber::findOrFail($id);
        $this->contactId = $contact->id;
        $this->contact_category_id = $contact->contact_category_id;
        $this->name = $contact->name;
        $this->designation = $contact->designation;
        $this->phone = $contact->phone;
        $this->alt_phone = $contact->alt_phone;
        $this->email = $contact->email;
        $this->address = $contact->address;
        $this->division_id = $contact->division_id;
        $this->district_id = $contact->district_id;
        $this->thana_id = $contact->thana_id;
        $this->unit_name = $contact->unit_name;
        $this->type = $contact->type;
        $this->status = $contact->status;
        $this->is_active = $contact->is_active;
        $this->is_featured = $contact->is_featured;
        $this->extra_attributes = $contact->extra_attributes ?? [
            'ambulance_type' => '',
            'driver_name' => '',
            'available_time' => '',
        ];
    }

    public function delete($id)
    {
        ContactNumber::findOrFail($id)->delete();
        $this->loadData();
    }

    public function resetForm()
    {
        $this->reset(['contactId', 'contact_category_id', 'name', 'designation', 'phone', 'alt_phone', 'email', 'address', 'division_id', 'district_id', 'thana_id', 'unit_name', 'type', 'status', 'is_active', 'is_featured', 'extra_attributes']);
        $this->is_active = true;
        $this->is_featured = false;
        $this->extra_attributes = [
            'ambulance_type' => '',
            'driver_name' => '',
            'available_time' => '',
        ];
    }
};
?>

<div class="space-y-6">

    <h2 class="text-xl font-bold mb-4">Contact Management</h2>

    <!-- Form -->
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Category -->
        <div>
            <flux:select wire:model="contact_category_id" label="Category">
                <option value="">-- Select --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </flux:select>
            @error('contact_category_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Division / District / Thana -->
        <div>
            <flux:select wire:model.live="division_id" label="Division">
                <option value="">-- Select --</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }} ({{ $division->user->email }})</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <flux:select wire:model.live="district_id" label="District">
                <option value="">-- Select --</option>
                @foreach ($districts as $district)
                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <flux:select wire:model.live="thana_id" label="Thana">
                <option value="">-- Select --</option>
                @foreach ($thanas as $thana)
                    <option value="{{ $thana->id }}">{{ $thana->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div>
            <flux:input type="text" wire:model="unit_name" label="Unit Name (Optional)" />
        </div>

        <!-- Phone & Alt Phone -->
        <div>
            <flux:input type="text" wire:model="phone" label="Phone" />
        </div>

        <!-- Name -->
        <div>
            <flux:input type="text" wire:model="name" label="Name" />
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Type --}}
        <div>
            <flux:select wire:model="type" label="Type (Optional)">
                <option value="">-- Select --</option>

                <!-- Ambulance Types -->
                <optgroup label="🚑 Ambulance" class="bg-white dark:bg-zinc-700">
                    <option value="AC">AC Ambulance</option>
                    <option value="Non-AC">Non-AC Ambulance</option>
                    <option value="ICU">ICU Ambulance</option>
                    <option value="CCU">CCU Ambulance</option>
                    <option value="Freezing">Freezing Ambulance</option>
                </optgroup>

                <!-- Police -->
                <optgroup label="👮 Police" class="bg-white dark:bg-zinc-700">
                    <option value="Police Station">Police Station</option>
                    <option value="Highway Police">Highway Police</option>
                    <option value="Traffic Police">Traffic Police</option>
                    <option value="DB Police">Detective Branch (DB)</option>
                </optgroup>

                <!-- RAB -->
                <optgroup label="⚡ RAB" class="bg-white dark:bg-zinc-700">
                    <option value="RAB Camp">RAB Camp</option>
                    <option value="RAB Headquarters">RAB Headquarters</option>
                    <option value="RAB Emergency">RAB Emergency Response</option>
                </optgroup>

                <!-- Fire Service -->
                <optgroup label="🔥 Fire Service" class="bg-white dark:bg-zinc-700">
                    <option value="Fire Station">Fire Station</option>
                    <option value="Rescue Team">Rescue Team</option>
                    <option value="Fire Control Room">Fire Control Room</option>
                </optgroup>

                <!-- Others -->
                <optgroup label="🏥 Others" class="bg-white dark:bg-zinc-700">
                    <option value="Hospital">Hospital</option>
                    <option value="Clinic">Clinic</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Pharmacy">Pharmacy</option>
                    <option value="Blood Bank">Blood Bank</option>
                </optgroup>
            </flux:select>

        </div>

        <div>
            <flux:input type="text" wire:model="designation" label="Designation (Optional)" />
        </div>

        {{-- <div>
            <flux:input type="text" wire:model="alt_phone" label="Alt Phone" />
        </div> --}}

        <!-- Email & Designation -->
        <div>
            <flux:input type="email" wire:model="email" label="Email (Optional)" />
        </div>


        <!-- Status -->
        <div>
            <flux:select wire:model="status" label="Status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </flux:select>
        </div>

        <!-- Address -->
        <div class="col-span-2">
            <flux:textarea wire:model="address" label="Address (Optional)" />
        </div>

        <!-- Extra Attributes -->
        {{-- <div>
            <flux:input type="text" wire:model="extra_attributes.ambulance_type" label="Ambulance Type" />
        </div>
        <div>
            <flux:input type="text" wire:model="extra_attributes.driver_name" label="Driver Name" />
        </div>
        <div>
            <flux:input type="text" wire:model="extra_attributes.available_time" label="Available Time" />
        </div> --}}

        <!-- Checkboxes -->
        <div class="flex gap-4 col-span-2">
            <flux:checkbox type="checkbox" wire:model="is_active" label="Active" />
            <flux:checkbox type="checkbox" wire:model="is_featured" label="Featured" />
        </div>

        <!-- Buttons -->
        <div class="col-span-2 flex gap-2">
            <flux:button type="submit" variant="primary">
                {{ $contactId ? 'Update' : 'Create' }}
            </flux:button>
            <flux:button type="button" wire:click="resetForm">Reset</flux:button>
        </div>

    </form>

    <!-- Table -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-zinc-400/10">
                    <th class="px-2 py-1">Name</th>
                    <th class="px-2 py-1">Category</th>
                    <th class="px-2 py-1">Phone</th>
                    <th class="px-2 py-1">Alt Phone</th>
                    <th class="px-2 py-1">Email</th>
                    <th class="px-2 py-1">Designation</th>
                    <th class="px-2 py-1">Address</th>
                    <th class="px-2 py-1">Type</th>
                    <th class="px-2 py-1">Active</th>
                    <th class="px-2 py-1">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr class="border-b border-zinc-400/10 hover:bg-zinc-400/10">
                        <td class="px-2 py-1">{{ $contact->name }}</td>
                        <td class="px-2 py-1">{{ $contact->category->name ?? '-' }}</td>
                        <td class="px-2 py-1">{{ $contact->phone }}</td>
                        <td class="px-2 py-1">{{ $contact->alt_phone }}</td>
                        <td class="px-2 py-1">{{ $contact->email }}</td>
                        <td class="px-2 py-1">{{ $contact->designation }}</td>
                        <td class="px-2 py-1">{{ $contact->address }}</td>
                        <td class="px-2 py-1">{{ $contact->type }}</td>
                        <td class="px-2 py-1">{{ $contact->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-2 py-1 space-x-2">
                            <button wire:click="edit({{ $contact->id }})" class="text-blue-600">Edit</button>
                            <button wire:click="delete({{ $contact->id }})" class="text-red-600">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr class="border-b border-zinc-400/10 hover:bg-zinc-400/10">
                        <td colspan="12" class="text-center py-2">No contacts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
