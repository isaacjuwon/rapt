<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // General Settings
        $this->migrator->add('loans.loans_enabled', true);

        // Share Requirement Settings
        $this->migrator->add('loans.shares_requirement_percentage', 15.0);
        $this->migrator->add('loans.shares_requirement_enabled', true);

        // Loan Amount Settings
        $this->migrator->add('loans.minimum_loan_amount', 1000.0);
        $this->migrator->add('loans.maximum_loan_amount', 100000.0);

        // Interest Rate Settings
        $this->migrator->add('loans.default_interest_rate', 5.0);
        $this->migrator->add('loans.minimum_interest_rate', 1.0);
        $this->migrator->add('loans.maximum_interest_rate', 25.0);

        // Loan Term Settings
        $this->migrator->add('loans.minimum_loan_term_months', 6);
        $this->migrator->add('loans.maximum_loan_term_months', 60);

        // Payment Settings
        $this->migrator->add('loans.grace_period_days', 7);
        $this->migrator->add('loans.late_payment_fee', 500.00);

        // Validation Settings
        $this->migrator->add('loans.allow_multiple_active_loans', true);
        $this->migrator->add('loans.require_no_defaulted_loans', true);

        // Legacy Settings (keeping for compatibility)
        $this->migrator->add('loans.require_collateral', true);
        $this->migrator->add('loans.collateral_percentage', 50.0);
        $this->migrator->add('loans.credit_score_threshold', 600);
        $this->migrator->add('loans.auto_approval_enabled', false);
    }
};
