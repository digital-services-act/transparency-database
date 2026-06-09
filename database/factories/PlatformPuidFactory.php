<?php

namespace Database\Factories;

use App\Models\Platform;
use App\Models\PlatformPuid;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlatformPuidFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlatformPuid::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $platform_id = Platform::NonDsa()->get()->random()->id;

        return [
            'platform_id' => $platform_id,
            'puid' => $this->faker->regexify('[A-Za-z0-9]{500}'),
        ];
    }
}
