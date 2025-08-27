<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\EducationPlan; // Changed model
use Illuminate\Database\Eloquent\Builder;

new #[Title('Manage Education Plans')] class extends Component { // Changed title
    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'name';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'asc';

    public ?EducationPlan $selected_education_plan = null; // Changed property name

    /**
     * Reset the pagination when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Sort the education plans by the given column. // Changed text
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
     * Get the education plans with pagination. // Changed text
     */
    #[Computed]
    public function educationPlans(): \Illuminate\Pagination\LengthAwarePaginator // Changed computed property name
    {
        return EducationPlan::query() // Changed model
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
     * Select an education plan for deletion. // Changed text
     */
    public function deleteEducationPlan(EducationPlan $educationPlan): void // Changed method and parameter
    {
        $this->selected_education_plan = $educationPlan; // Changed property
    }

    /**
     * Delete the selected education plan. // Changed text
     */
    public function confirmDeleteEducationPlan(): void // Changed method
    {
        if ($this->selected_education_plan) { // Changed property
            $this->selected_education_plan->delete(); // Changed property
            $this->selected_education_plan = null; // Clear selected education plan // Changed property

            // Reset page if current page becomes empty after deletion
            if ($this->educationPlans->isEmpty() && $this->page > 1) { // Changed computed property
                $this->previousPage();
            }

            Toaster::success(__('Education Plan deleted successfully.')); // Changed text
        } else {
            Toaster::error(__('No education plan selected for deletion.')); // Changed text
        }
        $this->modal('delete-education-plan')->close(); // Changed modal name
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Education Plans') }}</flux:heading> // Changed text
            <flux:subheading>
                {{ __('Manage education plans in your application. You can view, create, edit, and delete education plans.') }} // Changed text
            </flux:subheading>
        </div>

        <flux:spacer />

        <flux:button :href="route('admin.educationplans.create')" wire:navigate> // Changed route
            {{ __('Create Education Plan') }}
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.250ms="search"
                type="text"
                icon="magnifying-glass"
                placeholder="{{ __('Search education plans...') }}" // Changed text
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
                @forelse ($this->educationPlans as $educationPlan) {{-- Changed computed property and loop variable --}}
                <flux:table.row wire:key="{{ $educationPlan->id }}">
                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <span class="font-medium">{{ $educationPlan->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        {{ \Illuminate\Support\Number::currency($educationPlan->price) }}
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        {{ $educationPlan->brand_id }}
                    </flux:table.cell>

                    <flux:table.cell align="start" class="whitespace-nowrap">
                        <flux:badge :variant="$educationPlan->status ? 'success' : 'danger'">
                            {{ $educationPlan->status ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $educationPlan->created_at->format('M d, Y h:i A') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" aria-label="{{ __('Open action menu') }}"></flux:button>

                            <flux:menu>
                                <flux:menu.item :href="route('admin.educationplans.edit', $educationPlan)" icon="pencil" wire:navigate> {{-- Changed route --}}
                                    {{ __('Edit') }}
                                </flux:menu.item>

                                <flux:modal.trigger name="delete-education-plan"> {{-- Changed modal name --}}
                                    <flux:menu.item
                                        wire:click="deleteEducationPlan({{ $educationPlan->id }})"
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
                                {{ __('No education plans found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->educationPlans->links() }}
    </div>

    <!-- Delete Education Plan Modal -->
    <flux:modal name="delete-education-plan" class="max-w-md"> {{-- Changed modal name --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Delete Education Plan') }}</flux:heading> // Changed text

            <flux:text>
                {{ __('Are you sure you want to delete the education plan ":educationPlanName"? This action cannot be undone.', ['educationPlanName' => $this->selected_education_plan?->name]) }} // Changed text and property
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:button
                    wire:click="$dispatch('close-modal', 'delete-education-plan')"
                    variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    wire:click="confirmDeleteEducationPlan"
                    variant="danger">
                    {{ __('Delete Education Plan') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
