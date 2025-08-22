<?php

use App\Models\Loan;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, Computed};

new #[Title('Loan Management')] class extends Component {
    public $showApplicationForm = false;
    public $showPaymentForm = false;
    public $selectedLoan = null;

    public $loanAmount = 0;
    public $loanTerm = 0;
    public $loanPurpose = '';

    public $paymentAmount = 0;
    public $paymentMethod = 'wallet';
    public $paymentReference = '';

    #[Computed]
    public function statistics()
    {
        $loans = auth()->user()->loans();
        return [
            'active_loans_count' => (clone $loans)->active()->count(),
            'pending_loans_count' => (clone $loans)->pending()->count(),
            'completed_loans_count' => (clone $loans)->completed()->count(),
            'outstanding_balance' => $loans->sum('remaining_balance'),
        ];
    }

    #[Computed]
    public function paymentMethodOptions()
    {
        return [
            'wallet' => 'Wallet Balance',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card Payment',
        ];
    }

    #[Computed]
    public function eligibility()
    {
        return auth()->user()->getLoanEligibilityDetails();
    }

    protected function rules()
    {
        $settings = app(App\Settings\LoanSettings::class);
        return [
            'loanAmount' => ['required', 'numeric', 'min:' . $settings->minimum_loan_amount, 'max:' . $settings->maximum_loan_amount],
            'loanTerm' => ['required', 'integer', 'min:' . $settings->minimum_loan_term_months, 'max:' . $settings->maximum_loan_term_months],
            'loanPurpose' => ['required', 'string', 'max:500'],
            'paymentAmount' => ['required', 'numeric', 'min:1'],
            'paymentMethod' => ['required', 'string', 'in:' . implode(',', array_keys($this->paymentMethodOptions))],
            'paymentReference' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function showApplicationForm(): void
    {
        $this->showApplicationForm = true;
        $this->resetApplicationForm();
    }

    public function hideApplicationForm(): void
    {
        $this->showApplicationForm = false;
        $this->resetApplicationForm();
    }

    public function showPaymentForm($loanId): void
    {
        $this->selectedLoan = auth()->user()->loans()->findOrFail($loanId);
        $this->showPaymentForm = true;
        $this->resetPaymentForm();
        if ($this->selectedLoan) {
            $this->paymentAmount = $this->selectedLoan->calculateInstallmentAmount();
        }
    }

    public function hidePaymentForm(): void
    {
        $this->showPaymentForm = false;
        $this->selectedLoan = null;
        $this->resetPaymentForm();
    }

    public function applyForLoan(): void
    {
        $this->validate([
            'loanAmount' => ['required', 'numeric', 'min:1000'],
            'loanTerm' => ['required', 'integer', 'min:6'],
            'loanPurpose' => ['required', 'string', 'max:500'],
        ]);

        try {
            $user = auth()->user();
            if (!$user->check30PercentShareRequirement($this->loanAmount)) {
                Toaster::error('You do not meet the 30% share ownership requirement for this loan amount.');
                return;
            }
            $loan = $user->applyForLoan($this->loanAmount, $this->loanTerm, $this->loanPurpose);
            $this->hideApplicationForm();
            Toaster::success("Your loan application has been submitted successfully! Loan Number: {$loan->loan_number}");
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    public function makePayment(): void
    {
        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:1'],
            'paymentMethod' => ['required', 'string'],
            'paymentReference' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            if (!$this->selectedLoan) {
                Toaster::error('No loan selected for payment.');
                return;
            }

            match ($this->paymentMethod) {
                'wallet' => $this->processWalletPayment(),
                'bank_transfer' => $this->processBankTransferPayment(),
                'card' => $this->processCardPayment(),
                default => throw new \Exception('Invalid payment method'),
            };

            $this->hidePaymentForm();
            Toaster::success('Payment processed successfully!');
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    private function processWalletPayment(): void
    {
        $user = auth()->user();
        if ($user->getWalletBalance() < $this->paymentAmount) {
            throw new \Exception('Insufficient wallet balance.');
        }
        $this->selectedLoan->makePayment($this->paymentAmount, 'wallet', $this->paymentReference);
    }

    private function processBankTransferPayment(): void
    {
        $this->selectedLoan->makePayment($this->paymentAmount, 'bank_transfer', $this->paymentReference);
    }

    private function processCardPayment(): void
    {
        $this->selectedLoan->makePayment($this->paymentAmount, 'card', $this->paymentReference);
    }

    public function resetApplicationForm(): void
    {
        $this->loanAmount = 0;
        $this->loanTerm = 0;
        $this->loanPurpose = '';
    }

    public function resetPaymentForm(): void
    {
        $this->paymentAmount = 0;
        $this->paymentMethod = 'wallet';
        $this->paymentReference = '';
    }
}; ?>

<div>
    <flux:heading size="xl">Loan Management</flux:heading>
    <flux:subheading>Manage your loans, apply for new loans, and make payments</flux:subheading>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text size="sm" color="secondary">Total Loans</flux:text>
                    <flux:text size="lg" weight="semibold">
                        {{ $this->statistics['active_loans_count'] + $this->statistics['pending_loans_count'] + $this->statistics['completed_loans_count'] }}
                    </flux:text>
                </div>
                <flux:icon icon="document-text" variant="solid" class="text-blue-500" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text size="sm" color="secondary">Active Loans</flux:text>
                    <flux:text size="lg" weight="semibold">{{ $this->statistics['active_loans_count'] }}</flux:text>
                </div>
                <flux:icon icon="check-circle" variant="solid" class="text-green-500" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text size="sm" color="secondary">Pending Loans</flux:text>
                    <flux:text size="lg" weight="semibold">{{ $this->statistics['pending_loans_count'] }}</flux:text>
                </div>
                <flux:icon icon="clock" variant="solid" class="text-yellow-500" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text size="sm" color="secondary">Outstanding</flux:text>
                    <flux:text size="lg" weight="semibold">${{ number_format($this->statistics['outstanding_balance'], 2) }}</flux:text>
                </div>
                <flux:icon icon="currency-dollar" variant="solid" class="text-purple-500" />
            </div>
        </flux:card>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        @if($this->eligibility['can_apply'])
        <flux:button wire:click="showApplicationForm" variant="primary">
            <flux:icon icon="plus-circle" slot="prefix" />
            Apply for New Loan
        </flux:button>
        @endif
    </div>

    <flux:card class="mb-6">
        <flux:heading size="lg">Active Loans</flux:heading>

        @php
        $activeLoans = auth()->user()->loans()->active()->get();
        @endphp

        @if($activeLoans->count() > 0)
        <flux:table class="mt-4">
            <flux:table.head>
                <flux:table.heading>Loan Number</flux:table.heading>
                <flux:table.heading>Amount</flux:table.heading>
                <flux:table.heading>Status</flux:table.heading>
                <flux:table.heading>Balance</flux:table.heading>
                <flux:table.heading>Actions</flux:table.heading>
            </flux:table.head>
            <flux:table.body>
                @foreach($activeLoans as $loan)
                <flux:table.row>
                    <flux:table.cell>{{ $loan->loan_number }}</flux:table.cell>
                    <flux:table.cell>${{ number_format($loan->amount, 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :variant="$loan->status === 'approved' ? 'success' : 'warning'">
                            {{ $loan->status }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>${{ number_format($loan->remaining_balance, 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button wire:click="showPaymentForm({{ $loan->id }})" size="sm" variant="outline">
                            Make Payment
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.body>
        </flux:table>
        @else
        <flux:text color="secondary" class="mt-4">No active loans found.</flux:text>
        @endif
    </flux:card>

    @if($showApplicationForm)
    <flux:modal wire:model="showApplicationForm" title="Apply for New Loan">
        <flux:form wire:submit="applyForLoan">
            <flux:input
                wire:model="loanAmount"
                label="Loan Amount"
                type="number"
                step="0.01"
                min="1000"
                required />

            <flux:input
                wire:model="loanTerm"
                label="Loan Term (months)"
                type="number"
                min="6"
                required />

            <flux:textarea
                wire:model="loanPurpose"
                label="Purpose"
                placeholder="Briefly describe the purpose of this loan"
                required />

            <flux:button type="submit" variant="primary">Submit Application</flux:button>
            <flux:button wire:click="hideApplicationForm" variant="outline">Cancel</flux:button>
        </flux:form>
    </flux:modal>
    @endif

    @if($showPaymentForm)
    <flux:modal wire:model="showPaymentForm" title="Make Payment">
        <flux:form wire:submit="makePayment">
            @if($selectedLoan)
            <div class="mb-4">
                <flux:text size="sm" color="secondary">Loan Number: {{ $selectedLoan->loan_number }}</flux:text>
                <flux:text size="sm" color="secondary">Outstanding Balance: ${{ number_format($selectedLoan->remaining_balance, 2) }}</flux:text>
            </div>
            @endif

            <flux:input
                wire:model="paymentAmount"
                label="Payment Amount"
                type="number"
                step="0.01"
                min="1"
                required />

            <flux:select
                wire:model="paymentMethod"
                label="Payment Method"
                required>
                @foreach($this->paymentMethodOptions as $key => $label)
                <flux:option :value="$key">{{ $label }}</flux:option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model="paymentReference"
                label="Reference (optional)"
                placeholder="Transaction reference" />

            <flux:button type="submit" variant="primary">Make Payment</flux:button>
            <flux:button wire:click="hidePaymentForm" variant="outline">Cancel</flux:button>
        </flux:form>
    </flux:modal>
    @endif
</div>