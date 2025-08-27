<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

new #[Title('Create Permission')] class extends Component {
    public string $name = '';
    public string $guard_name = 'web'; // Default guard name

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')],
            'guard_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function store(): void
    {
        $validated = $this->validate();

        Permission::create($validated);

        Toaster::success(__('Permission created successfully.'));

        $this->redirect(route('admin.permissions.index'), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Create Permission') }}</flux:heading>
            <flux:subheading>
                {{ __('Fill in the details to create a new permission.') }}
            </flux:subheading>
        </div>

        <flux:spacer />
    </div>

    <flux:card>
        <form wire:submit="store" class="space-y-4">
            <flux:input
                label="{{ __('Permission Name') }}"
                wire:model="name"
                type="text"
                placeholder="{{ __('Enter permission name') }}"
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

            <div class="flex justify-end gap-2">
                <flux:button :href="route('admin.permissions.index')" wire:navigate variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button type="submit">
                    {{ __('Create Permission') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
