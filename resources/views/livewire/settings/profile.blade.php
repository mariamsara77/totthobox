<?php

use App\Models\User;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\ClassLevel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar = null;
    public ?string $currentAvatarUrl = null;
    public ?string $profession = '';
    public ?string $location = '';
    public ?string $bio = '';

    public $division_id = null;
    public $district_id = null;
    public $thana_id = null;
    public $class_level_id = null;

    public string $selected_role = '';
    public array $available_roles = [];

    public $classLevels = [];
    public $divisions = [];
    public $districts = [];
    public $thanas = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->profession = $user->profession ?? '';
        $this->bio = $user->bio ?? '';
        $this->location = $user->location ?? '';
        $this->division_id = $user->division_id;
        $this->district_id = $user->district_id;
        $this->thana_id = $user->thana_id;
        $this->class_level_id = $user->class_level_id;

        $this->selected_role = $user->getRoleNames()->first() ?? 'user';

        // ডাইনামিক রোল ফিল্টারিং
        $this->available_roles = Role::pluck('name')->filter(function ($roleName) use ($user) {
            // ১. 'user' রোলটি ডিফল্ট হিসেবে সবসময় থাকবে
            if (strtolower($roleName) === 'user')
                return true;

            // ২. বর্তমান ইউজার যদি অলরেডি এই রোলে থাকে, তবে সেটি দেখাবে
            if ($this->selected_role === $roleName)
                return true;

            // ৩. পারমিশন নাম তৈরি করা (যেমন: assign editor)
            $permissionName = 'assign ' . strtolower($roleName);

            // চেক করা হচ্ছে পারমিশনটি ডেটাবেসে আছে কি না এবং ইউজারের সেই পারমিশন আছে কি না
            // Permission::whereName(...)->exists() ব্যবহার করলে এরর আসবে না
            return Permission::where('name', $permissionName)->exists() && $user->hasPermissionTo($permissionName);

        })->unique()->toArray();

        $this->currentAvatarUrl = $user->getFirstMediaUrl('avatars', 'thumb');
        $this->divisions = Division::select('id', 'name')->get();
        $this->classLevels = ClassLevel::select('id', 'name')->get();
        $this->loadDependentData();
    }

    public function loadDependentData()
    {
        if ($this->division_id) {
            $this->districts = District::where('division_id', $this->division_id)->select('id', 'name')->get();
        }
        if ($this->district_id) {
            $this->thanas = Thana::where('district_id', $this->district_id)->select('id', 'name')->get();
        }
    }

    public function updatedDivisionId($value)
    {
        $this->districts = District::where('division_id', $value)->select('id', 'name')->get();
        $this->reset(['district_id', 'thana_id', 'thanas']);
    }

    public function updatedDistrictId($value)
    {
        $this->thanas = Thana::where('district_id', $value)->select('id', 'name')->get();
        $this->reset('thana_id');
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'selected_role' => ['required', Rule::in($this->available_roles)],
            'class_level_id' => [Rule::requiredIf(strtolower($this->selected_role) === 'student'), 'nullable', 'exists:class_levels,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'profession' => $this->profession,
            'bio' => $this->bio,
            'location' => $this->location,
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'class_level_id' => (strtolower($this->selected_role) === 'student') ? $this->class_level_id : null,
        ]);

        if ($user->getRoleNames()->first() !== $this->selected_role) {
            $user->syncRoles([$this->selected_role ?: 'user']);
        }

        if ($this->avatar) {
            $user->addMedia($this->avatar->getRealPath())
                ->usingFileName(Str::slug($this->name) . '-' . time() . '.' . $this->avatar->getClientOriginalExtension())
                ->toMediaCollection('avatars');
            $this->avatar = null;
            $this->currentAvatarUrl = $user->fresh()->getFirstMediaUrl('avatars', 'thumb');
        }

        $this->dispatch('profile-updated', name: $user->name);
        session()->flash('success', 'তথ্য সফলভাবে সংরক্ষিত হয়েছে।');
    }

    public function removeCurrentRole()
    {
        $user = Auth::user();
        $user->syncRoles(['user']);
        $this->selected_role = 'user';
        $this->dispatch('profile-updated', name: $user->name);
    }

    public function removeAvatar(): void
    {
        if ($this->avatar) {
            $this->avatar = null;
            return;
        }
        Auth::user()->clearMediaCollection('avatars');
        $this->currentAvatarUrl = null;
    }
}; ?>

