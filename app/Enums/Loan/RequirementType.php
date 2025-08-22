<?php

declare(strict_types=1);

namespace App\Enums\Loan;

enum RequirementType: string
{
    case WALLET_PERCENTAGE = 'wallet_percentage';
    case MINIMUM_BALANCE = 'minimum_balance';
    case CREDIT_SCORE = 'credit_score';
    case INCOME_VERIFICATION = 'income_verification';
    case EMPLOYMENT_VERIFICATION = 'employment_verification';
    case COLLATERAL = 'collateral';
    case GUARANTOR = 'guarantor';
    case DOCUMENT = 'document';
    case SHARE_OWNERSHIP = 'share_ownership';
    case MEMBERSHIP_DURATION = 'membership_duration';
    case CUSTOM = 'custom';
    
    public function label(): string
    {
        return match($this) {
            self::WALLET_PERCENTAGE => 'Wallet Percentage',
            self::MINIMUM_BALANCE => 'Minimum Balance',
            self::CREDIT_SCORE => 'Credit Score',
            self::INCOME_VERIFICATION => 'Income Verification',
            self::EMPLOYMENT_VERIFICATION => 'Employment Verification',
            self::COLLATERAL => 'Collateral',
            self::GUARANTOR => 'Guarantor',
            self::DOCUMENT => 'Document',
            self::SHARE_OWNERSHIP => 'Share Ownership',
            self::MEMBERSHIP_DURATION => 'Membership Duration',
            self::CUSTOM => 'Custom Requirement',
        };
    }
    
    public function description(): string
    {
        return match($this) {
            self::WALLET_PERCENTAGE => 'Percentage of loan amount that must be available in wallet',
            self::MINIMUM_BALANCE => 'Minimum wallet balance required',
            self::CREDIT_SCORE => 'Minimum credit score threshold',
            self::INCOME_VERIFICATION => 'Proof of income required',
            self::EMPLOYMENT_VERIFICATION => 'Employment status verification',
            self::COLLATERAL => 'Collateral assets required',
            self::GUARANTOR => 'Guarantor required for loan',
            self::DOCUMENT => 'Specific documents required',
            self::SHARE_OWNERSHIP => 'Minimum share ownership percentage',
            self::MEMBERSHIP_DURATION => 'Minimum platform membership duration',
            self::CUSTOM => 'Custom requirement defined by admin',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::WALLET_PERCENTAGE => 'blue',
            self::MINIMUM_BALANCE => 'green',
            self::CREDIT_SCORE => 'purple',
            self::INCOME_VERIFICATION => 'yellow',
            self::EMPLOYMENT_VERIFICATION => 'orange',
            self::COLLATERAL => 'red',
            self::GUARANTOR => 'pink',
            self::DOCUMENT => 'gray',
            self::SHARE_OWNERSHIP => 'indigo',
            self::MEMBERSHIP_DURATION => 'teal',
            self::CUSTOM => 'stone',
        };
    }
    
    public function icon(): string
    {
        return match($this) {
            self::WALLET_PERCENTAGE => 'wallet',
            self::MINIMUM_BALANCE => 'currency-dollar',
            self::CREDIT_SCORE => 'chart-bar',
            self::INCOME_VERIFICATION => 'document-text',
            self::EMPLOYMENT_VERIFICATION => 'briefcase',
            self::COLLATERAL => 'home',
            self::GUARANTOR => 'user-group',
            self::DOCUMENT => 'clipboard-document',
            self::SHARE_OWNERSHIP => 'share',
            self::MEMBERSHIP_DURATION => 'calendar',
            self::CUSTOM => 'cog',
        };
    }
    
    public function isNumeric(): bool
    {
        return in_array($this, [
            self::WALLET_PERCENTAGE,
            self::MINIMUM_BALANCE,
            self::CREDIT_SCORE,
            self::SHARE_OWNERSHIP,
            self::MEMBERSHIP_DURATION,
        ]);
    }
    
    public function isDocumentBased(): bool
    {
        return in_array($this, [
            self::INCOME_VERIFICATION,
            self::EMPLOYMENT_VERIFICATION,
            self::DOCUMENT,
        ]);
    }
    
    public function isSystemValidated(): bool
    {
        return in_array($this, [
            self::WALLET_PERCENTAGE,
            self::MINIMUM_BALANCE,
            self::CREDIT_SCORE,
            self::SHARE_OWNERSHIP,
            self::MEMBERSHIP_DURATION,
        ]);
    }
    
    public function isManualValidation(): bool
    {
        return in_array($this, [
            self::INCOME_VERIFICATION,
            self::EMPLOYMENT_VERIFICATION,
            self::COLLATERAL,
            self::GUARANTOR,
            self::DOCUMENT,
        ]);
    }
    
    public function validationRules(): array
    {
        return match($this) {
            self::WALLET_PERCENTAGE => ['required', 'numeric', 'min:0', 'max:100'],
            self::MINIMUM_BALANCE => ['required', 'numeric', 'min:0'],
            self::CREDIT_SCORE => ['required', 'numeric', 'min:300', 'max:850'],
            self::INCOME_VERIFICATION => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            self::EMPLOYMENT_VERIFICATION => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            self::COLLATERAL => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            self::GUARANTOR => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            self::DOCUMENT => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            self::SHARE_OWNERSHIP => ['required', 'numeric', 'min:0', 'max:100'],
            self::MEMBERSHIP_DURATION => ['required', 'numeric', 'min:0'],
            self::CUSTOM => ['required'],
        };
    }
}
