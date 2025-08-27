<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, Computed};

new #[Title('Apply for Loan')] class extends Component {
    public $amount = 0;
    public $termMonths = 0;
    public $purpose = '';
    public $applicationSubmitted = false;

    #[Computed]
    public function eligibility()
    {
        return auth()->user()->getLoanEligibilityDetails();
    }

    #[Computed]
    public function interestRate()
    {
        return app(App\Settings\LoanSettings::class)->default_interest_rate;
    }

    #[Computed]
    public function monthlyPayment()
    {
        if ($this->amount > 0 && $this->termMonths > 0) {
            $user = auth()->user();
            $interestAmount = $user->calculateSimpleInterest($this->amount, $this->interestRate, $this->termMonths);
            $totalRepayment = $this->amount + $interestAmount;
            return round($totalRepayment / $this->termMonths, 2);
        }
        return 0;
    }

    #[Computed]
    public function totalRepayment()
    {
        if ($this->amount > 0 && $this->termMonths > 0) {
            $user = auth()->user();
            $interestAmount = $user->calculateSimpleInterest($this->amount, $this->interestRate, $this->termMonths);
            return $this->amount + $interestAmount;
        }
        return 0;
    }

    protected function rules()
    {
        $settings = app(App\Settings\LoanSettings::class);

        return [
            'amount' => ['required', 'numeric', 'min:' . $settings->minimum_loan_amount, 'max:' . $settings->maximum_loan_amount],
            'termMonths' => ['required', 'integer', 'min:' . $settings->minimum_loan_term_months, 'max:' . $settings->maximum_loan_term_months],
            'purpose' => ['required', 'string', 'max:500'],
        ];
    }

    protected function messages()
    {
        $settings = app(App\Settings\LoanSettings::class);

        return [
            'amount.required' => 'Please enter the loan amount.',
            'amount.min' => 'Minimum loan amount is ' . \Illuminate\Support\Number::currency($settings->minimum_loan_amount) . '.',
            'amount.max' => 'Maximum loan amount is ' . \Illuminate\Support\Number::currency($settings->maximum_loan_amount) . '.',
            'termMonths.required' => 'Please enter the loan term.',
            'termMonths.min' => 'Minimum loan term is ' . $settings->minimum_loan_term_months . ' months.',
            'termMonths.max' => 'Maximum loan term is ' . $settings->maximum_loan_term_months . ' months.',
            'purpose.required' => 'Please state the purpose of the loan.',
            'purpose.max' => 'Purpose cannot exceed 500 characters.',
        ];
    }

    public function apply(): void
    {
        $this->validate();

        try {
            $user = auth()->user();

            if (!$user->check30PercentShareRequirement($this->amount)) {
                Toaster::error('You do not meet the 30% share ownership requirement for this loan amount.');
                return;
            }

            $loan = $user->applyForLoan($this->amount, $this->termMonths, $this->purpose);

            $this->applicationSubmitted = true;
            Toaster::success("Your loan application has been submitted successfully! Loan Number: {$loan->loan_number}");
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->amount = 0;
        $this->termMonths = 0;
        $this->purpose = '';
        $this->applicationSubmitted = false;
    }

    public function getMaxLoanAmount(): float
    {
        $user = auth()->user();
        $shareValue = $user->getShareValue();
        $settings = app(App\Settings\LoanSettings::class);
        return $shareValue / ($settings->shares_requirement_percentage / 100);
    }
}; ?>

