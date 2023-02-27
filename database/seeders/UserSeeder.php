<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(20)->create();

        //Create fake admin for masquerade purposes
        User::factory()->create([
            'eu_login_username'=>'dsa-poc-user',
            'email'=>'dsa-poc-user@dsa.eu',
            'name'=>'DSA Administrator'
        ]);

    }
}
