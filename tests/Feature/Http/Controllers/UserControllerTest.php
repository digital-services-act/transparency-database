<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Statement;
use App\Models\User;
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
        $this->setUpFullySeededDatabase();
        /** @var User $user */
        $user = $this->signInAsAdmin();


        $this->assertCount(10, Statement::all());
        $total_users_start = User::count();


        $statement = Statement::all()->random();
        $user = $statement->user;



        $statement_count = $user->statements()->get()->count(); // at least 1


        // delete the user and assert we deleted
        $this->delete(route('user.destroy', [$user]))->assertRedirect(route('user.index'));

        // the statements stay the platform id is in the statements.
        $this->assertCount(10, Statement::all());
        $this->assertCount($total_users_start - 1, User::all());

    }
}
