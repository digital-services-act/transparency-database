<?php

namespace App\Console\Commands;

use App\Models\DayArchive;
use App\Models\PlatformDayTotal;
use App\Models\Statement;
use App\Services\PlatformDayTotalsService;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\PlatformSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ResetApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset-application {--force} {--reallyforce}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the entire application';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (env('APP_ENV') != 'production' || ($this->option('force') && $this->option('reallyforce'))) {
            PlatformSeeder::resetPlatforms();
            UserSeeder::resetUsers();
            PermissionsSeeder::resetRolesAndPermissions();
            PlatformDayTotal::query()->forceDelete();
            Statement::query()->forceDelete();
            Statement::factory()->count(1000)->create();
            $this->info('Reset has completed.');

            if ($this->confirm('Optimize the opensearch index?', true)) {
                $this->call('statements:optimize-index');
                $this->info('Optimize has completed.');
            }

            DayArchive::query()->forceDelete();
            if ($this->confirm('Create Day Archives?', true)) {
                $yesterday = Carbon::yesterday();
                $date = $yesterday->clone();
                $date->subDays(10);
                while($date < $yesterday)
                {
                    $this->call('statements:day-archive', ['date' => $date->format('Y-m-d')]);
                    $date->addDay();
                }
                $this->info('Day Archives created.');
            }

            if ($this->confirm('Compile the day totals?', true)) {
                $this->call('platform:compile-day-totals');

                $this->call('platform:compile-day-totals', ['platform_id' => 'all', 'attribute' => 'decision_visibility', 'value' => 'all']);
                $this->call('platform:compile-day-totals', ['platform_id' => 'all', 'attribute' => 'decision_monetary', 'value' => 'all']);
                $this->call('platform:compile-day-totals', ['platform_id' => 'all', 'attribute' => 'decision_provision', 'value' => 'all']);
                $this->call('platform:compile-day-totals', ['platform_id' => 'all', 'attribute' => 'decision_account', 'value' => 'all']);

                $this->call('platform:compile-day-totals', ['platform_id' => 'all', 'attribute' => 'decision_ground', 'value' => 'DECISION_GROUND_ILLEGAL_CONTENT']);
                $this->call('platform:compile-day-totals', ['platform_id' => 'all', 'attribute' => 'decision_ground', 'value' => 'DECISION_GROUND_INCOMPATIBLE_CONTENT']);

                $this->call('platform:compile-day-totals-categories');
                $this->info('Day totals has completed.');
            }
        } else {
            $this->error('Oh hell no!');
            $this->error('We do not run this in production.');
            $this->error('I might do it if you use the force.');
            $this->error('Even then, you are going to have to really force it.');
        }
    }
}
