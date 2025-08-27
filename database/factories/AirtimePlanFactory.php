<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AirtimePlan>
 */
class AirtimePlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Airtime Plan',
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'network_id' => $this->faker->numberBetween(1, 5), // Assuming 5 networks
        ];
    }
}
