<?php

declare(strict_types=1);

namespace App\Enums\Loan;

enum Status: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DISBURSED = 'disbursed';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case DEFAULTED = 'defaulted';
    case CANCELLED = 'cancelled';
    
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Review',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::DISBURSED => 'Disbursed',
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::DEFAULTED => 'Defaulted',
            self::CANCELLED => 'Cancelled',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::UNDER_REVIEW => 'blue',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::DISBURSED => 'purple',
            self::ACTIVE => 'green',
            self::COMPLETED => 'gray',
            self::DEFAULTED => 'red',
            self::CANCELLED => 'gray',
        };
    }
    
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'clock',
            self::UNDER_REVIEW => 'magnifying-glass',
            self::APPROVED => 'check-circle',
            self::REJECTED => 'x-circle',
            self::DISBURSED => 'banknotes',
            self::ACTIVE => 'arrow-path',
            self::COMPLETED => 'check-badge',
            self::DEFAULTED => 'exclamation-triangle',
            self::CANCELLED => 'x-mark',
        };
    }
    
    public function canBeEdited(): bool
    {
        return in_array($this, [self::PENDING, self::UNDER_REVIEW]);
    }
    
    public function canBeApproved(): bool
    {
        return in_array($this, [self::PENDING, self::UNDER_REVIEW]);
    }
    
    public function canBeRejected(): bool
    {
        return in_array($this, [self::PENDING, self::UNDER_REVIEW]);
    }
    
    public function canBeDisbursed(): bool
    {
        return $this === self::APPROVED;
    }
    
    public function canBeRepaid(): bool
    {
        return in_array($this, [self::ACTIVE, self::DISBURSED]);
    }
    
    public function isActive(): bool
    {
        return in_array($this, [self::ACTIVE, self::DISBURSED]);
    }
    
    public function isCompleted(): bool
    {
        return in_array($this, [self::COMPLETED, self::DEFAULTED, self::CANCELLED]);
    }
}
