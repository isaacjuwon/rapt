<?php

declare(strict_types=1);

namespace App\Enums\Loan;

enum DisbursementMethod: string
{
    case WALLET_TRANSFER = 'wallet_transfer';
    case BANK_TRANSFER = 'bank_transfer';
    case CHECK = 'check';
    case CASH = 'cash';
    case MOBILE_MONEY = 'mobile_money';
    case CRYPTOCURRENCY = 'cryptocurrency';
    case DIRECT_DEPOSIT = 'direct_deposit';
    
    public function label(): string
    {
        return match($this) {
            self::WALLET_TRANSFER => 'Wallet Transfer',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CHECK => 'Check',
            self::CASH => 'Cash',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CRYPTOCURRENCY => 'Cryptocurrency',
            self::DIRECT_DEPOSIT => 'Direct Deposit',
        };
    }
    
    public function description(): string
    {
        return match($this) {
            self::WALLET_TRANSFER => 'Transfer funds directly to user\'s wallet',
            self::BANK_TRANSFER => 'Transfer funds to user\'s bank account',
            self::CHECK => 'Issue a check to the user',
            self::CASH => 'Provide cash to the user',
            self::MOBILE_MONEY => 'Transfer to user\'s mobile money account',
            self::CRYPTOCURRENCY => 'Transfer in cryptocurrency',
            self::DIRECT_DEPOSIT => 'Direct deposit to user\'s account',
        };
    }
    
    public function processingTime(): string
    {
        return match($this) {
            self::WALLET_TRANSFER => 'Instant',
            self::BANK_TRANSFER => '1-3 business days',
            self::CHECK => '5-7 business days',
            self::CASH => 'Same day',
            self::MOBILE_MONEY => 'Instant',
            self::CRYPTOCURRENCY => '10-60 minutes',
            self::DIRECT_DEPOSIT => '1-2 business days',
        };
    }
    
    public function feePercentage(): float
    {
        return match($this) {
            self::WALLET_TRANSFER => 0.0,
            self::BANK_TRANSFER => 1.5,
            self::CHECK => 2.0,
            self::CASH => 0.0,
            self::MOBILE_MONEY => 1.0,
            self::CRYPTOCURRENCY => 2.5,
            self::DIRECT_DEPOSIT => 0.5,
        };
    }
    
    public function minimumFee(): float
    {
        return match($this) {
            self::WALLET_TRANSFER => 0.0,
            self::BANK_TRANSFER => 5.0,
            self::CHECK => 10.0,
            self::CASH => 0.0,
            self::MOBILE_MONEY => 2.0,
            self::CRYPTOCURRENCY => 15.0,
            self::DIRECT_DEPOSIT => 3.0,
        };
    }
    
    public function maximumFee(): float
    {
        return match($this) {
            self::WALLET_TRANSFER => 0.0,
            self::BANK_TRANSFER => 50.0,
            self::CHECK => 100.0,
            self::CASH => 0.0,
            self::MOBILE_MONEY => 25.0,
            self::CRYPTOCURRENCY => 500.0,
            self::DIRECT_DEPOSIT => 35.0,
        };
    }
    
    public function requiresAdditionalInfo(): bool
    {
        return in_array($this, [
            self::BANK_TRANSFER,
            self::CHECK,
            self::MOBILE_MONEY,
            self::CRYPTOCURRENCY,
            self::DIRECT_DEPOSIT,
        ]);
    }
    
    public function getRequiredFields(): array
    {
        return match($this) {
            self::BANK_TRANSFER => ['bank_name', 'account_number', 'routing_number'],
            self::CHECK => ['mailing_address'],
            self::MOBILE_MONEY => ['phone_number', 'provider'],
            self::CRYPTOCURRENCY => ['wallet_address', 'cryptocurrency_type'],
            self::DIRECT_DEPOSIT => ['bank_name', 'account_number', 'routing_number'],
            default => [],
        };
    }
    
    public function isAvailableForAmount(float $amount): bool
    {
        return match($this) {
            self::CASH => $amount <= 5000,
            self::CHECK => $amount >= 100,
            self::CRYPTOCURRENCY => $amount >= 50,
            default => true,
        };
    }
    
    public function getPopularOptions(): array
    {
        return [
            self::WALLET_TRANSFER,
            self::BANK_TRANSFER,
            self::DIRECT_DEPOSIT,
        ];
    }
    
    public function calculateFee(float $amount): float
    {
        $percentageFee = ($amount * $this->feePercentage()) / 100;
        $fee = max($percentageFee, $this->minimumFee());
        return min($fee, $this->maximumFee());
    }
    
    public function getEstimatedDeliveryDate(\DateTime $disbursementDate): \DateTime
    {
        $businessDaysToAdd = match($this) {
            self::WALLET_TRANSFER => 0,
            self::BANK_TRANSFER => 2,
            self::CHECK => 6,
            self::CASH => 0,
            self::MOBILE_MONEY => 0,
            self::CRYPTOCURRENCY => 0,
            self::DIRECT_DEPOSIT => 1,
        };
        
        $deliveryDate = clone $disbursementDate;
        $daysAdded = 0;
        
        while ($daysAdded < $businessDaysToAdd) {
            $deliveryDate->modify('+1 day');
            if ($deliveryDate->format('N') < 6) { // Monday to Friday
                $daysAdded++;
            }
        }
        
        return $deliveryDate;
    }
    
    public function isInstant(): bool
    {
        return in_array($this, [
            self::WALLET_TRANSFER,
            self::CASH,
            self::MOBILE_MONEY,
            self::CRYPTOCURRENCY,
        ]);
    }
}
