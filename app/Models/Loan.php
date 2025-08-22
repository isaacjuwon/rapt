<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasFactory;

    // Loan status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DISBURSED = 'disbursed';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DEFAULTED = 'defaulted';
    public const STATUS_REJECTED = 'rejected';

    // Loan type constants
    public const TYPE_PERSONAL = 'personal';
    public const TYPE_BUSINESS = 'business';
    public const TYPE_EDUCATION = 'education';
    public const TYPE_AGRICULTURE = 'agriculture';
    public const TYPE_EMERGENCY = 'emergency';

    // Payment frequency constants
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_BIWEEKLY = 'biweekly';
    public const FREQUENCY_MONTHLY = 'monthly';

    protected $fillable = [
        'user_id',
        'loan_number',
        'loan_type',
        'principal_amount',
        'interest_rate',
        'total_payable',
        'total_paid',
        'remaining_balance',
        'term_months',
        'total_installments',
        'paid_installments',
        'disbursement_date',
        'first_payment_date',
        'last_payment_date',
        'expected_end_date',
        'payment_frequency',
        'status',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'disbursement_date' => 'date',
        'first_payment_date' => 'date',
        'last_payment_date' => 'date',
        'expected_end_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_payable' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    /**
     * Get the user that owns the loan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payments for the loan.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    /**
     * Get the transactions for the loan.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calculate the installment amount.
     */
    public function calculateInstallmentAmount(): float
    {
        if ($this->total_installments <= 0) {
            return 0;
        }

        return round($this->total_payable / $this->total_installments, 2);
    }

    /**
     * Calculate the number of installments that have been paid.
     */
    public function calculatePaidInstallments(): int
    {
        $installmentAmount = $this->calculateInstallmentAmount();
        if ($installmentAmount <= 0) {
            return 0;
        }

        return (int) floor($this->total_paid / $installmentAmount);
    }

    /**
     * Check if the loan can be disbursed.
     */
    public function canBeDisbursed(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the loan is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_DISBURSED, self::STATUS_ACTIVE]);
    }

    /**
     * Check if the loan is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the loan has remaining balance.
     */
    public function hasRemainingBalance(): bool
    {
        return $this->remaining_balance > 0;
    }

    /**
     * Get the next payment due date.
     */
    public function getNextPaymentDueDate(): ?\Carbon\Carbon
    {
        if (!$this->isActive() || !$this->hasRemainingBalance()) {
            return null;
        }

        $nextPayment = $this->payments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        return $nextPayment?->due_date;
    }

    /**
     * Get the overdue payment amount.
     */
    public function getOverdueAmount(): float
    {
        return $this->payments()
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');
    }

    /**
     * Update loan status based on payment progress.
     */
    public function updateStatus(): void
    {
        if ($this->remaining_balance <= 0 && $this->total_paid >= $this->total_payable) {
            $this->status = self::STATUS_COMPLETED;
        } elseif ($this->total_paid > 0 && $this->status === self::STATUS_DISBURSED) {
            $this->status = self::STATUS_ACTIVE;
        }

        $this->save();
    }

    /**
     * Generate a unique loan number.
     */
    public static function generateLoanNumber(): string
    {
        $year = date('Y');
        $sequence = str_pad(
            self::whereYear('created_at', $year)->count() + 1,
            6,
            '0',
            STR_PAD_LEFT
        );

        return "LN{$year}{$sequence}";
    }

    /**
     * Get loan status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DISBURSED => 'Disbursed',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_DEFAULTED => 'Defaulted',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get loan type options.
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_PERSONAL => 'Personal',
            self::TYPE_BUSINESS => 'Business',
            self::TYPE_EDUCATION => 'Education',
            self::TYPE_AGRICULTURE => 'Agriculture',
            self::TYPE_EMERGENCY => 'Emergency',
        ];
    }

    /**
     * Get payment frequency options.
     */
    public static function getFrequencyOptions(): array
    {
        return [
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_BIWEEKLY => 'Bi-weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
        ];
    }

    /**
     * Get the human-readable status.
     */
    public function getStatusLabel(): string
    {
        return self::getStatusOptions()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get the human-readable loan type.
     */
    public function getTypeLabel(): string
    {
        return self::getTypeOptions()[$this->loan_type] ?? ucfirst($this->loan_type);
    }

    /**
     * Get the human-readable payment frequency.
     */
    public function getFrequencyLabel(): string
    {
        return self::getFrequencyOptions()[$this->payment_frequency] ?? ucfirst($this->payment_frequency);
    }

    /**
     * Get the progress percentage of the loan.
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_payable <= 0) {
            return 0;
        }

        return min(100, ($this->total_paid / $this->total_payable) * 100);
    }

    /**
     * Scope a query to only include active loans.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_DISBURSED, self::STATUS_ACTIVE]);
    }

    /**
     * Scope a query to only include pending loans.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include completed loans.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include loans of a given type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('loan_type', $type);
    }

    /**
     * Scope a query to only include loans for a given user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
