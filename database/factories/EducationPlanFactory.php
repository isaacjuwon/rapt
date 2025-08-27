<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationPlan>
 */
class EducationPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Education Plan',
            'amount' => $this->faker->randomFloat(2, 1000, 20000),
            'provider_id' => $this->faker->numberBetween(1, 10), // Assuming 10 providers
        ];
    }
}
