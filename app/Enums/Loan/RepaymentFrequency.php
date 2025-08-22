<?php

declare(strict_types=1);

namespace App\Enums\Loan;

enum RepaymentFrequency: string
{
    case WEEKLY = 'weekly';
    case BI_WEEKLY = 'bi_weekly';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case SEMI_ANNUALLY = 'semi_annually';
    case ANNUALLY = 'annually';
    case LUMP_SUM = 'lump_sum';
    
    public function label(): string
    {
        return match($this) {
            self::WEEKLY => 'Weekly',
            self::BI_WEEKLY => 'Bi-Weekly',
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::SEMI_ANNUALLY => 'Semi-Annually',
            self::ANNUALLY => 'Annually',
            self::LUMP_SUM => 'Lump Sum',
        };
    }
    
    public function description(): string
    {
        return match($this) {
            self::WEEKLY => 'Payments due every week',
            self::BI_WEEKLY => 'Payments due every two weeks',
            self::MONTHLY => 'Payments due once per month',
            self::QUARTERLY => 'Payments due once every three months',
            self::SEMI_ANNUALLY => 'Payments due twice per year',
            self::ANNUALLY => 'Payments due once per year',
            self::LUMP_SUM => 'Single payment at loan maturity',
        };
    }
    
    public function intervalInDays(): int
    {
        return match($this) {
            self::WEEKLY => 7,
            self::BI_WEEKLY => 14,
            self::MONTHLY => 30,
            self::QUARTERLY => 90,
            self::SEMI_ANNUALLY => 182,
            self::ANNUALLY => 365,
            self::LUMP_SUM => 0,
        };
    }
    
    public function paymentsPerYear(): int
    {
        return match($this) {
            self::WEEKLY => 52,
            self::BI_WEEKLY => 26,
            self::MONTHLY => 12,
            self::QUARTERLY => 4,
            self::SEMI_ANNUALLY => 2,
            self::ANNUALLY => 1,
            self::LUMP_SUM => 1,
        };
    }
    
    public function isRecurring(): bool
    {
        return $this !== self::LUMP_SUM;
    }
    
    public function calculateTotalPayments(int $termMonths): int
    {
        if ($this === self::LUMP_SUM) {
            return 1;
        }
        
        $totalDays = $termMonths * 30; // Approximate days
        $intervalDays = $this->intervalInDays();
        
        return (int) ceil($totalDays / $intervalDays);
    }
    
    public function calculatePaymentDate(\DateTime $startDate, int $paymentNumber): \DateTime
    {
        if ($this === self::LUMP_SUM) {
            // For lump sum, return the end date based on term
            return (clone $startDate)->modify('+12 months'); // Default to 1 year
        }
        
        $interval = $this->intervalInDays() . ' days';
        return (clone $startDate)->modify('+' . ($paymentNumber - 1) . ' ' . $interval);
    }
    
    public function formatPaymentSchedule(int $termMonths): string
    {
        $totalPayments = $this->calculateTotalPayments($termMonths);
        
        return match($this) {
            self::WEEKLY => "{$totalPayments} weekly payments",
            self::BI_WEEKLY => "{$totalPayments} bi-weekly payments",
            self::MONTHLY => "{$totalPayments} monthly payments",
            self::QUARTERLY => "{$totalPayments} quarterly payments",
            self::SEMI_ANNUALLY => "{$totalPayments} semi-annual payments",
            self::ANNUALLY => "{$totalPayments} annual payments",
            self::LUMP_SUM => "Single lump sum payment",
        };
    }
    
    public function recommendedForTerm(int $termMonths): bool
    {
        return match($this) {
            self::WEEKLY => $termMonths <= 6,
            self::BI_WEEKLY => $termMonths <= 12,
            self::MONTHLY => $termMonths > 3 && $termMonths <= 60,
            self::QUARTERLY => $termMonths > 12 && $termMonths <= 36,
            self::SEMI_ANNUALLY => $termMonths > 24 && $termMonths <= 60,
            self::ANNUALLY => $termMonths > 36,
            self::LUMP_SUM => $termMonths <= 3,
        };
    }
    
    public function getPopularOptions(): array
    {
        return [
            self::MONTHLY,
            self::BI_WEEKLY,
            self::WEEKLY,
        ];
    }
}
