<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mtnBrand = \App\Models\Brand::where('name', 'MTN')->first();
        $gloBrand = \App\Models\Brand::where('name', 'Glo')->first();
        $airtelBrand = \App\Models\Brand::where('name', 'Airtel')->first();
        $mobileBrand = \App\Models\Brand::where('name', '9mobile')->first();

        if ($mtnBrand) {
            \App\Models\DataPlan::create([
                'name' => 'MTN 1GB Daily',
                'description' => '1GB data for 24 hours',
                'status' => true,
                'api_code' => 'MTN1GBD',
                'service_id' => '101',
                'reference' => 'MTN-DAILY-1GB',
                'type' => 'daily',
                'duration' => '1 day',
                'price' => 300.00,
                'discounted_price' => 280.00,
                'brand_id' => $mtnBrand->id,
            ]);
        }

        if ($gloBrand) {
            \App\Models\DataPlan::create([
                'name' => 'Glo 2GB Weekly',
                'description' => '2GB data for 7 days',
                'status' => true,
                'api_code' => 'GLO2GBW',
                'service_id' => '102',
                'reference' => 'GLO-WEEKLY-2GB',
                'type' => 'weekly',
                'duration' => '7 days',
                'price' => 500.00,
                'discounted_price' => 480.00,
                'brand_id' => $gloBrand->id,
            ]);
        }

        if ($airtelBrand) {
            \App\Models\DataPlan::create([
                'name' => 'Airtel 5GB Monthly',
                'description' => '5GB data for 30 days',
                'status' => true,
                'api_code' => 'AIRTEL5GBM',
                'service_id' => '103',
                'reference' => 'AIRTEL-MONTHLY-5GB',
                'type' => 'monthly',
                'duration' => '30 days',
                'price' => 1500.00,
                'discounted_price' => 1450.00,
                'brand_id' => $airtelBrand->id,
            ]);
        }

        if ($mobileBrand) {
            \App\Models\DataPlan::create([
                'name' => '9mobile 10GB Monthly',
                'description' => '10GB data for 30 days',
                'status' => true,
                'api_code' => '9MOBILE10GBM',
                'service_id' => '104',
                'reference' => '9MOBILE-MONTHLY-10GB',
                'type' => 'monthly',
                'duration' => '30 days',
                'price' => 2500.00,
                'discounted_price' => 2400.00,
                'brand_id' => $mobileBrand->id,
            ]);
        }
    }
}
