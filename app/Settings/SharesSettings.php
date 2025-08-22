<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SharesSettings extends Settings
{
    public bool $shares_enabled = true;
    public float $minimum_share_amount = 1000.00;
    public float $maximum_share_amount = 1000000.00;
    public int $share_dividend_period = 90; // days
    public float $default_dividend_rate = 5.5; // percentage
    public bool $auto_compound_dividends = false;

    public static function group(): string
    {
        return 'shares';
    }
}
