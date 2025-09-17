<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\StatementAlpha;
use App\Services\StatementSearchService;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscordSeeder extends Seeder
{
    protected $nr = 500000;
    protected $platformName = 'Discord Netherlands B.V.';
    /**
     * Run the database seeds.
     */
    public function run(StatementSearchService $opensearch): void
    {
        $platform = Platform::where('name', $this->platformName)->first();
        if (!$platform) {
            $platform = Platform::factory()->create([
                'name' => $this->platformName,
            ]);
            $this->command->info('Created platform ' . $this->platformName);
        }

        if (!Statement::where('platform_id', $platform->id)->count()) {
            $start = Carbon::now();
            Statement::factory()->count($this->nr / 2)->create(function () use ($platform) {
                $faker = \Faker\Factory::create();

                return [
                    'platform_id' => $platform->id,
                    'puid' => $faker->randomNumber(9)
                              . $faker->randomNumber(9)
                              . '-'
                              . $faker->randomNumber(9)
                              . $faker->randomNumber(9)
                              . '-user',
                    'created_at' => $faker->dateTimeBetween('2025-07-01', 'now'),
                ];
            });
            $this->command->info('Created ' . $this->nr / 2 . ' statements_beta for '. $this->platformName . ' in ' . Carbon::now()->diffForHumans($start));

            $start = Carbon::now();
            StatementAlpha::factory()->count($this->nr / 2)->create(function () use ($platform) {
                $faker = \Faker\Factory::create();

                return [
                    'platform_id' => $platform->id,
                    'puid' => $faker->randomNumber(9)
                              . $faker->randomNumber(9)
                              . '-'
                              . $faker->randomNumber(9)
                              . $faker->randomNumber(9)
                              . '-user',
                    'created_at' => $faker->dateTimeBetween('-1 years', '2025-07-01'),
                ];
            });
            $this->command->info('Created ' . $this->nr / 2 . ' statements for ' . $this->platformName . ' in ' . Carbon::now()->diffForHumans($start));

            $start = Carbon::now();
            Statement::where('platform_id', $platform->id)->chunk(1000, function ($statements) use ($opensearch) {
                $opensearch->bulkIndexStatements($statements);
            });
            StatementAlpha::where('platform_id', $platform->id)->chunk(1000, function ($statements) use ($opensearch) {
                $opensearch->bulkIndexStatements($statements);
            });
            $this->command->info('Indexed statements for ' . $this->platformName . ' in ' . Carbon::now()->diffForHumans($start));
        }
    }
}
