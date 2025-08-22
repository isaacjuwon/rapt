<?php

use App\Enums\WalletType;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $currentWalletType = 'main';
    public array $wallets = [];
    public float $totalBalance = 0.0;

    public function mount(): void
    {
        $this->loadWallets();
    }

    public function loadWallets(): void
    {
        $user = Auth::user();

        $this->wallets = $user->getWalletsWithBalances();
        $this->totalBalance = $user->getWalletBalance();
    }

    public function switchWallet(string $walletType): void
    {
        if (WalletType::isValid($walletType)) {
            $this->currentWalletType = $walletType;
        }
    }

    public function getCurrentWallet(): array
    {
        return $this->wallets[$this->currentWalletType] ?? $this->wallets['main'];
    }

    public function getWalletIcon(string $walletType): string
    {
        return match ($walletType) {
            'main' => 'wallet',
            'bonus' => 'gift',
            'cashback' => 'arrow-path',
            default => 'wallet',
        };
    }

    public function getWalletColor(string $walletType): string
    {
        return match ($walletType) {
            'main' => 'blue',
            'bonus' => 'green',
            'cashback' => 'purple',
            default => 'blue',
        };
    }
};
?>

<div class="w-full">
    <!-- Main Wallet Card -->
    <flux:card
        class="relative overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-950 dark:to-indigo-900 border-0 shadow-lg">

        <!-- Card Content -->
        <div class="relative z-10 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <flux:icon :name="$this->getWalletIcon($currentWalletType)"
                        class="!size-8 text-blue-600 dark:text-blue-400" />
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $this->getCurrentWallet()['label'] }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Current Balance
                        </p>
                    </div>
                </div>

                <!-- Wallet Switcher -->
                <div>
                    <!-- Desktop: button group -->
                    <div
                        class="hidden sm:flex items-center space-x-1 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-lg p-1">
                        @foreach($wallets as $type => $wallet)
                        <flux:button
                            wire:click="switchWallet('{{ $type }}')"
                            variant="{{ $currentWalletType === $type ? 'outline' : 'ghost' }}"
                            size="sm"
                            class="!text-xs {{ $currentWalletType === $type 
                                ? '!bg-' . $this->getWalletColor($type) . '-600 !text-white' 
                                : '!text-gray-600 dark:!text-gray-400 hover:!bg-gray-200 dark:hover:!bg-gray-700' }}">
                            {{ $wallet['label'] }}
                        </flux:button>
                        @endforeach
                    </div>

                    <!-- Mobile: select dropdown -->
                    <div class="sm:hidden">
                        <flux:select wire:model="currentWalletType" wire:change="switchWallet($event.target.value)">
                            @foreach($wallets as $type => $wallet)
                            <option value="{{ $type }}">{{ $wallet['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>

            <!-- Balance Display -->
            <div class="mb-6">
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
                    ₦{{ number_format($this->getCurrentWallet()['balance'], 2) }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Total Balance: ₦{{ number_format($totalBalance, 2) }}
                </div>
            </div>

            <!-- Only Add Funds -->
            <div>
                <flux:button variant="outline" size="sm" class="!text-xs">
                    <flux:icon name="plus" class="!size-4" />
                    Add Funds
                </flux:button>
            </div>
        </div>
    </flux:card>
</div>