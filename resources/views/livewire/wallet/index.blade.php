<?php

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Enums\WalletType;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $activeWalletType = 'main';
    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';

    #[Computed]
    public function wallets()
    {
        return Auth::user()->wallets()->get();
    }

    #[Computed]
    public function activeWallet()
    {
        return $this->wallets->firstWhere('type', $this->activeWalletType);
    }

    #[Computed]
    public function transactions()
    {
        return WalletTransaction::where('loggable_type', User::class)
            ->where('loggable_id', Auth::id())
            ->when($this->activeWalletType, fn($q) => $q->where('wallet_type', $this->activeWalletType))
            ->when($this->search, fn($q) => $q->where(function ($query) {
                $query->where('reference', 'like', "%{$this->search}%")
                    ->orWhere('notes', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('transaction_type', $this->filterType))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function totalBalance()
    {
        return $this->wallets->sum('balance');
    }

    public function setWalletType(string $type): void
    {
        $this->activeWalletType = $type;
        $this->resetPage();
    }

    public function getWalletIcon(string $type): string
    {
        return match ($type) {
            'main' => 'wallet',
            'referral' => 'gift',
            'commission' => 'briefcase',
            default => 'wallet',
        };
    }
}; ?>

<div class="space-y-6">
    <x-page-header :title="__('My Wallet')" :description="__('Manage your funds and view transaction history')">
        <x-slot:actions>
            <flux:button variant="primary" :href="route('wallet.fund')">
                <flux:icon name="plus" class="mr-2" />
                {{ __('Fund Wallet') }}
            </flux:button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <flux:card class="md:col-span-1 bg-gradient-to-br from-blue-600 to-blue-800 text-white overflow-hidden relative">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-8 -left-2 w-32 h-32 bg-white/10 rounded-full"></div>
            <div class="relative z-10">
                <div class="text-sm text-blue-200">{{ __('Total Balance') }}</div>
                <div class="text-3xl font-bold mt-1">{{ \Illuminate\Support\Number::currency($this->totalBalance) }}</div>
            </div>
        </flux:card>

        @foreach($this->wallets as $wallet)
        @if($wallet->type->value !== 'main')
        <flux:card
            class="{{ implode(' ', ['cursor-pointer transition-all duration-200', $wallet->type->value === $this->activeWalletType ? 'ring-2 ring-blue-500 shadow-lg' : 'hover:shadow-md']) }}"
            wire:click="setWalletType('{{ $wallet->type->value }}')">
            <div class="flex items-center gap-4">
                <div @class([ 'w-12 h-12 rounded-full flex items-center justify-center' , 'bg-green-100 text-green-600'=> $wallet->type->value === 'referral',
                    'bg-purple-100 text-purple-600' => $wallet->type->value === 'commission',
                    ])>
                    <flux:icon :name="$this->getWalletIcon($wallet->type->value)" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $wallet->type->getLabel() }}</div>
                    <div class="text-xl font-bold">{{ \Illuminate\Support\Number::currency($wallet->balance) }}</div>
                </div>
            </div>
        </flux:card>
        @endif
        @endforeach
    </div>

    @if($this->activeWallet)
    <flux:card>
        <flux:heading>{{ $this->activeWallet->type->getLabel() }} Wallet History</flux:heading>
        <flux:subheading>View the transaction history for your {{ strtolower($this->activeWallet->type->getLabel()) }} wallet.</flux:subheading>

        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search reference or notes..." />
            </div>
            <flux:select wire:model.live="filterStatus">
                <option value="">All Statuses</option>
                <option value="success">Success</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </flux:select>
            <flux:select wire:model.live="filterType">
                <option value="">All Types</option>
                <option value="increment">Credit</option>
                <option value="decrement">Debit</option>
            </flux:select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $transaction->notes ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $transaction->reference }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div @class([ 'text-sm font-semibold' , 'text-green-600'=> $transaction->isIncrement(),
                                'text-red-600' => !$transaction->isIncrement(),
                                ])>
                                {{ $transaction->isIncrement() ? '+' : '-' }} {{ \Illuminate\Support\Number::currency($transaction->amount) }}
                            </div>
                            <flux:badge :variant="$transaction->status === 'success' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger')" class="mt-1">
                                {{ ucfirst($transaction->status) }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ \Illuminate\Support\Number::currency($transaction->to_balance) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-12">
                            <div class="text-center">
                                <flux:icon name="document" class="w-12 h-12 mx-auto text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No transactions found</h3>
                                <p class="mt-1 text-sm text-gray-500">No transactions match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->transactions->hasPages())
        <div class="mt-6">
            {{ $this->transactions->links() }}
        </div>
        @endif
    </flux:card>
    @endif
</div>