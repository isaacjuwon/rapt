<?php

use App\Models\User;
use App\Models\Wallet;
use App\Enums\WalletType;
use App\Actions\Payment\PaymentAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Illuminate\Http\RedirectResponse;

new class extends Component {
    #[Rule('required|numeric|min:100')]
    public $amount = '';

    #[Rule('required')]
    public $walletType = 'main';

    #[Rule('required|string')]
    public $paymentMethod = 'paystack';

    #[Rule('nullable|string')]
    public $reference = '';

    public $isProcessing = false;

    #[Computed]
    public function wallets()
    {
        return Auth::user()->wallets()->whereIn('type', WalletType::getDepositableTypes())->get();
    }

    #[Computed]
    public function paymentMethods()
    {
        return [
            'paystack' => 'Paystack',
            'flutterwave' => 'Flutterwave',
            'bank_transfer' => 'Bank Transfer',
        ];
    }

    public function initializePayment()
    {
        $this->validate();

        $this->isProcessing = true;

        try {
            // Initialize payment using PaymentAction
            $paymentAction = new PaymentAction(app(\App\Managers\ApiManager::class));

            $result = $paymentAction->initializeWalletFunding(
                amount: (float) $this->amount,
                walletType: $this->walletType,
                paymentMethod: $this->paymentMethod,
                reference: $this->reference
            );

            if ($result['success'] && isset($result['authorization_url'])) {
                // Store reference for verification after payment
                $this->reference = $result['reference'];

                // Redirect to Paystack payment page
                return $paymentAction->redirectToPayment($result['authorization_url']);
            } else {
                throw new \Exception($result['message'] ?? 'Payment initialization failed');
            }
        } catch (\Exception $e) {
            $this->isProcessing = false;
            session()->flash('error', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['amount', 'walletType', 'paymentMethod', 'reference']);
    }
} ?>

<div class="space-y-6">
    <!-- Header -->
    <flux:card>
        <flux:heading size="lg">Fund Wallet</flux:heading>
        <flux:text size="sm" color="secondary">Add funds to your wallet using various payment methods</flux:text>

        <div class="mt-4">
            <flux:button variant="outline" href="{{ route('wallet.index') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Wallet
            </flux:button>
        </div>
    </flux:card>

    <!-- Messages -->
    @if (session()->has('success'))
    <flux:callout variant="success" title="Success">
        {{ session('success') }}
    </flux:callout>
    @endif

    @if (session()->has('error'))
    <flux:callout variant="error" title="Error">
        {{ session('error') }}
    </flux:callout>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <flux:card>
                <flux:heading size="md">Payment Details</flux:heading>

                <form wire:submit.prevent="initializePayment" class="space-y-6 mt-6">
                    <!-- Amount -->
                    <flux:field label="Amount (₦)">
                        <flux:input
                            wire:model="amount"
                            type="number"
                            placeholder="Enter amount (minimum: ₦100)"
                            min="100"
                            step="0.01" />
                        <flux:text size="xs" color="secondary">Minimum funding amount is ₦100</flux:text>
                        @error('amount')
                        <flux:text size="sm" color="danger">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <!-- Wallet Type -->
                    <flux:field label="Fund Wallet">
                        <flux:select wire:model="walletType">
                            @foreach($this->wallets as $wallet)
                            <option value="{{ $wallet->type }}">
                                {{ $wallet->type->getLabel() }} (Current: ₦{{ number_format($wallet->balance, 2) }})
                            </option>
                            @endforeach
                        </flux:select>
                        @error('walletType')
                        <flux:text size="sm" color="danger">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <!-- Payment Method -->
                    <flux:field label="Payment Method">
                        <flux:radio-group wire:model="paymentMethod">
                            @foreach($this->paymentMethods as $key => $name)
                            <flux:radio value="{{ $key }}" label="{{ $name }}" />
                            @endforeach
                        </flux:radio-group>
                        @error('paymentMethod')
                        <flux:text size="sm" color="danger">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <flux:button
                            type="submit"
                            variant="primary"
                            class="flex-1"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                Pay ₦{{ number_format($amount ?: 0, 2) }}
                            </span>
                            <span wire:loading>
                                Processing...
                            </span>
                        </flux:button>
                        <flux:button type="button" variant="outline" wire:click="resetForm">
                            Reset
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Security Info -->
            <flux:card>
                <flux:heading size="md">Payment Security</flux:heading>
                <div class="space-y-3 mt-4">
                    <div class="flex items-center gap-2">
                        <flux:icon variant="success" />
                        <flux:text size="sm">SSL Encrypted</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon variant="success" />
                        <flux:text size="sm">PCI Compliant</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon variant="success" />
                        <flux:text size="sm">Instant Funding</flux:text>
                    </div>
                </div>
            </flux:card>

            <!-- Limits -->
            <flux:card>
                <flux:heading size="md">Funding Limits</flux:heading>
                <div class="space-y-2 mt-4">
                    <div class="flex justify-between">
                        <flux:text size="sm" color="secondary">Minimum</flux:text>
                        <flux:text size="sm" weight="medium">₦100</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text size="sm" color="secondary">Maximum</flux:text>
                        <flux:text size="sm" weight="medium">₦1,000,000</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text size="sm" color="secondary">Daily Limit</flux:text>
                        <flux:text size="sm" weight="medium">₦5,000,000</flux:text>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div