<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waecBrand = \App\Models\Brand::where('name', 'WAEC')->first();
        $necoBrand = \App\Models\Brand::where('name', 'NECO')->first();
        $jambBrand = \App\Models\Brand::where('name', 'JAMB')->first();

        if ($waecBrand) {
            \App\Models\EducationPlan::create([
                'name' => 'WAEC Scratch Card',
                'description' => 'WAEC Result Checker Scratch Card',
                'status' => true,
                'api_code' => 'WAEC_SCRATCH',
                'service_id' => '301',
                'reference' => 'WAEC-CARD',
                'type' => 'result_checker',
                'duration' => null,
                'price' => 2500.00,
                'discounted_price' => 2450.00,
                'brand_id' => $waecBrand->id,
            ]);
        }

        if ($necoBrand) {
            \App\Models\EducationPlan::create([
                'name' => 'NECO Scratch Card',
                'description' => 'NECO Result Checker Scratch Card',
                'status' => true,
                'api_code' => 'NECO_SCRATCH',
                'service_id' => '302',
                'reference' => 'NECO-CARD',
                'type' => 'result_checker',
                'duration' => null,
                'price' => 2000.00,
                'discounted_price' => 1950.00,
                'brand_id' => $necoBrand->id,
            ]);
        }

        if ($jambBrand) {
            \App\Models\EducationPlan::create([
                'name' => 'JAMB UTME Pin',
                'description' => 'JAMB UTME Registration Pin',
                'status' => true,
                'api_code' => 'JAMB_UTME',
                'service_id' => '303',
                'reference' => 'JAMB-PIN',
                'type' => 'registration',
                'duration' => null,
                'price' => 5000.00,
                'discounted_price' => 4900.00,
                'brand_id' => $jambBrand->id,
            ]);
        }
    }
}
