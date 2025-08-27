<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CablePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dstvBrand = \App\Models\Brand::where('name', 'DSTV')->first();
        $gotvBrand = \App\Models\Brand::where('name', 'GOTV')->first();
        $startimesBrand = \App\Models\Brand::where('name', 'Startimes')->first();

        if ($dstvBrand) {
            \App\Models\CablePlan::create([
                'name' => 'DSTV Compact',
                'description' => 'DSTV Compact Bouquet',
                'status' => true,
                'api_code' => 'DSTV_COMPACT',
                'service_id' => '401',
                'reference' => 'DSTV-COMPACT',
                'type' => 'monthly',
                'duration' => '30 days',
                'price' => 9000.00,
                'discounted_price' => 8800.00,
                'brand_id' => $dstvBrand->id,
            ]);
        }

        if ($gotvBrand) {
            \App\Models\CablePlan::create([
                'name' => 'GOTV Max',
                'description' => 'GOTV Max Bouquet',
                'status' => true,
                'api_code' => 'GOTV_MAX',
                'service_id' => '402',
                'reference' => 'GOTV-MAX',
                'type' => 'monthly',
                'duration' => '30 days',
                'price' => 4000.00,
                'discounted_price' => 3900.00,
                'brand_id' => $gotvBrand->id,
            ]);
        }

        if ($startimesBrand) {
            \App\Models\CablePlan::create([
                'name' => 'Startimes Basic',
                'description' => 'Startimes Basic Bouquet',
                'status' => true,
                'api_code' => 'STARTIMES_BASIC',
                'service_id' => '403',
                'reference' => 'STARTIMES-BASIC',
                'type' => 'monthly',
                'duration' => '30 days',
                'price' => 2500.00,
                'discounted_price' => 2400.00,
                'brand_id' => $startimesBrand->id,
            ]);
        }
    }
}
