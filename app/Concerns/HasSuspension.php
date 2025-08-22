<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\SuspensionDuration;
use Carbon\Carbon;

trait HasSuspension
{
    /**
     * Check if the user is currently suspended
     */
    public function isSuspended(): bool
    {
        if (is_null($this->suspended_until)) {
            return false;
        }

        // If suspended_until is in the past, the suspension has expired
        if (Carbon::now()->greaterThan($this->suspended_until)) {
            $this->clearSuspension();
            return false;
        }

        return true;
    }

    /**
     * Check if the user is permanently suspended
     */
    public function isPermanentlySuspended(): bool
    {
        return !is_null($this->suspended_until) &&
            $this->suspended_until->year === 9999;
    }

    /**
     * Suspend the user for a specific duration
     */
    public function suspend(SuspensionDuration $duration, ?string $reason = null): void
    {
        $suspendedUntil = $duration->isPermanent()
            ? Carbon::create(9999, 12, 31, 23, 59, 59) // Far future date for permanent
            : $duration->getEndDate();

        $this->update([
            'suspended_until' => $suspendedUntil,
            'suspension_reason' => $reason,
        ]);
    }

    /**
     * Suspend the user until a specific date
     */
    public function suspendUntil(Carbon $until, ?string $reason = null): void
    {
        $this->update([
            'suspended_until' => $until,
            'suspension_reason' => $reason,
        ]);
    }

    /**
     * Clear the suspension (unsuspend the user)
     */
    public function clearSuspension(): void
    {
        $this->update([
            'suspended_until' => null,
            'suspension_reason' => null,
        ]);
    }

    /**
     * Get the suspension end date in a human-readable format
     */
    public function getSuspensionEndDateAttribute(): ?string
    {
        if (!$this->suspended_until) {
            return null;
        }

        if ($this->isPermanentlySuspended()) {
            return 'Permanent';
        }

        return $this->suspended_until->format('M j, Y \a\t g:i A');
    }

    /**
     * Get the time remaining for suspension
     */
    public function getSuspensionTimeRemainingAttribute(): ?string
    {
        if (!$this->isSuspended()) {
            return null;
        }

        if ($this->isPermanentlySuspended()) {
            return 'Permanent';
        }

        return $this->suspended_until->diffForHumans();
    }

    /**
     * Scope to get only suspended users
     */
    public function scopeSuspended($query)
    {
        return $query->whereNotNull('suspended_until')
            ->where('suspended_until', '>', Carbon::now());
    }

    /**
     * Scope to get users with expired suspensions
     */
    public function scopeExpiredSuspensions($query)
    {
        return $query->whereNotNull('suspended_until')
            ->where('suspended_until', '<=', Carbon::now())
            ->where('suspended_until', '!=', Carbon::create(9999, 12, 31, 23, 59, 59));
    }

    /**
     * Scope to get permanently suspended users
     */
    public function scopePermanentlySuspended($query)
    {
        return $query->where('suspended_until', Carbon::create(9999, 12, 31, 23, 59, 59));
    }
}
