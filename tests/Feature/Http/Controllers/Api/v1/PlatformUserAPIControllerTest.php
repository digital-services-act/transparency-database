<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
#use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;


class PlatformUserAPIControllerTest extends TestCase
{
    #use AdditionalAssertions;
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
    public function api_platform_user_store_creates_the_user(): void
    {
        $this->signInAsOnboarding();

        $platform = Platform::first();

        $this->emails = [
            'emails' => [
                'email1@platform.com',
                'email2@platform.com',
            ]
        ];

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]),
            $this->emails, [
                'Accept' => 'application/json'
            ]);

        $response->assertStatus(Response::HTTP_CREATED);

        //If we retry with the same users, we get an error as the emails can't be duplicates
        $retry = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]),
            $this->emails, [
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
            ]
        ]);
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
                [
                    'email' => 'email1@platform.com',
                    'platform_id' => $platform->id
                ]
            );

        $platform_user->assignRole('Contributor');


        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]),
            $this->emails, [
                'Accept' => 'application/json'
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);


        $this->signIn($platform_user);

        $this->get(route('statement.create'))->assertOk();
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

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]),
            $this->emails, [
                'Accept' => 'application/json'
            ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->checkOnboarding($platform_user->fresh(), $platform);
    }

    /**
     * @test
     */
    public function it_should_onboard_two_users_with_plus_sign_in_email(): void
    {
        $this->withoutExceptionHandling();

        $this->signInAsOnboarding();

        $platform = Platform::create([
            'name' => 'test onboarding',
            'dsa_common_id' => 'test-common-id'
        ]);

        $platform_user = User::factory()
            ->create(
                [
                    'email' => 'email@platform.com',
                    'platform_id' => $platform->id
                ]
            );

        $this->emails = [
            'emails' => [
                'email+dsa1@platform.com',
                'email+dsa2@platform.com'
            ]
        ];

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]),
            $this->emails, [
                'Accept' => 'application/json'
            ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertCount(3, $platform->fresh()->users);
    }

    /**
     * @test
     */
    public function it_should_not_allow_same_user_on_different_platforms(): void
    {
        $this->signInAsOnboarding();

        // Create two different platforms
        $platform1 = Platform::factory()->create(['name' => 'Platform 1']);
        $platform2 = Platform::factory()->create(['name' => 'Platform 2']);

        $email = 'test.user@example.com';
        $emails = [
            'emails' => [$email]
        ];

        // Create user for platform 1
        $response1 = $this->post(
            route('api.v1.platform-users.store', ['platform' => $platform1->dsa_common_id]),
            $emails,
            ['Accept' => 'application/json']
        );

        $response1->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'platform_id' => $platform1->id
        ]);

        // Try to create the same user for platform 2
        $response2 = $this->post(
            route('api.v1.platform-users.store', ['platform' => $platform2->dsa_common_id]),
            $emails,
            ['Accept' => 'application/json']
        );

        $response2->assertStatus(422);
        $response2->assertJson([
            "message" => "The email test.user@example.com is already known in the system.",
            "errors" => [
                "emails.0" => [
                    "The email test.user@example.com is already known in the system."
                ]
            ]
        ]);

        // Verify user still belongs to platform 1
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'platform_id' => $platform1->id
        ]);
    }

    /**
     * @param $platform
     * @return void
     */
    public function checkOnboarding(mixed $user, $platform): void
    {
        $this->signIn($user);

        $response = $this->get(route('statement.create'));
        $response->assertOk();
    }


}