<section class="max-w-2xl mx-auto">
    @include('partials.settings-heading')


    <form wire:submit="updateProfileInformation" class="mt-6 space-y-8">

        {{-- Avatar Section --}}
        <flux:field>
            <flux:label>প্রোফাইল ছবি</flux:label>
            <div class="mt-2 flex items-center gap-6">
                <div class="relative size-20 flex-shrink-0">
                    @php
                        $previewUrl = null;
                        try {
                            if ($avatar && method_exists($avatar, 'temporaryUrl'))
                                $previewUrl = $avatar->temporaryUrl();
                        } catch (\Exception $e) {
                        }
                    @endphp
                    <flux:avatar :src="$previewUrl ?? ($currentAvatarUrl ?: null)" size="4xl" :name="$name"
                        class="rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700" />
                </div>
                <div class="flex-1">
                    <flux:input type="file" wire:model="avatar" accept="image/*" variant="filled" size="xs" />
                    <flux:error name="avatar" />
                    @if($avatar || $currentAvatarUrl)
                        <button type="button" wire:click="removeAvatar"
                            class="mt-1 text-xs text-red-500 hover:text-red-600 transition">ছবিটি বাদ দিন</button>
                    @endif
                </div>
            </div>
        </flux:field>

        <flux:separator variant="subtle" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input wire:model="name" label="সম্পূর্ণ নাম" required />
            <flux:input wire:model="email" label="ইমেইল ঠিকানা" type="email" required />
            <div>
                <div class="flex mb-2 items-center justify-between">

                    <flux:label>
                        অ্যাকাউন্টের ধরন (Role)
                    </flux:label>
                    @if($selected_role !== 'user')
                        <flux:button variant="ghost" wire:click="removeCurrentRole" class="!text-red-500" size="xs">রোল
                            রিমুভ করুন
                        </flux:button>
                    @else
                        <div></div>
                    @endif

                </div>
                <flux:select wire:model.live="selected_role" variant="listbox">
                    @foreach($available_roles as $role)
                        <flux:select.option value="{{ $role }}">{{ ucfirst($role) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input wire:model="profession" label="পেশা" />
        </div>

        <div x-show="$wire.selected_role.toLowerCase() === 'student'" x-collapse>
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl">
                <flux:select wire:model="class_level_id" label="আপনার শ্রেণী" variant="listbox"
                    placeholder="নির্বাচন করুন">
                    @foreach ($classLevels as $level)
                        <flux:select.option value="{{ $level->id }}">{{ $level->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div wire:ignore>
            <flux:editor wire:model="bio" label="নিজের সম্পর্কে" />
        </div>

        <flux:input wire:model="location" label="ঠিকানা" />

        <flux:separator variant="subtle" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <flux:select wire:model.live="division_id" label="বিভাগ" variant="listbox" placeholder="নির্বাচন করুন">
                {{-- <flux:select.option value="" selected>নির্বাচন করুন</flux:select.option> --}}
                @foreach($divisions as $division) <flux:select.option value="{{ $division->id }}">
                        {{ $division->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="district_id" label="জেলা" :disabled="!$division_id" variant="listbox"
                placeholder="নির্বাচন করুন">
                <flux:select.option value="">নির্বাচন করুন</flux:select.option>
                @foreach($districts as $district) <flux:select.option value="{{ $district->id }}">
                        {{ $district->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="thana_id" label="থানা" :disabled="!$district_id" variant="listbox"
                placeholder="নির্বাচন করুন">
                <flux:select.option value="">নির্বাচন করুন</flux:select.option>
                @foreach($thanas as $thana) <flux:select.option value="{{ $thana->id }}">{{ $thana->name }}
                </flux:select.option> @endforeach
            </flux:select>
        </div>

        <div class="flex items-center justify-between pt-4">
            @if($selected_role !== 'user')
                <flux:button variant="ghost" wire:click="removeCurrentRole" class="text-red-500">রোল রিমুভ করুন
                </flux:button>
            @else
                <div></div>
            @endif

            <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                <span>সংরক্ষণ করুন</span>
            </flux:button>
        </div>
    </form>

</section>