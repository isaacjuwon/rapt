<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

new #[Title('Manage Roles')] class extends Component {
    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'name';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'asc';

    public ?Role $selected_role = null;

    /**
     * Reset the pagination when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Sort the roles by the given column.
     */
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /**
     * Get the roles with pagination.
     */
    #[Computed]
    public function roles(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Role::query()
            ->when($this->search, function (Builder $query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->when($this->sortBy, function (Builder $query) {
                $query->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(10)
            ->onEachSide(2);
    }

    /**
     * Select a role for deletion.
     */
    public function deleteRole(Role $role): void
    {
        $this->selected_role = $role;
    }

    /**
     * Delete the selected role.
     */
    public function confirmDeleteRole(): void
    {
        if ($this->selected_role) {
            $this->selected_role->delete();
            $this->selected_role = null; // Clear selected role

            // Reset page if current page becomes empty after deletion
            if ($this->roles->isEmpty() && $this->page > 1) {
                $this->previousPage();
            }

            Toaster::success(__('Role deleted successfully.'));
        } else {
            Toaster::error(__('No role selected for deletion.'));
        }
        $this->modal('delete-role')->close();
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Roles') }}</flux:heading>
            <flux:subheading>
                {{ __('Manage roles in your application. You can view, create, edit, and delete roles.') }}
            </flux:subheading>
        </div>

        <flux:spacer />

        <flux:button :href="route('admin.roles.create')" wire:navigate>
            {{ __('Create Role') }}
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.250ms="search"
                type="text"
                icon="magnifying-glass"
                placeholder="{{ __('Search roles...') }}"
                autocomplete="off"
                clearable
                class="w-full" />
        </div>
    </div>

    <div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    wire:click="sort('name')"
                    sortable
                    :sorted="$sortBy === 'name'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Name') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('guard_name')"
                    sortable
                    :sorted="$sortBy === 'guard_name'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Guard Name') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('created_at')"
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection">
                    {{ __('Created At') }}
                </flux:table.column>

                <flux:table.column>
                    {{ __('Actions') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->roles as $role)
                <flux:table.row wire:key="{{ $role->id }}">
                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <span class="font-medium">{{ $role->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <flux:badge variant="outline">{{ $role->guard_name }}</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $role->created_at->format('M d, Y h:i A') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" aria-label="{{ __('Open action menu') }}"></flux:button>

                            <flux:menu>
                                <flux:menu.item :href="route('admin.roles.edit', $role)" icon="pencil" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:menu.item>

                                <flux:modal.trigger name="delete-role">
                                    <flux:menu.item
                                        wire:click="deleteRole({{ $role->id }})"
                                        icon="trash"
                                        variant="danger">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">
                        <div class="flex items-center justify-center gap-2.5 py-32">
                            <flux:icon.inbox variant="mini" />
                            <flux:heading>
                                {{ __('No roles found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->roles->links() }}
    </div>

    <!-- Delete Role Modal -->
    <flux:modal name="delete-role" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Delete Role') }}</flux:heading>

            <flux:text>
                {{ __('Are you sure you want to delete the role ":roleName"? This action cannot be undone.', ['roleName' => $this->selected_role?->name]) }}
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:button
                    wire:click="$dispatch('close-modal', 'delete-role')"
                    variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    wire:click="confirmDeleteRole"
                    variant="danger">
                    {{ __('Delete Role') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
