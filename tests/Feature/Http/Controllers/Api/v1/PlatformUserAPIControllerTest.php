<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;


class PlatformUserAPIControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    private array $required_fields;
    private Platform $platform;


    protected function setUp(): void
    {
        parent::setUp();

        $this->required_fields = [
            'name' => 'New Platform',
            'vlop' => 0
        ];
    }

    /**
     * @test
     */
    public function api_platform_user_store_requires_auth()
    {
        $this->setUpFullySeededDatabase();
        // Not signing in.
        $platform = Platform::first();

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), $this->required_fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_platform_user_store_creates_the_invitations()
    {
        $this->setUpFullySeededDatabase();
//        $this->withoutExceptionHandling();

        $this->signInAsAdmin();

        $platform = Platform::first();

        $users = User::factory()
            ->count(2)
            ->sequence(
                ['email' => 'email1@platform.com'],
                ['email' => 'email2@platform.com'],
            )
            ->create();


        $this->required_fields = [
            'emails' => [
                $users[0]->email,
                $users[1]->email,
            ]
        ];

        $response = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), $this->required_fields, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(2, $platform->fresh()->invitations);

        //If we retry with the same users, we get an error as the emails can't be duplicates
        $retry = $this->post(route('api.v1.platform-users.store', ['platform' => $platform->dsa_common_id]), $this->required_fields, [
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


}

