<?php

namespace Database\Factories;

use App\Models\DayArchive;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DayArchive>
 */
class DayArchiveFactory extends Factory
{
    protected $model = DayArchive::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->date();

        return [
            'date' => $date,
            'url' => $this->faker->url(),
            'urllight' => $this->faker->url(),
            'completed_at' => null,
            'total' => $this->faker->numberBetween(100, 10000),
            'size' => $this->faker->numberBetween(1000000, 10000000), // 1MB to 10MB
            'sizelight' => $this->faker->numberBetween(500000, 5000000), // 500KB to 5MB
            'sha1' => $this->faker->sha1(),
            'sha1light' => $this->faker->sha1(),
            'sha1url' => $this->faker->url(),
            'sha1urllight' => $this->faker->url(),
            'platform_id' => null,
            'zipsize' => $this->faker->numberBetween(800000, 8000000), // 800KB to 8MB
            'ziplightsize' => $this->faker->numberBetween(400000, 4000000), // 400KB to 4MB
        ];
    }

    /**
     * Indicate that the archive is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the archive belongs to a specific platform.
     */
    public function forPlatform(?Platform $platform = null): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_id' => $platform?->id ?? Platform::factory(),
        ]);
    }

    /**
     * Indicate that the archive is global (not platform-specific).
     */
    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_id' => null,
        ]);
    }
}
