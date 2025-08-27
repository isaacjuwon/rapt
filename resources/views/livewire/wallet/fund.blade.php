<?php

use App\Models\User;
use App\Models\Wallet;
use App\Enums\WalletType;
use App\Actions\Payment\PaymentAction;
use App\Actions\Account\GenerateVirtualAccount;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Illuminate\Http\RedirectResponse;

new class extends Component {
    #[Rule('required|numeric|min:{{ app(App\Settings\WalletSettings::class)->minimum_deposit }}|max:{{ app(App\Settings\WalletSettings::class)->maximum_deposit }}')]
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
            'paystack' => [
                'name' => 'Paystack',
                'icon' => 'credit-card'
            ],
            'virtual_account' => [
                'name' => 'Virtual Account',
                'icon' => 'banknotes'
            ],
        ];
    }

    #[Computed]
    public function hasVirtualAccount(): bool
    {
        return Auth::user()->accounts()->exists();
    }

    public function initializePayment()
    {
        $this->validate();

        $this->isProcessing = true;

        try {
            $paymentAction = new PaymentAction(app(\App\Managers\ApiManager::class));

            $result = $paymentAction->initializeWalletFunding(
                amount: (float) $this->amount,
                walletType: $this->walletType,
                paymentMethod: $this->paymentMethod,
                reference: $this->reference
            );

            if ($result['success'] && isset($result['authorization_url'])) {
                $this->reference = $result['reference'];
                return $paymentAction->redirectToPayment($result['authorization_url']);
            } else {
                throw new \Exception($result['message'] ?? 'Payment initialization failed');
            }
        } catch (\Exception $e) {
            $this->isProcessing = false;
            session()->flash('error', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    public function generateAccount(GenerateVirtualAccount $generateVirtualAccount)
    {
        $this->isProcessing = true;

        try {
            $user = Auth::user();
            if ($user->accounts()->exists()) {
                throw new \Exception('You already have a virtual account.');
            }
            $account = $generateVirtualAccount->execute($user);

            if ($account) {
                session()->flash('success', 'Virtual account generated successfully! Account Number: ' . $account->account_number . ' (' . $account->bank_name . ')');
            } else {
                throw new \Exception('Failed to generate virtual account. Please try again.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate virtual account: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
            $this->redirect(route('wallet.fund'), navigate: true); // Refresh page to show account details
        }
    }

    public function resetForm()
    {
        $this->reset(['amount', 'walletType', 'paymentMethod', 'reference']);
    }
}; ?>

<div class="space-y-6">
    <x-page-header :title="__('Fund Wallet')" :description="__('Add funds to your wallet using various payment methods')">
        <x-slot:actions>
            <flux:button variant="outline" :href="route('wallet.index')">
                <flux:icon name="arrow-left" class="mr-2" />
                {{ __('Back to Wallet') }}
            </flux:button>
        </x-slot:actions>
    </x-page-header>

    @if (session()->has('success'))
        <flux:callout variant="success" :title="__('Success')" :message="session('success')" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" :title="__('Error')" :message="session('error')" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <flux:card class="p-6 mb-6">
                @if ($this->hasVirtualAccount)
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white">{{ __('Your Virtual Account Details') }}</h4>
                        @php
                            $account = Auth::user()->accounts->first();
                        @endphp
                        <div class="flex flex-col gap-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>{{ __('Bank Name:') }}</strong> {{ $account->bank_name }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>{{ __('Account Number:') }}</strong> {{ $account->account_number }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>{{ __('Account Name:') }}</strong> {{ $account->account_name }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Transfer to this account to fund your wallet instantly.') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center p-6 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                        <flux:icon name="banknotes" class="w-12 h-12 mb-4 text-gray-400" />
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-2">{{ __('Generate Virtual Account') }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-4">{{ __('Generate a dedicated virtual account for instant wallet funding.') }}</p>
                        <flux:button
                            type="button"
                            variant="primary"
                            wire:click="generateAccount"
                            wire:loading.attr="disabled"
                            wire:target="generateAccount"
                        >
                            <span wire:loading.remove wire:target="generateAccount">
                                {{ __('Generate Account') }}
                            </span>
                            <span wire:loading wire:target="generateAccount">
                                {{ __('Generating...') }}
                            </span>
                        </flux:button>
                    </div>
                @endif
            </flux:card>

            <form wire:submit.prevent="initializePayment" class="space-y-6">
                <flux:card class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Fund Wallet via Paystack') }}</h3>

                    <div class="space-y-6">
                        <flux:input
                            wire:model.live="amount"
                            :label="__('Amount')"
                            type="number"
                            placeholder="Enter amount"
                            min="100"
                            step="0.01"
                            :hint="__('Minimum funding amount is ' . \Illuminate\Support\Number::currency(100))"
                            :error="$errors->first('amount')"
                        />

                        <flux:select
                            wire:model="walletType"
                            :label="__('Fund Wallet')"
                            :error="$errors->first('walletType')"
                        >
                            @foreach($this->wallets as $wallet)
                                <option value="{{ $wallet->type }}">
                                    {{ $wallet->type->getLabel() }} (Balance: {{ \Illuminate\Support\Number::currency($wallet->balance) }})
                                </option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <flux:button type="button" variant="ghost" wire:click="resetForm">
                            {{ __('Reset') }}
                        </flux:button>
                        <flux:button
                            type="submit"
                            variant="primary"
                            wire:loading.attr="disabled"
                            wire:target="initializePayment"
                        >
                            <span wire:loading.remove wire:target="initializePayment">
                                Pay {{ \Illuminate\Support\Number::currency((float)$amount ?: 0) }}
                            </span>
                            <span wire:loading wire:target="initializePayment">
                                Processing...
                            </span>
                        </flux:button>
                    </div>
                </flux:card>
            </form>
        </div>

        <div class="space-y-6">
            <flux:card class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Payment Security') }}</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <flux:icon name="shield-check" class="w-5 h-5 text-green-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">SSL Encrypted Connection</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:icon name="lock-closed" class="w-5 h-5 text-green-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">PCI DSS Compliant</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:icon name="bolt" class="w-5 h-5 text-green-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Instant Wallet Funding</span>
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Funding Limits') }}</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Minimum Amount</span>
                        <span class="text-sm font-semibold">{{ \Illuminate\Support\Number::currency(100) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Maximum Amount</span>
                        <span class="text-sm font-semibold">{{ \Illuminate\Support\Number::currency(1000000) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Daily Limit</span>
                        <span class="text-sm font-semibold">{{ \Illuminate\Support\Number::currency(5000000) }}</span>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
