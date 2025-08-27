<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AirtimePlan;
use App\Models\DataPlan;
use App\Models\EducationPlan;
use App\Models\ElectricityPlan;
use App\Models\CablePlan;
use App\Models\Brand;

class PlansAndBrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AirtimePlan::factory()->count(5)->create();
        DataPlan::factory()->count(5)->create();
        EducationPlan::factory()->count(5)->create();
        ElectricityPlan::factory()->count(5)->create();
        CablePlan::factory()->count(5)->create();
        Brand::factory()->count(5)->create();
    }
}
