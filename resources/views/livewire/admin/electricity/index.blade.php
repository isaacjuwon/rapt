<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\ElectricityPlan; // Changed model
use Illuminate\Database\Eloquent\Builder;

new #[Title('Manage Electricity Plans')] class extends Component { // Changed title
    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'name';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'asc';

    public ?ElectricityPlan $selected_electricity_plan = null; // Changed property name

    /**
     * Reset the pagination when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Sort the electricity plans by the given column. // Changed text
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
     * Get the electricity plans with pagination. // Changed text
     */
    #[Computed]
    public function electricityPlans(): \Illuminate\Pagination\LengthAwarePaginator // Changed computed property name
    {
        return ElectricityPlan::query() // Changed model
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
     * Select an electricity plan for deletion. // Changed text
     */
    public function deleteElectricityPlan(ElectricityPlan $electricityPlan): void // Changed method and parameter
    {
        $this->selected_electricity_plan = $electricityPlan; // Changed property
    }

    /**
     * Delete the selected electricity plan. // Changed text
     */
    public function confirmDeleteElectricityPlan(): void // Changed method
    {
        if ($this->selected_electricity_plan) { // Changed property
            $this->selected_electricity_plan->delete(); // Changed property
            $this->selected_electricity_plan = null; // Clear selected electricity plan // Changed property

            // Reset page if current page becomes empty after deletion
            if ($this->electricityPlans->isEmpty() && $this->page > 1) { // Changed computed property
                $this->previousPage();
            }

            Toaster::success(__('Electricity Plan deleted successfully.')); // Changed text
        } else {
            Toaster::error(__('No electricity plan selected for deletion.')); // Changed text
        }
        $this->modal('delete-electricity-plan')->close(); // Changed modal name
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Electricity Plans') }}</flux:heading> // Changed text
            <flux:subheading>
                {{ __('Manage electricity plans in your application. You can view, create, edit, and delete electricity plans.') }} // Changed text
            </flux:subheading>
        </div>

        <flux:spacer />

        <flux:button :href="route('admin.electricity.create')" wire:navigate> // Changed route
            {{ __('Create Electricity Plan') }} // Changed text
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.250ms="search"
                type="text"
                icon="magnifying-glass"
                placeholder="{{ __('Search electricity plans...') }}" // Changed text
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
                @forelse ($this->electricityPlans as $electricityPlan) {{-- Changed computed property and loop variable --}}
                <flux:table.row wire:key="{{ $electricityPlan->id }}">
                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <span class="font-medium">{{ $electricityPlan->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        {{ $electricityPlan->brand_id }}
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <flux:badge :variant="$electricityPlan->status ? 'success' : 'danger'">
                            {{ $electricityPlan->status ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $electricityPlan->created_at->format('M d, Y h:i A') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" aria-label="{{ __('Open action menu') }}"></flux:button>

                            <flux:menu>
                                <flux:menu.item :href="route('admin.electricity.edit', $electricityPlan)" icon="pencil" wire:navigate> {{-- Changed route --}}
                                    {{ __('Edit') }}
                                </flux:menu.item>

                                <flux:modal.trigger name="delete-electricity-plan"> {{-- Changed modal name --}}
                                    <flux:menu.item
                                        wire:click="deleteElectricityPlan({{ $electricityPlan->id }})"
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
                    <flux:table.cell colspan="4"> {{-- Adjusted colspan --}}
                        <div class="flex items-center justify-center gap-2.5 py-32">
                            <flux:icon.inbox variant="mini" />
                            <flux:heading>
                                {{ __('No electricity plans found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->electricityPlans->links() }} {{-- Changed computed property --}}
    </div>

    <!-- Delete Electricity Plan Modal -->
    <flux:modal name="delete-electricity-plan" class="max-w-md"> {{-- Changed modal name --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Delete Electricity Plan') }}</flux:heading> // Changed text

            <flux:text>
                {{ __('Are you sure you want to delete the electricity plan ":electricityPlanName"? This action cannot be undone.', ['electricityPlanName' => $this->selected_electricity_plan?->name]) }} // Changed text and property
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:button
                    wire:click="$dispatch('close-modal', 'delete-electricity-plan')"
                    variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    wire:click="confirmDeleteElectricityPlan"
                    variant="danger">
                    {{ __('Delete Electricity Plan') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>