<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\DataPlan;
use Illuminate\Database\Eloquent\Builder;

new #[Title('Manage Data Plans')] class extends Component {
    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'name';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'asc';

    public ?DataPlan $selected_data_plan = null;

    /**
     * Reset the pagination when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Sort the data plans by the given column.
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
     * Get the data plans with pagination.
     */
    #[Computed]
    public function dataPlans(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return DataPlan::query()
            ->when($this->search, function (Builder $query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->when($this->sortBy, function (Builder $query) {
                $query->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(10)
            ->onEachSide(2);
    }

    /**
     * Select a data plan for deletion.
     */
    public function deleteDataPlan(DataPlan $dataPlan): void
    {
        $this->selected_data_plan = $dataPlan;
    }

    /**
     * Delete the selected data plan.
     */
    public function confirmDeleteDataPlan(): void
    {
        if ($this->selected_data_plan) {
            $this->selected_data_plan->delete();
            $this->selected_data_plan = null; // Clear selected data plan

            // Reset page if current page becomes empty after deletion
            if ($this->dataPlans->isEmpty() && $this->page > 1) {
                $this->previousPage();
            }

            Toaster::success(__('Data Plan deleted successfully.'));
        } else {
            Toaster::error(__('No data plan selected for deletion.'));
        }
        $this->modal('delete-data-plan')->close();
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Data Plans') }}</flux:heading>
            <flux:subheading>
                {{ __('Manage data plans in your application. You can view, create, edit, and delete data plans.') }}
            </flux:subheading>
        </div>

        <flux:spacer />

        <flux:button :href="route('admin.data.create')" wire:navigate>
            {{ __('Create Data Plan') }}
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.250ms="search"
                type="text"
                icon="magnifying-glass"
                placeholder="{{ __('Search data plans...') }}"
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
                    wire:click="sort('price')"
                    sortable
                    :sorted="$sortBy === 'price'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Price') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('brand_id')"
                    sortable
                    :sorted="$sortBy === 'brand_id'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Brand ID') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('status')"
                    sortable
                    :sorted="$sortBy === 'status'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Status') }}
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
                @forelse ($this->dataPlans as $dataPlan)
                <flux:table.row wire:key="{{ $dataPlan->id }}">
                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <span class="font-medium">{{ $dataPlan->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        {{ \Illuminate\Support\Number::currency($dataPlan->price) }}
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        {{ $dataPlan->brand_id }}
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <flux:badge :variant="$dataPlan->status ? 'success' : 'danger'">
                            {{ $dataPlan->status ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $dataPlan->created_at->format('M d, Y h:i A') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" aria-label="{{ __('Open action menu') }}"></flux:button>

                            <flux:menu>
                                <flux:menu.item :href="route('admin.data.edit', $dataPlan)" icon="pencil" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:menu.item>

                                <flux:modal.trigger name="delete-data-plan">
                                    <flux:menu.item
                                        wire:click="deleteDataPlan({{ $dataPlan->id }})"
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
                    <flux:table.cell colspan="5"> {{-- Adjusted colspan --}}
                        <div class="flex items-center justify-center gap-2.5 py-32">
                            <flux:icon.inbox variant="mini" />
                            <flux:heading>
                                {{ __('No data plans found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->dataPlans->links() }}
    </div>

    <!-- Delete Data Plan Modal -->
    <flux:modal name="delete-data-plan" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Delete Data Plan') }}</flux:heading>

            <flux:text>
                {{ __('Are you sure you want to delete the data plan ":dataPlanName"? This action cannot be undone.', ['dataPlanName' => $this->selected_data_plan?->name]) }}
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:button
                    wire:click="$dispatch('close-modal', 'delete-data-plan')"
                    variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    wire:click="confirmDeleteDataPlan"
                    variant="danger">
                    {{ __('Delete Data Plan') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>