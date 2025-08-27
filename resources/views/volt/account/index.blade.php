<x-app-layout>
        <x-page-header :title="__('Virtual Account')" :description="__('Manage your virtual bank accounts.')" />

    <div class="space-y-6">
        @if (session()->has('success'))
            <flux:callout variant="success" :title="__('Success')" :message="session('success')" />
        @endif

        @if (session()->has('error'))
            <flux:callout variant="danger" :title="__('Error')" :message="session('error')" />
        @endif

        @if ($virtualAccount)
            <flux:card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Your Virtual Account Details</h3>
                    <flux:icon name="banknotes" class="w-8 h-8 text-gray-500" />
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Account Name:</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $virtualAccount->account_name }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Account Number:</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $virtualAccount->account_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Bank Name:</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $virtualAccount->bank_name }}</span>
                    </div>
                </div>
            </flux:card>
        @else
            <flux:card class="p-6 text-center">
                <div class="flex flex-col items-center justify-center mb-4">
                    <flux:icon name="banknotes" class="w-12 h-12 text-gray-400 mb-4" />
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Virtual Account Found</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Generate a virtual account to easily receive payments.</p>
                </div>
                <flux:button
                    type="button"
                    variant="primary"
                    wire:click="generateAccount"
                    wire:loading.attr="disabled"
                    wire:target="generateAccount"
                >
                    <span wire:loading.remove wire:target="generateAccount">
                        Generate Virtual Account
                    </span>
                    <span wire:loading wire:target="generateAccount">
                        Generating...
                    </span>
                </flux:button>
            </flux:card>
        @endif
    </div>
</x-app-layout>
