<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\{User, District, Division, Thana};
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new class extends Component {
    use WithFileUploads, WithPagination;

    public $userId, $name, $email, $phone, $status = 'active';
    public $division_id, $district_id, $thana_id, $address;
    public $profession, $education, $bio, $note, $role;
    public $avatar;

    public $showForm = false;
    public $formType = 'create';
    public $viewType = 'active';
    public $search = '';

    public $roleFilter = '', $statusFilter = '';
    public $sortField = 'created_at', $sortDirection = 'desc';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', $this->formType === 'create' ? 'unique:users,email' : Rule::unique('users', 'email')->ignore($this->userId)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required',
            'status' => 'required|in:active,inactive,suspended',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'thana_id' => 'nullable|exists:thanas,id',
            'avatar' => 'nullable|image|max:10240', // 10MB Allowed for high-res mobile photos
        ];
    }

    public function getUsersProperty()
    {
        $query = $this->viewType === 'trashed' ? User::onlyTrashed() : User::query();
        return $query->with(['roles', 'division', 'district'])
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->roleFilter, fn($q) => $q->role($this->roleFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function showCreateForm()
    {
        $this->reset(['userId', 'name', 'email', 'phone', 'status', 'division_id', 'district_id', 'thana_id', 'address', 'profession', 'education', 'bio', 'note', 'role', 'avatar']);
        $this->resetErrorBag();
        $this->formType = 'create';
        $this->showForm = true;
    }

    public function showEditForm($id)
    {
        $this->resetErrorBag();
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->status = $user->status;
        $this->division_id = $user->division_id;
        $this->district_id = $user->district_id;
        $this->thana_id = $user->thana_id;
        $this->address = $user->address;
        $this->profession = $user->profession;
        $this->education = $user->education;
        $this->bio = $user->bio;
        $this->note = $user->note;
        $this->role = $user->getRoleNames()->first();

        $this->formType = 'edit';
        $this->showForm = true;
    }

    public function saveUser()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'address' => $this->address,
            'profession' => $this->profession,
            'education' => $this->education,
            'bio' => $this->bio,
            'note' => $this->note,
        ];

        if ($this->formType === 'create') {
            $data['password'] = bcrypt(Str::random(12));
            $user = User::create($data);
            $user->assignRole($this->role);
        } else {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            $user->syncRoles([$this->role]);
        }

        // Spatie Media Library Integration
        if ($this->avatar) {
            $user->addMedia($this->avatar->getRealPath())
                ->usingFileName($this->avatar->getClientOriginalName())
                ->toMediaCollection('avatars');
        }

        $this->showForm = false;
        $this->dispatch('notify', ['message' => 'User saved successfully!', 'type' => 'success']);
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
    }
    public function restoreUser($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
    }
    public function toggleView($type)
    {
        $this->viewType = $type;
        $this->resetPage();
    }

    public function with()
    {
        return [
            'divisions' => Division::all(),
            'districts' => $this->division_id ? District::where('division_id', $this->division_id)->get() : collect(),
            'thanas' => $this->district_id ? Thana::where('district_id', $this->district_id)->get() : collect(),
            'roles_list' => Role::all(),
        ];
    }
}; ?>

