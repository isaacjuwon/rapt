<?php

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Enums\WalletType;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $activeWalletType = 'main';
    public $search = '';
    public $filterStatus = '';
    public $filterType = '';

    public function getWalletsProperty()
    {
        return Auth::user()->wallets()->get();
    }

    public function getActiveWalletProperty()
    {
        return Auth::user()->wallets()->where('type', $this->activeWalletType)->first();
    }

    public function getTransactionsProperty()
    {
        return WalletTransaction::where('loggable_type', User::class)
            ->where('loggable_id', Auth::id())
            ->when($this->activeWalletType, fn($q) => $q->where('wallet_type', $this->activeWalletType))
            ->when($this->search, fn($q) => $q->where('reference', 'like', "%{$this->search}%")
                ->orWhere('notes', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('transaction_type', $this->filterType))
            ->latest()
            ->paginate(10);
    }

    public function getTotalBalanceProperty()
    {
        return Auth::user()->wallets()->sum('balance');
    }

    public function setWalletType($type)
    {
        $this->activeWalletType = $type;
        $this->resetPage();
    }
} ?>

<div class="space-y-6">
    <!-- Header -->
    <flux:card>
        <flux:heading size="lg">My Wallet</flux:heading>
        <flux:text size="sm">Manage your funds and view transaction history</flux:text>

        <div class="mt-4">
            <flux:button variant="primary" href="{{ route('wallet.fund') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Fund Wallet
            </flux:button>
        </div>
    </flux:card>

    <!-- Wallet Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Balance -->
        <flux:card class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
            <flux:heading size="md">Total Balance</flux:heading>
            <flux:text size="2xl" weight="bold">₦{{ number_format($this->totalBalance, 2) }}</flux:text>
        </flux:card>

        <!-- Individual Wallets -->
        @foreach($this->wallets as $wallet)
        <flux:card
            class="cursor-pointer hover:shadow-md transition-shadow {{ $wallet->type === $this->activeWalletType ? 'ring-2 ring-blue-500' : '' }}"
            wire:click="setWalletType('{{ $wallet->type }}')">
            <flux:heading size="sm">{{ $wallet->type->getLabel() }}</flux:heading>
            <flux:text size="xl" weight="bold">₦{{ number_format($wallet->balance, 2) }}</flux:text>
            <flux:text size="xs">
                {{ $wallet->type === $this->activeWalletType ? 'Active' : 'Click to view' }}
            </flux:text>
        </flux:card>
        @endforeach
    </div>

    <!-- Transaction History -->
    <flux:card>
        <flux:heading size="md">Transaction History</flux:heading>

        <!-- Filters -->
        <div class="mt-4 flex flex-col sm:flex-row gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search transactions..."
                class="flex-1" />
            <flux:select wire:model="filterStatus">
                <option value="">All Status</option>
                <option value="success">Success</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </flux:select>
            <flux:select wire:model="filterType">
                <option value="">All Types</option>
                <option value="increment">Credit</option>
                <option value="decrement">Debit</option>
            </flux:select>
        </div>

        <!-- Transactions Table -->
        <div class="mt-6 overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Date</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Reference</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Type</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Amount</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Balance</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->transactions as $transaction)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 px-4 text-sm font-mono">{{ $transaction->reference }}</td>
                        <td class="py-3 px-4">
                            <flux:badge :variant="$transaction->isIncrement() ? 'success' : 'danger'">
                                {{ $transaction->isIncrement() ? 'Credit' : 'Debit' }}
                            </flux:badge>
                        </td>
                        <td class="py-3 px-4 text-sm font-medium {{ $transaction->isIncrement() ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->isIncrement() ? '+' : '-' }}₦{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-sm">₦{{ number_format($transaction->to_balance, 2) }}</td>
                        <td class="py-3 px-4">
                            <flux:badge :variant="$transaction->status === 'success' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger')">
                                {{ ucfirst($transaction->status) }}
                            </flux:badge>
                        </td>
                        <td class="py-3 px-4 text-sm max-w-xs truncate">{{ $transaction->notes ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($this->transactions->hasPages())
        <div class="mt-6">
            {{ $this->transactions->links() }}
        </div>
        @endif

        <!-- Empty State -->
        @if($this->transactions->count() === 0)
        <div class="text-center py-12">
            <flux:text size="lg" weight="medium">No transactions found</flux:text>
            <flux:text size="sm">Get started by funding your wallet.</flux:text>
        </div>
        @endif
    </flux:card>
</div>