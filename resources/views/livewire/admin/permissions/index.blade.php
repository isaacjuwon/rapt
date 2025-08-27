<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

new #[Title('Manage Permissions')] class extends Component {
    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'name';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'asc';

    public ?Permission $selected_permission = null;

    /**
     * Reset the pagination when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Sort the permissions by the given column.
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
     * Get the permissions with pagination.
     */
    #[Computed]
    public function permissions(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Permission::query()
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
     * Select a permission for deletion.
     */
    public function deletePermission(Permission $permission): void
    {
        $this->selected_permission = $permission;
    }

    /**
     * Delete the selected permission.
     */
    public function confirmDeletePermission(): void
    {
        if ($this->selected_permission) {
            $this->selected_permission->delete();
            $this->selected_permission = null; // Clear selected permission

            // Reset page if current page becomes empty after deletion
            if ($this->permissions->isEmpty() && $this->page > 1) {
                $this->previousPage();
            }

            Toaster::success(__('Permission deleted successfully.'));
        } else {
            Toaster::error(__('No permission selected for deletion.'));
        }
        $this->modal('delete-permission')->close();
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Permissions') }}</flux:heading>
            <flux:subheading>
                {{ __('Manage permissions in your application. You can view, create, edit, and delete permissions.') }}
            </flux:subheading>
        </div>

        <flux:spacer />

        <flux:button :href="route('admin.permissions.create')" wire:navigate>
            {{ __('Create Permission') }}
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.250ms="search"
                type="text"
                icon="magnifying-glass"
                placeholder="{{ __('Search permissions...') }}"
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
                @forelse ($this->permissions as $permission)
                <flux:table.row wire:key="{{ $permission->id }}">
                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <span class="font-medium">{{ $permission->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <flux:badge variant="outline">{{ $permission->guard_name }}</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $permission->created_at->format('M d, Y h:i A') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" aria-label="{{ __('Open action menu') }}"></flux:button>

                            <flux:menu>
                                <flux:menu.item :href="route('admin.permissions.edit', $permission)" icon="pencil" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:menu.item>

                                <flux:modal.trigger name="delete-permission">
                                    <flux:menu.item
                                        wire:click="deletePermission({{ $permission->id }})"
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
                                {{ __('No permissions found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->permissions->links() }}
    </div>

    <!-- Delete Permission Modal -->
    <flux:modal name="delete-permission" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Delete Permission') }}</flux:heading>

            <flux:text>
                {{ __('Are you sure you want to delete the permission ":permissionName"? This action cannot be undone.', ['permissionName' => $this->selected_permission?->name]) }}
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:button
                    wire:click="$dispatch('close-modal', 'delete-permission')"
                    variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    wire:click="confirmDeletePermission"
                    variant="danger">
                    {{ __('Delete Permission') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
