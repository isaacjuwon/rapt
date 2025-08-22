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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">My Wallet</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage your funds and view transaction history</p>
    </div>

    <!-- Wallet Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Balance Card -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Balance</p>
                    <p class="text-2xl font-bold">₦{{ number_format($this->totalBalance, 2) }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Wallet Type Cards -->
        @foreach($this->wallets as $wallet)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-2 {{ $wallet->type === $this->activeWalletType ? 'border-blue-500' : 'border-transparent' }} cursor-pointer hover:shadow-md transition-shadow"
            wire:click="setWalletType('{{ $wallet->type }}')">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $wallet->type->getLabel() }}</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">₦{{ number_format($wallet->balance, 2) }}</p>
                </div>
                <div class="{{ $wallet->type === WalletType::MAIN ? 'bg-green-100 text-green-600' : ($wallet->type === WalletType::BONUS ? 'bg-yellow-100 text-yellow-600' : 'bg-purple-100 text-purple-600') }} p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ $wallet->type === $this->activeWalletType ? 'Active' : 'Click to view' }}
            </div>
        </div>
        @endforeach
    </div>

    <!-- Fund Wallet Button -->
    <div class="flex justify-end">
        <flux:button variant="primary" href="{{ route('volt.fund') }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Fund Wallet
        </flux:button>
    </div>

    <!-- Transaction History -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Transaction History</h2>
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search -->
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search transactions..."
                        class="w-full sm:w-64" />

                    <!-- Status Filter -->