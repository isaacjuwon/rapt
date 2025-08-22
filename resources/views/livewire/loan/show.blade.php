<?php

use App\Models\Loan;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, Computed};

new #[Title('Loan Details')] class extends Component {
    public $loanId;
    public $loan;
    public $payments;

    public function mount($id): void
    {
        $this->loanId = $id;
        $this->loadLoan();
    }

    public function loadLoan(): void
    {
        $this->loan = auth()->user()->loans()->with(['payments'])->findOrFail($this->loanId);
        $this->payments = $this->loan->payments()->orderBy('due_date')->get();
    }

    #[Computed]
    public function paymentStatus()
    {
        return fn($payment) => match ($payment->status) {
            'paid' => 'success',
            'pending' => $payment->due_date < now() ? 'danger' : 'warning',
            'overdue' => 'danger',
            default => 'muted',
        };
    }

    #[Computed]
    public function paymentStatusLabel()
    {
        return fn($payment) => match ($payment->status) {
            'paid' => 'Paid',
            'pending' => $payment->due_date < now() ? 'Overdue' : 'Pending',
            'overdue' => 'Overdue',
            default => ucfirst($payment->status),
        };
    }

    #[Computed]
    public function statusVariant()
    {
        return match ($this->loan->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'disbursed' => 'success',
            'active' => 'success',
            'completed' => 'success',
            'defaulted' => 'danger',
            'rejected' => 'danger',
            default => 'muted',
        };
    }
}; ?>

