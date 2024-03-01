<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\ArchivedStatement;

class ArchivedStatementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ArchivedStatement::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $platform_id = Platform::NonDsa()->get()->random()->id;

        return [
            'platform_id' => $platform_id,
            'puid' => $this->faker->regexify('[A-Za-z0-9]{500}'),
            'uuid' => $this->faker->uuid(),
            'date_received' => $this->faker->dateTime(),
        ];
    }
}
