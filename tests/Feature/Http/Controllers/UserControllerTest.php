<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Statement;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @return void
     * @test
     */
    public function deleting_user_deletes_the_rest()
    {
        $this->seed();
        PermissionsSeeder::resetRolesAndPermissions();
        /** @var User $user */
        $user = $this->signInAsAdmin();


        $this->assertCount(10, Statement::all());
        $total_users_start = User::count();


        $statement = Statement::all()->random();
        $user = $statement->user;



        $statement_count = $user->statements()->get()->count(); // at least 1


        // delete the platform and assert we deleted
        $this->delete(route('user.destroy', [$user]))->assertRedirect(route('user.index'));

        $this->assertCount(10 - $statement_count, Statement::all());
        $this->assertCount($total_users_start - 1, User::all());

    }
}
