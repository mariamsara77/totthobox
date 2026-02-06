<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

new class extends Component {
    // Form properties
    public $roleId;
    public $name;
    public $selectedPermissions = [];

    // UI States
    public $showModal = false;
    public $search = '';

    public function with()
    {
        return [
            'roles' => Role::with('permissions')
                ->where('name', 'like', '%' . $this->search . '%')
                ->get(),
            'allPermissions' => Permission::all()->groupBy(function ($perm) {
                // গ্রুপের নাম বের করার লজিক (e.g., 'edit users' -> 'users')
                return explode(' ', $perm->name)[1] ?? 'others';
            }),
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->roleId = null;
        $this->name = '';
        $this->selectedPermissions = [];
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|unique:roles,name,' . $this->roleId,
            'selectedPermissions' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            $role = Role::updateOrCreate(['id' => $this->roleId], ['name' => $this->name, 'guard_name' => 'web']);
            $role->syncPermissions($this->selectedPermissions);
        });

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', 'Role saved successfully!');
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin') {
            return $this->dispatch('notify', 'Cannot delete Super Admin!');
        }
        $role->delete();
        $this->dispatch('notify', 'Role deleted successfully.');
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <flux:heading size="xl" level="1">
            Role Management
            <flux:subheading>Manage administrative roles and their specific access permissions.</flux:subheading>
        </flux:heading>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Create New Role
        </flux:button>
    </header>

    <flux:separator variant="subtle" />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full md:w-80">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search roles..." icon="magnifying-glass" />
        </div>
        <flux:badge color="zinc" variant="outline" class="self-start uppercase tracking-widest text-[10px]">
            Total Roles: {{ count($roles) }}
        </flux:badge>
    </div>


    <flux:table>
        <flux:table.columns>
            <flux:table.column>Role Identity</flux:table.column>
            <flux:table.column>Assigned Access</flux:table.column>
            <flux:table.column align="end">Operations</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($roles as $role)
                <flux:table.row :key="$role->id">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:icon name="shield-check" class="text-zinc-400" variant="micro" />
                            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $role->name }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($role->permissions->take(5) as $permission)
                                <flux:badge size="sm" variant="subtle" class="text-[10px] uppercase">
                                    {{ $permission->name }}
                                </flux:badge>
                            @endforeach
                            @if ($role->permissions->count() > 5)
                                <span
                                    class="text-xs text-zinc-500 self-center pl-1">+{{ $role->permissions->count() - 5 }}
                                    more</span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            <flux:button wire:click="edit({{ $role->id }})" variant="ghost" size="sm"
                                icon="pencil-square" inset="top" />
                            <flux:button wire:click="delete({{ $role->id }})"
                                wire:confirm="Are you sure you want to delete this role?" variant="ghost" size="sm"
                                icon="trash" class="text-red-500 hover:text-red-600" inset="top" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="py-12 text-center text-zinc-500">
                        No roles found matching your criteria.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model="showModal" class="md:w-[650px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $roleId ? 'Update Role' : 'Create New Role' }}</flux:heading>
                <flux:subheading>Set the name and permissions for this role.</flux:subheading>
            </div>

            <flux:input label="Role Name" wire:model="name" placeholder="e.g. Editor" />

            <div class="space-y-3">
                <flux:label>Permissions Matrix</flux:label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[350px] overflow-y-auto p-1 custom-scrollbar">
                    @foreach ($allPermissions as $group => $perms)
                        <div
                            class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50">
                            <div
                                class="flex items-center gap-2 mb-3 border-b border-zinc-200 dark:border-zinc-700 pb-2">
                                <flux:icon name="folder" variant="micro" class="text-zinc-400" />
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">{{ $group }}</span>
                            </div>

                            <div class="space-y-2">
                                @foreach ($perms as $perm)
                                    <flux:checkbox wire:model="selectedPermissions" :value="$perm->name"
                                        :label="str_replace(['manage-', 'view-'], '', $perm->name)" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <flux:error name="selectedPermissions" />
            </div>

            <div class="flex gap-3 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
