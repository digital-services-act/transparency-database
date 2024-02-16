<?php

namespace App\Console\Commands\Setup;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Admins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dsa:setup-admins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the administrators of the website';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        self::generatePrivilegedUsers();
        Artisan::call('db:seed', [
            'class' => 'PermissionsSeeder',
            '--force' => true,
        ]);

        $this->info('The command was successful!');
    }

    /**
     * @return void
     */
    public static function generatePrivilegedUsers(): void
    {

        $dsa_platform = Platform::firstOrCreate([
            'name' => Platform::LABEL_DSA_TEAM,
            'url' => 'https://transparency.dsa.ec.europa.eu',
        ]);

        // Create an admin user for each email in the .env ADMIN_EMAILS
        $admin_emails = config('dsa.ADMIN_EMAILS');
        $admin_emails = explode(",", (string) $admin_emails);

        $admin_usernames = config('dsa.ADMIN_USERNAMES');
        $admin_usernames = explode(",", (string) $admin_usernames);
        foreach ($admin_emails as $index => $admin_email) {
            if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                $parts = explode("@", $admin_email);
                User::firstOrCreate(
                    ['email' => $admin_email],
                    [
                        'name' => $parts[0],
                        'password' => bcrypt(random_int(0, mt_getrandmax())),
                        'platform_id' => $dsa_platform->id
                    ]);
            }
        }
    }
}
