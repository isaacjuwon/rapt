<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Share extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'total_shares',
        'available_shares',
        'price_per_share',
        'minimum_purchase',
        'maximum_purchase',
        'dividend_rate',
        'revenue_share_percentage',
        'voting_rights',
        'is_active',
        'is_transferable',
        'launch_date',
        'metadata',
    ];

    protected $casts = [
        'total_shares' => 'integer',
        'available_shares' => 'integer',
        'price_per_share' => 'decimal:2',
        'minimum_purchase' => 'integer',
        'maximum_purchase' => 'integer',
        'dividend_rate' => 'decimal:4',
        'revenue_share_percentage' => 'decimal:4',
        'voting_rights' => 'boolean',
        'is_active' => 'boolean',
        'is_transferable' => 'boolean',
        'launch_date' => 'datetime',
        'metadata' => 'array',
    ];

    public function userShares(): HasMany
    {
        return $this->hasMany(UserShare::class);
    }

    public function shareTransactions(): HasMany
    {
        return $this->hasMany(ShareTransaction::class);
    }



    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_shares', '>', 0);
    }

    public function scopeTransferable($query)
    {
        return $query->where('is_transferable', true);
    }

    // Helper methods
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price_per_share, 2);
    }

    public function getFormattedTotalValueAttribute(): string
    {
        $totalValue = $this->total_shares * $this->price_per_share;
        return '$' . number_format($totalValue, 2);
    }

    public function getFormattedAvailableValueAttribute(): string
    {
        $availableValue = $this->available_shares * $this->price_per_share;
        return '$' . number_format($availableValue, 2);
    }

    public function getOwnershipPercentageAttribute(): float
    {
        if ($this->total_shares == 0) {
            return 0;
        }

        $ownedShares = $this->total_shares - $this->available_shares;
        return ($ownedShares / $this->total_shares) * 100;
    }

    public function getAvailabilityPercentageAttribute(): float
    {
        if ($this->total_shares == 0) {
            return 0;
        }

        return ($this->available_shares / $this->total_shares) * 100;
    }

    public function getFormattedDividendRateAttribute(): string
    {
        if (!$this->dividend_rate) {
            return 'No dividends';
        }

        return number_format($this->dividend_rate, 2) . '% annually';
    }

    public function getFormattedRevenueShareAttribute(): string
    {
        if (!$this->revenue_share_percentage) {
            return 'No revenue share';
        }

        return number_format($this->revenue_share_percentage, 2) . '% of site revenue';
    }

    public function getShareTypeColorAttribute(): string
    {
        if ($this->voting_rights && $this->dividend_rate > 0) {
            return 'purple'; // Premium shares
        } elseif ($this->voting_rights) {
            return 'blue'; // Voting shares
        } elseif ($this->dividend_rate > 0) {
            return 'green'; // Dividend shares
        }

        return 'gray'; // Basic shares
    }

    public function getTotalSharesOwned(): int
    {
        return (int) $this->userShares()->where('is_active', true)->sum('quantity');
    }

    public function getTotalTradingVolume(): int
    {
        return (int) $this->shareTransactions()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('quantity');
    }

    public function canPurchase(int $quantity): bool
    {
        return $this->is_active &&
            $this->available_shares >= $quantity &&
            $quantity >= $this->minimum_purchase &&
            (!$this->maximum_purchase || $quantity <= $this->maximum_purchase);
    }

    public function purchaseShares(int $quantity): void
    {
        if (!$this->canPurchase($quantity)) {
            throw new \Exception('Cannot purchase the requested quantity of shares');
        }

        $this->update([
            'available_shares' => $this->available_shares - $quantity,
        ]);
    }

    public function returnShares(int $quantity): void
    {
        $this->update([
            'available_shares' => $this->available_shares + $quantity,
        ]);
    }
}