<div>
    <flux:heading size="xl">Loan Details</flux:heading>
    <flux:subheading>View your loan information and payment schedule</flux:subheading>

    <flux:button href="{{ route('loan.index') }}" variant="outline" class="mb-6">
        <flux:icon icon="arrow-left" slot="prefix" />
        Back to My Loans
    </flux:button>

    @if ($loan)
    <flux:card class="mb-6">
        <flux:heading size="lg">Loan Overview</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
            <div>
                <flux:text size="sm" color="secondary">Loan Number</flux:text>
                <flux:text weight="semibold">{{ $loan->loan_number }}</flux:text>
            </div>
            <div>
                <flux:text size="sm" color="secondary">Status</flux:text>
                <flux:badge variant="{{ $this->statusVariant }}">
                    {{ $loan->getStatusLabel() }}
                </flux:badge>
            </div>
            <div>
                <flux:text size="sm" color="secondary">Purpose</flux:text>
                <flux:text weight="semibold">{{ $loan->purpose }}</flux:text>
            </div>
        </div>
    </flux:card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <flux:card>
            <flux:heading size="lg">Financial Details</flux:heading>
            <div class="space-y-3 mt-4">
                <div class="flex justify-between">
                    <flux:text color="secondary">Principal Amount:</flux:text>
                    <flux:text weight="semibold">${{ number_format($loan->principal_amount, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text color="secondary">Interest Rate:</flux:text>
                    <flux:text weight="semibold">{{ $loan->interest_rate }}%</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text color="secondary">Total Payable:</flux:text>
                    <flux:text weight="semibold">${{ number_format($loan->total_payable, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text color="secondary">Total Paid:</flux:text>
                    <flux:text weight="semibold" color="success">${{ number_format($loan->total_paid, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text color="secondary">Remaining Balance:</flux:text>
                    <flux:text weight="semibold" color="warning">${{ number_format($loan->remaining_balance, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text color="secondary">Monthly Installment:</flux:text>
                    <flux:text weight="semibold">${{ number_format($loan->calculateInstallmentAmount(), 2) }}</flux:text>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg">Timeline</flux:heading>
            <div class="space-y-3 mt-4">
                <div class="flex justify-between">
                    <flux:text color="secondary">Application Date:</flux:text>
                    <flux:text weight="semibold">{{ $loan->created_at->format('M d, Y') }}</flux:text>
                </div>
                @if ($loan->disbursement_date)
                <div class="flex justify-between">
                    <flux:text color="secondary">Disbursement Date:</flux:text>
                    <flux:text weight="semibold">{{ $loan->disbursement_date->format('M d, Y') }}</flux:text>
                </div>
                @endif
                <div class="flex justify-between">
                    <flux:text color="secondary">Term:</flux:text>
                    <flux:text weight="semibold">{{ $loan->term_months }} months</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text color="secondary">Payment Frequency:</flux:text>
                    <flux:text weight="semibold">{{ $loan->getFrequencyLabel() }}</flux:text>
                </div>
                @if ($loan->first_payment_date)
                <div class="flex justify-between">
                    <flux:text color="secondary">First Payment:</flux:text>
                    <flux:text weight="semibold">{{ $loan->first_payment_date->format('M d, Y') }}</flux:text>
                </div>
                @endif
                <div class="flex justify-between">
                    <flux:text color="secondary">Expected End Date:</flux:text>
                    <flux:text weight="semibold">{{ $loan->expected_end_date?->format('M d, Y') ?? 'N/A' }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card class="mb-6">
        <flux:heading size="lg">Repayment Progress</flux:heading>
        <div class="mt-4">
            <div class="flex justify-between items-center mb-2">
                <flux:text size="sm" color="secondary">Progress</flux:text>
                <flux:text size="sm" weight="semibold">{{ number_format($loan->getProgressPercentage(), 1) }}%</flux:text>
            </div>
            <flux:progress :value="$loan->getProgressPercentage()" class="mb-4" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                    <flux:text color="secondary">{{ $loan->paid_installments }} / {{ $loan->total_installments }}</flux:text>
                    <flux:text weight="medium">Installments Paid</flux:text>
                </div>
                <div>
                    <flux:text color="success">${{ number_format($loan->total_paid, 2) }}</flux:text>
                    <flux:text weight="medium">Amount Paid</flux:text>
                </div>
                <div>
                    <flux:text color="warning">${{ number_format($loan->remaining_balance, 2) }}</flux:text>
                    <flux:text weight="medium">Remaining</flux:text>
                </div>
            </div>
        </div>
    </flux:card>

    <flux:card>
        <flux:heading size="lg">Payment Schedule</flux:heading>

        @if ($payments->count() > 0)
        <flux:table class="mt-4">
            <flux:table.head>
                <flux:table.heading>Payment #</flux:table.heading>
                <flux:table.heading>Due Date</flux:table.heading>
                <flux:table.heading>Amount</flux:table.heading>
                <flux:table.heading>Status</flux:table.heading>
                <flux:table.heading>Paid Date</flux:table.heading>
            </flux:table.head>
            <flux:table.body>
                @foreach ($payments as $payment)
                <flux:table.row>
                    <flux:table.cell>{{ $payment->installment_number }}</flux:table.cell>
                    <flux:table.cell>{{ $payment->due_date->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>${{ number_format($payment->amount, 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge variant="{{ $this->paymentStatus($payment) }}">
                            {{ $this->paymentStatusLabel($payment) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $payment->paid_at?->format('M d, Y') ?? '-' }}</flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.body>
        </flux:table>
        @else
        <div class="text-center py-8">
            <flux:icon icon="calendar" variant="solid" class="w-16 h-16 mx-auto mb-4 text-gray-400" />
            <flux:heading size="lg" color="secondary" class="mb-2">No payment schedule found</flux:heading>
            <flux:text color="secondary">Payment schedule will be available once the loan is approved.</flux:text>
        </div>
        @endif
    </flux:card>
    @else
    <flux:card>
        <div class="text-center py-8">
            <flux:icon icon="exclamation-circle" variant="solid" class="w-16 h-16 mx-auto mb-4 text-red-500" />
            <flux:heading size="lg" color="secondary" class="mb-2">Loan not found</flux:heading>
            <flux:text color="secondary" class="mb-4">The loan you're looking for doesn't exist or you don't have access to it.</flux:text>
            <flux:button href="{{ route('loan.index') }}" variant="primary">
                Back to My Loans
            </flux:button>
        </div>
    </flux:card>
    @endif
</div>