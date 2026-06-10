<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    private const PLATFORM_COUNT = 19;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        self::resetPlatforms();
    }

    public static function resetPlatforms()
    {
        Platform::query()->forceDelete();

        for ($i = 1; $i <= self::PLATFORM_COUNT; $i++) {
            Platform::create([
                'name' => 'Platform '.$i,
                'dsa_common_id' => 'seed-platform-'.$i,
                'vlop' => 1,
            ]);
        }

        // Create the generic DSA platform for the DSA Team
        Platform::create([
            'name' => Platform::LABEL_DSA_TEAM,
            'vlop' => 1,
        ]);
    }
}
