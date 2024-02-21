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
    public function deleting_user_deletes_the_rest(): void
    {

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

    /**
     * @return void
     * @test
     */
    public function support_should_be_able_to_create_user(): void
    {
        /** @var User $user */
        $user = $this->signInAsSupport();

        $user_count = User::count();


        $response = $this->post(route('user.store'), ['email' => 'foo@bar.com', 'roles' => [1,2], 'platform_id' => 1], [
            'Accept' => 'application/json'
        ]);

        $this->assertCount($user_count + 1, User::all());

        $response->assertRedirect();


    }
}
