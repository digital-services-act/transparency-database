<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class PlatformAPIControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private array $requiredFields;
    private Platform $platform;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requiredFields = [
            'name' => 'New Platform',
            'vlop' => 0,
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

        $response = $this->post(route('api.v1.platform.store'), $this->requiredFields, [
            'Accept' => 'application/json',
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

        $this->requiredFields['dsa_common_id'] = '123-ABC-456';

        $response = $this->post(route('api.v1.platform.store'), $this->requiredFields, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $createdPlatform = Platform::firstWhere('id', $response->json('id'));

        $this->assertEquals('123-ABC-456', $createdPlatform->dsa_common_id);
        $this->assertCount(21, Platform::all());
    }

    /**
     * @test
     */
    public function it_should_give_platform_data()
    {
        $this->withoutExceptionHandling();
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $platform = Platform::factory()->create([
            'name' => 'test platform',
            'dsa_common_id' => 'foobar',
        ]);

        $response = $this->get(route('api.v1.platform.get', ['platform' => 'foobar']), $this->requiredFields, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }
}
