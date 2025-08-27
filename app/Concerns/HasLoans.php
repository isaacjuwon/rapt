<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Loan;
use App\Models\Share;
use App\Settings\LoanSettings;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasLoans
{
    /**
     * Get the loans for the user.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the active loans for the user.
     */
    public function activeLoans(): Builder
    {
        return $this->loans()->active();
    }

    /**
     * Get the pending loans for the user.
     */
    public function pendingLoans(): Builder
    {
        return $this->loans()->pending();
    }

    /**
     * Get the completed loans for the user.
     */
    public function completedLoans(): Builder
    {
        return $this->loans()->completed();
    }

    /**
     * Get the loan history for the user.
     */
    public function getLoanHistory(): Collection
    {
        return $this->loans()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get the active loans for the user.
     */
    public function getActiveLoans(): Collection
    {
        return $this->activeLoans()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get loan eligibility details for the user.
     */
    public function getLoanEligibilityDetails(): array
    {
        $settings = app(LoanSettings::class);
        $totalShareValue = $this->getShareValue();
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

    /**
     * Check if the user has defaulted loans.
     */
    public function hasDefaultedLoans(): bool
    {
        return $this->loans()->where('status', Loan::STATUS_DEFAULTED)->exists();
    }

    /**
     * Check 30% share requirement for loan amount.
     */
    public function check30PercentShareRequirement(float $loanAmount): bool
    {
        $settings = app(LoanSettings::class);

        return !$settings->shares_requirement_enabled ||
            $this->getShareValue() >= ($loanAmount * ($settings->shares_requirement_percentage / 100));
    }

    /**
     * Calculate simple interest.
     */
    public function calculateSimpleInterest(float $principal, float $rate, int $termMonths): float
    {
        return $principal * ($rate / 100) * ($termMonths / 12);
    }

    /**
     * Apply for a loan.
     */
    public function applyForLoan(float $amount, int $termMonths, string $purpose, string $loanType = 'personal'): Loan
    {
        $settings = app(LoanSettings::class);

        if (!$settings->loans_enabled) {
            throw ValidationException::withMessages([
                'loan_application' => ['Loan applications are currently disabled.'],
            ]);
        }

        // Validate loan limits
        match (true) {
            $amount < $settings->minimum_loan_amount => throw ValidationException::withMessages([
                'amount' => ["Minimum loan amount is {$settings->minimum_loan_amount}."]
            ]),
            $amount > $settings->maximum_loan_amount => throw ValidationException::withMessages([
                'amount' => ["Maximum loan amount is {$settings->maximum_loan_amount}."]
            ]),
            $termMonths < $settings->minimum_loan_term_months => throw ValidationException::withMessages([
                'term_months' => ["Minimum loan term is {$settings->minimum_loan_term_months} months."]
            ]),
            $termMonths > $settings->maximum_loan_term_months => throw ValidationException::withMessages([
                'term_months' => ["Maximum loan term is {$settings->maximum_loan_term_months} months."]
            ]),
            !$this->check30PercentShareRequirement($amount) => throw ValidationException::withMessages([
                'shares' => ['You do not meet the share ownership requirement for this loan amount.']
            ]),
            $settings->require_no_defaulted_loans && $this->hasDefaultedLoans() => throw ValidationException::withMessages([
                'loans' => ['You cannot apply for a new loan while having defaulted loans.']
            ]),
            !$settings->allow_multiple_active_loans && $this->activeLoans()->exists() => throw ValidationException::withMessages([
                'loans' => ['You cannot have multiple active loans.']
            ]),
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

    /**
     * Get the total amount borrowed by the user.
     */
    public function getTotalAmountBorrowed(): float
    {
        return $this->loans()->sum('principal_amount');
    }

    /**
     * Get the total amount repaid by the user.
     */
    public function getTotalAmountRepaid(): float
    {
        return $this->loans()->sum('total_paid');
    }

    /**
     * Get the total outstanding balance for the user.
     */
    public function getTotalOutstandingBalance(): float
    {
        return $this->loans()->sum('remaining_balance');
    }

    /**
     * Get loan statistics for the user.
     */
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
