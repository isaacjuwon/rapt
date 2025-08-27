<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(BrandSeeder::class);
        $this->call(AirtimePlanSeeder::class);
        $this->call(DataPlanSeeder::class);
        $this->call(ElectricityPlanSeeder::class);
        $this->call(EducationPlanSeeder::class);
        $this->call(CablePlanSeeder::class);

        $this->call(PlansAndBrandsSeeder::class);
        $this->call(PermissionsAndRolesSeeder::class);
    }
}
