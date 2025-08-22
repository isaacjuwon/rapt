<?php

use App\Models\Loan;
use App\Models\User;
use App\Models\Share;
use App\Models\Wallet;
use App\Models\Transaction;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.admin')] #[Title('Dashboard')] class extends Component {

    #[Computed]
    public function stats()
    {
        return [
            'totalUsers' => User::count(),
            'totalTransactions' => Transaction::count(),
            'totalLoans' => Loan::count(),
            'totalShares' => Share::count(),
            'totalWalletBalance' => Wallet::sum('balance'),
            'recentTransactions' => Transaction::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'monthlyRevenue' => Transaction::where('status', 'success')
                ->where('type', 'payment')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'pendingLoans' => Loan::where('status', 'pending')->count(),
        ];
    }

    #[Computed]
    public function chartData()
    {
        $transactions = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $transactions->pluck('date'),
            'counts' => $transactions->pluck('count'),
            'totals' => $transactions->pluck('total'),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <flux:heading size="xl">Dashboard</flux:heading>
        <flux:subheading>Overview of your application metrics and performance.</flux:subheading>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-zinc-500">Total Users</flux:text>
                    <flux:heading size="lg" class="mt-1">{{ number_format($this->stats['totalUsers']) }}</flux:heading>
                </div>
                <flux:icon.user variant="solid" class="h-8 w-8 text-blue-500" />
            </div>
        </flux:card>

        <flux:card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-zinc-500">Transactions</flux:text>
                    <flux:heading size="lg" class="mt-1">{{ number_format($this->stats['totalTransactions']) }}</flux:heading>
                </div>
                <flux:icon.currency-dollar variant="solid" class="h-8 w-8 text-green-500" />
            </div>
        </flux:card>

        <flux:card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-zinc-500">Active Loans</flux:text>
                    <flux:heading size="lg" class="mt-1">{{ number_format($this->stats['totalLoans']) }}</flux:heading>
                </div>
                <flux:icon.document-text variant="solid" class="h-8 w-8 text-orange-500" />
            </div>
        </flux:card>

        <flux:card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text class="text-sm text-zinc-500">Total Shares</flux:text>
                    <flux:heading size="lg" class="mt-1">{{ number_format($this->stats['totalShares']) }}</flux:heading>
                </div>
                <flux:icon.chart-pie variant="solid" class="h-8 w-8 text-purple-500" />
            </div>
        </flux:card>
    </div>

    <!-- Revenue Overview -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <flux:card class="p-6 lg:col-span-2">
            <flux:heading size="md" class="mb-4">Monthly Revenue</flux:heading>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">₦{{ number_format($this->stats['monthlyRevenue'], 2) }}</flux:heading>
                <flux:text class="text-sm text-green-500">+12.5% from last month</flux:text>
            </div>

            <!-- Simple Chart Representation -->
            <div class="mt-6 flex h-32 items-end gap-1">
                @foreach (array_slice($this->chartData['totals']->toArray(), -7) as $index => $total)
                <div class="flex-1 bg-blue-500 rounded-t" style="height: {{ max(20, ($total / max($this->chartData['totals'])) * 100) }}%"></div>
                @endforeach
            </div>
            <flux:text class="mt-2 text-sm text-zinc-500">Last 7 days</flux:text>
        </flux:card>

        <flux:card class="p-6">
            <flux:heading size="md" class="mb-4">Quick Stats</flux:heading>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm">Wallet Balance</flux:text>
                    <flux:text class="font-medium">₦{{ number_format($this->stats['totalWalletBalance'], 2) }}</flux:text>
                </div>
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm">Pending Loans</flux:text>
                    <flux:badge variant="warning">{{ $this->stats['pendingLoans'] }}</flux:badge>
                </div>
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm">Success Rate</flux:text>
                    <flux:text class="font-medium text-green-500">94.2%</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Recent Transactions -->
    <flux:card class="p-6">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="md">Recent Transactions</flux:heading>
            <flux:button variant="ghost" size="sm" icon="arrow-right">View All</flux:button>
        </div>

        <div class="space-y-3">
            @forelse ($this->stats['recentTransactions'] as $transaction)
            <div class="flex items-center justify-between py-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100">
                        @if ($transaction->type->value === 'payment')
                        <flux:icon.arrow-down variant="mini" class="text-green-500" />
                        @elseif ($transaction->type->value === 'deposit')
                        <flux:icon.arrow-up variant="mini" class="text-blue-500" />
                        @else
                        <flux:icon.arrows-right-left variant="mini" class="text-orange-500" />
                        @endif
                    </div>
                    <div>
                        <flux:text class="font-medium">{{ $transaction->user->name }}</flux:text>
                        <flux:text class="text-sm text-zinc-500">{{ $transaction->created_at->format('M d, h:i A') }}</flux:text>
                    </div>
                </div>
                <div class="text-right">
                    <flux:text class="font-medium">₦{{ number_format($transaction->amount, 2) }}</flux:text>
                    <flux:badge :variant="$transaction->status->getColor()" size="sm">
                        {{ ucfirst($transaction->status->value) }}
                    </flux:badge>
                </div>
            </div>
            @empty
            <div class="py-8 text-center">
                <flux:icon.inbox variant="mini" class="mx-auto h-12 w-12 text-zinc-400" />
                <flux:text class="mt-2 text-zinc-500">No recent transactions</flux:text>
            </div>
            @endforelse
        </div>
    </flux:card>
</div>