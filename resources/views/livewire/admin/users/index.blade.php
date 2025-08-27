<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;


new #[Title('Manage Users')] class extends Component {

    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'created_at';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'desc';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?User $selected_user = null;

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
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->resetErrorBag();
        $this->selected_user = null;
    }

    /**
     * Sort the users by the given column.
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
     * Get the users with pagination.
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, function (Builder $query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->sortBy, function (Builder $query) {
                $query->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(10)
            ->onEachSide(2);
    }

    /**
     * Handle an incoming create user request.
     */
    public function createUser(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $this->resetValues();
        $this->resetPage();
        $this->modal('create-user')->close();

        Toaster::success(__('User created successfully.'));
    }

    /**
     * Select a user for editing.
     */
    public function editUser(User $user): void
    {
        $this->selected_user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Update the selected user.
     */
    public function updateUser(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class . ',email,' . $this->selected_user->id],
        ]);

        if ($this->name === $this->selected_user->name && $this->email === $this->selected_user->email) {
            Toaster::info(__('Nothing changed.'));

            return;
        }

        $this->selected_user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->resetValues();
        $this->modal('edit-user')->close();

        Toaster::success(__('User updated successfully.'));
    }

    /**
     * Select a user for deletion.
     */
    public function deleteUser(User $user): void
    {
        $this->selected_user = $user;
    }

    /**
     * Delete the selected user.
     */
    public function confirmDeleteUser(): void
    {
        if ($this->selected_user->id === Auth::user()->id) {
            Toaster::error(__('You cannot delete your own account.'));

            return;
        }

        User::destroy($this->selected_user->id);

        $this->resetValues();
        if ($this->users->isEmpty()) {
            $this->resetPage();
        }
        $this->modal('delete-user')->close();

        Toaster::success(__('User deleted successfully.'));
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Users') }}</flux:heading>
            <flux:subheading>
                {{ __('Manage users in your application. You can create, edit, and delete users.') }}
            </flux:subheading>
        </div>

        <flux:spacer />

        <flux:modal.trigger name="create-user">
            <flux:button
                variant="primary"
                icon="user-plus">
                {{ __('Create user') }}
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
                    wire:click="sort('email_verified_at')"
                    sortable
                    :sorted="$sortBy === 'email_verified_at'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Email status') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('is_verified')"
                    sortable
                    :sorted="$sortBy === 'is_verified'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Verification Status') }}
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
                @forelse ($this->users as $user)
                <flux:table.row
                    wire:target="search"
                    wire:loading.delay.long.class="opacity-75"
                    wire:key="{{ $user->id }}">
                    <flux:table.cell
                        align="start"
                        class="flex items-center gap-3">
                        @if ($user->avatar)
                        <flux:avatar
                            src="{{ $user->avatar }}"
                            size="sm" />
                        @else
                        <flux:avatar
                            initials="{{ $user->initials() }}"
                            size="sm" />
                        @endif
                        <div>
                            <flux:heading class="!mb-0">
                                {{ $user->name }}
                            </flux:heading>
                            <flux:subheading size="sm">
                                {{ $user->email }}
                            </flux:subheading>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell
                        align="start"
                        class="whitespace-nowrap">
                        @if ($user->email_verified_at)
                        <flux:badge
                            size="sm"
                            icon="check-badge">
                            {{ __('Verified') }}
                        </flux:badge>
                        @else
                        <flux:badge
                            size="sm"
                            icon="x-circle"
                            class="opacity-50">
                            {{ __('Unverified') }}
                        </flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell
                        align="start"
                        class="whitespace-nowrap">
                        @if ($user->isVerified())
                        <flux:badge
                            size="sm"
                            icon="check-badge">
                            {{ __('Verified') }}
                        </flux:badge>
                        @else
                        <flux:badge
                            size="sm"
                            icon="x-circle"
                            class="opacity-50">
                            {{ __('Unverified') }}
                        </flux:badge>
                        @endif
                    </flux:table.table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $user->created_at->diffForHumans() }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $user->updated_at->diffForHumans() }}
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
                                <flux:modal.trigger name="edit-user">
                                    <flux:menu.item
                                        wire:click="editUser({{ $user->id }})"
                                        icon="pencil-square">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>

                                <flux:modal.trigger name="delete-user">
                                    <flux:menu.item
                                        wire:click="deleteUser({{ $user->id }})"
                                        icon="trash"
                                        variant="danger"
                                        :disabled="$user->id === auth()->id()">
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

        {{ $this->users->links() }}
    </div>

    <flux:modal
        wire:close="resetValues"
        name="create-user"
        class="w-sm max-w-[calc(100vw-3rem)]">
        <form
            wire:submit="createUser"
            class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create user') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Create a new user account.') }}
                </flux:text>
            </div>

            <flux:input
                wire:model="name"
                type="text"
                label="{{ __('Name') }}"
                placeholder="{{ __('Your name') }}"
                autocomplete="name"
                autofocus />

            <flux:input
                wire:model="email"
                type="email"
                label="{{ __('Email') }}"
                placeholder="{{ __('Your email address') }}"
                autocomplete="email" />

            <flux:input
                wire:model="password"
                type="password"
                label="{{ __('Password') }}"
                placeholder="{{ __('Your password') }}"
                autocomplete="password"
                viewable />

            <flux:input
                wire:model="password_confirmation"
                type="password"
                label="{{ __('Password confirmation') }}"
                placeholder="{{ __('Your password confirmation') }}"
                autocomplete="password"
                viewable />

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
        name="edit-user"
        class="w-sm max-w-[calc(100vw-3rem)]">
        <form
            wire:submit="updateUser"
            class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit user') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Update a user account.') }}
                </flux:text>

                <div class="mt-2 flex min-h-10 items-center gap-3">
                    <flux:icon.loading
                        wire:loading.delay.long
                        wire:target="editUser, resetValues"
                        variant="mini"
                        class="inline" />

                    <div
                        wire:loading.delay.long.hidden
                        wire:target="editUser, resetValues">
                        <flux:heading size="sm">
                            {{ $selected_user?->name }}
                        </flux:heading>
                        <flux:text>
                            {{ $selected_user?->email }}
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

            <flux:input
                wire:model="email"
                type="email"
                label="{{ __('Email') }}"
                placeholder="{{ __('Your email address') }}"
                autocomplete="email" />

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
        name="delete-user"
        :closable="false"
        class="w-md max-w-[calc(100vw-3rem)]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ __('Are you sure you want to delete this user?') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Once the user is deleted, all of its resources and data will be permanently deleted.') }}
                </flux:subheading>

                <div class="mt-2 flex min-h-10 items-center gap-3">
                    <flux:icon.loading
                        wire:loading.delay.long
                        wire:target="deleteUser, resetValues"
                        variant="mini"
                        class="inline" />

                    <div
                        wire:loading.delay.long.hidden
                        wire:target="deleteUser, resetValues">
                        <flux:heading size="sm">
                            {{ $selected_user?->name }}
                        </flux:heading>
                        <flux:text>
                            {{ $selected_user?->email }}
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
                    wire:click="confirmDeleteUser"
                    variant="danger">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>