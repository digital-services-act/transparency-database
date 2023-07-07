<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @return void
     * @test
     */
    public function deleting_platform_deletes_the_rest()
    {
        $this->seed();
        /** @var User $user */
        $user = $this->signIn();
        PermissionsSeeder::resetRolesAndPermissions();
        $user->assignRole('Admin');

        Statement::factory()->count(10)->create();
        $this->assertCount(10, Statement::all());
        $total_users_start = User::count();



        $statement = Statement::all()->random();
        $user = $statement->user;
        $platform = $user->platform;

        $platform_count = Platform::all()->count();
        $statement_count = $platform->statements()->get()->count(); // at least 1
        $user_count = $platform->users()->get()->count(); // at least 1

        // delete the platform and assert we deleted
        $this->delete(route('platform.destroy', [$platform]))->assertRedirect(route('platform.index'));

        $this->assertCount(10 - $statement_count, Statement::all());
        $this->assertCount($total_users_start - $user_count, User::all());
        $this->assertCount($platform_count - 1, Platform::all());
    }
}
