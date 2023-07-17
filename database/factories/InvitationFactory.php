<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $platform_id = Platform::whereNot('name', Platform::LABEL_DSA_TEAM)->get()->random()->id;

        return [
            'email' => $this->faker->email,
            'platform_id' => $platform_id
        ];
    }
}
