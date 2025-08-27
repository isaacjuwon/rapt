<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WalletSettings extends Settings
{
    public bool $wallet_enabled = true;
    public float $minimum_deposit = 100.00;
    public float $maximum_deposit = 1000000.00;
    public float $minimum_withdrawal = 1000.00;
    public float $maximum_withdrawal = 1000000.00;

    public static function group(): string
    {
        return 'wallet';
    }
}
