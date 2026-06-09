<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DatabaseVelocity>
 */
class DatabaseVelocityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'max_statement_id' => $this->faker->numberBetween(100000, 999999),
            'rows_per_second' => $this->faker->randomFloat(2, 0, 500),
        ];
    }
}
