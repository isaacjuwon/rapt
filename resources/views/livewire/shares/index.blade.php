<?php

use App\Models\User;
use App\Models\Share;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Events\Share\SharePurchased;
use App\Events\Share\ShareSaleRequested;

new class extends Component {
    public int $buyQuantity = 1;
    public int $sellQuantity = 1;

    public function buyShares(): void
    {
        try {
            $user = auth()->user();
            $shareTransaction = $user->buyShares($this->buyQuantity);

            event(new SharePurchased($shareTransaction));

            Toaster::success("Successfully purchased {$this->buyQuantity} shares!");
            $this->buyQuantity = 1;
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    public function sellShares(): void
    {
        try {
            $user = auth()->user();
            $shareTransaction = $user->sellShares($this->sellQuantity);

            event(new ShareSaleRequested($shareTransaction));

            Toaster::success("Share sale request submitted for admin approval!");
            $this->sellQuantity = 1;
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    #[Computed]
    public function shareInfo(): ?Share
    {
        return Share::first();
    }

    #[Computed]
    public function userShares(): int
    {
        return auth()->user()->getTotalShares();
    }

    #[Computed]
    public function shareValue(): float
    {
        return auth()->user()->getShareValue();
    }

    #[Computed]
    public function canBuyShares(): bool
    {
        return auth()->user()->canBuyShares($this->buyQuantity);
    }

    #[Computed]
    public function canSellShares(): bool
    {
        return auth()->user()->canSellShares($this->sellQuantity);
    }

    #[Computed]
    public function recentTransactions()
    {
        return auth()->user()->getRecentShareTransactions(5);
    }

    #[Computed]
    public function pendingTransactions()
    {
        return auth()->user()->shareTransactions()
            ->where('status', 'pending')
            ->where('type', 'sell')
            ->latest()
            ->get();
    }
}; ?>

<div class="space-y-6" wire:loading.class="opacity-50">
    <!-- Share Overview -->
    <flux:card>
        <flux:heading size="lg">Share Overview</flux:heading>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Website Shares Info -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Website Shares</h3>
                @if ($this->shareInfo)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold">${{ number_format($this->shareInfo->price_per_share, 2) }}</div>
                        <div class="text-sm text-gray-500">Price per Share</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $this->shareInfo->available_shares }}</div>
                        <div class="text-sm text-gray-500">Available Shares</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $this->shareInfo->total_shares }}</div>
                        <div class="text-sm text-gray-500">Total Shares</div>
                    </div>
                </div>
                @else
                <div class="text-center text-gray-500">
                    No shares available for purchase
                </div>
                @endif
            </div>

            <!-- Your Shares Info -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Your Shares</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $this->userShares() }}</div>
                        <div class="text-sm text-gray-500">Shares Owned</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">${{ number_format($this->shareValue(), 2) }}</div>
                        <div class="text-sm text-gray-500">Current Value</div>
                    </div>
                </div>
            </div>
        </div>
    </flux:card>

    <!-- Buy/Sell Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Buy Shares -->
        <flux:card>
            <flux:heading size="lg">Buy Shares</flux:heading>

            <div class="space-y-4">
                <flux:input
                    label="Quantity"
                    type="number"
                    wire:model.live="buyQuantity"
                    min="1"
                    step="1" />

                <div class="text-sm text-gray-600">
                    Total Cost: ${{ number_format($this->buyQuantity * ($this->shareInfo->price_per_share ?? 0), 2) }}
                </div>

                <flux:button
                    wire:click="buyShares"
                    :disabled="!$this->canBuyShares()"
                    wire:loading.attr="disabled"
                    wire:target="buyShares"
                    class="w-full">
                    Buy Shares
                </flux:button>
            </div>
        </flux:card>

        <!-- Sell Shares -->
        <flux:card>
            <flux:heading size="lg">Sell Shares</flux:heading>

            <div class="space-y-4">
                <flux:input
                    label="Quantity"
                    type="number"
                    wire:model.live="sellQuantity"
                    min="1"
                    step="1"
                    max="{{ $this->userShares() }}" />

                <div class="text-sm text-gray-600">
                    Total Value: ${{ number_format($this->sellQuantity * ($this->shareInfo->price_per_share ?? 0), 2) }}
                </div>

                <flux:button
                    wire:click="sellShares"
                    :disabled="!$this->canSellShares()"
                    wire:loading.attr="disabled"
                    wire:target="sellShares"
                    variant="danger"
                    class="w-full">
                    Sell Shares
                </flux:button>
            </div>
        </flux:card>
    </div>

    <!-- Pending Transactions Notice -->
    @if ($this->pendingTransactions->count() > 0)
    <flux:card class="border-yellow-200 bg-yellow-50">
        <flux:heading size="lg" class="text-yellow-800">Pending Share Sales</flux:heading>

        <div class="space-y-3 mt-4">
            @foreach ($this->pendingTransactions as $transaction)
            <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                <div>
                    <div class="font-semibold">{{ $transaction->quantity }} shares</div>
                    <div class="text-sm text-gray-500">
                        Submitted: {{ $transaction->created_at->format('M j, Y g:i A') }}
                    </div>
                    <div class="text-sm text-yellow-600">
                        Pending admin approval - ${{ number_format($transaction->total_amount, 2) }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Pending
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 text-sm text-yellow-700">
            <strong>Note:</strong> Share sales require admin approval. Once approved, the funds will be credited to your wallet.
        </div>
    </flux:card>
    @endif

    <!-- Admin Approval Notice -->
    <flux:card class="border-blue-200 bg-blue-50">
        <flux:heading size="lg" class="text-blue-800">Important Information</flux:heading>

        <div class="mt-4 space-y-2 text-sm text-blue-700">
            <div>• <strong>Buying shares:</strong> Instant execution when you have sufficient wallet balance.</div>
            <div>• <strong>Selling shares:</strong> Requires admin approval for security reasons.</div>
            <div>• <strong>Wallet updates:</strong> Your wallet will be credited immediately after admin approval.</div>
            <div>• <strong>Transaction history:</strong> All transactions are recorded and visible below.</div>
        </div>
    </flux:card>

    <!-- Recent Transactions -->
    <flux:card>
        <flux:heading size="lg">Recent Transactions</flux:heading>

        @if ($this->recentTransactions->count() > 0)
        <div class="space-y-2">
            @foreach ($this->recentTransactions as $transaction)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                <div class="flex flex-col">
                    <span class="font-semibold {{ $transaction->type === 'buy' ? 'text-green-600' : 'text-red-600' }}">
                        {{ strtoupper($transaction->type) }}
                        @if ($transaction->status === 'pending')
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>
                        @endif
                    </span>
                    <span class="text-sm text-gray-500">
                        {{ $transaction->created_at->format('M j, Y g:i A') }}
                    </span>
                </div>
                <div class="text-right">
                    <div class="font-semibold">
                        {{ $transaction->quantity }} shares
                    </div>
                    <div class="text-sm text-gray-500">
                        @if ($transaction->type === 'buy')
                        <span class="text-red-600">-${{ number_format($transaction->total_amount, 2) }}</span>
                        @else
                        @if ($transaction->status === 'pending')
                        <span class="text-yellow-600">Pending: ${{ number_format($transaction->total_amount, 2) }}</span>
                        @else
                        <span class="text-green-600">+${{ number_format($transaction->total_amount, 2) }}</span>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-gray-500 py-4">
            No transactions yet
        </div>
        @endif
    </flux:card>
</div>