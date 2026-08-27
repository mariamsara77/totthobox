<?php

use Livewire\Volt\Component;
use App\Models\ContactNumber;
use App\Models\ContactCategory;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\{Computed, Validate, On, Url};
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContactExport;
use Flux\Flux;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    public array $activities = [];
    public ?array $selectedActivity = null;

    // Collections
    public $categories = [];
    public $divisions = [];
    public $districts = [];
    public $thanas = [];

    // Form Fields
    public $contactId; // 'keep: true' সরিয়ে দিন

    #[Validate('required|exists:contact_categories,id')]
    #[Url(except: '')]
    public $contact_category_id = '';

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:255')]
    public $designation = '';

    #[Validate('nullable|string|max:50')]
    public $phone = '';

    #[Validate('nullable|string|max:50')]
    public $alt_phone = '';

    #[Validate('nullable|email|max:255')]
    public $email = '';

    #[Validate('nullable|string|max:500')]
    public $address = '';

    #[Validate('nullable|exists:divisions,id')]
    #[Url(except: '')]
    public $division_id = '';

    #[Validate('nullable|exists:districts,id')]
    #[Url(except: '')]
    public $district_id = '';

    #[Validate('nullable|exists:thanas,id')]
    #[Url(except: '')]
    public $thana_id = '';

    #[Validate('nullable|string|max:255')]
    public $unit_name = '';

    #[Validate('nullable|string|max:100')]
    public $type = '';

    #[Validate('required|in:active,inactive')]
    public $status = 'active';

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('boolean')]
    public $is_featured = false;

    #[Url(except: 'active')]
    public $viewType = 'active';

    #[Url(except: '')]
    public $search = '';

    public $form_division_id = '';
    public $form_district_id = '';
    public $form_thana_id = '';

    public $form_districts = [];
    public $form_thanas = [];

    public function mount()
    {
        $this->loadData();
        $this->divisions = Division::select('id', 'name')->get();
        $this->categories = ContactCategory::select('id', 'name')->get();
    }

    public function loadData()
    {
        // Data is loaded via computed property
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function updatedDivisionId($value)
    {
        $this->resetPage();
        $this->districts = $value ? District::where('division_id', $value)->select('id', 'name')->get() : [];
        $this->reset(['district_id', 'thana_id']);
        $this->thanas = [];
    }

    public function updatedDistrictId($value)
    {
        $this->resetPage();
        $this->thanas = $value ? Thana::where('district_id', $value)->select('id', 'name')->get() : [];
        $this->thana_id = null;
    }

    public function updatedFormDivisionId($value)
    {
        $this->form_districts = $value ? District::where('division_id', $value)->select('id', 'name')->get() : [];
        $this->form_district_id = '';
        $this->form_thana_id = '';
        $this->form_thanas = [];
    }

    public function updatedFormDistrictId($value)
    {
        $this->form_thanas = $value ? Thana::where('district_id', $value)->select('id', 'name')->get() : [];
        $this->form_thana_id = '';
    }

    #[Computed]
    public function contacts()
    {
        return ($this->viewType === 'trashed' ? ContactNumber::onlyTrashed() : ContactNumber::query())
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->division_id, fn($q) => $q->where('division_id', $this->division_id))
            ->when($this->district_id, fn($q) => $q->where('district_id', $this->district_id))
            ->when($this->thana_id, fn($q) => $q->where('thana_id', $this->thana_id))
            ->when($this->contact_category_id, fn($q) => $q->where('contact_category_id', $this->contact_category_id))
            ->with(['category', 'division', 'district', 'thana'])
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'categories' => $this->categories,
            'divisions' => $this->divisions,
            'districts' => $this->districts,
            'thanas' => $this->thanas,
        ];
    }

    public function showCreateForm()
    {
        $this->resetValidation();
        $this->reset(['contactId', 'contact_category_id', 'name', 'designation', 'phone', 'alt_phone', 'email', 'address', 'division_id', 'district_id', 'thana_id', 'unit_name', 'type', 'status', 'is_active', 'is_featured']);
        $this->is_active = true;
        $this->is_featured = false;
        $this->districts = [];
        $this->thanas = [];
        $this->dispatch('modal-show', name: 'contact-form');
    }

    public function showEditForm($id)
    {
        $this->resetValidation();
        $contact = ContactNumber::withTrashed()->findOrFail($id);

        $this->contactId = $contact->id;
        $this->contact_category_id = $contact->contact_category_id;
        $this->name = $contact->name;
        $this->designation = $contact->designation;
        $this->phone = $contact->phone;
        $this->alt_phone = $contact->alt_phone;
        $this->email = $contact->email;
        $this->address = $contact->address;
        $this->form_division_id = $contact->division_id;
        $this->form_district_id = $contact->district_id;
        $this->form_thana_id = $contact->thana_id;
        $this->unit_name = $contact->unit_name;
        $this->type = $contact->type;
        $this->status = $contact->status;
        $this->is_active = (bool) $contact->is_active;
        $this->is_featured = (bool) $contact->is_featured;

        // Load form dropdowns
        if ($this->form_division_id) {
            $this->form_districts = District::where('division_id', $this->form_division_id)->get();
        }
        if ($this->form_district_id) {
            $this->form_thanas = Thana::where('district_id', $this->form_district_id)->get();
        }

        $this->dispatch('modal-show', name: 'contact-form');
    }

    public function save()
    {
        $this->validate();

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
                'division_id' => $this->form_division_id ?: null, // Changed
                'district_id' => $this->form_district_id ?: null, // Changed
                'thana_id' => $this->form_thana_id ?: null, // Changed
                'unit_name' => $this->unit_name,
                'type' => $this->type,
                'status' => $this->status,
                'is_active' => $this->is_active,
                'is_featured' => $this->is_featured,
            ],
        );

        $this->dispatch('modal-close', name: 'contact-form');

        // Flux Toast Implementation
        Flux::toast(heading: 'সফল', text: 'যোগাযোগ তথ্যটি সংরক্ষিত হয়েছে।', variant: 'success');

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'contactId',
            'name',
            'designation',
            'phone',
            'alt_phone',
            'email',
            'address',
            'unit_name',
            'type',
            'status',
            'is_active',
            'is_featured',
            'form_division_id', // Added
            'form_district_id', // Added
            'form_thana_id', // Added
        ]);

        $this->form_districts = []; // Added
        $this->form_thanas = []; // Added
        $this->is_active = true;
        $this->is_featured = false;
    }

    public function delete($id)
    {
        ContactNumber::find($id)->delete();
        $this->dispatch('toast', variant: 'warning', text: 'Item moved to trash.');
    }

    public function restore($id)
    {
        ContactNumber::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('toast', variant: 'success', text: 'Item restored successfully.');
    }

    public function forceDelete($id)
    {
        $contact = ContactNumber::onlyTrashed()->findOrFail($id);
        $contact->forceDelete();
        $this->dispatch('toast', variant: 'error', text: 'Item deleted permanently.');
    }

    // View activity logs with detailed changes
    public function viewLogs(int $id): void
    {
        try {
            $this->activities = Activity::query()
                ->with('causer')
                ->where('subject_id', $id)
                ->where('subject_type', ContactNumber::class)
                ->latest()
                ->get()
                ->map(function ($activity) {
                    try {
                        $properties = $activity->properties ?? collect();

                        // Safely convert to array if it's a collection
                        $propertiesArray = $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties;

                        // Format the changes for display with null checks
                        if ($activity->event === 'updated' && isset($propertiesArray['changes'])) {
                            $activity->formatted_changes = $this->formatChanges($propertiesArray['changes']);
                        } elseif ($activity->event === 'created' && isset($propertiesArray['created_data'])) {
                            $activity->formatted_data = $propertiesArray['created_data'];
                        } elseif ($activity->event === 'deleted' && isset($propertiesArray['deleted_data'])) {
                            $activity->formatted_data = $propertiesArray['deleted_data'];
                        } elseif ($activity->event === 'force_deleted' && isset($propertiesArray['permanently_deleted_data'])) {
                            $activity->formatted_data = $propertiesArray['permanently_deleted_data'];
                        }

                        return $activity;
                    } catch (\Exception $e) {
                        // Return a safe version of the activity if there's an error
                        $activity->formatted_changes = [];
                        $activity->formatted_data = [];
                        return $activity;
                    }
                })
                ->toArray();
        } catch (\Exception $e) {
            $this->activities = [];
            $this->dispatch('toast', variant: 'error', text: 'Error loading activity logs.');
        }

        $this->dispatch('modal-show', name: 'activity-logs');
    }

    private function formatChanges($changes): array
    {
        $formatted = [];

        if (!is_array($changes) || empty($changes)) {
            return $formatted;
        }

        foreach ($changes as $field => $change) {
            // Skip if change is not an array with old/new structure
            if (!is_array($change) || !isset($change['old']) || !isset($change['new'])) {
                continue;
            }

            // Determine field type for display
            $type = 'text';
            if (in_array($field, ['address', 'description'])) {
                $type = 'textarea';
            } elseif (in_array($field, ['type'])) {
                $type = 'categories';
            }

            // Safely get old and new values
            $oldValue = $change['old'] ?? null;
            $newValue = $change['new'] ?? null;

            // Format arrays and objects
            if (is_array($oldValue) || is_object($oldValue)) {
                $oldValue = json_encode($oldValue, JSON_UNESCAPED_UNICODE);
            }
            if (is_array($newValue) || is_object($newValue)) {
                $newValue = json_encode($newValue, JSON_UNESCAPED_UNICODE);
            }

            $formatted[] = [
                'field' => $this->formatFieldName($field),
                'type' => $type,
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $formatted;
    }

    private function formatFieldName($field): string
    {
        $names = [
            'name' => 'Name',
            'designation' => 'Designation',
            'phone' => 'Phone',
            'alt_phone' => 'Alternative Phone',
            'email' => 'Email',
            'address' => 'Address',
            'division_id' => 'Division',
            'district_id' => 'District',
            'thana_id' => 'Thana',
            'unit_name' => 'Unit Name',
            'type' => 'Type',
            'status' => 'Status',
            'is_active' => 'Active Status',
            'is_featured' => 'Featured Status',
            'contact_category_id' => 'Category',
        ];

        return $names[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    // View specific activity details
    public function viewActivityDetails(int $activityId): void
    {
        try {
            $activity = Activity::with('causer')->find($activityId);

            if ($activity) {
                $properties = $activity->properties ?? collect();

                $this->selectedActivity = [
                    'id' => $activity->id,
                    'event' => $activity->event ?? 'unknown',
                    'description' => $activity->description ?? '',
                    'causer' => $activity->causer?->name ?? 'System',
                    'created_at' => $activity->created_at ? $activity->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A'),
                    'properties' => $properties instanceof \Illuminate\Support\Collection ? $properties->toArray() : (array) $properties,
                ];

                $this->dispatch('modal-show', name: 'activity-detail');
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', variant: 'error', text: 'Error loading activity details.');
        }
    }

    public function export()
    {
        // ফাইল নামের শেষে .csv দিন
        return Excel::download(new ContactExport(), 'contacts_' . now()->format('Y-m-d') . '.csv');
    }
};
?>

<div class="">
    {{-- Header Section --}}
    <div @class([
        'flex',
        'flex-col',
        'md:flex-row',
        'justify-between',
        'items-start',
        'md:items-center',
        'gap-4"',
        'mb-6',
    ])>
        <div>
            <flux:heading size="xl">Contact Management</flux:heading>
            <flux:subheading>Manage emergency contacts, police stations, hospitals, and other important numbers.
            </flux:subheading>
        </div>
        <div @class(['flex', 'items-center', 'gap-42'])>
            <flux:radio.group wire:model.live="viewType" variant="segmented" size="sm">
                <flux:radio value="active" label="Active" />
                <flux:radio value="trashed" label="Trash" />
            </flux:radio.group>
            <flux:button wire:click="showCreateForm" icon="plus" variant="primary" size="sm">Create New
            </flux:button>
        </div>
        <flux:button wire:click="export" variant="primary" size="sm">
            Export Data
        </flux:button>
    </div>

    {{-- Filters --}}
    <div @class([
        'flex',
        'gap-42',
        'mt-2',
        'overflow-x-auto',
        'pb-2',
        'scrollbar-hiden',
    ])>
        {{-- Search --}}
        <flux:input wire:model.live.debounce.500ms="search" placeholder="Search by name, phone, or email..."
            icon="magnifying-glass" />


        <flux:select wire:model.live="contact_category_id" placeholder="Filter by category" class="min-w-40">
            <flux:select.option value="">All Category</flux:select.option>
            @foreach ($categories as $category)
                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        {{-- Divisions --}}
        <flux:select wire:model.live="division_id" placeholder="Filter by Division" class="min-w-40">
            <flux:select.option value="">All Divisions</flux:select.option>
            @foreach ($divisions as $division)
                <flux:select.option value="{{ $division->id }}">{{ $division->name }}</flux:select.option>
            @endforeach
        </flux:select>

        {{-- Districts --}}
        <flux:select wire:model.live="district_id" placeholder="Filter by District" class="min-w-40"
            :disabled="empty($division_id)">
            <flux:select.option value="">All Districts</flux:select.option>
            @foreach ($districts as $district)
                <flux:select.option value="{{ $district->id }}">{{ $district->name }}</flux:select.option>
            @endforeach
        </flux:select>

        {{-- Thanas --}}
        <flux:select wire:model.live="thana_id" placeholder="Filter by Thana" class="min-w-40"
            :disabled="empty($district_id)">
            <flux:select.option value="">All Thanas</flux:select.option>
            @foreach ($thanas as $thana)
                <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->contacts">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column>Contact Info</flux:table.column>
            <flux:table.column>Location</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->contacts as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell @class(['font-medium'])>
                        <div>{{ $item->name }}</div>
                        @if ($item->designation)
                            <div @class(['text-xs', 'text-zinc-500'])>{{ $item->designation }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="blue">{{ $item->category->name ?? 'N/A' }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div @class(['text-sm'])>{{ $item->phone }}</div>
                        @if ($item->alt_phone)
                            <div @class(['text-xs', 'text-zinc-500'])>{{ $item->alt_phone }}</div>
                        @endif
                        @if ($item->email)
                            <div @class(['text-xs', 'text-zinc-500'])>{{ $item->email }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div @class(['text-sm'])>{{ $item->district->name ?? 'N/A' }}</div>
                        <div @class(['text-xs', 'text-zinc-500'])>{{ $item->division->name ?? '' }}</div>
                        @if ($item->unit_name)
                            <div @class(['text-xs', 'text-zinc-500'])>{{ $item->unit_name }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($item->is_featured)
                            <flux:badge size="sm" color="amber">Featured</flux:badge>
                        @endif
                        @if ($item->is_active)
                            <flux:badge size="sm" color="green">Active</flux:badge>
                        @else
                            <flux:badge size="sm" color="red">Inactive</flux:badge>
                        @endif
                        @if ($item->type)
                            <flux:badge size="sm" color="zinc" @class(['mt-1'])>{{ $item->type }}
                            </flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @if ($viewType === 'active')
                            <flux:button variant="ghost" size="sm" icon="clock"
                                wire:click="viewLogs({{ $item->id }})" title="View Activity Logs" />

                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                wire:click="showEditForm({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                wire:confirm="Are you sure?" wire:click="delete({{ $item->id }})" />
                        @else
                            <flux:button variant="ghost" size="sm" icon="arrow-path" color="green"
                                wire:click="restore({{ $item->id }})" />
                            <flux:button variant="ghost" size="sm" icon="x-mark" color="red"
                                wire:confirm="This will be deleted permanently!"
                                wire:click="forceDelete({{ $item->id }})" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" @class(['text-center', 'py-10', 'text-zinc-400'])>No records found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Form --}}
    <flux:modal name="contact-form" @class(['md:w-240'])>
        <form wire:submit="save" @class(['space-y-6'])>
            <div>
                <flux:heading size="lg">{{ $contactId ? 'Edit Contact' : 'Add New Contact' }}
                </flux:heading>
                <flux:subheading>Manage emergency contact details and location information.</flux:subheading>
            </div>

            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4"'])>
                <flux:select wire:model="contact_category_id" label="Category" placeholder="Select Category">
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="name" label="Name" placeholder="Enter contact name..." />
            </div>

            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4"'])>
                <flux:input wire:model="designation" label="Designation (Optional)"
                    placeholder="Enter designation..." />
                <flux:input wire:model="unit_name" label="Unit Name (Optional)" placeholder="Enter unit name..." />
            </div>

            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4"'])>
                <flux:input wire:model="phone" label="Phone" placeholder="Enter phone number..." />
                <flux:input wire:model="alt_phone" label="Alternative Phone (Optional)"
                    placeholder="Enter alternative phone..." />
            </div>

            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4"'])>
                <flux:input wire:model="email" type="email" label="Email (Optional)"
                    placeholder="Enter email address..." />
                <flux:select wire:model="type" label="Type (Optional)" placeholder="Select Type">
                    <flux:select.option disabled @class([
                        'font-bold',
                        'text-zinc-800',
                        'dark:text-white',
                        'bg-zinc-50',
                        'dark:bg-zinc-800',
                    ])>
                        🚑 Ambulance
                    </flux:select.option>
                    <flux:select.option value="AC">AC Ambulance</flux:select.option>
                    <flux:select.option value="Non-AC">Non-AC Ambulance</flux:select.option>
                    <flux:select.option value="ICU">ICU Ambulance</flux:select.option>
                    <flux:select.option value="CCU">CCU Ambulance</flux:select.option>
                    <flux:select.option value="Freezing">Freezing Ambulance</flux:select.option>

                    <flux:select.option disabled @class([
                        'font-bold',
                        'text-zinc-800',
                        'dark:text-white',
                        'bg-zinc-50',
                        'dark:bg-zinc-800',
                        'border-t',
                        'border-zinc-200',
                    ])>
                        👮 Police
                    </flux:select.option>
                    <flux:select.option value="Police Station">Police Station</flux:select.option>
                    <flux:select.option value="Highway Police">Highway Police</flux:select.option>
                    <flux:select.option value="Traffic Police">Traffic Police</flux:select.option>
                    <flux:select.option value="DB Police">Detective Branch (DB)</flux:select.option>

                    <flux:select.option disabled @class([
                        'font-bold',
                        'text-zinc-800',
                        'dark:text-white',
                        'bg-zinc-50',
                        'dark:bg-zinc-800',
                        'border-t',
                        'border-zinc-200',
                    ])>
                        🔥 Fire Service
                    </flux:select.option>
                    <flux:select.option value="Fire Station">Fire Station</flux:select.option>
                    <flux:select.option value="Rescue Team">Rescue Team</flux:select.option>
                    <flux:select.option value="Fire Control Room">Fire Control Room</flux:select.option>
                </flux:select>
            </div>

            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-3', 'gap-4"'])>
                <flux:select wire:model.live="form_division_id" label="Division" placeholder="Select Division">
                    <option value="">Select Division</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="form_district_id" label="District" placeholder="Select District"
                    :disabled="!$form_division_id">
                    <option value="">Select District</option>
                    @foreach ($form_districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="form_thana_id" label="Thana" placeholder="Select Thana"
                    :disabled="!$form_district_id">
                    <option value="">Select Thana</option>
                    @foreach ($form_thanas as $thana)
                        <option value="{{ $thana->id }}">{{ $thana->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:textarea wire:model="address" label="Address (Optional)" placeholder="Enter full address..."
                    rows="3" />
            </div>

            <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4"'])>
                <flux:select wire:model="status" label="Status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </flux:select>
            </div>

            <div @class(['flex', 'gap-4"'])>
                <flux:checkbox wire:model="is_active" label="Active" />
                <flux:checkbox wire:model="is_featured" label="Show as Featured" />
            </div>

            <div @class([
                'flex',
                'justify-end',
                'gap-4"',
                'pt-6',
                'border-t',
                'border-zinc-400/25',
            ])>
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Save Contact
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Activity Logs Modal --}}
    <flux:modal name="activity-logs" @class(['w-full'])>
        <div @class(['space-y-6'])>
            <div @class(['flex', 'justify-between', 'items-center'])>
                <div>
                    <flux:heading size="lg">Activity History</flux:heading>
                    <flux:subheading>Detailed change log with before/after values</flux:subheading>
                </div>
            </div>

            <div @class(['space-y-6', 'pr-2'])>
                @forelse($activities as $log)
                    @php
                        $event = $log['event'] ?? 'unknown';
                        $createdAt = isset($log['created_at'])
                            ? \Carbon\Carbon::parse($log['created_at'])->format('d M Y, h:i A')
                            : '';
                        $causer = $log['causer'] ?? null;
                        $causerName = $causer['name'] ?? 'System';
                        $causerAvatar = $causer['avatar_url'] ?? null;
                        $formattedChanges = $log['formatted_changes'] ?? [];
                        $formattedData = $log['formatted_data'] ?? [];
                    @endphp

                    <div
                        class="relative pl-4 border-l-2 {{ $event === 'created'
                            ? 'border-green-500'
                            : ($event === 'updated'
                                ? 'border-blue-500'
                                : ($event === 'restored'
                                    ? 'border-yellow-500'
                                    : 'border-red-500')) }}">
                        <div @class(['flex', 'flex-col', 'gap-4"'])>
                            {{-- Header --}}
                            <div @class(['flex', 'justify-between', 'items-center'])>
                                <div @class(['flex', 'items-center', 'gap-42'])>
                                    <flux:badge size="sm"
                                        color="{{ $event === 'created'
                                            ? 'green'
                                            : ($event === 'updated'
                                                ? 'blue'
                                                : ($event === 'restored'
                                                    ? 'yellow'
                                                    : 'red')) }}">
                                        {{ ucfirst($event) }}
                                    </flux:badge>
                                    <span @class(['text-sm', 'font-medium', 'text-zinc-700'])>
                                        {{ $log['description'] ?? 'No description' }}
                                    </span>
                                </div>
                                <span @class(['text-xs', 'text-zinc-500'])>
                                    {{ $createdAt }}
                                </span>
                            </div>

                            {{-- User --}}
                            <div @class(['flex', 'items-center', 'gap-42', 'text-sm'])>
                                <span @class(['text-zinc-600'])>By:</span>
                                <flux:profile :chevron="false" name="{{ $causerName }}"
                                    avatar="{{ $causerAvatar }}" />
                            </div>

                            {{-- Changes Display --}}
                            @if ($event === 'updated' && !empty($formattedChanges))
                                <div @class(['mt-2', 'space-y-3'])>
                                    @foreach ($formattedChanges as $change)
                                        @php
                                            $field = $change['field'] ?? 'Unknown Field';
                                            $type = $change['type'] ?? 'text';
                                            $oldValue = $change['old'] ?? '(empty)';
                                            $newValue = $change['new'] ?? '(empty)';
                                        @endphp

                                        <div @class(['bg-zinc-50', 'rounded-lg', 'p-3'])>
                                            <div @class(['text-sm', 'font-medium', 'text-zinc-700', 'mb-2'])>
                                                {{ $field }}
                                            </div>

                                            @if ($type === 'textarea')
                                                <div @class(['grid', 'grid-cols-2', 'gap-4"'])>
                                                    <div>
                                                        <div @class(['text-xs', 'text-red-500', 'mb-2'])>Before:</div>
                                                        <div @class([
                                                            'text-xs',
                                                            'bg-white',
                                                            'p-2',
                                                            'rounded',
                                                            'border',
                                                            'border-zinc-200',
                                                            'max-h-32',
                                                            'overflow-y-auto',
                                                        ])>
                                                            {!! nl2br(e($oldValue)) !!}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div @class(['text-xs', 'text-green-500', 'mb-2'])>After:</div>
                                                        <div @class([
                                                            'text-xs',
                                                            'bg-white',
                                                            'p-2',
                                                            'rounded',
                                                            'border',
                                                            'border-zinc-200',
                                                            'max-h-32',
                                                            'overflow-y-auto',
                                                        ])>
                                                            {!! nl2br(e($newValue)) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($type === 'categories')
                                                <div @class(['grid', 'grid-cols-2', 'gap-4"'])>
                                                    <div>
                                                        <div @class(['text-xs', 'text-red-500', 'mb-2'])>Before:</div>
                                                        <div @class(['flex', 'flex-wrap', 'gap-41'])>
                                                            @foreach (explode(', ', $oldValue) as $cat)
                                                                @if ($cat && $cat !== 'None' && $cat !== '(empty)')
                                                                    <flux:badge size="sm" color="red"
                                                                        variant="subtle">{{ $cat }}
                                                                    </flux:badge>
                                                                @else
                                                                    <span @class(['text-xs', 'text-zinc-400'])>None</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div @class(['text-xs', 'text-green-500', 'mb-2'])>After:</div>
                                                        <div @class(['flex', 'flex-wrap', 'gap-41'])>
                                                            @foreach (explode(', ', $newValue) as $cat)
                                                                @if ($cat && $cat !== 'None' && $cat !== '(empty)')
                                                                    <flux:badge size="sm" color="green"
                                                                        variant="subtle">{{ $cat }}
                                                                    </flux:badge>
                                                                @else
                                                                    <span @class(['text-xs', 'text-zinc-400'])>None</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div @class(['grid', 'grid-cols-2', 'gap-4"'])>
                                                    <div>
                                                        <div @class(['text-xs', 'text-red-500', 'mb-2'])>Before:</div>
                                                        <div @class([
                                                            'text-sm',
                                                            'bg-white',
                                                            'p-2',
                                                            'rounded',
                                                            'border',
                                                            'border-zinc-200',
                                                            'break-words',
                                                        ])>
                                                            {{ $oldValue }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div @class(['text-xs', 'text-green-500', 'mb-2'])>After:</div>
                                                        <div @class([
                                                            'text-sm',
                                                            'bg-white',
                                                            'p-2',
                                                            'rounded',
                                                            'border',
                                                            'border-zinc-200',
                                                            'break-words',
                                                        ])>
                                                            {{ $newValue }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(in_array($event, ['created', 'deleted', 'force_deleted']) && !empty($formattedData))
                                <div @class(['mt-2', 'bg-zinc-50', 'rounded-lg', 'p-3'])>
                                    <div @class(['grid', 'grid-cols-1', 'gap-42'])>
                                        @foreach ($formattedData as $key => $value)
                                            @php
                                                $displayValue = is_array($value)
                                                    ? implode(', ', $value)
                                                    : (string) $value;
                                            @endphp
                                            <div @class(['flex'])>
                                                <span
                                                    @class(['text-xs', 'font-medium', 'text-zinc-500', 'w-32'])>{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span
                                                    @class(['text-sm', 'break-words'])>{{ $displayValue ?: '(empty)' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- View Details Button --}}
                            <div @class(['flex', 'justify-end'])>
                                <flux:button size="xs" variant="ghost" icon="eye"
                                    wire:click="viewActivityDetails({{ $log['id'] ?? 0 }})">
                                    View Full Details
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div @class(['text-center', 'py-8', 'text-zinc-400'])>
                        <flux:icon name="clock" @class(['w-12', 'h-12', 'mx-auto', 'mb-2']) />
                        <p>No activity logs found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </flux:modal>

    {{-- Activity Detail Modal --}}
    <flux:modal name="activity-detail" @class(['w-full', 'max-w-3xl'])>
        @if ($selectedActivity)
            @php
                $event = $selectedActivity['event'] ?? 'unknown';
                $eventColor =
                    $event === 'created'
                        ? 'green'
                        : ($event === 'updated'
                            ? 'blue'
                            : ($event === 'restored'
                                ? 'yellow'
                                : 'red'));
                $description = $selectedActivity['description'] ?? '';
                $causer = $selectedActivity['causer'] ?? 'System';
                $createdAt = $selectedActivity['created_at'] ?? '';
                $properties = $selectedActivity['properties'] ?? null;
            @endphp

            <div @class(['space-y-6'])>
                <div @class(['flex', 'justify-between', 'items-center'])>
                    <flux:heading size="lg">Activity Details</flux:heading>
                    <flux:badge color="{{ $eventColor }}">
                        {{ ucfirst($event) }}
                    </flux:badge>
                </div>

                <div @class(['grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4'])>
                    <div @class(['md:col-span-2'])>
                        <flux:label>Description</flux:label>
                        <div @class(['mt-1', 'text-sm', 'text-zinc-700'])>
                            {{ $description }}
                        </div>
                    </div>

                    <div>
                        <flux:label>Performed by</flux:label>
                        <div @class(['mt-1', 'text-sm', 'text-zinc-700'])>
                            {{ $causer }}
                        </div>
                    </div>

                    <div>
                        <flux:label>Date & Time</flux:label>
                        <div @class(['mt-1', 'text-sm', 'text-zinc-700'])>
                            {{ $createdAt }}
                        </div>
                    </div>
                </div>

                @if ($properties)
                    <div>
                        <flux:label>Full Properties</flux:label>
                        <div @class([
                            'bg-zinc-950',
                            'p-4',
                            'rounded-lg',
                            'text-emerald-400',
                            'text-xs',
                            'font-mono',
                            'overflow-auto',
                            'max-h-60',
                            'border',
                            'border-zinc-800',
                            'whitespace-pre-wrap',
                            'mt-2',
                        ])>
                            {{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>
</div>
