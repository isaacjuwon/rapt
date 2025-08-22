<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'share_id',
        'wallet_id',
        'transaction_id',
        'type',
        'quantity',
        'price_per_share',
        'total_amount',
        'fees',
        'net_amount',
        'status',
        'notes',
        'metadata',
        'executed_at',
        'from_user_id', // For transfers between users
        'to_user_id',   // For transfers between users
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price_per_share' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
        'executed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeBuys($query)
    {
        return $query->where('type', 'buy');
    }

    public function scopeSells($query)
    {
        return $query->where('type', 'sell');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByShare($query, int $shareId)
    {
        return $query->where('share_id', $shareId);
    }

    // Helper methods
    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 8);
    }

    public function getFormattedPricePerShareAttribute(): string
    {
        return '$' . number_format($this->price_per_share, 2);
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function getFormattedFeesAttribute(): string
    {
        return '$' . number_format($this->fees, 2);
    }

    public function getFormattedNetAmountAttribute(): string
    {
        return '$' . number_format($this->net_amount, 2);
    }

    public function getFormattedTypeAttribute(): string
    {
        return ucfirst($this->type);
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'buy' => 'green',
            'sell' => 'red',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getTransactionIconAttribute(): string
    {
        return match ($this->type) {
            'buy' => 'arrow-down-circle',
            'sell' => 'arrow-up-circle',
            default => 'currency-dollar',
        };
    }

    public function isBuy(): bool
    {
        return $this->type === 'buy';
    }

    public function isSell(): bool
    {
        return $this->type === 'sell';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function canCancel(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'executed_at' => now(),
        ]);
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }

    public function cancel(?string $reason = null): void
    {
        if (!$this->canCancel()) {
            throw new \Exception('Transaction cannot be cancelled');
        }

        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->transaction_id) {
                $transaction->transaction_id = 'SHR' . date('YmdHis') . rand(1000, 9999);
            }

            if (!$transaction->executed_at && $transaction->status === 'completed') {
                $transaction->executed_at = now();
            }

            // Calculate net amount if not provided
            if (!$transaction->net_amount) {
                if ($transaction->type === 'buy') {
                    $transaction->net_amount = $transaction->total_amount + $transaction->fees;
                } else {
                    $transaction->net_amount = $transaction->total_amount - $transaction->fees;
                }
            }
        });
    }
}
