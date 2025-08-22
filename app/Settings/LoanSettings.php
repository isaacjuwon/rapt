<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LoanSettings extends Settings
{
    // General Settings
    public bool $loans_enabled = true;

    // Share Requirement Settings
    public float $shares_requirement_percentage = 30.0; // Updated to 30% as per new requirement
    public bool $shares_requirement_enabled = true;

    // Loan Amount Settings
    public float $minimum_loan_amount = 1000.0;
    public float $maximum_loan_amount = 100000.0;

    // Interest Rate Settings
    public float $default_interest_rate = 5.0; // Changed from 15% to 5%
    public float $minimum_interest_rate = 1.0;
    public float $maximum_interest_rate = 25.0;

    // Loan Term Settings
    public int $minimum_loan_term_months = 6;
    public int $maximum_loan_term_months = 60;

    // Payment Settings
    public int $grace_period_days = 7;
    public float $late_payment_fee = 500.00;

    // Validation Settings
    public bool $allow_multiple_active_loans = true;
    public bool $require_no_defaulted_loans = true;

    // Legacy Settings (keeping for compatibility)
    public bool $require_collateral = true;
    public float $collateral_percentage = 50.0; // percentage of loan amount
    public int $credit_score_threshold = 600;
    public bool $auto_approval_enabled = false;

    public static function group(): string
    {
        return 'loans';
    }
}
