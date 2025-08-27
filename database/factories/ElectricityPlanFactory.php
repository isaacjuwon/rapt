<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ElectricityPlan>
 */
class ElectricityPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Electricity Plan',
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'meter_type' => $this->faker->randomElement(['prepaid', 'postpaid']),
            'disco_id' => $this->faker->numberBetween(1, 5), // Assuming 5 discos
        ];
    }
}
