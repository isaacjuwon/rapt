<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use App\Models\Brand;
use Illuminate\View\View;
use App\Models\AirtimePlan;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;


new #[Title('Manage Airtime')] #[Layout('layouts.components.admin')] class extends Component {

    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'created_at';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'desc';

    public string $name = '';

    public string $description = '';

    public string $status = '';

    public string $brand_id = '';

    public ?AirtimePlan $selected_plan = null;

    /**
     * Reset the pagination when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset the form values.
     */
    public function resetValues(): void
    {
        $this->reset(['name', 'description', 'status', 'brand_id']);
        $this->resetErrorBag();
        $this->selected_plan = null;
    }

    /**
     * Sort the airtimePlans by the given column.
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
     * Get the airtimePlans with pagination.
     */
    #[Computed]
    public function airtimePlans(): LengthAwarePaginator
    {
        return AirtimePlan::query()
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
     * Select a airtimePlan for deletion.
     */
    public function deleteAirtimePlan(AirtimePlan $airtimePlan): void
    {
        $this->selected_plan = $airtimePlan;
    }

    /**
     * Delete the selected airtimePlan.
     */
    public function confirmDeleteAirtimePlan(): void
    {
        if ($this->selected_plan->id === Auth::airtimePlan()->id) {
            Toaster::error(__('You cannot delete your own account.'));

            return;
        }

        AirtimePlan::destroy($this->selected_plan->id);

        $this->resetValues();
        if ($this->airtimePlans->isEmpty()) {
            $this->resetPage();
        }
        $this->modal('delete-airtimePlan')->close();

        Toaster::success(__('AirtimePlan deleted successfully.'));
    }

    public function with(): array
    {
        return [
            'brands' => Brand::all(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Airtime Plans') }}</flux:heading>
            <flux:subheading>
                {{ __('Manage airtime Plans in your application. You can create, edit, and delete airtime Plans.') }}
            </flux:subheading>
        </div>

        <flux:spacer />

        <livewire:admin.airtime.create />
    </div>

    <div>
        <div class="mb-2">
            <flux:input
                wire:model.live.debounce.250ms="search"
                type="text"
                icon="magnifying-glass"
                placeholder="{{ __('Search...') }}"
                autocomplete="off"
                autofocus
                clearable
                class="w-full sm:max-w-72" />
        </div>

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
                    {{ __('Brand') }}
                </flux:table.column>


                <flux:table.column
                    wire:click="sort('created_at')"
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection">
                    {{ __('Created at') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('updated_at')"
                    sortable
                    :sorted="$sortBy === 'updated_at'"
                    :direction="$sortDirection">
                    {{ __('Updated at') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->airtimePlans as $airtimePlan)
                <flux:table.row
                    wire:target="search"
                    wire:loading.delay.long.class="opacity-75"
                    wire:key="{{ $airtimePlan->id }}">
                    <flux:table.cell
                        align="start"
                        class="flex items-center gap-3">
                        @if ($airtimePlan->avatar)
                        <flux:avatar
                            src="{{ $airtimePlan->image }}"
                            size="sm" />
                        @else
                        <flux:avatar
                            initials="{{ $airtimePlan->initials }}"
                            size="sm" />
                        @endif
                        <div>
                            <flux:heading class="!mb-0">
                                {{ $airtimePlan->name }}
                            </flux:heading>
                            <flux:subheading size="sm">
                                {{ $airtimePlan->description }}
                            </flux:subheading>
                        </div>
                    </flux:table.cell>


                    <flux:table.cell class="whitespace-nowrap">
                        {{ $airtimePlan->created_at->diffForHumans() }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $airtimePlan->updated_at->diffForHumans() }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown
                            position="bottom"
                            align="end">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="ellipsis-horizontal"
                                inset="top bottom"
                                aria-label="{{ __('Open action menu') }}"></flux:button>

                            <flux:menu>
                                <flux:menu.item
                                    wire:click="$dispatch('load::plan', { 'airtimePlan' : '{{ $airtimePlan->id }}'})"
                                    icon="pencil-square">
                                    {{ __('Edit') }}
                                </flux:menu.item>

                                <flux:modal.trigger name="delete-airtimePlan">
                                    <flux:menu.item
                                        wire:click="deleteAirtimePlan({{ $airtimePlan->id }})"
                                        icon="trash"
                                        variant="danger"
                                        :disabled="$airtimePlan->id === auth()->id()">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        <div
                            class="flex items-center justify-center gap-2.5 py-32">
                            <flux:icon.inbox variant="mini" />

                            <flux:heading>
                                {{ __('No data found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->airtimePlans->links() }}
    </div>




    <flux:modal
        wire:close="resetValues"
        name="delete-airtimePlan"
        :closable="false"
        class="w-md max-w-[calc(100vw-3rem)]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ __('Are you sure you want to delete this airtime Plan?') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Once the airtime Plan is deleted, all of its resources and data will be permanently deleted.') }}
                </flux:subheading>

                <div class="mt-2 flex min-h-10 items-center gap-3">
                    <flux:icon.loading
                        wire:loading.delay.long
                        wire:target="deleteAirtimePlan, resetValues"
                        variant="mini"
                        class="inline" />

                    <div
                        wire:loading.delay.long.hidden
                        wire:target="deleteAirtimePlan, resetValues">
                        <flux:heading size="sm">
                            {{ $selected_plan?->name }}
                        </flux:heading>
                        <flux:text>
                            {{ $selected_plan?->description }}
                        </flux:text>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    wire:click="confirmDeleteAirtimePlan"
                    variant="danger">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <livewire:admin.airtime.edit />
</div>