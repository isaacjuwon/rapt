<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'share_id',
        'quantity',
        'purchase_price',
        'total_paid',
        'purchase_date',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'purchase_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class);
    }

    // Helper methods
    public function updateCurrentValue(): void
    {
        $currentValue = $this->quantity * $this->share->current_price;
        $unrealizedGainLoss = $currentValue - $this->total_cost;

        $this->update([
            'current_value' => $currentValue,
            'unrealized_gain_loss' => $unrealizedGainLoss,
            'last_updated_at' => now(),
        ]);
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 8);
    }

    public function getFormattedAverageCostAttribute(): string
    {
        return '$' . number_format($this->average_cost, 2);
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return '$' . number_format($this->total_cost, 2);
    }

    public function getFormattedCurrentValueAttribute(): string
    {
        return '$' . number_format($this->current_value ?? 0, 2);
    }

    public function getFormattedUnrealizedGainLossAttribute(): string
    {
        $value = $this->unrealized_gain_loss ?? 0;
        $sign = $value >= 0 ? '+' : '';
        return $sign . '$' . number_format($value, 2);
    }

    public function getFormattedRealizedGainLossAttribute(): string
    {
        $value = $this->realized_gain_loss ?? 0;
        $sign = $value >= 0 ? '+' : '';
        return $sign . '$' . number_format($value, 2);
    }

    public function getGainLossColorAttribute(): string
    {
        $value = $this->unrealized_gain_loss ?? 0;

        if ($value > 0) {
            return 'green';
        } elseif ($value < 0) {
            return 'red';
        }

        return 'gray';
    }

    public function getGainLossPercentageAttribute(): float
    {
        if (!$this->total_cost || $this->total_cost == 0) {
            return 0;
        }

        return (($this->unrealized_gain_loss ?? 0) / $this->total_cost) * 100;
    }

    public function getFormattedGainLossPercentageAttribute(): string
    {
        $percentage = $this->gain_loss_percentage;
        $sign = $percentage >= 0 ? '+' : '';
        return $sign . number_format($percentage, 2) . '%';
    }

    public function addShares(float $quantity, float $pricePerShare): void
    {
        $newTotalCost = $this->total_cost + ($quantity * $pricePerShare);
        $newQuantity = $this->quantity + $quantity;
        $newAverageCost = $newTotalCost / $newQuantity;

        $this->update([
            'quantity' => $newQuantity,
            'average_cost' => $newAverageCost,
            'total_cost' => $newTotalCost,
            'first_purchased_at' => $this->first_purchased_at ?? now(),
        ]);

        $this->updateCurrentValue();
    }

    public function removeShares(float $quantity, float $pricePerShare): float
    {
        if ($quantity > $this->quantity) {
            throw new \Exception('Cannot sell more shares than owned');
        }

        $saleValue = $quantity * $pricePerShare;
        $costBasis = $quantity * $this->average_cost;
        $realizedGainLoss = $saleValue - $costBasis;

        $newQuantity = $this->quantity - $quantity;
        $newTotalCost = $this->total_cost - $costBasis;

        $this->update([
            'quantity' => $newQuantity,
            'total_cost' => $newTotalCost,
            'realized_gain_loss' => $this->realized_gain_loss + $realizedGainLoss,
        ]);

        $this->updateCurrentValue();

        return $realizedGainLoss;
    }

    public function canSell(float $quantity): bool
    {
        return $this->quantity >= $quantity;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($userShare) {
            $userShare->updateCurrentValue();
        });

        static::updated(function ($userShare) {
            if ($userShare->wasChanged(['quantity']) && $userShare->quantity <= 0) {
                $userShare->delete();
            }
        });
    }
}
