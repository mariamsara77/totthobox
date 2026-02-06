<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

new class extends Component {
    public $name;
    public $permissionId;
    public $search = '';
    public $showModal = false;

    public function with()
    {
        return [
            'permissions' => Permission::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->get(),
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $this->permissionId = $permission->id;
        $this->name = $permission->name;
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->permissionId = null;
        $this->name = '';
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', Rule::unique('permissions', 'name')->ignore($this->permissionId)],
        ]);

        $permissionName = strtolower($this->name);

        Permission::updateOrCreate(['id' => $this->permissionId], ['name' => $permissionName, 'guard_name' => 'web']);

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', 'Permission saved successfully!');
    }

    public function delete($id)
    {
        Permission::findOrFail($id)->delete();
        $this->dispatch('notify', 'Permission deleted!');
    }
}; ?>

<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <flux:heading size="xl" level="1">
            Permissions Matrix
            <flux:subheading italic>Define specific atomic actions for the system access control.</flux:subheading>
        </flux:heading>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Add Permission
        </flux:button>
    </header>

    <flux:separator variant="subtle" />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full md:w-80">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Filter permissions..."
                icon="magnifying-glass" variant="filled" />
        </div>
        <flux:badge color="zinc" variant="outline" class="self-start uppercase tracking-widest text-[10px]">
            Total Actions: {{ count($permissions) }}
        </flux:badge>
    </div>


    <flux:table>
        <flux:table.columns>
            <flux:table.column>System Key</flux:table.column>
            <flux:table.column>Registry Date</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($permissions as $permission)
                <flux:table.row :key="$permission->id">
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:icon name="key" variant="micro" class="text-zinc-400" />
                            <span class="font-mono text-sm font-medium text-blue-600 dark:text-blue-400">
                                {{ $permission->name }}
                            </span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-500">
                        {{ $permission->created_at->format('M d, Y') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            <flux:button wire:click="edit({{ $permission->id }})" variant="ghost" size="sm"
                                icon="pencil-square" inset="top" />

                            <flux:button wire:click="delete({{ $permission->id }})"
                                wire:confirm="Permanent action: Delete this permission?" variant="ghost" size="sm"
                                icon="trash" class="text-red-500 hover:text-red-600" inset="top" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="py-16 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <flux:icon name="shield-exclamation" class="h-8 w-8 text-zinc-300" />
                            <span class="text-zinc-500">No security keys found in the registry.</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model="showModal" class="md:w-[450px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $permissionId ? 'Update Permission' : 'Register Action' }}
                </flux:heading>
                <flux:subheading>Ensure the name is unique and follows the 'verb-noun' pattern.</flux:subheading>
            </div>

            <flux:input label="Identifier Name" wire:model="name" placeholder="e.g. manage-settings" icon="tag"
                autofocus />

            <div class="flex gap-3 justify-end pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Confirm Entry</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
