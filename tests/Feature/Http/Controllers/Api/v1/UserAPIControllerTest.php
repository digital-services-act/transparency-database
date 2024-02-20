<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserAPIControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    /**
     * @test
     */
    public function it_should_not_find_the_route_for_unknown_user(): void
    {

        $this->signInAsOnboarding();


        $response = $this->get(route('api.v1.user.get', ['email' => 'foo@bar.com']), [
            'Accept' => 'application/json',
        ]);


        $response->assertStatus(Response::HTTP_NOT_FOUND);

    }

    /**
     * @test
     */
    public function it_should_return_invited_users_as_inactive(): void
    {

        $this->signInAsOnboarding();


        Invitation::factory()
            ->create(
                ['email' => 'foo@bar.com']
            );

        $response = $this->get(route('api.v1.user.get', ['email' => 'foo@bar.com']), [
            'Accept' => 'application/json',
        ]);


        $response->assertStatus(Response::HTTP_OK);

        $this->assertFalse($response->json('active'));

    }

    /**
     * @test
     */
    public function it_should_say_email_is_not_active(): void
    {

        $this->signInAsOnboarding();

        User::factory()
            ->create(
                ['email' => 'foo@bar.com']
            );

        $response = $this->get(route('api.v1.user.get', ['email' => 'foo@bar.com']), [
            'Accept' => 'application/json',
        ]);


        $response->assertStatus(Response::HTTP_OK);

        $this->assertFalse($response->json('active'));

    }

    /**
     * @test
     */
    public function it_should_say_user_is_active_when_he_created_a_token(): void
    {

        $this->signInAsOnboarding();

        $user = User::factory()
            ->create(
                ['email' => 'foo@bar.com']
            );

        $user->createToken(User::API_TOKEN_KEY)->plainTextToken;

        $response = $this->get(route('api.v1.user.get', ['email' => 'foo@bar.com']), [
            'Accept' => 'application/json',
        ]);


        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue($response->json('active'));

    }


}
