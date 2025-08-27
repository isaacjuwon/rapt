<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ElectricityPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ekedcBrand = \App\Models\Brand::where('name', 'EKEDC')->first();
        $ikedcBrand = \App\Models\Brand::where('name', 'IKEDC')->first();

        if ($ekedcBrand) {
            \App\Models\ElectricityPlan::create([
                'name' => 'EKEDC Prepaid',
                'description' => 'Eko Electricity Distribution Company Prepaid Meter',
                'status' => true,
                'api_code' => 'EKEDC_PREPAID',
                'service_id' => '201',
                'brand_id' => $ekedcBrand->id,
            ]);
        }

        if ($ikedcBrand) {
            \App\Models\ElectricityPlan::create([
                'name' => 'IKEDC Postpaid',
                'description' => 'Ikeja Electricity Distribution Company Postpaid Meter',
                'status' => true,
                'api_code' => 'IKEDC_POSTPAID',
                'service_id' => '202',
                'brand_id' => $ikedcBrand->id,
            ]);
        }
    }
}
