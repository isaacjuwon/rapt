<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataPlan>
 */
class DataPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Data Plan',
            'amount' => $this->faker->randomFloat(2, 500, 10000),
            'network_id' => $this->faker->numberBetween(1, 5),
            'data_volume' => $this->faker->numberBetween(100, 10000) . 'MB',
        ];
    }
}
