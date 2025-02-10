<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // The database seeder at this point in development should not be run anymore
        // and potentially affect data already in the DB.
        // Inside the Parent TestCase there is not functionality wrap up and
        // create a test bed scenario.
        $this->call([
            PlatformSeeder::class,
            UserSeeder::class,
            PermissionsSeeder::class,
            StatementSeeder::class,
            OnboardingPermissionsSeeder::class,
            ResearchPermissionsSeeder::class
        ]);
    }
}
