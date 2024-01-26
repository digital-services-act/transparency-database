<?php

namespace App\Console\Commands;

use App\Models\DayArchive;
use App\Models\Statement;
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
        if (config('app.env') !== 'production' || ($this->option('force') && $this->option('reallyforce'))) {
            PlatformSeeder::resetPlatforms();
            UserSeeder::resetUsers();
            PermissionsSeeder::resetRolesAndPermissions();
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
                $date->subDays(100);
                while($date < $yesterday)
                {
                    $this->call('statements:day-archive', ['date' => $date->format('Y-m-d')]);
                    $date->addDay();
                }
                $this->info('Day Archives created.');
            }
        } else {
            $this->error('Oh hell no!');
            $this->error('We do not run this in production.');
            $this->error('I might do it if you use the force.');
            $this->error('Even then, you are going to have to really force it.');
        }
    }
}
