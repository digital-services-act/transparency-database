<?php

namespace Database\Factories;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalAccessTokenFactory extends Factory
{
    protected $model = PersonalAccessToken::class;

    public function definition()
    {
        return [
            'tokenable_id' => User::factory(),
            'tokenable_type' => User::class,
            'name' => $this->faker->word,
            'token' => $this->faker->sha256,
            'abilities' => '["*"]',
            'last_used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
