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



class PlatformAPIControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    private array $required_fields;
    private Platform $platform;


    protected function setUp(): void
    {
        parent::setUp();

        $this->required_fields = [
            'name' => 'New Platform',
            'url' => 'https://wedontcare.com',
            'vlop' => 0
        ];
    }




    /**
     * @test
     */
    public function api_platform_store_requires_auth()
    {
        $this->setUpFullySeededDatabase();
        // Not signing in.
        $this->assertCount(20, Platform::all());
        $response = $this->post(route('api.v1.platform.store'), $this->required_fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_platform_store_works()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(20, Platform::all());

        $response = $this->post(route('api.v1.platform.store'), $this->required_fields, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(21, Platform::all());

//        $statement = Statement::where('uuid', $response->json('uuid'))->first();
//        $this->assertNotNull($statement);
//        $this->assertEquals('API', $statement->method);
//        $this->assertEquals($user->id, $statement->user->id);
//        $this->assertInstanceOf(Carbon::class, $statement->application_date);
//        $this->assertNull($statement->account_type);
//        $this->assertNull($statement->content_language);
    }





}

