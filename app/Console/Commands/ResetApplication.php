<?php

namespace App\Console\Commands;

use App\Models\PlatformDayTotal;
use App\Models\Statement;
use App\Services\PlatformDayTotalsService;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\PlatformSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;

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
            $this->info('You should now run: "platform:compile-day-totals all all all 400"');
        } else {
            $this->error('Oh hell no!');
            $this->error('We do not run this in production.');
            $this->error('I might do it if you use the force.');
            $this->error('Even then, you are going to have to really force it.');
        }
    }
}
