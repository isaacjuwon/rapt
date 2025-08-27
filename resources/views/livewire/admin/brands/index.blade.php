<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use App\Models\Brand;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

use App\Enums\Media\MediaCollectionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.admin')] #[Title('Manage Brands')] class extends Component {

    use WithPagination;
    use WithFileUploads;

    public  $image; //register image

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'created_at';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'desc';

    public string $name = '';

    public string $description = '';

    public string $status = '';

    public ?Brand $selected_brand = null;

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
        $this->reset(['name', 'description', 'status']);
        $this->resetErrorBag();
        $this->selected_brand = null;
    }

    /**
     * Sort the brands by the given column.
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
     * Get the brands with pagination.
     */
    #[Computed]
    public function brands(): LengthAwarePaginator
    {
        return Brand::query()
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
     * Handle an incoming create brand request.
     */
    public function createBrand(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        $brand = Brand::create([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        $brand->addMedia($this->image)->toMediaCollection(MediaCollectionType::Brand->value);
        $brand->refresh();

        $this->resetValues();
        $this->resetPage();
        $this->modal('create-brand')->close();

        Toaster::success(__('Brand created successfully.'));
    }

    /**
     * Select a brand for editing.
     */
    public function editBrand(Brand $brand): void
    {
        $this->selected_brand = $brand;
        $this->name = $brand->name;
        $this->description = $brand->description;
        $this->status = $brand->status;
        $this->image = $brand->image;
    }

    /**
     * Update the selected brand.
     */
    public function updateBrand(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        /*  if ($this->name === $this->selected_brand->name && $this->description === $this->selected_brand->description) {
            Toaster::info(__('Nothing changed.'));

            return;
        } */


        $this->selected_brand->update([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        $this->selected_brand->addMedia($this->image)->toMediaCollection(MediaCollectionType::Brand->value);

        $this->modal('edit-brand')->close();

        Toaster::success(__('Brand updated successfully.'));
    }

    /**
     * Select a brand for deletion.
     */
    public function deleteBrand(Brand $brand): void
    {
        $this->selected_brand = $brand;
    }

    /**
     * Delete the selected brand.
     */
    public function confirmDeleteBrand(): void
    {
        if ($this->selected_brand->id === Auth::brand()->id) {
            Toaster::error(__('You cannot delete your own account.'));

            return;
        }

        Brand::destroy($this->selected_brand->id);

        $this->resetValues();
        if ($this->brands->isEmpty()) {
            $this->resetPage();
        }
        $this->modal('delete-brand')->close();

        Toaster::success(__('Brand deleted successfully.'));
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Brands') }}</flux:heading>
            <flux:subheading>
                {{ __('Manage brands in your application. You can create, edit, and delete brands.') }}
            </flux:subheading>
        </div>

        <flux:spacer />

        <flux:modal.trigger name="create-brand">
            <flux:button
                variant="primary"
                icon="user-plus">
                {{ __('Create brand') }}
            </flux:button>
        </flux:modal.trigger>
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
                    wire:click="sort('description')"
                    sortable
                    :sorted="$sortBy === 'description'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Description') }}
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
                @forelse ($this->brands as $brand)
                <flux:table.row
                    wire:target="search"
                    wire:loading.delay.long.class="opacity-75"
                    wire:key="{{ $brand->id }}">
                    <flux:table.cell
                        align="start"
                        class="flex items-center gap-3">
                        @if ($brand->image)
                        <flux:avatar
                            src="{{ $brand->image_url }}"
                            size="sm" />
                        @else
                        <flux:avatar
                            initials="{{ $brand->initials() }}"
                            size="sm" />
                        @endif
                        <div>
                            <flux:heading class="!mb-0">
                                {{ $brand->name }}
                            </flux:heading>
                            <flux:subheading size="sm">
                                {{ $brand->description }}
                            </flux:subheading>
                        </div>
                    </flux:table.cell>


                    <flux:table.cell class="whitespace-nowrap">
                        {{ $brand->created_at->diffForHumans() }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $brand->updated_at->diffForHumans() }}
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
                                <flux:modal.trigger name="edit-brand">
                                    <flux:menu.item
                                        wire:click="editBrand({{ $brand->id }})"
                                        icon="pencil-square">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>

                                <flux:modal.trigger name="delete-brand">
                                    <flux:menu.item
                                        wire:click="deleteBrand({{ $brand->id }})"
                                        icon="trash"
                                        variant="danger"
                                        :disabled="$brand->id === auth()->id()">
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

        {{ $this->brands->links() }}
    </div>

    <flux:modal
        wire:close="resetValues"
        name="create-brand"
        class="w-sm max-w-[calc(100vw-3rem)]">
        <form
            wire:submit="createBrand"
            class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create brand') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Create a new brand account.') }}
                </flux:text>
            </div>

            <flux:input
                wire:model="name"
                type="text"
                label="{{ __('Name') }}"
                placeholder="{{ __('Your name') }}"
                autocomplete="name"
                autofocus />

            <flux:textarea
                wire:model="description"
                type="description"
                label="{{ __('Description') }}"
                placeholder="{{ __('Your description') }}"
                rows="2" />

            <flux:input
                type="file"
                wire:model="image" />



            <flux:field variant="outline">
                <flux:label for="status">{{ __('Status') }}</flux:label>
                <flux:switch
                    id="status"
                    wire:model="status"
                    :checked="$status" />
                <flux:error name="status" />
            </flux:field>


            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="submit"
                    variant="primary">
                    {{ __('Create') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal
        wire:close="resetValues"
        name="edit-brand"
        class="w-sm max-w-[calc(100vw-3rem)]">
        <form
            wire:submit="updateBrand"
            class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit brand') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Update a brand account.') }}
                </flux:text>

                <div class="mt-2 flex min-h-10 items-center gap-3">
                    <flux:icon.loading
                        wire:loading.delay.long
                        wire:target="editBrand, resetValues"
                        variant="mini"
                        class="inline" />

                    <div
                        wire:loading.delay.long.hidden
                        wire:target="editBrand, resetValues">
                        <flux:heading size="sm">
                            {{ $selected_brand?->name }}
                        </flux:heading>
                        <flux:text>
                            {{ $selected_brand?->description }}
                        </flux:text>
                    </div>
                </div>
            </div>

            <flux:input
                wire:model="name"
                type="text"
                label="{{ __('Name') }}"
                placeholder="{{ __('Your name') }}"
                autocomplete="name"
                autofocus />

            <flux:textarea
                wire:model="description"
                type="description"
                label="{{ __('Description') }}"
                placeholder="{{ __('Your description') }}"
                rows="2" />

            <flux:input
                type="file"
                wire:model="image" />


            <flux:field variant="outline">
                <flux:label for="status">{{ __('Status') }}</flux:label>
                <flux:switch
                    id="status"
                    wire:model="status"
                    :checked="$status" />
                <flux:error name="status" />
            </flux:field>


            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="submit"
                    variant="primary">
                    {{ __('Update') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal
        wire:close="resetValues"
        name="delete-brand"
        :closable="false"
        class="w-md max-w-[calc(100vw-3rem)]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ __('Are you sure you want to delete this brand?') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Once the brand is deleted, all of its resources and data will be permanently deleted.') }}
                </flux:subheading>

                <div class="mt-2 flex min-h-10 items-center gap-3">
                    <flux:icon.loading
                        wire:loading.delay.long
                        wire:target="deleteBrand, resetValues"
                        variant="mini"
                        class="inline" />

                    <div
                        wire:loading.delay.long.hidden
                        wire:target="deleteBrand, resetValues">
                        <flux:heading size="sm">
                            {{ $selected_brand?->name }}
                        </flux:heading>
                        <flux:text>
                            {{ $selected_brand?->description }}
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
                    wire:click="confirmDeleteBrand"
                    variant="danger">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>