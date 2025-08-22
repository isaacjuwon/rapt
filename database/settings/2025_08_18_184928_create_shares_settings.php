<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shares.shares_enabled', true);
        $this->migrator->add('shares.minimum_share_amount', 1000.00);
        $this->migrator->add('shares.maximum_share_amount', 1000000.00);
        $this->migrator->add('shares.share_dividend_period', 90);
        $this->migrator->add('shares.default_dividend_rate', 5.5);
        $this->migrator->add('shares.auto_compound_dividends', false);
    }
};
