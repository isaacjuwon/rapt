<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'transaction_id',
        'installment_number',
        'amount',
        'principal',
        'interest',
        'remaining_balance',
        'due_date',
        'paid_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'principal' => 'decimal:2',
        'interest' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    /**
     * Get the loan that owns the payment.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the transaction associated with the payment.
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Mark the payment as paid.
     */
    public function markAsPaid(float $amount, string $method, string $description = null): void
    {
        // Update payment status
        $this->paid_date = now();
        
        if ($amount >= $this->amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }
        
        $this->save();

        // Update loan
        $loan = $this->loan;
        $loan->makePayment($amount, $method, $description ?? 'Payment for installment #' . $this->installment_number);
    }
}
