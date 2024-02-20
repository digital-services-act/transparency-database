<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;


class PlatformUserAPIControllerTest extends TestCase
{
    use AdditionalAssertions;
    use RefreshDatabase;
    use WithFaker;
    private array $emails;

    private Platform $platform;

    /**
     * @test
     */
    public function api_platform_user_store_requires_auth(): void
    {

        // Not signing in.

        $platform = Platform::first();

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), [], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_platform_user_store_creates_the_invitations(): void
    {


        $this->signInAsOnboarding();

        $platform = Platform::first();

        $this->emails = [
            'emails' => [
                'email1@platform.com',
                'email2@platform.com',
            ]
        ];

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), $this->emails, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(2, $platform->fresh()->invitations);

        //If we retry with the same users, we get an error as the emails can't be duplicates
        $retry = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), $this->emails, [
            'Accept' => 'application/json'
        ]);

        $retry->assertStatus(422);

        $retry->assertJson([
            "message" => "The email email1@platform.com is already known in the system. (and 1 more error)",
            "errors" => [
                "emails.0" => [
                    "The email email1@platform.com is already known in the system."
                ],
                "emails.1" => [
                    "The email email2@platform.com is already known in the system."
                ]
            ]]);


    }

    /**
     * @test
     */
    public function it_should_not_create_duplicates(): void
    {
        // If the user already logged in with EU Login and belongs to a platform, we don't need to add the user again.



        $this->signInAsOnboarding();

        $platform = Platform::first();

        $this->emails = [
            'emails' => [
                'email1@platform.com'
            ]
        ];

        //Create a user with the same email
        $platform_user = User::factory()
            ->create(
                ['email' => 'email1@platform.com',
                    'platform_id' => $platform->id]
            );

        $platform_user->assignRole('Contributor');


        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), $this->emails, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertCount(0, $platform->fresh()->invitations);


        $this->signIn($platform_user);

        $this->get(route('statement.create'))->assertOk();


    }

    /**
     * @test
     */
    public function creates_the_invitations_when_user_not_linked_to_the_platform(): void
    {
        // If the user already logged in with EU Login, he will have no rights.
        // Once the invitation has been created, he will get linked to the platform once he visits the website.



        $this->signInAsOnboarding();

        $platform = Platform::factory()->create([
            'dsa_common_id' => "foobarID"
        ]);


        $this->emails = [
            'emails' => [
                'email_new@platform.com'
            ]
        ];

        //Create a user with the same email
        $platform_user = User::factory()
            ->create(
                [
                    'email' => 'email_new@platform.com',
                    'platform_id' => null
                ]
            );


        $response = $this->post(route('api.v1.platform-users.store', ['platform' => 'foobarID']), $this->emails, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(1, $platform->fresh()->invitations);

        $this->checkOnboarding($platform_user, $platform);


    }

    /**
     * @test
     */
    public function it_should_onboard_the_user(): void
    {


        $this->withoutExceptionHandling();

        $this->signInAsOnboarding();

        $platform = Platform::first();

        $this->emails = [
            'emails' => [
                'email1@platform.com'
            ]
        ];

        //Create a user with the same email
        $platform_user = User::factory()
            ->create(
                [
                    'email' => 'email1@platform.com',
                    'platform_id' => null
                ]
            );

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), $this->emails, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(1, $platform->fresh()->invitations);

        $this->checkOnboarding($platform_user, $platform);

    }

    /**
     * @param $platform
     * @return void
     */
    public function checkOnboarding(mixed $user, $platform): void
    {
        $this->signIn($user);
        //The accept of the invitation is done automatically on the CasGuard.php
        $user->acceptInvitation();

        $response = $this->get(route('statement.create'));
        $response->assertOk();
        $this->assertCount(0, $platform->fresh()->invitations);
    }


}

