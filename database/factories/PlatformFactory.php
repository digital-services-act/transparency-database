<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PlatformFactory extends Factory
{





    private function generatePlatformNames() {

    }




    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $platform_names = [];
        for ($i = 1; $i <= 50; $i++) {
            $platform_names[] = 'Platform ' . $i;
        }

        return [
            'name' => $this->faker->unique()->randomElement($platform_names),
            'dsa_common_id' => $this->faker->uuid(),
        ];
    }
}