<section class="p-4 lg:p-8">
    @if ($showForm)
        <flux:card class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <flux:heading size="xl">{{ $formType === 'create' ? 'Create New Member' : 'Edit Member Profile' }}
                </flux:heading>
                <flux:button wire:click="$set('showForm', false)" variant="ghost" icon="x-mark" />
            </div>

            <form wire:submit="saveUser" class="space-y-6">
                {{-- Avatar Upload Section --}}
                <div class="flex items-center gap-6 p-4 rounded-lg">
                    <div class="relative inline-block">
                        <div
                            class="relative size-24 overflow-hidden rounded-full border border-zinc-200 dark:border-zinc-700">
                            {{-- প্রোফাইল ইমেজ বা প্রিভিউ --}}
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="object-cover size-full">
                            @elseif ($userId && User::find($userId)->hasMedia('avatars'))
                                <img src="{{ User::find($userId)->getFirstMediaUrl('avatars', 'thumb') }}"
                                    class="object-cover size-full">
                            @else
                                <flux:icon name="user" class="size-full text-zinc-400 bg-zinc-400/10 p-4" />
                            @endif

                            {{-- আপলোডিং ইন্ডিকেটর (সঠিকভাবে সেন্টার করা) --}}
                            <div wire:loading.flex wire:target="avatar"
                                class="absolute inset-0 items-center justify-center bg-zinc-900/50 backdrop-blur-[1px] z-10">
                                <flux:icon.loading class="!text-white" />
                            </div>
                        </div>

                        {{-- আপলোড বাটন --}}
                        <label
                            class="absolute bottom-0 right-0 bg-white dark:bg-zinc-800 p-1.5 shadow-md rounded-full cursor-pointer border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 transition-colors">
                            <flux:icon name="camera" variant="mini" class="text-zinc-600 dark:text-zinc-300" />
                            <input type="file" wire:model="avatar" class="hidden" accept="image/*">
                        </label>
                    </div>
                    <div>
                        <flux:heading>Profile Photo</flux:heading>
                        <flux:subheading>PNG, JPG or GIF. Max 1MB.</flux:subheading>
                        @error('avatar')
                            <flux:error class="mt-1 text-sm" :message="$message" />
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input label="Full Name" wire:model="name" icon="user" />
                    <flux:input label="Email Address" wire:model="email" icon="envelope" />
                    <flux:input label="Phone Number" wire:model="phone" icon="phone" />

                    <flux:select label="Primary Role" wire:model="role">
                        <option value="">Select Role</option>
                        @foreach ($roles_list as $r)
                            <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:separator text="Location Details" />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:select label="Division" wire:model.live="division_id">
                        <option value="">Select Division</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div->id }}">{{ $div->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select label="District" wire:model.live="district_id" :disabled="!$division_id">
                        <option value="">Select District</option>
                        @foreach ($districts as $dis)
                            <option value="{{ $dis->id }}">{{ $dis->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select label="Thana" wire:model="thana_id" :disabled="!$district_id">
                        <option value="">Select Thana</option>
                        @foreach ($thanas as $th)
                            <option value="{{ $th->id }}">{{ $th->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:textarea label="Full Address" wire:model="address" rows="2" />

                <flux:separator text="Professional Information" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input label="Profession" wire:model="profession" />
                    <flux:input label="Education" wire:model="education" />
                    <div class="md:col-span-2" wire:ignore>
                        <flux:editor label="Short Bio" wire:model="bio" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t">
                    <flux:button wire:click="$set('showForm', false)" variant="ghost">Discard</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ $formType === 'create' ? 'Create User' : 'Update User' }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @else
        {{-- List View --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <flux:heading size="xl">Member Directory</flux:heading>
                <flux:subheading>Total {{ $this->users->total() }} users found in the system.</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:button.group>
                    <flux:button wire:click="toggleView('active')" :variant="$viewType === 'active' ? 'primary' : 'filled'">
                        Active</flux:button>
                    <flux:button wire:click="toggleView('trashed')"
                        :variant="$viewType === 'trashed' ? 'primary' : 'filled'" color="red">Trash</flux:button>
                </flux:button.group>
                <flux:button wire:click="showCreateForm" variant="primary" icon="plus">Add Member</flux:button>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 mb-6">
            <flux:input wire:model.live.debounce.400ms="search" class="flex-1 min-w-[300px]" icon="magnifying-glass"
                placeholder="Search by name, email, or bio..." />
            <flux:select wire:model.live="roleFilter" class="w-full md:w-48">
                <option value="">All Roles</option>
                @foreach ($roles_list as $r)
                    <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column sortable wire:click="sortBy('name')">Member</flux:table.column>
                <flux:table.column>Location</flux:table.column>
                <flux:table.column>Profession</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" src="{{ $user->getFirstMediaUrl('avatars', 'thumb') }}"
                                    name="{{ $user->name }}" color="auto" />
                                <div>
                                    <div class="font-medium">{{ $user->name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-sm">{{ $user->district?->name ?? 'N/A' }}</div>
                            <div class="text-xs text-zinc-500">{{ $user->division?->name }}</div>
                        </flux:table.cell>
                        <flux:table.cell class="italic text-zinc-600 dark:text-zinc-400">{{ $user->profession ?: '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm"
                                :color="$user->status === 'active' ? 'green' : ($user->status === 'suspended' ? 'red' : 'zinc')"
                                inset="top bottom">
                                {{ ucfirst($user->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($viewType === 'active')
                                <flux:button wire:click="showEditForm({{ $user->id }})" variant="ghost" size="sm"
                                    icon="pencil-square" />
                                <flux:button wire:confirm="Are you sure?" wire:click="deleteUser({{ $user->id }})" variant="ghost"
                                    size="sm" icon="trash" color="red" />
                            @else
                                <flux:button wire:click="restoreUser({{ $user->id }})" variant="ghost" size="sm" icon="arrow-path"
                                    color="green" />
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-12">No members match your criteria.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    @endif
</section>