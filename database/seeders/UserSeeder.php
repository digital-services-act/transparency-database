<?php

namespace Database\Seeders;

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
        User::query()->delete();
        PersonalAccessToken::query()->delete();

        User::factory()->count(20)->create();

        //Create fake admin for masquerade purposes
        User::factory()->create([
            'eu_login_username'=>'dsa-poc-user',
            'email'=>'dsa-poc-user@dsa.eu',
            'name'=>'DSA Administrator'
        ]);
    }
}
