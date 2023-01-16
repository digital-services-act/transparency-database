<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Entity;

class EntityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Entity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'kind' => $this->faker->randomElement(["individual","organization"]),
            'address_line_1' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'address_line_2' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'city' => $this->faker->city,
            'state' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'zip' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'url' => $this->faker->url,
        ];
    }
}
