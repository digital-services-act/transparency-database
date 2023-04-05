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
    protected $signature = 'reset-application';

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
        UserSeeder::resetUsers();
        PermissionsSeeder::resetRolesAndPermissions();
        Statement::query()->delete();
        Statement::factory()->count(2000)->create();
    }
}
