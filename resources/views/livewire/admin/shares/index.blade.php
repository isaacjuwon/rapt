<?php

use App\Models\ShareTransaction;
use App\Models\User;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $type = 'all';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    #[Computed]
    public function pendingTransactions()
    {
        return ShareTransaction::with('user')
            ->where('status', 'pending')
            ->where('type', 'sell')
            ->latest()
            ->take(10)
            ->get();
    }

    #[Computed]
    public function transactions()
    {
        $query = ShareTransaction::with('user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->type !== 'all', function ($query) {
                $query->where('type', $this->type);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    #[Computed]
    public function stats()
    {
        return [
            'total_transactions' => ShareTransaction::count(),
            'pending_transactions' => ShareTransaction::where('status', 'pending')->count(),
            'completed_transactions' => ShareTransaction::where('status', 'completed')->count(),
            'rejected_transactions' => ShareTransaction::where('status', 'rejected')->count(),
        ];
    }

    public function approveTransaction(int $transactionId): void
    {
        try {
            $transaction = ShareTransaction::findOrFail($transactionId);
            $user = $transaction->user;

            $user->approveShareSale($transactionId);

            Toaster::success('Share sale approved successfully!');
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    public function rejectTransaction(int $transactionId): void
    {
        try {
            $transaction = ShareTransaction::findOrFail($transactionId);
            $user = $transaction->user;

            $user->rejectShareSale($transactionId, 'Rejected by admin');

            Toaster::success('Share sale rejected successfully!');
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = 'all';
        $this->type = 'all';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
    }
}; ?>

<div>
    <!-- Header -->
    <flux:header>
        <flux:heading size="xl">Share Transactions Management</flux:heading>
        <flux:subheading>Manage and approve share sale transactions</flux:subheading>
    </flux:header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <flux:card>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $this->stats['total_transactions'] }}</div>
                <div class="text-sm text-gray-500">Total Transactions</div>
            </div>
        </flux:card>

        <flux:card>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $this->stats['pending_transactions'] }}</div>
                <div class="text-sm text-gray-500">Pending Approval</div>
            </div>
        </flux:card>

        <flux:card>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $this->stats['completed_transactions'] }}</div>
                <div class="text-sm text-gray-500">Completed</div>
            </div>
        </flux:card>

        <flux:card>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ $this->stats['rejected_transactions'] }}</div>
                <div class="text-sm text-gray-500">Rejected</div>
            </div>
        </flux:card>
    </div>

    <!-- Pending Transactions Alert -->
    @if ($this->pendingTransactions->count() > 0)
    <flux:card class="mb-6 border-yellow-200 bg-yellow-50">
        <flux:heading size="lg" class="text-yellow-800">Pending Share Sales Requiring Approval</flux:heading>

        <div class="space-y-3 mt-4">
            @foreach ($this->pendingTransactions as $transaction)
            <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="text-sm font-semibold">{{ $transaction->user->initials() }}</span>
                    </div>
                    <div>
                        <div class="font-semibold">{{ $transaction->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $transaction->quantity }} shares - ${{ number_format($transaction->total_amount, 2) }}</div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <flux:button
                        wire:click="approveTransaction({{ $transaction->id }})"
                        size="sm"
                        class="bg-green-600 hover:bg-green-700">
                        Approve
                    </flux:button>
                    <flux:button
                        wire:click="rejectTransaction({{ $transaction->id }})"
                        size="sm"
                        variant="danger"
                        class="bg-red-600 hover:bg-red-700">
                        Reject
                    </flux:button>
                </div>
            </div>
            @endforeach
        </div>
    </flux:card>
    @endif

    <!-- Filters -->
    <flux:card class="mb-6">
        <flux:heading>Filters</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <flux:input
                label="Search"
                wire:model.live="search"
                placeholder="Search by transaction ID or user..." />

            <flux:select
                label="Status"
                wire:model.live="status">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
            </flux:select>

            <flux:select
                label="Type"
                wire:model.live="type">
                <option value="all">All Types</option>
                <option value="buy">Buy</option>
                <option value="sell">Sell</option>
            </flux:select>

            <div class="flex items-end">
                <flux:button
                    wire:click="resetFilters"
                    variant="outline"
                    class="w-full">
                    Reset Filters
                </flux:button>
            </div>
        </div>
    </flux:card>

    <!-- Transactions Table -->
    <flux:card>
        <flux:heading>All Transactions</flux:heading>

        <div class="overflow-x-auto mt-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('transaction_id')">
                            Transaction ID
                            @if ($sortBy === 'transaction_id')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('type')">
                            Type
                            @if ($sortBy === 'type')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('status')">
                            Status
                            @if ($sortBy === 'status')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('created_at')">
                            Date
                            @if ($sortBy === 'created_at')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($this->transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                            {{ $transaction->transaction_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-semibold">{{ $transaction->user->initials() }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $transaction->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $transaction->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $transaction->type === 'buy' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ strtoupper($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($transaction->quantity, 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${{ number_format($transaction->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($transaction->status === 'pending')
                                    bg-yellow-100 text-yellow-800
                                @elseif ($transaction->status === 'completed')
                                    bg-green-100 text-green-800
                                @elseif ($transaction->status === 'rejected')
                                    bg-red-100 text-red-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if ($transaction->status === 'pending' && $transaction->type === 'sell')
                            <div class="flex space-x-2">
                                <flux:button
                                    wire:click="approveTransaction({{ $transaction->id }})"
                                    size="sm"
                                    class="bg-green-600 hover:bg-green-700">
                                    Approve
                                </flux:button>
                                <flux:button
                                    wire:click="rejectTransaction({{ $transaction->id }})"
                                    size="sm"
                                    variant="danger"
                                    class="bg-red-600 hover:bg-red-700">
                                    Reject
                                </flux:button>
                            </div>
                            @else
                            <span class="text-gray-400">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($this->transactions->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $this->transactions->links() }}
        </div>
        @endif
    </flux:card>
</div>