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
    protected $platformName = 'Discord Netherlands B.V.';
    /**
     * Run the database seeds.
     */
    public function run(StatementSearchService $opensearch): void
    {
        $start = Carbon::now();

        $count = $this->command->ask('How many statements do you want to create? (Default: 5000)', 5000);

        $platform = Platform::where('name', $this->platformName)->first();
        if (!$platform) {
            $platform = Platform::factory()->create([
                'name' => $this->platformName,
            ]);
            $this->command->info('Created platform ' . $this->platformName);
        }

        $secondsPerDay = 86400;

        $statementsBetaStartId = (Statement::max('id') ?? 1000000000) + 1;
        $statementsBetaCreatedAt = Carbon::createFromIsoFormat('YYYY-MM-DD', '2025-07-01')->startOfDay();
        $recordsPerDay = ceil(($count / 2) / Carbon::now()->diffInDays($statementsBetaCreatedAt));
        $i = 0;

        // statements_beta records: both faulty and correct PUIDs
        Statement::factory()->count($count / 2)->create(function () use ($platform, &$statementsBetaStartId, $statementsBetaCreatedAt, &$i, $secondsPerDay, $recordsPerDay) {
            $faker = \Faker\Factory::create();

            $dayOffset = intdiv($i, $recordsPerDay);
            $indexOfDay = $i % $recordsPerDay;
            $secondOffset = intdiv($secondsPerDay, $recordsPerDay) * $indexOfDay;

            $createdAt = $statementsBetaCreatedAt->copy()->addDays($dayOffset)->addSeconds($secondOffset);
            $endOfDay = $createdAt->copy()->startOfDay()->setTime(23, 0, 0);
            $i++;

            if ($createdAt->gte('2025-08-15 00:00:00')) {
                $puid = $faker->uuid();
            } else {
                $puid = $faker->randomNumber(9)
                    . $faker->randomNumber(9)
                    . '-'
                    . $faker->randomNumber(9)
                    . $faker->randomNumber(9)
                    . '-user';
            }

            if ($createdAt->gte($endOfDay)) {
                $createdAt->setTime(23, 59, $i%59 === 0 ? 59 : $i%59);
            }

            return [
                'id' => $statementsBetaStartId++,
                'created_at' => $createdAt,
                'platform_id' => $platform->id,
                'puid' => $puid
            ];
        });

        $this->command->info('Created ' . $count / 2 . ' statements_beta for '. $this->platformName . ' in ' . Carbon::now()->diffForHumans($start));

        $start = Carbon::now();

        $statementsStartId = (StatementAlpha::max('id') ?? 1000000) + 1;
        $statementsCreatedAt = Carbon::createFromIsoFormat('YYYY-MM-DD', '2024-12-10')->startOfDay();
        $recordsPerDay = ceil(($count / 2) / $statementsBetaCreatedAt->diffInDays($statementsCreatedAt));
        $j = 0;

        // statements records: only faulty PUIDs
        StatementAlpha::factory()->count($count / 2)->create(function () use ($platform, &$statementsStartId, &$statementsCreatedAt, &$j, $secondsPerDay, $recordsPerDay) {
            $faker = \Faker\Factory::create();

            $dayOffset = intdiv($j, $recordsPerDay);
            $indexOfDay = $j % $recordsPerDay;
            $secondOffset = intdiv($secondsPerDay, $recordsPerDay) * $indexOfDay;

            $createdAt = $statementsCreatedAt->copy()->addDays($dayOffset)->addSeconds($secondOffset);
            $endOfDay = $createdAt->copy()->startOfDay()->setTime(23, 0, 0);
            $j++;

            if ($createdAt->gte($endOfDay)) {
                $createdAt->setTime(23, 59, $j%59 === 0 ? 59 : $j%59);
            }

            return [
                'id' => $statementsStartId++,
                'platform_id' => $platform->id,
                'puid' => $faker->randomNumber(9)
                            . $faker->randomNumber(9)
                            . '-'
                            . $faker->randomNumber(9)
                            . $faker->randomNumber(9)
                            . '-user',
                'created_at' => $createdAt,
            ];
        });
        $this->command->info('Created ' . $count / 2 . ' statements for ' . $this->platformName . ' in ' . Carbon::now()->diffForHumans($start));

        $start = Carbon::now();
        Statement::where('platform_id', $platform->id)->chunk(1000, function ($statements) use ($opensearch) {
            $opensearch->bulkIndexStatements($statements);
        });
        StatementAlpha::where('platform_id', $platform->id)->where('created_at', '>', '2025-02-01 00:00:00')->chunk(1000, function ($statements) use ($opensearch) {
            $opensearch->bulkIndexStatements($statements);
        });
        $this->command->info('Indexed statements for ' . $this->platformName . ' in ' . Carbon::now()->diffForHumans($start));
    }
}
