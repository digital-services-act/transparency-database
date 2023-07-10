<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        self::resetPlatforms();
    }

    public static function resetPlatforms()
    {
        Platform::query()->forceDelete();
        Platform::factory()->count(19)->create();

        // Create the generic DSA platform for the DSA Team
        Platform::create([
            'name' => Platform::LABEL_DSA_TEAM,
            'url' => 'https://transparency.dsa.ec.europa.eu',
            'type' => 'PLATFORM_TYPE_OTHER'
        ]);
    }

}
