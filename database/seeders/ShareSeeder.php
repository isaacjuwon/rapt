<?php

namespace Database\Seeders;

use App\Models\Share;
use Illuminate\Database\Seeder;

class ShareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default website shares
        Share::create([
            'name' => 'Website Shares',
            'description' => 'Ownership shares in the website platform',
            'total_shares' => 10000,
            'available_shares' => 10000,
            'price_per_share' => 10.00,
            'minimum_purchase' => 1,
            'maximum_purchase' => 1000,
            'dividend_rate' => 5.00,
            'revenue_share_percentage' => 2.50,
            'voting_rights' => true,
            'is_active' => true,
            'is_transferable' => true,
            'launch_date' => now(),
        ]);
    }
}
