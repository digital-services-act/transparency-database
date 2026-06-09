<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Sanctum\PersonalAccessToken;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        self::resetUsers();
    }

    public static function resetUsers()
    {
        // Delete all the users and recreate the cas masq user.
        User::query()->forceDelete();
        PersonalAccessToken::query()->delete();

        User::factory()->count(20)->create();

        $dsa_platform = Platform::getDsaPlatform();

        // Create an admin user for each email in the .env ADMIN_EMAILS
        $admin_emails = config('dsa.ADMIN_EMAILS');
        $admin_emails = explode(',', $admin_emails);
        $admin_usernames = config('dsa.ADMIN_USERNAMES');
        $admin_usernames = explode(',', $admin_usernames);
        foreach ($admin_emails as $index => $admin_email) {
            if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                $parts = explode('@', $admin_email);
                User::factory()->create([
                    'email' => $admin_email,
                    'name' => $parts[0],
                    'platform_id' => $dsa_platform->id,
                ]);
            }
        }
    }
}
