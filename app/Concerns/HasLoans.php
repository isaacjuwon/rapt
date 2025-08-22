<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Loan;
use App\Models\Share;
use App\Settings\LoanSettings;

trait HasLoans
{
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans()
    {
        return $this->loans()->active();
    }

    public function pendingLoans()
    {
        return $this->loans()->pending();
    }

    public function completedLoans()
    {
        return $this->loans()->completed();
    }

    public function getLoanHistory(): array
    {
        return $this->loans()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($loan) => [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'principal_amount' => $loan->principal_amount,
                'interest_rate' => $loan->interest_rate,
                'total_payable' => $loan->total_payable,
                'total_paid' => $loan->total_paid,
                'remaining_balance' => $loan->remaining_balance,
                'term_months' => $loan->term_months,
                'status' => $loan->status,
                'status_label' => $loan->getStatusLabel(),
                'loan_type' => $loan->loan_type,
                'loan_type_label' => $loan->getTypeLabel(),
                'purpose' => $loan->purpose,
                'disbursement_date' => $loan->disbursement_date?->format('Y-m-d'),
                'first_payment_date' => $loan->first_payment_date?->format('Y-m-d'),
                'expected_end_date' => $loan->expected_end_date?->format('Y-m-d'),
                'payment_frequency' => $loan->payment_frequency,
                'progress_percentage' => $loan->getProgressPercentage(),
                'created_at' => $loan->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $loan->updated_at->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    public function getActiveLoans(): array
    {
        return $this->activeLoans()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($loan) => [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'principal_amount' => $loan->principal_amount,
                'interest_rate' => $loan->interest_rate,
                'total_payable' => $loan->total_payable,
                'total_paid' => $loan->total_paid,
                'remaining_balance' => $loan->remaining_balance,
                'term_months' => $loan->term_months,
                'installment_amount' => $loan->calculateInstallmentAmount(),
                'status' => $loan->status,
                'status_label' => $loan->getStatusLabel(),
                'loan_type' => $loan->loan_type,
                'loan_type_label' => $loan->getTypeLabel(),
                'purpose' => $loan->purpose,
                'disbursement_date' => $loan->disbursement_date?->format('Y-m-d'),
                'next_payment_date' => $loan->getNextPaymentDueDate()?->format('Y-m-d'),
                'overdue_amount' => $loan->getOverdueAmount(),
                'progress_percentage' => $loan->getProgressPercentage(),
            ])
            ->toArray();
    }

    public function getLoanEligibilityDetails(): array
    {
        $settings = app(LoanSettings::class);
        $totalShareValue = $this->getTotalShareValue();
        $hasDefaultedLoans = $this->hasDefaultedLoans();
        $hasActiveLoans = $this->activeLoans()->exists();
        $shareOwnershipPercentage = $this->getShareOwnershipPercentage();

        $canApply = !$hasDefaultedLoans &&
            (!$settings->allow_multiple_active_loans || !$hasActiveLoans) &&
            (!$settings->require_no_defaulted_loans || !$hasDefaultedLoans);

        if ($settings->shares_requirement_enabled) {
            $meetsShareRequirement = $shareOwnershipPercentage >= $settings->shares_requirement_percentage;
            $canApply = $canApply && $meetsShareRequirement;
        }

        return [
            'can_apply' => $canApply,
            'meets_share_requirement' => $settings->shares_requirement_enabled ?
                $shareOwnershipPercentage >= $settings->shares_requirement_percentage : true,
            'has_defaulted_loans' => $hasDefaultedLoans,
            'has_active_loans' => $hasActiveLoans,
            'share_ownership_percentage' => $shareOwnershipPercentage,
            'total_share_value' => $totalShareValue,
            'max_loan_amount' => $settings->shares_requirement_enabled ?
                $totalShareValue / ($settings->shares_requirement_percentage / 100) :
                $settings->maximum_loan_amount,
            'shares_requirement_percentage' => $settings->shares_requirement_percentage,
            'shares_requirement_enabled' => $settings->shares_requirement_enabled,
        ];
    }

    public function hasDefaultedLoans(): bool
    {
        return $this->loans()->where('status', Loan::STATUS_DEFAULTED)->exists();
    }

    public function getTotalShareValue(): float
    {
        return $this->getShareValue();
    }

    public function getShareOwnershipPercentage(): float
    {
        $totalShares = Share::sum('value');
        $userShares = $this->getTotalShareValue();

        return $totalShares > 0 ? ($userShares / $totalShares) * 100 : 0;
    }

    public function check30PercentShareRequirement(float $loanAmount): bool
    {
        $settings = app(LoanSettings::class);

        return !$settings->shares_requirement_enabled ||
            $this->getTotalShareValue() >= ($loanAmount * 0.30); // 30% requirement
    }

    public function calculateSimpleInterest(float $principal, float $rate, int $termMonths): float
    {
        return $principal * ($rate / 100) * ($termMonths / 12);
    }

    public function applyForLoan(float $amount, int $termMonths, string $purpose, string $loanType = 'personal'): Loan
    {
        $settings = app(LoanSettings::class);

        if (!$settings->loans_enabled) {
            throw new \Exception('Loan applications are currently disabled.');
        }

        // Validate loan limits
        match (true) {
            $amount < $settings->minimum_loan_amount => throw new \Exception("Minimum loan amount is {$settings->minimum_loan_amount}."),
            $amount > $settings->maximum_loan_amount => throw new \Exception("Maximum loan amount is {$settings->maximum_loan_amount}."),
            $termMonths < $settings->minimum_loan_term_months => throw new \Exception("Minimum loan term is {$settings->minimum_loan_term_months} months."),
            $termMonths > $settings->maximum_loan_term_months => throw new \Exception("Maximum loan term is {$settings->maximum_loan_term_months} months."),
            !$this->check30PercentShareRequirement($amount) => throw new \Exception('You do not meet the share ownership requirement for this loan amount.'),
            $settings->require_no_defaulted_loans && $this->hasDefaultedLoans() => throw new \Exception('You cannot apply for a new loan while having defaulted loans.'),
            !$settings->allow_multiple_active_loans && $this->activeLoans()->exists() => throw new \Exception('You cannot have multiple active loans.'),
            default => null,
        };

        // Calculate loan details
        $interestRate = $settings->default_interest_rate;
        $interestAmount = $this->calculateSimpleInterest($amount, $interestRate, $termMonths);
        $totalPayable = $amount + $interestAmount;

        return $this->loans()->create([
            'loan_number' => Loan::generateLoanNumber(),
            'loan_type' => $loanType,
            'disbursement_date' => now(),
            'first_payment_date' => now()->addMonth(),
            'expected_end_date' => now()->addMonths($termMonths),
            'principal_amount' => $amount,
            'interest_rate' => $interestRate,
            'total_payable' => $totalPayable,
            'total_paid' => 0,
            'remaining_balance' => $totalPayable,
            'term_months' => $termMonths,
            'total_installments' => $termMonths,
            'paid_installments' => 0,
            'payment_frequency' => Loan::FREQUENCY_MONTHLY,
            'status' => Loan::STATUS_PENDING,
            'purpose' => $purpose,
        ]);
    }

    public function getTotalAmountBorrowed(): float
    {
        return $this->loans()->sum('principal_amount');
    }

    public function getTotalAmountRepaid(): float
    {
        return $this->loans()->sum('total_paid');
    }

    public function getTotalOutstandingBalance(): float
    {
        return $this->loans()->sum('remaining_balance');
    }

    public function getLoanStatistics(): array
    {
        $totalBorrowed = $this->getTotalAmountBorrowed();
        $totalRepaid = $this->getTotalAmountRepaid();

        return [
            'total_borrowed' => $totalBorrowed,
            'total_repaid' => $totalRepaid,
            'total_outstanding' => $this->getTotalOutstandingBalance(),
            'repayment_percentage' => $totalBorrowed > 0 ? ($totalRepaid / $totalBorrowed) * 100 : 0,
            'active_loans_count' => $this->activeLoans()->count(),
            'completed_loans_count' => $this->completedLoans()->count(),
            'defaulted_loans_count' => $this->loans()->where('status', Loan::STATUS_DEFAULTED)->count(),
        ];
    }
}