<div>
    <flux:heading size="xl">Apply for Loan</flux:heading>
    <flux:subheading>Get the funds you need with our simple loan system</flux:subheading>

    <flux:card class="mb-6">
        <flux:heading size="lg">Eligibility Check</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <div class="flex items-center gap-2">
                <flux:icon icon="check-circle" variant="solid" class="{{ $this->eligibility['can_apply'] ? 'text-green-500' : 'text-red-500' }}" />
                <flux:text size="sm">Can Apply: {{ $this->eligibility['can_apply'] ? 'Yes' : 'No' }}</flux:text>
            </div>

            <div class="flex items-center gap-2">
                <flux:icon icon="shield-check" variant="solid" class="{{ $this->eligibility['meets_share_requirement'] ? 'text-green-500' : 'text-red-500' }}" />
                <flux:text size="sm">{{ number_format($this->eligibility['shares_requirement_percentage'], 1) }}% Share Requirement</flux:text>
            </div>

            <div class="flex items-center gap-2">
                <flux:icon icon="x-circle" variant="solid" class="{{ !$this->eligibility['has_defaulted_loans'] ? 'text-green-500' : 'text-red-500' }}" />
                <flux:text size="sm">Defaulted Loans: {{ $this->eligibility['has_defaulted_loans'] ? 'Yes' : 'No' }}</flux:text>
            </div>

            <div class="flex items-center gap-2">
                <flux:icon icon="chart-bar" variant="solid" class="text-blue-500" />
                <flux:text size="sm">Share Ownership: {{ number_format($this->eligibility['share_ownership_percentage'], 2) }}%</flux:text>
            </div>
        </div>

        @if (!$this->eligibility['can_apply'])
        <flux:callout variant="danger" class="mt-4">
            <flux:icon icon="exclamation-triangle" slot="icon" />
            You are not currently eligible to apply for a loan. Please check the requirements above.
        </flux:callout>
        @endif
    </flux:card>

    @if (!$applicationSubmitted)
    <flux:card>
        <flux:heading size="lg">Loan Application</flux:heading>

        <form wire:submit="apply" class="space-y-6">
            <div>
                <flux:label>Loan Amount</flux:label>
                <flux:input
                    type="number"
                    wire:model.live="amount"
                    min="1000"
                    max="{{ $this->getMaxLoanAmount() }}"
                    step="100"
                    placeholder="Enter loan amount" />
                <flux:error name="amount" />
                <flux:text size="xs" color="secondary">Maximum: {{ \Illuminate\Support\Number::currency($this->getMaxLoanAmount()) }}</flux:text>
            </div>

            <div>
                <flux:label>Loan Term (Months)</flux:label>
                <flux:input
                    type="number"
                    wire:model.live="termMonths"
                    min="6"
                    max="60"
                    placeholder="Enter loan term in months" />
                <flux:error name="termMonths" />
            </div>

            <div>
                <flux:label>Purpose of Loan</flux:label>
                <flux:textarea
                    wire:model="purpose"
                    placeholder="Please state the purpose of this loan"
                    rows="3" />
                <flux:error name="purpose" />
            </div>

            @if ($this->amount > 0 && $this->termMonths > 0)
            <flux:callout variant="muted">
                <flux:heading size="sm">Loan Summary</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mt-2">
                    <div>
                        <flux:text color="secondary">Interest Rate:</flux:text>
                        <flux:text weight="medium" color="primary">{{ $this->interestRate }}%</flux:text>
                    </div>
                    <div>
                        <flux:text color="secondary">Monthly Payment:</flux:text>
                        <flux:text weight="medium" color="success">{{ \Illuminate\Support\Number::currency($this->monthlyPayment) }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="secondary">Total Repayment:</flux:text>
                        <flux:text weight="medium" color="primary">{{ \Illuminate\Support\Number::currency($this->totalRepayment) }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="secondary">Total Interest:</flux:text>
                        <flux:text weight="medium" color="warning">{{ \Illuminate\Support\Number::currency($this->totalRepayment - floatval($this->amount)) }}</flux:text>
                    </div>
                </div>
            </flux:callout>
            @endif

            <div class="flex justify-end gap-3">
                <flux:button type="button" variant="outline" wire:click="resetForm">
                    Reset
                </flux:button>
                <flux:button
                    type="submit"
                    variant="primary"
                    :disabled="!$this->eligibility['can_apply'] || !$this->eligibility['meets_share_requirement']"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Apply for Loan</span>
                    <span wire:loading>Applying...</span>
                </flux:button>
            </div>
        </form>
    </flux:card>
    @else
    <flux:card>
        <div class="text-center py-8">
            <flux:icon icon="check-circle" variant="solid" class="w-16 h-16 mx-auto mb-4 text-green-500" />
            <flux:heading size="lg" class="mb-4">Application Submitted</flux:heading>
            <flux:text color="secondary" class="mb-6">Your application is now under review. You will be notified once a decision is made.</flux:text>
            <div class="flex justify-center gap-3">
                <flux:button href="{{ route('loan.index') }}" variant="outline">
                    View My Loans
                </flux:button>
                <flux:button wire:click="resetForm" variant="primary">
                    Apply for Another Loan
                </flux:button>
            </div>
        </div>
    </flux:card>
    @endif
</div>