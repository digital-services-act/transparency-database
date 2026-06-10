<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class UserSeeder extends Seeder
{
    private const USER_COUNT = 20;

    private const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uOXkd59uO2tfgauyWQgFgzG';

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

        $platform_ids = Platform::nonDsa()->pluck('id')->values();

        for ($i = 1; $i <= self::USER_COUNT; $i++) {
            User::unguarded(static function () use ($i, $platform_ids): void {
                User::create([
                    'name' => 'Seed User '.$i,
                    'email' => sprintf('seed-user-%02d@example.test', $i),
                    'platform_id' => $platform_ids[($i - 1) % $platform_ids->count()],
                    'email_verified_at' => now(),
                    'password' => self::PASSWORD_HASH, // password
                    'remember_token' => Str::random(10),
                ]);
            });
        }

        $dsa_platform = Platform::getDsaPlatform();

        // Create an admin user for each email in the .env ADMIN_EMAILS
        $admin_emails = config('dsa.ADMIN_EMAILS');
        $admin_emails = explode(',', $admin_emails);
        foreach ($admin_emails as $admin_email) {
            if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                $parts = explode('@', $admin_email);
                User::unguarded(static function () use ($admin_email, $dsa_platform, $parts): void {
                    User::create([
                        'email' => $admin_email,
                        'name' => $parts[0],
                        'platform_id' => $dsa_platform->id,
                        'email_verified_at' => now(),
                        'password' => self::PASSWORD_HASH, // password
                        'remember_token' => Str::random(10),
                    ]);
                });
            }
        }
    }
}
