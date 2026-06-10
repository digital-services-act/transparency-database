<?php

namespace Tests\Feature\Database;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_with_deterministic_base_records(): void
    {
        config(['dsa.ADMIN_EMAILS' => '']);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(20, Platform::count());
        $this->assertSame(19, Platform::nonDsa()->count());
        $this->assertDatabaseHas('platforms', [
            'name' => Platform::LABEL_DSA_TEAM,
        ]);

        $this->assertSame(20, User::count());
        $this->assertDatabaseHas('users', [
            'email' => 'seed-user-01@example.test',
            'name' => 'Seed User 1',
        ]);

        $this->assertSame(0, Statement::count());
    }
}
