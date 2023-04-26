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

    private $platform_names = [
        'Alibaba AliExpress',
        'Amazon Store',
        'Apple AppStore',
        'Booking.com',
        'Facebook',
        'Google Play',
        'Google Maps',
        'Google Shopping',
        'Instagram',
        'LinkedIn',
        'Pinterest',
        'Snapchat',
        'TikTok',
        'Twitter',
        'Wikipedia',
        'YouTube',
        'Zalando',
        'Bing',
        'Google Search',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->randomElement($this->platform_names),
            'url' => $this->faker->url(),
            'type' => $this->faker->randomElement(array_keys(Platform::PLATFORM_TYPES)),
        ];
    }
}
