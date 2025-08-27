<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Share;
use App\Models\UserShare;
use Illuminate\Support\Str;
use App\Models\ShareTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

trait HasShares
{
    /**
     * Manages user's share holdings and transactions.
     */

    /**
     * Get user's share holdings
     */
    public function userShares(): HasMany
    {
        return $this->hasMany(UserShare::class);
    }

    /**
     * Get user's share transactions
     */
    public function shareTransactions(): HasMany
    {
        return $this->hasMany(ShareTransaction::class);
    }

    /**
     * Get total number of shares owned by user
     */
    public function getTotalShares(): int
    {
        return $this->userShares()->sum('quantity');
    }

    /**
     * Get current value of user's shares
     */
    public function getShareValue(): float
    {
        $share = Share::first();
        if (!$share) {
            return 0.0;
        }

        return $this->getTotalShares() * $share->price_per_share;
    }

    /**
     * Buy shares for the user
     */
    public function buyShares(int $quantity): ShareTransaction
    {
        $settings = app(\App\Settings\SharesSettings::class);
        if (!$settings->shares_enabled) {
            throw new \RuntimeException('Shares are currently disabled.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }

        $share = Share::first();
        if (!$share) {
            throw new \RuntimeException('No shares available for purchase');
        }

        $totalCost = $quantity * $share->price_per_share;

        if ($totalCost < $settings->minimum_share_amount) {
            throw new \InvalidArgumentException('Purchase amount is below the minimum limit.');
        }

        if ($totalCost > $settings->maximum_share_amount) {
            throw new \InvalidArgumentException('Purchase amount is above the maximum limit.');
        }

        if ($share->available_shares < $quantity) {
            throw new \InvalidArgumentException('Not enough shares available');
        }

        $totalCost = $quantity * $share->price_per_share;

        // Check if user has sufficient balance
        if (!$this->hasSufficientBalance($totalCost)) {
            throw new \InvalidArgumentException('Insufficient wallet balance');
        }

        return DB::transaction(function () use ($share, $quantity, $totalCost) {
            // Debit user's wallet
            $this->debitWallet($totalCost, "Purchase of {$quantity} shares");

            // Update or create user share record
            $userShare = $this->userShares()->first();

            if ($userShare) {
                $userShare->quantity += $quantity;
                $userShare->save();
            } else {
                $this->userShares()->create([
                    'share_id' => $share->id,
                    'quantity' => $quantity,
                    'purchase_date' => now(),
                    'total_paid' => $totalCost,
                    'purchase_price' => $share->price_per_share,
                ]);
            }

            // Update available shares
            $share->available_shares -= $quantity;
            $share->save();

            // Create transaction record
            $transaction = $this->shareTransactions()->create([
                'type' => 'buy',
                'share_id' => $share->id,
                'quantity' => $quantity,
                'price_per_share' => $share->price_per_share,
                'total_amount' => $totalCost,
                'net_amount' => $totalCost, // Assuming no fees for simplicity
                'transaction_id' => Str::random(6),
                'wallet_id' => $this->mainWallet->id

            ]);

            return $transaction;
        });
    }

    /**
     * Sell shares for the user (requires admin approval)
     */
    public function sellShares(int $quantity): ShareTransaction
    {
        $settings = app(\App\Settings\SharesSettings::class);
        if (!$settings->shares_enabled) {
            throw new \RuntimeException('Shares are currently disabled.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }

        $userShare = $this->userShares()->first();
        if (!$userShare || $userShare->quantity < $quantity) {
            throw new \InvalidArgumentException('Not enough shares to sell');
        }

        $share = Share::first();
        if (!$share) {
            throw new \RuntimeException('Share information not available');
        }

        $totalAmount = $quantity * $share->price_per_share;

        return DB::transaction(function () use ($share, $quantity, $totalAmount) {
            // Create pending transaction record (requires admin approval)
            $transaction = $this->shareTransactions()->create([
                'type' => 'sell',
                'quantity' => $quantity,
                'price_per_share' => $share->price_per_share,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => 'Pending admin approval',
            ]);

            return $transaction;
        });
    }

    /**
     * Approve share sale transaction (admin only)
     */
    public function approveShareSale(int $transactionId): ShareTransaction
    {
        $transaction = $this->shareTransactions()->findOrFail($transactionId);

        if ($transaction->type !== 'sell' || $transaction->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending sell transactions can be approved');
        }

        $share = Share::first();
        if (!$share) {
            throw new \RuntimeException('Share information not available');
        }

        $userShare = $this->userShares()->first();
        if (!$userShare || $userShare->quantity < $transaction->quantity) {
            throw new \InvalidArgumentException('Not enough shares to sell');
        }

        return DB::transaction(function () use ($transaction, $share, $userShare) {
            // Credit user's wallet
            $this->creditWallet($transaction->total_amount, "Sale of {$transaction->quantity} shares");

            // Update user share record
            $newQuantity = $userShare->quantity - $transaction->quantity;
            if ($newQuantity > 0) {
                $userShare->quantity = $newQuantity;
                $userShare->save();
            } else {
                $userShare->delete();
            }

            // Update available shares
            $share->available_shares += $transaction->quantity;
            $share->save();

            // Update transaction status
            $transaction->update([
                'status' => 'completed',
                'notes' => 'Approved by admin',
            ]);

            return $transaction;
        });
    }

    /**
     * Reject share sale transaction (admin only)
     */
    public function rejectShareSale(int $transactionId, ?string $reason = null): ShareTransaction
    {
        $transaction = $this->shareTransactions()->findOrFail($transactionId);

        if ($transaction->type !== 'sell' || $transaction->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending sell transactions can be rejected');
        }

        $transaction->update([
            'status' => 'rejected',
            'notes' => $reason ?? 'Rejected by admin',
        ]);

        return $transaction;
    }

    /**
     * Get recent share transactions
     */
    public function getRecentShareTransactions(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->shareTransactions()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Check if user can buy shares
     */
    public function canBuyShares(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $share = Share::first();
        if (!$share || $share->available_shares < $quantity) {
            return false;
        }

        return $this->hasSufficientBalance($quantity * $share->price_per_share);
    }

    /**
     * Check if user can sell shares
     */
    public function canSellShares(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $userShare = $this->userShares()->first();
        return $userShare && $userShare->quantity >= $quantity;
    }

    /**
     * Get formatted share value for display
     */
    public function getFormattedShareValue(): string
    {
        return number_format($this->getShareValue(), 2);
    }

    /**
     * Get share value attribute
     */
    public function getShareValueAttribute(): float
    {
        return $this->getShareValue();
    }

    /**
     * Get total shares attribute
     */
    public function getTotalSharesAttribute(): int
    {
        return $this->getTotalShares();
    }

    /**
     * Get the share ownership percentage for the user.
     */
    public function getShareOwnershipPercentage(): float
    {
        $totalShares = Share::sum('value');
        $userShares = $this->getShareValue();

        return $totalShares > 0 ? ($userShares / $totalShares) * 100 : 0;
    }
}