<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AirtimePlanSeeder extends Seeder
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
            \App\Models\AirtimePlan::create([
                'name' => 'MTN N100 Airtime',
                'description' => 'N100 Airtime for MTN',
                'status' => true,
                'api_code' => 'MTN100',
                'service_id' => '1',
                'brand_id' => $mtnBrand->id,
            ]);
        }

        if ($gloBrand) {
            \App\Models\AirtimePlan::create([
                'name' => 'Glo N200 Airtime',
                'description' => 'N200 Airtime for Glo',
                'status' => true,
                'api_code' => 'GLO200',
                'service_id' => '2',
                'brand_id' => $gloBrand->id,
            ]);
        }

        if ($airtelBrand) {
            \App\Models\AirtimePlan::create([
                'name' => 'Airtel N500 Airtime',
                'description' => 'N500 Airtime for Airtel',
                'status' => true,
                'api_code' => 'AIRTEL500',
                'service_id' => '3',
                'brand_id' => $airtelBrand->id,
            ]);
        }

        if ($mobileBrand) {
            \App\Models\AirtimePlan::create([
                'name' => '9mobile N1000 Airtime',
                'description' => 'N1000 Airtime for 9mobile',
                'status' => true,
                'api_code' => '9MOBILE1000',
                'service_id' => '4',
                'brand_id' => $mobileBrand->id,
            ]);
        }
    }
}
