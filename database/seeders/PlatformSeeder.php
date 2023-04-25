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
        Platform::query()->delete();
        Platform::factory()->count(20)->create();
    }

}
