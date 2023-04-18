<?php

namespace App\Console\Commands;

use App\Models\Statement;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;

class ResetApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset-application {--force}';

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
        if (env('APP_ENV') != 'production' || $this->option('force')) {
            UserSeeder::resetUsers();
            PermissionsSeeder::resetRolesAndPermissions();
            Statement::query()->delete();
            Statement::factory()->count(1000)->create();
        } else {
            $this->error('Oh hell no, we do not run this in production. I might do it if you use the force.');
        }
    }
}
