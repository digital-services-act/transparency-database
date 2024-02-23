<?php

namespace Tests\Feature\Auth;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportActionsTest extends TestCase
{


    use RefreshDatabase;

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

    /**
     * @return void
     * @test
     */
    public function support_should_be_able_to_create_platform(): void
    {
        /** @var User $user */
        $this->signInAsSupport();

        $platform_count = Platform::count();

        $response = $this->post(route('platform.store'), ['name' => 'ACME Inc.', 'vlop' => 0], [
            'Accept' => 'application/json'
        ]);

        $this->assertCount($platform_count + 1, Platform::all());

        $response->assertRedirect();


    }

    /**
     * @return void
     * @test
     */
    public function support_should_not_be_able_to_delete_a_platform(): void
    {
        /** @var User $user */
        $this->signInAsSupport();

        $response = $this->delete(route('platform.destroy', ['platform' => 1]), [], [
            'Accept' => 'application/json'
        ]);

        $response->assertRedirectToRoute('platform.index');

    }




}
