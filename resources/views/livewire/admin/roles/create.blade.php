<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;

new #[Title('Create Role')] class extends Component {
    public string $name = '';
    public string $guard_name = 'web'; // Default guard name
    public array $selectedPermissions = [];

    public Collection $allPermissions;

    public function mount(): void
    {
        $this->allPermissions = Permission::all()->groupBy('guard_name');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'guard_name' => ['required', 'string', 'max:255'],
            'selectedPermissions' => ['nullable', 'array'],
        ];
    }

    public function store(): void
    {
        $validated = $this->validate();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        $role->syncPermissions($validated['selectedPermissions']);

        Toaster::success(__('Role created successfully.'));

        $this->redirect(route('admin.roles.index'), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Create Role') }}</flux:heading>
            <flux:subheading>
                {{ __('Fill in the details to create a new role and assign permissions.') }}
            </flux:subheading>
        </div>

        <flux:spacer />
    </div>

    <flux:card>
        <form wire:submit="store" class="space-y-4">
            <flux:input
                label="{{ __('Role Name') }}"
                wire:model="name"
                type="text"
                placeholder="{{ __('Enter role name') }}"
                required
                autofocus />

            <flux:select
                label="{{ __('Guard Name') }}"
                wire:model="guard_name"
                placeholder="{{ __('Select guard name') }}"
                required>
                <flux:select.option value="web">{{ __('Web') }}</flux:select.option>
                <flux:select.option value="api">{{ __('API') }}</flux:select.option>
            </flux:select>

            <flux:label>{{ __('Permissions') }}</flux:label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($this->allPermissions as $guardName => $permissions)
                    <div class="border p-4 rounded-lg">
                        <h4 class="font-semibold mb-2">{{ ucfirst($guardName) }} Guard</h4>
                        @foreach ($permissions as $permission)
                            <div class="flex items-center mb-2">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label class="ml-2 text-sm text-gray-600">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2">
                <flux:button :href="route('admin.roles.index')" wire:navigate variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button type="submit">
                    {{ __('Create Role') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
